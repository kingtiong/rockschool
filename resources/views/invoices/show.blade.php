@php($cycle = $invoice->cycle)
@php($enrollment = $cycle?->enrollment)
@php($plan = $enrollment?->feePlan)
@php($student = $invoice->student)
@php($charges = $cycle?->additionalCharges ?? collect())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; padding: 24px; }
        .muted { color: #6B7280; }
        .row { display: flex; justify-content: space-between; gap: 24px; }
        .box { border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; }
        .title { font-size: 28px; letter-spacing: 1px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; text-align: left; }
        th { background: #111827; color: white; font-weight: 600; font-size: 12px; letter-spacing: .06em; text-transform: uppercase; }
        .right { text-align: right; }
        .total { font-weight: 700; }
        .badge { background: #F3F4F6; padding: 6px 10px; border-radius: 6px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="row" style="align-items:flex-start;">
        <div>
            <div class="title">INVOICE</div>
            <div class="muted" style="margin-top:6px;">{{ config('app.name', 'Music School') }}</div>
        </div>
        <div class="box" style="min-width: 320px;">
            <div class="row"><div class="muted">Invoice Number</div><div><strong>{{ $invoice->invoice_number }}</strong></div></div>
            <div class="row" style="margin-top:8px;"><div class="muted">Invoice Date</div><div>{{ $invoice->issue_date->format('F j, Y') }}</div></div>
            <div class="row" style="margin-top:8px;"><div class="muted">Payment Due</div><div>{{ $invoice->due_date->format('F j, Y') }}</div></div>
            <div class="row" style="margin-top:12px; align-items:center;">
                <div class="muted">Amount Due (MYR)</div>
                <div class="badge">RM {{ number_format($invoice->total_amount_cents / 100, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top:20px;">
        <div class="box" style="flex: 1;">
            <div class="muted" style="font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Bill To</div>
            <div style="margin-top:8px;"><strong>{{ $student?->name ?? '—' }}</strong></div>
            <div class="muted" style="margin-top:4px;">{{ $student?->email ?? '—' }}</div>
        </div>
        <div class="box" style="flex: 1;">
            <div class="muted" style="font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Details</div>
            <div style="margin-top:8px;"><span class="muted">Plan:</span> <strong>{{ $plan?->name ?? '—' }}</strong></div>
            <div style="margin-top:4px;"><span class="muted">Cycle:</span> #{{ $cycle?->cycle_number ?? '—' }}</div>
            <div style="margin-top:4px;"><span class="muted">Branch:</span> {{ $enrollment?->branch?->name ?? '—' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Items</th>
                <th class="right">Quantity</th>
                <th class="right">Price</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div><strong>{{ $plan?->name ?? 'Lesson Fees' }}</strong></div>
                    <div class="muted">Cycle #{{ $cycle?->cycle_number ?? '—' }}</div>
                </td>
                <td class="right">1</td>
                <td class="right">RM {{ number_format($invoice->base_amount_cents / 100, 2) }}</td>
                <td class="right">RM {{ number_format($invoice->base_amount_cents / 100, 2) }}</td>
            </tr>
            @foreach($charges as $ch)
                <tr>
                    <td>
                        <div><strong>{{ $ch->description }}</strong></div>
                        <div class="muted">Additional charge</div>
                    </td>
                    <td class="right">1</td>
                    <td class="right">RM {{ number_format($ch->amount_cents / 100, 2) }}</td>
                    <td class="right">RM {{ number_format($ch->amount_cents / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right total">Total</td>
                <td class="right total">RM {{ number_format($invoice->total_amount_cents / 100, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="box" style="margin-top:18px;">
        <div class="muted" style="font-size:12px; text-transform:uppercase; letter-spacing:.06em;">Notes / Terms</div>
        <div style="margin-top:8px;" class="muted">
            Please keep this invoice for your reference. Payment confirmation is required to start the next cycle.
        </div>
    </div>
</body>
</html>

