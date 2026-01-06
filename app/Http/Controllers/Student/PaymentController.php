<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Payment;
use App\Models\PaymentAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Request $request, Cycle $cycle): View
    {
        $user = $request->user();

        abort_unless($cycle->enrollment()->where('student_id', $user->id)->exists(), 403);

        return view('student.payments.create', [
            'cycle' => $cycle->load(['enrollment.feePlan', 'payment.attachments', 'additionalCharges']),
        ]);
    }

    public function store(Request $request, Cycle $cycle): RedirectResponse
    {
        $user = $request->user();

        abort_unless($cycle->enrollment()->where('student_id', $user->id)->exists(), 403);

        $validated = $request->validate([
            'bank_reference' => ['nullable', 'string', 'max:255'],
            'slip' => ['required', 'file', 'max:5120'], // 5MB
        ]);

        $payment = Payment::query()->firstOrCreate(
            ['cycle_id' => $cycle->id],
            [
                'student_id' => $user->id,
                'amount_cents' => $cycle->loadMissing('additionalCharges')->totalDueCents(),
                'method' => 'bank_transfer',
            ],
        );

        $payment->update([
            'student_id' => $user->id,
            'amount_cents' => $cycle->loadMissing('additionalCharges')->totalDueCents(),
            'status' => Payment::STATUS_PENDING_REVIEW,
            'bank_reference' => $validated['bank_reference'] ?? null,
            'submitted_at' => now(),
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);

        $file = $request->file('slip');
        $path = $file->store('payment-slips', 'public');

        PaymentAttachment::create([
            'payment_id' => $payment->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_user_id' => $user->id,
        ]);

        $cycle->update(['status' => Cycle::STATUS_PAYMENT_SUBMITTED]);

        return redirect()->route('student.cycles.index');
    }
}
