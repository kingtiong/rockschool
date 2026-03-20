<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ManagementUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_can_view_students_list(): void
    {
        $management = User::factory()->create(['role' => 'management']);

        $this->actingAs($management)
            ->get('/management/users?role=student')
            ->assertStatus(200);
    }

    public function test_management_can_create_teacher(): void
    {
        $management = User::factory()->create(['role' => 'management']);

        $this->actingAs($management)
            ->post('/management/users', [
                'role' => 'teacher',
                'name' => 'Teacher A',
                'email' => 'teacher-a@example.com',
                'password' => 'password123!',
                'password_confirmation' => 'password123!',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'teacher-a@example.com',
            'role' => 'teacher',
        ]);
    }

    public function test_non_management_cannot_create_teacher(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post('/management/users', [
                'role' => 'teacher',
                'name' => 'Teacher X',
                'email' => 'teacher-x@example.com',
                'password' => 'password123!',
                'password_confirmation' => 'password123!',
            ])
            ->assertStatus(403);
    }

    public function test_management_can_void_student_classes(): void
    {
        $management = User::factory()->create(['role' => 'management']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $startAt = Carbon::parse('2026-01-05 15:00:00');
        $scheduled = Lesson::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'classroom_number' => 1,
            'scheduled_start_at' => $startAt,
            'scheduled_end_at' => $startAt->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
        ]);

        $completed = Lesson::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'classroom_number' => 1,
            'scheduled_start_at' => $startAt->copy()->addWeek(),
            'scheduled_end_at' => $startAt->copy()->addWeek()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_COMPLETED,
        ]);

        $this->actingAs($management)
            ->post("/management/users/{$student->id}/void-classes")
            ->assertStatus(302);

        $this->assertDatabaseHas('lessons', [
            'id' => $scheduled->id,
            'status' => Lesson::STATUS_CANCELLED,
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $completed->id,
            'status' => Lesson::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('reschedule_requests', [
            'lesson_id' => $scheduled->id,
            'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
            'reason' => 'Management void classes',
        ]);
    }

    public function test_management_can_delete_teacher(): void
    {
        $management = User::factory()->create(['role' => 'management']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($management)
            ->delete("/management/users/{$teacher->id}")
            ->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id,
        ]);
    }
}

