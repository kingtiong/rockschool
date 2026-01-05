<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'day' => ['nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : now()->startOfDay();

        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(7);

        $dayName = $validated['day'] ?? 'monday';
        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];
        $selectedDate = $weekStart->copy()->addDays($dayOffsets[$dayName] ?? 0);

        $lessons = Lesson::query()
            ->with(['teacher', 'student'])
            ->where('scheduled_start_at', '<', $weekEnd)
            ->where('scheduled_end_at', '>', $weekStart)
            ->orderBy('scheduled_start_at')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $d,
                'lessons' => $lessons
                    ->filter(fn (Lesson $l) => $l->scheduled_start_at->toDateString() === $d->toDateString())
                    ->values(),
            ];
        }

        $selectedDay = collect($days)->first(fn (array $d) => $d['date']->toDateString() === $selectedDate->toDateString());

        return view('management.timetable.index', [
            'date' => $date,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => $days,
            'selectedDayName' => $dayName,
            'selectedDay' => $selectedDay,
        ]);
    }

    public function createSlot(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : now()->startOfDay();

        return view('management.timetable.create-slot', [
            'date' => $date,
            'students' => User::query()->where('role', 'student')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'minutes_per_lesson' => ['required', 'integer', 'in:30,45,60'],
            'interval_weeks' => ['required', 'integer', 'in:1,2'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        abort_unless($student->role === 'student', 422);

        $teacherId = $validated['teacher_id'] ? (int) $validated['teacher_id'] : null;
        if ($teacherId) {
            $teacher = User::findOrFail($teacherId);
            abort_unless($teacher->role === 'teacher', 422);
        }

        $enrollment = Enrollment::query()
            ->with('feePlan')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        abort_unless($enrollment !== null && $enrollment->feePlan !== null, 422, 'Student has no active enrollment.');

        $plan = $enrollment->feePlan;

        $minutes = (int) $validated['minutes_per_lesson'];
        if ((int) $plan->minutes_per_lesson_default === 45) {
            abort_unless($minutes === 45, 422);
        } else {
            abort_unless(in_array($minutes, $plan->allow_half_hour ? [30, 60] : [60], true), 422);
        }

        $startAt = Carbon::parse($validated['start_date'].' '.$validated['start_time']);
        $intervalWeeks = (int) $validated['interval_weeks'];

        DB::transaction(function () use ($enrollment, $plan, $teacherId, $minutes, $startAt, $intervalWeeks): void {
            // Keep enrollment in sync with management slot configuration.
            $enrollment->update([
                'teacher_id' => $teacherId,
                'minutes_per_lesson' => $minutes,
            ]);

            $lessonsPerCycle = (int) $plan->lessons_per_cycle;
            $cycleMinutesTotal = $lessonsPerCycle * $minutes;

            $cycleFeeCents = (int) $plan->cycle_fee_cents;
            if ($minutes === 30 && (int) $plan->minutes_per_lesson_default === 60 && $plan->allow_half_hour) {
                $cycleFeeCents = (int) round($cycleFeeCents / 2);
            }

            $cycleNumber = ((int) ($enrollment->cycles()->max('cycle_number') ?? 0)) + 1;

            $cycle = Cycle::create([
                'enrollment_id' => $enrollment->id,
                'cycle_fee_cents' => $cycleFeeCents,
                'lessons_per_cycle' => $lessonsPerCycle,
                'minutes_per_lesson' => $minutes,
                'cycle_minutes_total' => $cycleMinutesTotal,
                'cycle_number' => $cycleNumber,
                'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
                'starts_on' => $startAt->toDateString(),
            ]);

            for ($i = 1; $i <= $lessonsPerCycle; $i++) {
                $lessonStart = $startAt->copy()->addWeeks(($i - 1) * $intervalWeeks);
                Lesson::create([
                    'cycle_id' => $cycle->id,
                    'student_id' => $enrollment->student_id,
                    'teacher_id' => $teacherId,
                    'scheduled_start_at' => $lessonStart,
                    'scheduled_end_at' => $lessonStart->copy()->addMinutes($minutes),
                    'minutes' => $minutes,
                    'status' => Lesson::STATUS_SCHEDULED,
                    'sequence_in_cycle' => $i,
                    'cycle_size' => $lessonsPerCycle,
                ]);
            }
        });

        return redirect()->route('management.timetable.index', [
            'date' => $startAt->toDateString(),
        ]);
    }

    public function postpone(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        DB::transaction(function () use ($lesson, $user): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_ABSENCE,
                'requested_start_at' => null,
                'reason' => 'Management postponed lesson',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
            ]);

            $lesson->update([
                'status' => Lesson::STATUS_POSTPONED,
            ]);

            if (! $lesson->cycle_id) {
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

        return redirect()->route('management.timetable.index', [
            'date' => $lesson->scheduled_start_at->toDateString(),
        ]);
    }

    public function editReschedule(Request $request, Lesson $lesson): View
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        return view('management.timetable.reschedule', [
            'lesson' => $lesson->load(['student', 'teacher']),
        ]);
    }

    public function updateReschedule(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        $validated = $request->validate([
            'requested_start_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestedStartAt = Carbon::parse($validated['requested_start_at']);

        DB::transaction(function () use ($lesson, $user, $requestedStartAt, $validated): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_CHANGE,
                'requested_start_at' => $requestedStartAt,
                'reason' => $validated['reason'] ?? 'Management rescheduled lesson',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
            ]);

            $lesson->update([
                'scheduled_start_at' => $requestedStartAt,
                'scheduled_end_at' => $requestedStartAt->copy()->addMinutes($lesson->minutes),
                'status' => Lesson::STATUS_POSTPONED,
            ]);
        });

        return redirect()->route('management.timetable.index', [
            'date' => $requestedStartAt->toDateString(),
        ]);
    }
}
