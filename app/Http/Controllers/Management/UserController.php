<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Models\TeacherEarning;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:student,teacher'],
        ]);

        $role = $validated['role'] ?? 'student';

        return view('management.users.index', [
            'role' => $role,
            'users' => User::query()
                ->where('role', $role)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createStudent(): View
    {
        return view('management.users.create', [
            'role' => 'student',
        ]);
    }

    public function createTeacher(): View
    {
        return view('management.users.create', [
            'role' => 'teacher',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:student,teacher'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // Management-created users are treated as verified for smooth first login.
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('management.users.index', [
            'role' => $validated['role'],
        ]);
    }

    public function voidClasses(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor?->role === 'management', 403);
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 422, 'Only student/teacher can be voided.');

        $voided = $this->voidClassesForUser($user, $actor->id, 'Management void classes');

        return redirect()->route('management.users.index', [
            'role' => $user->role,
        ])->with('status', "Voided {$voided} class(es) for {$user->name}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor?->role === 'management', 403);
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 422, 'Only student/teacher can be deleted.');

        $role = $user->role;
        $name = $user->name;

        DB::transaction(function () use ($user, $actor): void {
            // Ensure future classes are explicitly voided before user removal.
            $this->voidClassesForUser($user, $actor->id, 'Management delete user');
            $user->delete();
        });

        return redirect()->route('management.users.index', [
            'role' => $role,
        ])->with('status', "{$name} deleted.");
    }

    private function voidClassesForUser(User $user, int $requestedByUserId, string $reason): int
    {
        return DB::transaction(function () use ($user, $requestedByUserId, $reason): int {
            $query = Lesson::query()
                ->whereIn('status', [Lesson::STATUS_SCHEDULED, Lesson::STATUS_POSTPONED])
                ->lockForUpdate();

            if ($user->role === 'student') {
                $query->where('student_id', $user->id);
            } else {
                $query->where('teacher_id', $user->id);
            }

            $lessons = $query->get();
            if ($lessons->isEmpty()) {
                return 0;
            }

            foreach ($lessons as $lesson) {
                RescheduleRequest::create([
                    'lesson_id' => $lesson->id,
                    'requested_by_user_id' => $requestedByUserId,
                    'type' => RescheduleRequest::TYPE_CHANGE,
                    'requested_start_at' => null,
                    'reason' => $reason,
                    'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                    'decided_by_user_id' => $requestedByUserId,
                    'decided_at' => now(),
                ]);

                $lesson->update([
                    'status' => Lesson::STATUS_CANCELLED,
                ]);

                TeacherEarning::query()
                    ->where('lesson_id', $lesson->id)
                    ->where('status', TeacherEarning::STATUS_UNPAID)
                    ->delete();
            }

            return $lessons->count();
        });
    }
}

