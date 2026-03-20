<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeePlan;
use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ManagementRescheduleRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_approve_moves_lesson_and_marks_it_scheduled(): void
    {
        $management = User::factory()->create(['role' => 'management']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $branch = Branch::create([
            'name' => 'Branch A',
            'active' => true,
            'classrooms_count' => 2,
        ]);

        $plan = FeePlan::create([
            'name' => 'Test Plan',
            'cycle_fee_cents' => 30000,
            'lessons_per_cycle' => 4,
            'minutes_per_lesson_default' => 60,
            'allow_half_hour' => true,
            'active' => true,
        ]);

        $enrollment = Enrollment::create([
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'fee_plan_id' => $plan->id,
            'minutes_per_lesson' => 60,
            'status' => 'active',
            'started_on' => '2026-01-01',
        ]);

        $cycle = Cycle::create([
            'enrollment_id' => $enrollment->id,
            'cycle_fee_cents' => $plan->cycle_fee_cents,
            'lessons_per_cycle' => 4,
            'minutes_per_lesson' => 60,
            'cycle_minutes_total' => 240,
            'cycle_number' => 1,
            'status' => Cycle::STATUS_ACTIVE,
            'starts_on' => '2026-01-05',
        ]);

        $lessonStart = Carbon::parse('2026-01-05 14:00:00');
        $lesson = Lesson::create([
            'cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'classroom_number' => 1,
            'scheduled_start_at' => $lessonStart,
            'scheduled_end_at' => $lessonStart->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
            'sequence_in_cycle' => 1,
            'cycle_size' => 4,
        ]);

        $requestedStart = Carbon::parse('2026-01-06 18:00:00');
        $request = RescheduleRequest::create([
            'lesson_id' => $lesson->id,
            'requested_by_user_id' => $student->id,
            'type' => RescheduleRequest::TYPE_CHANGE,
            'requested_start_at' => $requestedStart,
            'reason' => 'Need another time',
            'status' => RescheduleRequest::STATUS_PENDING,
        ]);

        $this->actingAs($management)
            ->post(route('management.reschedule-requests.approve', $request))
            ->assertRedirect(route('management.reschedule-requests.index'));

        $this->assertDatabaseHas('reschedule_requests', [
            'id' => $request->id,
            'status' => RescheduleRequest::STATUS_APPROVED,
            'decided_by_user_id' => $management->id,
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'scheduled_start_at' => $requestedStart->toDateTimeString(),
            'scheduled_end_at' => $requestedStart->copy()->addMinutes(60)->toDateTimeString(),
            'status' => Lesson::STATUS_SCHEDULED,
        ]);
    }
}

