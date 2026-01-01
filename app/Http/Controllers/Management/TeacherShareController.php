<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TeacherShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherShareController extends Controller
{
    public function index(): View
    {
        $teachers = User::query()
            ->where('role', 'teacher')
            ->orderBy('name')
            ->get();

        $currentShares = TeacherShare::query()
            ->whereNull('effective_to')
            ->get()
            ->keyBy('teacher_id');

        return view('management.teacher-shares.index', [
            'teachers' => $teachers,
            'currentShares' => $currentShares,
        ]);
    }

    public function upsert(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'teacher', 404);

        $validated = $request->validate([
            'percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Close any existing open-ended share
        TeacherShare::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('effective_to')
            ->update(['effective_to' => now()->toDateString()]);

        TeacherShare::create([
            'teacher_id' => $teacher->id,
            'percent' => (int) $validated['percent'],
            'effective_from' => now()->toDateString(),
            'effective_to' => null,
        ]);

        return redirect()->route('management.teacher-shares.index');
    }
}
