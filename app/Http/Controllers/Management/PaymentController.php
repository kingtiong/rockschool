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
        $invoices = Cycle::query()
            ->with(['enrollment.student', 'enrollment.feePlan', 'additionalCharges'])
            ->whereIn('status', [Cycle::STATUS_AWAITING_STUDENT_PAYMENT, Cycle::STATUS_PAYMENT_SUBMITTED])
            ->orderByDesc('id')
            ->get();

        return view('management.payments.index', [
            'invoices' => $invoices,
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

    public function markPaid(Request $request, Cycle $cycle): RedirectResponse
    {
        $validated = $request->validate([
            'paid_at' => ['required', 'date'],
            'amount_rm' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'string', 'in:cash,bank_transfer,manual'],
            'paid_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $cycle->loadMissing('additionalCharges', 'enrollment');

        $payment = Payment::query()->firstOrCreate(
            ['cycle_id' => $cycle->id],
            [
                'student_id' => $cycle->enrollment->student_id,
                'amount_cents' => (int) round(((float) $validated['amount_rm']) * 100),
                'method' => $validated['method'],
            ],
        );

        $payment->update([
            'student_id' => $cycle->enrollment->student_id,
            'amount_cents' => (int) round(((float) $validated['amount_rm']) * 100),
            'method' => $validated['method'],
            'status' => Payment::STATUS_APPROVED,
            'paid_at' => \Illuminate\Support\Carbon::parse($validated['paid_at']),
            'paid_note' => $validated['paid_note'] ?? null,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $cycle->update(['status' => Cycle::STATUS_PAID]);

        return redirect()->route('management.payments.index');
    }
}
