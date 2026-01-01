<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : now()->startOfDay();

        $start = $date->copy();
        $end = $date->copy()->addDay();

        $lessons = Lesson::query()
            ->with(['teacher', 'student'])
            ->where('scheduled_start_at', '<', $end)
            ->where('scheduled_end_at', '>', $start)
            ->orderBy('scheduled_start_at')
            ->get();

        // Hour buckets: [hourStart => count ongoing]
        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $hourStart = $start->copy()->addHours($h);
            $hourEnd = $hourStart->copy()->addHour();

            $count = $lessons->filter(function (Lesson $lesson) use ($hourStart, $hourEnd) {
                return $lesson->scheduled_start_at < $hourEnd && $lesson->scheduled_end_at > $hourStart;
            })->count();

            $hours[] = [
                'start' => $hourStart,
                'end' => $hourEnd,
                'count' => $count,
            ];
        }

        // Teacher clashes: overlapping lessons per teacher for this day
        $teachers = User::query()->where('role', 'teacher')->orderBy('name')->get()->keyBy('id');
        $clashes = [];

        $byTeacher = $lessons
            ->filter(fn (Lesson $l) => $l->teacher_id !== null)
            ->groupBy('teacher_id');

        foreach ($byTeacher as $teacherId => $teacherLessons) {
            /** @var \Illuminate\Support\Collection<int, Lesson> $teacherLessons */
            $sorted = $teacherLessons->sortBy('scheduled_start_at')->values();

            for ($i = 0; $i < $sorted->count() - 1; $i++) {
                $a = $sorted[$i];
                $b = $sorted[$i + 1];

                if ($a->scheduled_end_at > $b->scheduled_start_at) {
                    $clashes[] = [
                        'teacher' => $teachers->get($teacherId),
                        'a' => $a,
                        'b' => $b,
                    ];
                }
            }
        }

        return view('management.timetable.index', [
            'date' => $date,
            'hours' => $hours,
            'lessons' => $lessons,
            'clashes' => $clashes,
        ]);
    }
}
