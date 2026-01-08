<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

