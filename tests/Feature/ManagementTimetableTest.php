<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
