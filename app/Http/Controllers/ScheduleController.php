<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Lesson::query()->with(['student', 'teacher'])->orderBy('scheduled_start_at');

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        }

        return view('schedule.index', [
            'lessons' => $query->get(),
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'teacher' && $lesson->teacher_id === $user->id, 403);

        $lesson->update([
            'status' => Lesson::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->route('schedule.index');
    }
}

