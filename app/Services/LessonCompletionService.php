<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\TeacherEarning;
use App\Models\TeacherShare;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LessonCompletionService
{
    /**
     * Mark a lesson as completed (if scheduled) and handle:
     * - teacher earning creation
     * - cycle completion + next cycle creation
     */
    public function completeLesson(Lesson $lesson, ?string $remarks = null, ?Carbon $completedAt = null): void
    {
        DB::transaction(function () use ($lesson, $remarks, $completedAt): void {
            $lesson->refresh();

            if ($lesson->status === Lesson::STATUS_SCHEDULED) {
                $lesson->update([
                    'status' => Lesson::STATUS_COMPLETED,
                    'completed_at' => $completedAt ?? now(),
                    'remarks' => $remarks,
                ]);
            }

            $this->ensureTeacherEarning($lesson);
            $this->maybeCloseCycleAndCreateNext($lesson);
        });
    }

    /**
     * Auto-complete lessons that ended and create earnings.
     * Skips postponed lessons.
     *
     * @return array{processed:int, completed:int, earnings_created:int, cycles_completed:int, next_cycles_created:int}
     */
    public function releaseDueEarnings(int $limit = 200, bool $dryRun = false): array
    {
        $processed = 0;
        $completed = 0;
        $earningsCreated = 0;
        $cyclesCompleted = 0;
        $nextCyclesCreated = 0;

        $dueLessons = Lesson::query()
            ->whereNotNull('scheduled_end_at')
            ->where('scheduled_end_at', '<=', now())
            ->whereIn('status', [Lesson::STATUS_SCHEDULED, Lesson::STATUS_COMPLETED, Lesson::STATUS_MISSED])
            ->whereNotNull('cycle_id')
            ->whereNotNull('teacher_id')
            ->orderBy('scheduled_end_at')
            ->limit($limit)
            ->get();

        foreach ($dueLessons as $lesson) {
            DB::transaction(function () use ($lesson, $dryRun, &$processed, &$completed, &$earningsCreated, &$cyclesCompleted, &$nextCyclesCreated): void {
                /** @var Lesson $l */
                $l = Lesson::query()->lockForUpdate()->findOrFail($lesson->id);
                $processed++;

                if ($l->status === Lesson::STATUS_POSTPONED) {
                    return;
                }

                if ($l->status === Lesson::STATUS_SCHEDULED) {
                    $completed++;
                    if (! $dryRun) {
                        $l->update([
                            'status' => Lesson::STATUS_COMPLETED,
                            'completed_at' => $l->completed_at ?? now(),
                        ]);
                    }
                }

                $beforeEarning = TeacherEarning::query()->where('lesson_id', $l->id)->exists();
                if (! $dryRun) {
                    $this->ensureTeacherEarning($l);
                }
                $afterEarning = TeacherEarning::query()->where('lesson_id', $l->id)->exists();
                if (! $beforeEarning && $afterEarning) {
                    $earningsCreated++;
                }

                if (! $dryRun) {
                    $result = $this->maybeCloseCycleAndCreateNext($l);
                    $cyclesCompleted += $result['cycles_completed'];
                    $nextCyclesCreated += $result['next_cycles_created'];
                }
            });
        }

        return [
            'processed' => $processed,
            'completed' => $completed,
            'earnings_created' => $earningsCreated,
            'cycles_completed' => $cyclesCompleted,
            'next_cycles_created' => $nextCyclesCreated,
        ];
    }

    private function ensureTeacherEarning(Lesson $lesson): void
    {
        if (! $lesson->cycle_id || ! $lesson->teacher_id) {
            return;
        }

        $cycle = $lesson->cycle()->with('enrollment.feePlan')->first();
        if (! $cycle || $cycle->cycle_minutes_total <= 0) {
            return;
        }

        $share = TeacherShare::query()
            ->where('teacher_id', $lesson->teacher_id)
            ->whereNull('effective_to')
            ->latest('id')
            ->first();

        $percent = (int) ($share?->percent ?? 0);
        if ($percent <= 0) {
            return;
        }

        $lessonFeeCents = (int) round(($cycle->cycle_fee_cents * $lesson->minutes) / $cycle->cycle_minutes_total);
        $earningCents = (int) round($lessonFeeCents * ($percent / 100));

        TeacherEarning::query()->firstOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'teacher_id' => $lesson->teacher_id,
                'amount_cents' => max(0, $earningCents),
                'status' => TeacherEarning::STATUS_UNPAID,
                'calculated_at' => now(),
            ]
        );
    }

    /**
     * @return array{cycles_completed:int, next_cycles_created:int}
     */
    private function maybeCloseCycleAndCreateNext(Lesson $lesson): array
    {
        $cyclesCompleted = 0;
        $nextCyclesCreated = 0;

        if (! $lesson->cycle_id) {
            return ['cycles_completed' => 0, 'next_cycles_created' => 0];
        }

        /** @var Cycle|null $cycle */
        $cycle = Cycle::query()->with('enrollment.feePlan')->lockForUpdate()->find($lesson->cycle_id);
        if (! $cycle) {
            return ['cycles_completed' => 0, 'next_cycles_created' => 0];
        }

        $done = Lesson::query()
            ->where('cycle_id', $cycle->id)
            ->whereIn('status', [Lesson::STATUS_COMPLETED, Lesson::STATUS_MISSED])
            ->count();

        if ($done < (int) $cycle->lessons_per_cycle) {
            return ['cycles_completed' => 0, 'next_cycles_created' => 0];
        }

        $cycle->update(['status' => Cycle::STATUS_COMPLETED]);
        $cyclesCompleted++;

        $enrollment = $cycle->enrollment;
        $plan = $enrollment?->feePlan;
        if (! $enrollment || ! $plan) {
            return ['cycles_completed' => $cyclesCompleted, 'next_cycles_created' => 0];
        }

        $intervalWeeks = (int) ($cycle->interval_weeks ?? $enrollment->interval_weeks ?? 1);
        $intervalWeeks = max(1, $intervalWeeks);
        $lessonsPerCycle = (int) $cycle->lessons_per_cycle;
        $minutes = (int) $cycle->minutes_per_lesson;

        // Recompute cycle fee consistently with enrollment creation:
        // base fee (incl half-hour) then pro-rate by lesson count when interval is 2 weeks.
        $baseCycleFeeCents = (int) $plan->cycle_fee_cents;
        if ($minutes === 30 && (int) $plan->minutes_per_lesson_default === 60 && $plan->allow_half_hour) {
            $baseCycleFeeCents = (int) round($baseCycleFeeCents / 2);
        }
        $defaultLessonsPerCycle = max(1, (int) $plan->lessons_per_cycle);
        $cycleFeeCents = (int) round($baseCycleFeeCents * ($lessonsPerCycle / $defaultLessonsPerCycle));

        $nextCycleNumber = ((int) $cycle->cycle_number) + 1;
        $nextStartAt = ($lesson->scheduled_start_at ?? now())->copy()->addWeeks($intervalWeeks);

        $next = Cycle::create([
            'enrollment_id' => $enrollment->id,
            'cycle_fee_cents' => max(0, $cycleFeeCents),
            'lessons_per_cycle' => $lessonsPerCycle,
            'minutes_per_lesson' => $minutes,
            'interval_weeks' => $intervalWeeks,
            'cycle_minutes_total' => $lessonsPerCycle * $minutes,
            'cycle_number' => $nextCycleNumber,
            'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
            'starts_on' => $nextStartAt->toDateString(),
        ]);

        $branchId = $enrollment->branch_id ? (int) $enrollment->branch_id : null;
        $roomNumbers = [];
        if ($branchId) {
            $roomNumbers = Room::query()
                ->where('branch_id', $branchId)
                ->where('active', true)
                ->orderBy('number')
                ->pluck('number')
                ->map(fn ($n) => (int) $n)
                ->values()
                ->all();

            if (count($roomNumbers) === 0) {
                $roomNumbers = range(1, max(1, (int) (Branch::find($branchId)?->classrooms_count ?? 1)));
            }
        }

        for ($i = 1; $i <= $lessonsPerCycle; $i++) {
            $s = $nextStartAt->copy()->addWeeks(($i - 1) * $intervalWeeks);
            $e = $s->copy()->addMinutes($minutes);

            $roomNumber = 1;
            if ($branchId) {
                $roomNumber = 0;
                foreach ($roomNumbers as $r) {
                    $occupied = Lesson::query()
                        ->where('classroom_number', $r)
                        ->where('scheduled_start_at', '<', $e)
                        ->where('scheduled_end_at', '>', $s)
                        ->whereHas('cycle.enrollment', fn ($q) => $q->where('branch_id', $branchId))
                        ->exists();

                    if (! $occupied) {
                        $roomNumber = $r;
                        break;
                    }
                }

                // If no room found, fallback to first room (and allow later backfill/adjust).
                if ($roomNumber <= 0) {
                    $roomNumber = $roomNumbers[0] ?? 1;
                }
            }

            Lesson::create([
                'cycle_id' => $next->id,
                'student_id' => $enrollment->student_id,
                'teacher_id' => $enrollment->teacher_id,
                'classroom_number' => $roomNumber,
                'scheduled_start_at' => $s,
                'scheduled_end_at' => $e,
                'minutes' => $minutes,
                'status' => Lesson::STATUS_SCHEDULED,
                'sequence_in_cycle' => $i,
                'cycle_size' => $lessonsPerCycle,
            ]);
        }

        $nextCyclesCreated++;

        return ['cycles_completed' => $cyclesCompleted, 'next_cycles_created' => $nextCyclesCreated];
    }
}

