<?php

namespace Database\Seeders;

use App\Models\Lesson;
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

        // Seed a simple 4-lesson cycle so the UI has data immediately.
        $start = now()->startOfDay()->addDays(1)->setTime(14, 0); // tomorrow 2:00 PM
        for ($i = 1; $i <= 4; $i++) {
            $lessonStart = $start->copy()->addWeeks($i - 1);
            Lesson::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'scheduled_start_at' => $lessonStart,
                'scheduled_end_at' => $lessonStart->copy()->addMinutes(60),
                'minutes' => 60,
                'status' => Lesson::STATUS_SCHEDULED,
                'sequence_in_cycle' => $i,
                'cycle_size' => 4,
            ]);
        }
    }
}
