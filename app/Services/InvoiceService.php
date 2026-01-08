<?php

namespace App\Services;

use App\Mail\InvoiceIssued;
use App\Models\Cycle;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    public function createOrUpdateForCycle(Cycle $cycle): Invoice
    {
        $cycle->loadMissing(['enrollment.student', 'enrollment.feePlan', 'additionalCharges']);

        $base = (int) $cycle->cycle_fee_cents;
        $charges = (int) $cycle->additionalCharges->sum('amount_cents');
        $total = $base + $charges;

        $invoice = Invoice::query()->firstOrCreate(
            ['cycle_id' => $cycle->id],
            [
                'invoice_number' => $this->nextInvoiceNumber(),
                'student_id' => $cycle->enrollment->student_id,
                'base_amount_cents' => max(0, $base),
                'charges_amount_cents' => max(0, $charges),
                'total_amount_cents' => max(0, $total),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'status' => Invoice::STATUS_ISSUED,
            ]
        );

        $invoice->update([
            'student_id' => $cycle->enrollment->student_id,
            'base_amount_cents' => max(0, $base),
            'charges_amount_cents' => max(0, $charges),
            'total_amount_cents' => max(0, $total),
        ]);

        return $invoice;
    }

    public function sendToStudent(Invoice $invoice): void
    {
        $invoice->loadMissing(['student', 'cycle.enrollment.feePlan', 'cycle.additionalCharges']);
        $to = $invoice->student?->email;
        if (! $to) {
            return;
        }

        Mail::to($to)->send(new InvoiceIssued($invoice));
    }

    private function nextInvoiceNumber(): string
    {
        // Simple deterministic format: INV-YYYYMMDD-XXXX
        $today = now()->format('Ymd');
        $seq = (int) (Invoice::query()->where('invoice_number', 'like', "INV-{$today}-%")->count()) + 1;
        return sprintf('INV-%s-%04d', $today, $seq);
    }
}

