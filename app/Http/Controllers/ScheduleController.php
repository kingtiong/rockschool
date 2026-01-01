<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Models\TeacherEarning;
use App\Models\TeacherShare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Lesson::query()->with(['student', 'teacher'])->orderBy('scheduled_start_at');

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        }

        return view('schedule.index', [
            'lessons' => $query->get(),
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'teacher' && $lesson->teacher_id === $user->id, 403);

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($lesson, $validated): void {
            $lesson->update([
                'status' => Lesson::STATUS_COMPLETED,
                'completed_at' => now(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Create teacher earning when possible (requires a cycle + share %).
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

            // If this completes the cycle (e.g. 4/4), close it and create the next cycle.
            $cycleLessonsDone = Lesson::query()
                ->where('cycle_id', $cycle->id)
                ->whereIn('status', [Lesson::STATUS_COMPLETED, Lesson::STATUS_MISSED])
                ->count();

            if ($cycleLessonsDone >= $cycle->lessons_per_cycle) {
                $cycle->update(['status' => Cycle::STATUS_COMPLETED]);

                $enrollment = $cycle->enrollment;
                $plan = $enrollment?->feePlan;
                if (! $enrollment || ! $plan) {
                    return;
                }

                $nextCycleNumber = $cycle->cycle_number + 1;
                $nextStartAt = $lesson->scheduled_start_at->copy()->addWeek();

                $cycleFeeCents = (int) $plan->cycle_fee_cents;
                if ($enrollment->minutes_per_lesson === 30 && $plan->minutes_per_lesson_default === 60 && $plan->allow_half_hour) {
                    $cycleFeeCents = (int) round($cycleFeeCents / 2);
                }

                $next = \App\Models\Cycle::create([
                    'enrollment_id' => $enrollment->id,
                    'cycle_fee_cents' => $cycleFeeCents,
                    'lessons_per_cycle' => $plan->lessons_per_cycle,
                    'minutes_per_lesson' => $enrollment->minutes_per_lesson,
                    'cycle_minutes_total' => $plan->lessons_per_cycle * $enrollment->minutes_per_lesson,
                    'cycle_number' => $nextCycleNumber,
                    'status' => \App\Models\Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
                    'starts_on' => $nextStartAt->toDateString(),
                ]);

                for ($i = 1; $i <= $next->lessons_per_cycle; $i++) {
                    $s = $nextStartAt->copy()->addWeeks($i - 1);
                    Lesson::create([
                        'cycle_id' => $next->id,
                        'student_id' => $enrollment->student_id,
                        'teacher_id' => $enrollment->teacher_id,
                        'scheduled_start_at' => $s,
                        'scheduled_end_at' => $s->copy()->addMinutes($next->minutes_per_lesson),
                        'minutes' => $next->minutes_per_lesson,
                        'status' => Lesson::STATUS_SCHEDULED,
                        'sequence_in_cycle' => $i,
                        'cycle_size' => $next->lessons_per_cycle,
                    ]);
                }
            }
        });

        return redirect()->route('schedule.index');
    }

    public function absence(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $lesson->student_id === $user->id, 403);

        $threshold = $lesson->scheduled_start_at->copy()->subHours(2);
        $isOnTime = now()->lte($threshold);

        DB::transaction(function () use ($isOnTime, $lesson, $user): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_ABSENCE,
                'requested_start_at' => null,
                'reason' => 'Student absence notification',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => null,
                'decided_at' => now(),
            ]);

            if (! $isOnTime) {
                // Late notice: counts as missed (no shifting).
                $lesson->update([
                    'status' => Lesson::STATUS_MISSED,
                    'absence_notified_at' => now(),
                ]);
                return;
            }

            // On-time notice: push this lesson AND later scheduled lessons in the same cycle forward 1 week.
            $lesson->update([
                'status' => Lesson::STATUS_POSTPONED,
                'absence_notified_at' => now(),
            ]);

            if (! $lesson->cycle_id) {
                // Best-effort fallback: just move this single lesson.
                $lesson->update([
                    'scheduled_start_at' => $lesson->scheduled_start_at->copy()->addWeek(),
                    'scheduled_end_at' => $lesson->scheduled_end_at->copy()->addWeek(),
                ]);
                return;
            }

            $lessonsToShift = Lesson::query()
                ->where('cycle_id', $lesson->cycle_id)
                ->where('sequence_in_cycle', '>=', $lesson->sequence_in_cycle ?? 1)
                ->whereIn('status', [Lesson::STATUS_SCHEDULED, Lesson::STATUS_POSTPONED])
                ->orderBy('sequence_in_cycle')
                ->lockForUpdate()
                ->get();

            foreach ($lessonsToShift as $l) {
                $l->update([
                    'scheduled_start_at' => $l->scheduled_start_at->copy()->addWeek(),
                    'scheduled_end_at' => $l->scheduled_end_at->copy()->addWeek(),
                ]);
            }
        });

        return redirect()->route('schedule.index');
    }

    public function requestChange(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $lesson->student_id === $user->id, 403);

        $validated = $request->validate([
            'requested_start_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        RescheduleRequest::create([
            'lesson_id' => $lesson->id,
            'requested_by_user_id' => $user->id,
            'type' => RescheduleRequest::TYPE_CHANGE,
            'requested_start_at' => \Illuminate\Support\Carbon::parse($validated['requested_start_at']),
            'reason' => $validated['reason'] ?? null,
            'status' => RescheduleRequest::STATUS_PENDING,
        ]);

        return redirect()->route('schedule.index');
    }
}

