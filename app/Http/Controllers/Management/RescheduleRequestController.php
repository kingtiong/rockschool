<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\RescheduleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RescheduleRequestController extends Controller
{
    public function index(): View
    {
        return view('management.reschedule-requests.index', [
            'requests' => RescheduleRequest::query()
                ->with(['lesson.student', 'lesson.teacher', 'requestedBy'])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, RescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $rescheduleRequest->load('lesson');

        if ($rescheduleRequest->requested_start_at) {
            $lesson = $rescheduleRequest->lesson;
            $lesson->update([
                'scheduled_start_at' => $rescheduleRequest->requested_start_at,
                'scheduled_end_at' => $rescheduleRequest->requested_start_at->copy()->addMinutes($lesson->minutes),
                'status' => 'postponed',
            ]);
        }

        $rescheduleRequest->update([
            'status' => RescheduleRequest::STATUS_APPROVED,
            'decided_by_user_id' => $request->user()->id,
            'decided_at' => now(),
        ]);

        return redirect()->route('management.reschedule-requests.index');
    }

    public function reject(Request $request, RescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $rescheduleRequest->update([
            'status' => RescheduleRequest::STATUS_REJECTED,
            'decided_by_user_id' => $request->user()->id,
            'decided_at' => now(),
            'decision_note' => $validated['decision_note'] ?? null,
        ]);

        return redirect()->route('management.reschedule-requests.index');
    }
}
