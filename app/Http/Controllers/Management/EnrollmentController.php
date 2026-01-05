<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\FeePlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        return view('management.enrollments.index', [
            'enrollments' => Enrollment::query()
                ->with(['branch', 'student', 'teacher', 'feePlan'])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('management.enrollments.create', [
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
            'students' => User::query()->where('role', 'student')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
            'feePlans' => FeePlan::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'fee_plan_id' => ['required', 'integer', 'exists:fee_plans,id'],
            'minutes_per_lesson' => ['required', 'integer', 'in:30,45,60'],
            'started_on' => ['nullable', 'date'],
            'preferred_start_time' => ['nullable', 'date_format:H:i'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        abort_unless($student->role === 'student', 422);

        if (! empty($validated['teacher_id'])) {
            $teacher = User::findOrFail($validated['teacher_id']);
            abort_unless($teacher->role === 'teacher', 422);
        }

        $feePlan = FeePlan::findOrFail($validated['fee_plan_id']);

        // Validate duration rules
        $minutes = (int) $validated['minutes_per_lesson'];
        if ($feePlan->minutes_per_lesson_default === 45) {
            abort_unless($minutes === 45, 422);
        } else {
            abort_unless(in_array($minutes, $feePlan->allow_half_hour ? [30, 60] : [60], true), 422);
        }

        Enrollment::create([
            'branch_id' => $validated['branch_id'] ? (int) $validated['branch_id'] : null,
            'student_id' => (int) $validated['student_id'],
            'teacher_id' => $validated['teacher_id'] ? (int) $validated['teacher_id'] : null,
            'fee_plan_id' => (int) $validated['fee_plan_id'],
            'minutes_per_lesson' => $minutes,
            'status' => 'active',
            'started_on' => $validated['started_on'] ?? null,
            'preferred_start_time' => $validated['preferred_start_time'] ?? null,
        ]);

        return redirect()->route('management.enrollments.index');
    }
}
