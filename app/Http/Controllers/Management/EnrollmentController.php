<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeePlan;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        return view('management.enrollments.index', [
            'enrollments' => Enrollment::query()
                ->with(['branch', 'student', 'teacher', 'feePlan'])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('management.enrollments.create', [
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
            'students' => User::query()->where('role', 'student')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
            'feePlans' => FeePlan::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'fee_plan_id' => ['required', 'integer', 'exists:fee_plans,id'],
            'minutes_per_lesson' => ['required', 'integer', 'in:30,45,60'],
            'interval_weeks' => ['required', 'integer', 'in:1,2'],
            'started_on' => ['nullable', 'date'],
            'preferred_start_time' => ['nullable', 'date_format:H:i'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        abort_unless($student->role === 'student', 422);

        if (! empty($validated['teacher_id'])) {
            $teacher = User::findOrFail($validated['teacher_id']);
            abort_unless($teacher->role === 'teacher', 422);
        }

        $feePlan = FeePlan::findOrFail($validated['fee_plan_id']);

        // Validate duration rules
        $minutes = (int) $validated['minutes_per_lesson'];
        if ($feePlan->minutes_per_lesson_default === 45) {
            abort_unless($minutes === 45, 422);
        } else {
            abort_unless(in_array($minutes, $feePlan->allow_half_hour ? [30, 60] : [60], true), 422);
        }

        $createdCycleId = null;

        DB::transaction(function () use ($validated, $feePlan, $minutes, &$createdCycleId): void {
            $intervalWeeks = (int) $validated['interval_weeks'];
            $defaultLessonsPerCycle = (int) $feePlan->lessons_per_cycle;
            $lessonsPerCycle = max(1, (int) ceil($defaultLessonsPerCycle / max(1, $intervalWeeks)));

            $baseCycleFeeCents = (int) $feePlan->cycle_fee_cents;
            if ($minutes === 30 && (int) $feePlan->minutes_per_lesson_default === 60 && $feePlan->allow_half_hour) {
                $baseCycleFeeCents = (int) round($baseCycleFeeCents / 2);
            }

            // Pro-rate cycle fee by reduced lesson count when interval is 2 weeks.
            $cycleFeeCents = (int) round($baseCycleFeeCents * ($lessonsPerCycle / max(1, $defaultLessonsPerCycle)));

            $time = $validated['preferred_start_time'] ?? '14:00';
            $startsOn = $validated['started_on'] ?? now()->addDay()->toDateString();

            $enrollment = Enrollment::create([
                'branch_id' => $validated['branch_id'] ? (int) $validated['branch_id'] : null,
                'student_id' => (int) $validated['student_id'],
                'teacher_id' => $validated['teacher_id'] ? (int) $validated['teacher_id'] : null,
                'fee_plan_id' => (int) $validated['fee_plan_id'],
                'minutes_per_lesson' => $minutes,
                'interval_weeks' => $intervalWeeks,
                'status' => 'active',
                'started_on' => $startsOn,
                'preferred_start_time' => $time,
            ]);

            $cycleNumber = 1;

            $cycle = Cycle::create([
                'enrollment_id' => $enrollment->id,
                'cycle_fee_cents' => max(0, $cycleFeeCents),
                'lessons_per_cycle' => $lessonsPerCycle,
                'minutes_per_lesson' => $minutes,
                'interval_weeks' => $intervalWeeks,
                'cycle_minutes_total' => $lessonsPerCycle * $minutes,
                'cycle_number' => $cycleNumber,
                'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
                'starts_on' => $startsOn,
            ]);
            $createdCycleId = $cycle->id;

            // Create lessons immediately so the Schedule shows something right after enrollment.
            $startAt = Carbon::parse($startsOn.' '.$time);

            for ($i = 1; $i <= $lessonsPerCycle; $i++) {
                $lessonStart = $startAt->copy()->addWeeks(($i - 1) * $intervalWeeks);
                $lessonEnd = $lessonStart->copy()->addMinutes($minutes);

                $branchId = $enrollment->branch_id ? (int) $enrollment->branch_id : null;
                $roomNumber = 1;
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

                    $roomNumber = 0;
                    foreach ($roomNumbers as $r) {
                        $occupied = Lesson::query()
                            ->where('classroom_number', $r)
                            ->where('scheduled_start_at', '<', $lessonEnd)
                            ->where('scheduled_end_at', '>', $lessonStart)
                            ->whereHas('cycle.enrollment', fn ($q) => $q->where('branch_id', $branchId))
                            ->exists();

                        if (! $occupied) {
                            $roomNumber = $r;
                            break;
                        }
                    }

                    abort_unless($roomNumber > 0, 422, 'No classroom available for this time slot.');
                }

                Lesson::create([
                    'cycle_id' => $cycle->id,
                    'student_id' => $enrollment->student_id,
                    'teacher_id' => $enrollment->teacher_id,
                    'classroom_number' => $roomNumber,
                    'scheduled_start_at' => $lessonStart,
                    'scheduled_end_at' => $lessonEnd,
                    'minutes' => $minutes,
                    'status' => Lesson::STATUS_SCHEDULED,
                    'sequence_in_cycle' => $i,
                    'cycle_size' => $lessonsPerCycle,
                ]);
            }
        });

        if ($createdCycleId) {
            DB::afterCommit(function () use ($createdCycleId): void {
                $cycle = Cycle::query()->with(['enrollment.student', 'enrollment.feePlan', 'additionalCharges', 'invoice'])->find($createdCycleId);
                if (! $cycle) {
                    return;
                }
                $service = app(InvoiceService::class);
                $invoice = $service->createOrUpdateForCycle($cycle);
                $service->sendToStudent($invoice);
            });
        }

        return redirect()->route('management.enrollments.index');
    }
}
