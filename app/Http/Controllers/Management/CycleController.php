<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleController extends Controller
{
    public function create(Enrollment $enrollment): View
    {
        $enrollment->load(['student', 'teacher', 'feePlan']);

        return view('management.cycles.create', [
            'enrollment' => $enrollment,
        ]);
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->load(['feePlan']);

        $validated = $request->validate([
            'start_at' => ['required', 'date'],
        ]);

        $startAt = \Illuminate\Support\Carbon::parse($validated['start_at']);
        $plan = $enrollment->feePlan;

        $lessonsPerCycle = (int) $plan->lessons_per_cycle;
        $minutesPerLesson = (int) $enrollment->minutes_per_lesson;
        $cycleMinutesTotal = $lessonsPerCycle * $minutesPerLesson;

        $cycleFeeCents = (int) $plan->cycle_fee_cents;
        if ($minutesPerLesson === 30 && $plan->minutes_per_lesson_default === 60 && $plan->allow_half_hour) {
            $cycleFeeCents = (int) round($cycleFeeCents / 2);
        }

        $cycleNumber = ((int) ($enrollment->cycles()->max('cycle_number') ?? 0)) + 1;

        $cycle = Cycle::create([
            'enrollment_id' => $enrollment->id,
            'cycle_fee_cents' => $cycleFeeCents,
            'lessons_per_cycle' => $lessonsPerCycle,
            'minutes_per_lesson' => $minutesPerLesson,
            'cycle_minutes_total' => $cycleMinutesTotal,
            'cycle_number' => $cycleNumber,
            'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
            'starts_on' => $startAt->toDateString(),
        ]);

        for ($i = 1; $i <= $lessonsPerCycle; $i++) {
            $lessonStart = $startAt->copy()->addWeeks($i - 1);
            Lesson::create([
                'cycle_id' => $cycle->id,
                'student_id' => $enrollment->student_id,
                'teacher_id' => $enrollment->teacher_id,
                'scheduled_start_at' => $lessonStart,
                'scheduled_end_at' => $lessonStart->copy()->addMinutes($minutesPerLesson),
                'minutes' => $minutesPerLesson,
                'status' => Lesson::STATUS_SCHEDULED,
                'sequence_in_cycle' => $i,
                'cycle_size' => $lessonsPerCycle,
            ]);
        }

        return redirect()->route('management.enrollments.index');
    }
}
