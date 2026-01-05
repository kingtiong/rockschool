<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\RescheduleRequest;
use App\Models\TeacherEarning;
use App\Models\TeacherShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'day' => ['nullable', 'string', 'in:all,monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : now()->startOfDay();

        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);

        $dayName = $validated['day'] ?? 'all';
        $dayOffsets = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];
        $horizonWeeks = 8;

        $rangeStart = $weekStart->copy()->startOfDay();
        $rangeEnd = $weekStart->copy()->addWeeks($horizonWeeks)->startOfDay();
        $dates = [];

        if ($dayName === 'all') {
            // 8 weeks x 7 days = 56 dates on one page.
            $rangeEnd = $weekStart->copy()->addWeeks($horizonWeeks)->startOfDay();
            for ($d = 0; $d < ($horizonWeeks * 7); $d++) {
                $dates[] = $weekStart->copy()->addDays($d);
            }
        } else {
            $selectedDate = $weekStart->copy()->addDays($dayOffsets[$dayName] ?? 0);
            $rangeStart = $selectedDate->copy()->startOfDay();
            $rangeEnd = $selectedDate->copy()->addWeeks($horizonWeeks)->startOfDay();
            for ($w = 0; $w < $horizonWeeks; $w++) {
                $dates[] = $selectedDate->copy()->addWeeks($w);
            }
        }

        $branches = Branch::query()->where('active', true)->orderBy('name')->get();
        $selectedBranchId = $validated['branch_id'] ?? null;
        $selectedBranch = $selectedBranchId ? $branches->firstWhere('id', (int) $selectedBranchId) : null;

        $lessons = Lesson::query()
            ->with(['teacher', 'student', 'cycle.enrollment.branch'])
            ->where('scheduled_start_at', '<', $rangeEnd)
            ->where('scheduled_end_at', '>', $rangeStart)
            ->when($selectedBranch, function ($q) use ($selectedBranch) {
                // Include "orphan" lessons (no cycle) so they still appear.
                $q->where(function ($qq) use ($selectedBranch) {
                    $qq->whereHas('cycle.enrollment', function ($q2) use ($selectedBranch) {
                        $q2->where('branch_id', $selectedBranch->id);
                    })->orWhereNull('cycle_id');
                });
            })
            ->orderBy('scheduled_start_at')
            ->get();

        $firstLesson = $lessons->first();
        $firstLessonTimeKey = null;
        if ($firstLesson?->scheduled_start_at) {
            $slotStart = $firstLesson->scheduled_start_at->copy()->second(0);
            $slotStart->minute($slotStart->minute < 30 ? 0 : 30);
            $firstLessonTimeKey = $slotStart->format('H:i');
        }

        $gridStart = $rangeStart->copy()->setTime(8, 0);
        $gridEnd = $rangeStart->copy()->setTime(22, 0);
        $slotMinutes = 30;
        $slotCount = (int) (($gridEnd->diffInMinutes($gridStart)) / $slotMinutes);

        $roomModels = collect();
        $rooms = [];
        if ($selectedBranch) {
            $roomModels = Room::query()
                ->where('branch_id', $selectedBranch->id)
                ->where('active', true)
                ->orderBy('number')
                ->get();
            $rooms = $roomModels->pluck('number')->map(fn ($n) => (int) $n)->all();
        }
        if (count($rooms) === 0) {
            $roomsCount = max(1, (int) ($selectedBranch?->classrooms_count ?? 1));
            $rooms = range(1, $roomsCount);
        }

        $timeSlots = [];
        for ($m = 0; $m <= $gridEnd->diffInMinutes($gridStart); $m += $slotMinutes) {
            $timeSlots[] = $gridStart->copy()->addMinutes($m);
        }

        // Map lessons by [date][time][room] so the view can render fast.
        $grid = [];
        foreach ($lessons as $lesson) {
            /** @var Lesson $lesson */
            if (! $lesson->scheduled_start_at) {
                continue;
            }

            $dateKey = $lesson->scheduled_start_at->toDateString();
            // Place lesson in the nearest 30-min grid slot (floor).
            $slotStart = $lesson->scheduled_start_at->copy()->second(0);
            $slotStart->minute($slotStart->minute < 30 ? 0 : 30);
            $timeKey = $slotStart->format('H:i');

            // Default missing room to Room 1 so it still appears.
            $room = (int) ($lesson->classroom_number ?? 1);
            $room = max(1, $room);

            $grid[$dateKey][$timeKey][$room] = $lesson;
        }

        return view('management.timetable.index', [
            'date' => $date,
            'weekStart' => $weekStart,
            'selectedDayName' => $dayName,
            'horizonWeeks' => $horizonWeeks,
            'dates' => $dates,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'gridStart' => $gridStart,
            'gridEnd' => $gridEnd,
            'slotMinutes' => $slotMinutes,
            'slotCount' => $slotCount,
            'rooms' => $rooms,
            'roomModels' => $roomModels,
            'timeSlots' => $timeSlots,
            'grid' => $grid,
            'lessonsCount' => $lessons->count(),
            'firstLesson' => $firstLesson,
            'firstLessonTimeKey' => $firstLessonTimeKey,
        ]);
    }

    public function createSlot(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : now()->startOfDay();

        $branches = Branch::query()->where('active', true)->orderBy('name')->get();
        $branchId = $validated['branch_id'] ?? $branches->first()?->id;
        $selectedBranch = $branchId ? $branches->firstWhere('id', (int) $branchId) : null;

        return view('management.timetable.create-slot', [
            'date' => $date,
            'branches' => $branches,
            'branch' => $selectedBranch,
            'students' => User::query()->where('role', 'student')->orderBy('name')->get(),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'classroom_number' => ['nullable', 'integer', 'min:1', 'max:50'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'minutes_per_lesson' => ['required', 'integer', 'in:30,45,60'],
            'interval_weeks' => ['required', 'integer', 'in:1,2'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        abort_unless($student->role === 'student', 422);

        $teacherId = $validated['teacher_id'] ? (int) $validated['teacher_id'] : null;
        if ($teacherId) {
            $teacher = User::findOrFail($teacherId);
            abort_unless($teacher->role === 'teacher', 422);
        }

        $enrollment = Enrollment::query()
            ->with('feePlan')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        abort_unless($enrollment !== null && $enrollment->feePlan !== null, 422, 'Student has no active enrollment.');

        $plan = $enrollment->feePlan;
        $branchId = $validated['branch_id'] ? (int) $validated['branch_id'] : ($enrollment->branch_id ? (int) $enrollment->branch_id : null);
        $branch = $branchId ? Branch::find($branchId) : null;
        if ($branch) {
            abort_unless((int) $validated['classroom_number'] <= max(1, (int) $branch->classrooms_count), 422);
        }

        $minutes = (int) $validated['minutes_per_lesson'];
        if ((int) $plan->minutes_per_lesson_default === 45) {
            abort_unless($minutes === 45, 422);
        } else {
            abort_unless(in_array($minutes, $plan->allow_half_hour ? [30, 60] : [60], true), 422);
        }

        $startAt = Carbon::parse($validated['start_date'].' '.$validated['start_time']);
        $intervalWeeks = (int) $validated['interval_weeks'];

        $classroomNumber = $validated['classroom_number'] ? (int) $validated['classroom_number'] : null;

        DB::transaction(function () use ($enrollment, $plan, $teacherId, $minutes, $startAt, $intervalWeeks, $branchId, $classroomNumber): void {
            // Keep enrollment in sync with management slot configuration.
            $enrollment->update([
                'teacher_id' => $teacherId,
                'minutes_per_lesson' => $minutes,
                'branch_id' => $branchId,
            ]);

            $lessonsPerCycle = (int) $plan->lessons_per_cycle;
            $cycleMinutesTotal = $lessonsPerCycle * $minutes;

            $cycleFeeCents = (int) $plan->cycle_fee_cents;
            if ($minutes === 30 && (int) $plan->minutes_per_lesson_default === 60 && $plan->allow_half_hour) {
                $cycleFeeCents = (int) round($cycleFeeCents / 2);
            }

            $cycleNumber = ((int) ($enrollment->cycles()->max('cycle_number') ?? 0)) + 1;

            $cycle = Cycle::create([
                'enrollment_id' => $enrollment->id,
                'cycle_fee_cents' => $cycleFeeCents,
                'lessons_per_cycle' => $lessonsPerCycle,
                'minutes_per_lesson' => $minutes,
                'cycle_minutes_total' => $cycleMinutesTotal,
                'cycle_number' => $cycleNumber,
                'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
                'starts_on' => $startAt->toDateString(),
            ]);

            for ($i = 1; $i <= $lessonsPerCycle; $i++) {
                $lessonStart = $startAt->copy()->addWeeks(($i - 1) * $intervalWeeks);
                $lessonEnd = $lessonStart->copy()->addMinutes($minutes);

                $room = $classroomNumber;
                if (! $room) {
                    $roomNumbers = Room::query()
                        ->where('branch_id', $branchId)
                        ->where('active', true)
                        ->orderBy('number')
                        ->pluck('number')
                        ->map(fn ($n) => (int) $n)
                        ->values()
                        ->all();

                    if (count($roomNumbers) === 0) {
                        $roomNumbers = range(1, max(1, (int) (Branch::find($branchId)?->classrooms_count ?? 1)));
                    }

                    foreach ($roomNumbers as $r) {
                        $occupied = Lesson::query()
                            ->where('classroom_number', $r)
                            ->where('scheduled_start_at', '<', $lessonEnd)
                            ->where('scheduled_end_at', '>', $lessonStart)
                            ->whereHas('cycle.enrollment', fn ($q) => $q->where('branch_id', $branchId))
                            ->exists();

                        if (! $occupied) {
                            $room = $r;
                            break;
                        }
                    }

                    abort_unless($room, 422, 'No classroom available for this time slot.');
                }

                Lesson::create([
                    'cycle_id' => $cycle->id,
                    'student_id' => $enrollment->student_id,
                    'teacher_id' => $teacherId,
                    'classroom_number' => $room,
                    'scheduled_start_at' => $lessonStart,
                    'scheduled_end_at' => $lessonEnd,
                    'minutes' => $minutes,
                    'status' => Lesson::STATUS_SCHEDULED,
                    'sequence_in_cycle' => $i,
                    'cycle_size' => $lessonsPerCycle,
                ]);
            }
        });

        return redirect()->route('management.timetable.index', [
            'date' => $startAt->toDateString(),
        ]);
    }

    public function postpone(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        DB::transaction(function () use ($lesson, $user): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_ABSENCE,
                'requested_start_at' => null,
                'reason' => 'Management postponed lesson',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
            ]);

            $lesson->update([
                'status' => Lesson::STATUS_POSTPONED,
            ]);

            if (! $lesson->cycle_id) {
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

        return redirect()->route('management.timetable.index', [
            'date' => $lesson->scheduled_start_at->toDateString(),
        ]);
    }

    public function editReschedule(Request $request, Lesson $lesson): View
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        return view('management.timetable.reschedule', [
            'lesson' => $lesson->load(['student', 'teacher']),
        ]);
    }

    public function updateReschedule(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        $validated = $request->validate([
            'requested_start_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestedStartAt = Carbon::parse($validated['requested_start_at']);

        DB::transaction(function () use ($lesson, $user, $requestedStartAt, $validated): void {
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_CHANGE,
                'requested_start_at' => $requestedStartAt,
                'reason' => $validated['reason'] ?? 'Management rescheduled lesson',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
            ]);

            $lesson->update([
                'scheduled_start_at' => $requestedStartAt,
                'scheduled_end_at' => $requestedStartAt->copy()->addMinutes($lesson->minutes),
                'status' => Lesson::STATUS_POSTPONED,
            ]);
        });

        return redirect()->route('management.timetable.index', [
            'date' => $requestedStartAt->toDateString(),
        ]);
    }

    public function editTeacher(Request $request, Lesson $lesson): View
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        return view('management.timetable.change-teacher', [
            'lesson' => $lesson->load(['student', 'teacher']),
            'teachers' => User::query()->where('role', 'teacher')->orderBy('name')->get(),
        ]);
    }

    public function updateTeacher(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $teacher = User::findOrFail((int) $validated['teacher_id']);
        abort_unless($teacher->role === 'teacher', 422);

        $lesson->loadMissing('cycle');

        DB::transaction(function () use ($lesson, $teacher, $user, $validated): void {
            // Audit trail (best-effort): reuse reschedule_requests table with "change" type.
            RescheduleRequest::create([
                'lesson_id' => $lesson->id,
                'requested_by_user_id' => $user->id,
                'type' => RescheduleRequest::TYPE_CHANGE,
                'requested_start_at' => null,
                'reason' => $validated['reason'] ?? 'Management changed teacher',
                'status' => RescheduleRequest::STATUS_AUTO_APPLIED,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
            ]);

            $lesson->update([
                'teacher_id' => $teacher->id,
            ]);

            $earning = TeacherEarning::query()->where('lesson_id', $lesson->id)->first();
            if (! $earning) {
                return;
            }

            abort_unless($earning->status === TeacherEarning::STATUS_UNPAID, 422, 'Cannot change teacher after payout.');

            $cycle = $lesson->cycle;
            if (! $cycle || $cycle->cycle_minutes_total <= 0) {
                $earning->update([
                    'teacher_id' => $teacher->id,
                    'amount_cents' => 0,
                    'calculated_at' => now(),
                ]);
                return;
            }

            $share = TeacherShare::query()
                ->where('teacher_id', $teacher->id)
                ->whereNull('effective_to')
                ->latest('id')
                ->first();

            $percent = (int) ($share?->percent ?? 0);
            $lessonFeeCents = (int) round(($cycle->cycle_fee_cents * $lesson->minutes) / $cycle->cycle_minutes_total);
            $earningCents = (int) round($lessonFeeCents * ($percent / 100));

            $earning->update([
                'teacher_id' => $teacher->id,
                'amount_cents' => max(0, $earningCents),
                'calculated_at' => now(),
            ]);
        });

        return redirect()->route('management.timetable.index', [
            'date' => $lesson->scheduled_start_at->toDateString(),
        ]);
    }
}
