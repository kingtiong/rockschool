<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Lesson;
use App\Models\RescheduleRequest;
use App\Services\LessonCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $query = Lesson::query()
            ->with(['student', 'teacher', 'cycle.enrollment.branch'])
            ->orderBy('scheduled_start_at');

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        } elseif ($user->role === 'management' && ! empty($validated['branch_id'])) {
            $branchId = (int) $validated['branch_id'];
            $query->whereHas('cycle.enrollment', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return view('schedule.index', [
            'lessons' => $query->get(),
            'branches' => $user->role === 'management'
                ? Branch::query()->where('active', true)->orderBy('name')->get()
                : collect(),
            'selectedBranchId' => $user->role === 'management'
                ? ($validated['branch_id'] ?? null)
                : null,
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->role === 'teacher' && $lesson->teacher_id === $user->id, 403);

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        app(LessonCompletionService::class)->completeLesson($lesson, $validated['remarks'] ?? null);

        return redirect()->route('schedule.index');
    }

    public function absence(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $lesson->student_id === $user->id, 403);

        $threshold = $lesson->scheduled_start_at->copy()->subHours(2);
        $isOnTime = now()->lte($threshold);

        DB::transaction(function () use ($isOnTime, $lesson, $user): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_ABSENCE,
                'requested_start_at' => null,
                'reason' => 'Student absence notification',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => null,
                'decided_at' => now(),
            ]);

            if (! $isOnTime) {
                // Late notice: counts as missed (no shifting).
                $lesson->update([
                    'status' => Lesson::STATUS_MISSED,
                    'absence_notified_at' => now(),
                ]);
                return;
            }

            // On-time notice: push this lesson AND later scheduled lessons in the same cycle forward 1 week.
            $lesson->update([
                'status' => Lesson::STATUS_POSTPONED,
                'absence_notified_at' => now(),
            ]);

            if (! $lesson->cycle_id) {
                // Best-effort fallback: just move this single lesson.
                $lesson->update([
                    'scheduled_start_at' => $lesson->scheduled_start_at->copy()->addWeek(),
                    'scheduled_end_at' => $lesson->scheduled_end_at->copy()->addWeek(),
                ]);
                return;
            }

            $lessonsToShift = Lesson::query()
                ->where('cycle_id', $lesson->cycle_id)
                ->where('sequence_in_cycle', '>=', $lesson->sequence_in_cycle ?? 1)
                ->whereIn('status', [Lesson::STATUS_SCHEDULED, Lesson::STATUS_POSTPONED])
                ->orderBy('sequence_in_cycle')
                ->lockForUpdate()
                ->get();

            foreach ($lessonsToShift as $l) {
                $l->update([
                    'scheduled_start_at' => $l->scheduled_start_at->copy()->addWeek(),
                    'scheduled_end_at' => $l->scheduled_end_at->copy()->addWeek(),
                ]);
            }
        });

        return redirect()->route('schedule.index');
    }

    public function requestChange(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'student' && $lesson->student_id === $user->id, 403);

        $validated = $request->validate([
            'requested_start_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        RescheduleRequest::create([
            'lesson_id' => $lesson->id,
            'requested_by_user_id' => $user->id,
            'type' => RescheduleRequest::TYPE_CHANGE,
            'requested_start_at' => \Illuminate\Support\Carbon::parse($validated['requested_start_at']),
            'reason' => $validated['reason'] ?? null,
            'status' => RescheduleRequest::STATUS_PENDING,
        ]);

        return redirect()->route('schedule.index');
    }
}

