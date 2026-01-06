@php($invoice = $invoice ?? $this->invoice ?? null)
@php($cycle = $invoice?->cycle)
@php($plan = $cycle?->enrollment?->feePlan)
@php($student = $invoice?->student)

<p>Hi {{ $student?->name ?? 'there' }},</p>

<p>Your invoice is ready.</p>

<ul>
    <li><strong>Invoice:</strong> {{ $invoice?->invoice_number }}</li>
    <li><strong>Plan:</strong> {{ $plan?->name ?? '—' }}</li>
    <li><strong>Cycle:</strong> #{{ $cycle?->cycle_number ?? '—' }}</li>
    <li><strong>Amount Due:</strong> RM {{ number_format(($invoice?->total_amount_cents ?? 0) / 100, 2) }}</li>
</ul>

<p>The invoice is attached (HTML). You can open it and print/save as PDF.</p>

