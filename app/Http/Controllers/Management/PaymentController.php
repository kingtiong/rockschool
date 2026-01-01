<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('management.payments.index', [
            'payments' => Payment::query()
                ->with(['student', 'cycle.enrollment.feePlan', 'attachments'])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => Payment::STATUS_APPROVED,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $payment->cycle->update([
            'status' => Cycle::STATUS_PAID,
        ]);

        return redirect()->route('management.payments.index');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        $payment->cycle->update([
            'status' => Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
        ]);

        return redirect()->route('management.payments.index');
    }
}
