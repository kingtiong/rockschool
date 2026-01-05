<?php

namespace Tests\Feature;

use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeePlan;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ManagementTimetableTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_can_view_timetable(): void
    {
        $management = User::factory()->create([
            'role' => 'management',
        ]);

        $this->actingAs($management)
            ->get('/management/timetable')
            ->assertStatus(200);
    }

    public function test_management_can_view_timetable_for_a_specific_day(): void
    {
        $management = User::factory()->create(['role' => 'management']);

        $this->actingAs($management)
            ->get('/management/timetable?date=2026-01-05&day=tuesday')
            ->assertStatus(200);
    }

    public function test_management_can_open_add_slot_form(): void
    {
        $management = User::factory()->create([
            'role' => 'management',
        ]);

        $this->actingAs($management)
            ->get('/management/timetable/slots/create?date=2026-01-05')
            ->assertStatus(200);
    }

    public function test_management_can_postpone_and_shift_remaining_lessons_forward_one_week(): void
    {
        $management = User::factory()->create(['role' => 'management']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $plan = FeePlan::create([
            'name' => 'Test Plan',
            'cycle_fee_cents' => 30000,
            'lessons_per_cycle' => 4,
            'minutes_per_lesson_default' => 60,
            'allow_half_hour' => true,
            'active' => true,
        ]);

        $enrollment = Enrollment::create([
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

        $start1 = Carbon::parse('2026-01-05 14:00:00');
        $start2 = Carbon::parse('2026-01-12 14:00:00');

        $l1 = Lesson::create([
            'cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_start_at' => $start1,
            'scheduled_end_at' => $start1->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
            'sequence_in_cycle' => 1,
            'cycle_size' => 4,
        ]);

        $l2 = Lesson::create([
            'cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_start_at' => $start2,
            'scheduled_end_at' => $start2->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
            'sequence_in_cycle' => 2,
            'cycle_size' => 4,
        ]);

        $this->actingAs($management)
            ->post("/management/timetable/lessons/{$l1->id}/postpone")
            ->assertStatus(302);

        $this->assertDatabaseHas('lessons', [
            'id' => $l1->id,
            'status' => Lesson::STATUS_POSTPONED,
            'scheduled_start_at' => $start1->copy()->addWeek()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $l2->id,
            'scheduled_start_at' => $start2->copy()->addWeek()->toDateTimeString(),
        ]);
    }

    public function test_management_reschedule_changes_only_one_lesson(): void
    {
        $management = User::factory()->create(['role' => 'management']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $plan = FeePlan::create([
            'name' => 'Test Plan 2',
            'cycle_fee_cents' => 30000,
            'lessons_per_cycle' => 4,
            'minutes_per_lesson_default' => 60,
            'allow_half_hour' => true,
            'active' => true,
        ]);

        $enrollment = Enrollment::create([
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

        $start1 = Carbon::parse('2026-01-05 14:00:00');
        $start2 = Carbon::parse('2026-01-12 14:00:00');

        $l1 = Lesson::create([
            'cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_start_at' => $start1,
            'scheduled_end_at' => $start1->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
            'sequence_in_cycle' => 1,
            'cycle_size' => 4,
        ]);

        $l2 = Lesson::create([
            'cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'scheduled_start_at' => $start2,
            'scheduled_end_at' => $start2->copy()->addMinutes(60),
            'minutes' => 60,
            'status' => Lesson::STATUS_SCHEDULED,
            'sequence_in_cycle' => 2,
            'cycle_size' => 4,
        ]);

        $this->actingAs($management)
            ->put("/management/timetable/lessons/{$l1->id}/reschedule", [
                'requested_start_at' => '2026-01-06 15:00:00',
                'reason' => 'Test change',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('lessons', [
            'id' => $l1->id,
            'status' => Lesson::STATUS_POSTPONED,
            'scheduled_start_at' => '2026-01-06 15:00:00',
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $l2->id,
            'scheduled_start_at' => $start2->toDateTimeString(),
        ]);
    }

    public function test_non_management_cannot_view_timetable(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->get('/management/timetable')
            ->assertStatus(403);
    }
}
