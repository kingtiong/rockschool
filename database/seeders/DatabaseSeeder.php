<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeePlan;
use App\Models\Lesson;
use App\Models\TeacherShare;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $management = User::factory()->create([
            'name' => 'Management',
            'email' => 'management@example.com',
            'role' => 'management',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);

        $student = User::factory()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        $branch = Branch::create([
            'name' => 'Main Branch',
            'active' => true,
        ]);

        // Fee plans (RM -> cents)
        $plans = [
            ['Premiere', 220, 4, 60, true],
            ['Debut', 240, 4, 60, true],
            ['Grade 1 - 3', 260, 4, 60, true],
            ['Grade 4', 270, 4, 60, true],
            ['Grade 5', 290, 4, 60, true],
            ['Grade 6', 300, 4, 60, true],
            ['Grade 7', 330, 4, 45, false],
            ['Grade 8', 360, 4, 45, false],
        ];

        foreach ($plans as [$name, $rm, $lessons, $minutes, $allowHalf]) {
            FeePlan::create([
                'name' => $name,
                'cycle_fee_cents' => (int) round($rm * 100),
                'lessons_per_cycle' => $lessons,
                'minutes_per_lesson_default' => $minutes,
                'allow_half_hour' => $allowHalf,
                'active' => true,
            ]);
        }

        TeacherShare::create([
            'teacher_id' => $teacher->id,
            'percent' => 60,
            'effective_from' => now()->toDateString(),
            'effective_to' => null,
        ]);

        $feePlan = FeePlan::query()->where('name', 'Grade 6')->firstOrFail();

        $enrollment = Enrollment::create([
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'fee_plan_id' => $feePlan->id,
            'minutes_per_lesson' => 60,
            'status' => 'active',
            'started_on' => now()->toDateString(),
        ]);

        // Seed a simple cycle so the UI has data immediately.
        $lessonsPerCycle = (int) $feePlan->lessons_per_cycle;
        $minutesPerLesson = (int) $enrollment->minutes_per_lesson;
        $cycleMinutesTotal = $lessonsPerCycle * $minutesPerLesson;

        $cycle = Cycle::create([
            'enrollment_id' => $enrollment->id,
            'cycle_fee_cents' => $feePlan->cycle_fee_cents,
            'lessons_per_cycle' => $lessonsPerCycle,
            'minutes_per_lesson' => $minutesPerLesson,
            'cycle_minutes_total' => $cycleMinutesTotal,
            'cycle_number' => 1,
            'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
            'starts_on' => now()->addDay()->toDateString(),
        ]);

        $start = now()->startOfDay()->addDays(1)->setTime(14, 0); // tomorrow 2:00 PM
        for ($i = 1; $i <= $lessonsPerCycle; $i++) {
            $lessonStart = $start->copy()->addWeeks($i - 1);
            Lesson::create([
                'cycle_id' => $cycle->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'scheduled_start_at' => $lessonStart,
                'scheduled_end_at' => $lessonStart->copy()->addMinutes($minutesPerLesson),
                'minutes' => $minutesPerLesson,
                'status' => Lesson::STATUS_SCHEDULED,
                'sequence_in_cycle' => $i,
                'cycle_size' => $lessonsPerCycle,
            ]);
        }
    }
}
