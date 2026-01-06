@php($cycle = $invoice->cycle)
@php($enrollment = $cycle?->enrollment)
@php($plan = $enrollment?->feePlan)
@php($student = $invoice->student)
@php($charges = $cycle?->additionalCharges ?? collect())
@php($firstLesson = $cycle?->lessons?->sortBy('scheduled_start_at')->first())
@php($monthLabel = $cycle?->starts_on ? $cycle->starts_on->format('M Y') : now()->format('M Y'))
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
        .title { font-size: 40px; letter-spacing: 2px; font-weight: 800; }
        .small { font-size: 12px; }
        .logo { width: 280px; max-width: 280px; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; text-align: left; }
        th { background: #111827; color: white; font-weight: 600; font-size: 12px; letter-spacing: .06em; text-transform: uppercase; }
        .right { text-align: right; }
        .total { font-weight: 700; }
        .highlight { background: #F3F4F6; padding: 8px 12px; display: inline-block; font-weight: 700; }
        .sectionTitle { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #6B7280; }
    </style>
</head>
<body>
    <div class="row" style="align-items:flex-start;">
        <div>
            {{-- Place logo file here: public/shion-logo.png --}}
            <img class="logo" src="{{ asset('shion-logo.png') }}" alt="Shion Music">
        </div>
        <div style="text-align:right;">
            <div class="title">INVOICE</div>
            <div class="small" style="margin-top:10px; font-weight:700;">Shion Music Academy of Performing Arts</div>
            <div class="small" style="margin-top:4px;">62, Jalan Koperasi 1,</div>
            <div class="small">Taman Perpaduan Koperasi</div>
            <div class="small">Ipoh, Perak 31400</div>
            <div class="small">Malaysia</div>
            <div class="small" style="margin-top:10px;">Mobile: 0125170741</div>
            <div class="small">shionmusic.com</div>
        </div>
    </div>

    <div class="row" style="margin-top:22px;">
        <div style="flex:1;">
            <div class="sectionTitle">BILL TO</div>
            <div style="margin-top:8px; font-weight:700;">{{ $student?->name ?? '—' }}</div>
            <div class="small">{{ $student?->email ?? '—' }}</div>
        </div>
        <div style="flex:1; text-align:right;">
            <div class="row" style="justify-content:flex-end; gap:12px;"><div class="small muted">Invoice Number:</div><div class="small" style="font-weight:700;">{{ $invoice->invoice_number }}</div></div>
            <div class="row" style="justify-content:flex-end; gap:12px; margin-top:6px;"><div class="small muted">Invoice Date:</div><div class="small">{{ $invoice->issue_date->format('F j, Y') }}</div></div>
            <div class="row" style="justify-content:flex-end; gap:12px; margin-top:6px;"><div class="small muted">Payment Due:</div><div class="small">{{ $invoice->due_date->format('F j, Y') }}</div></div>
            <div class="row" style="justify-content:flex-end; gap:12px; margin-top:10px; align-items:center;">
                <div class="small muted">Amount Due (MYR):</div>
                <div class="highlight">RM {{ number_format($invoice->total_amount_cents / 100, 2) }}</div>
            </div>
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
                    <div><strong>FEES-{{ $plan?->id ?? 'RS' }}</strong></div>
                    <div class="muted">
                        {{ $plan?->name ?? 'Lesson Fees' }}
                        @if($cycle?->cycle_minutes_total)
                            ({{ number_format($cycle->cycle_minutes_total / 60, 0) }}hrs/month)
                        @endif
                    </div>
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
            <tr>
                <td colspan="3" class="right total">Amount Due (MYR):</td>
                <td class="right total">RM {{ number_format($invoice->total_amount_cents / 100, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top:18px;">
        <div class="sectionTitle">Notes / Terms</div>
        <div class="small" style="margin-top:8px;">
            {{ $plan?->name ?? 'Lesson Fees' }}
            @if($firstLesson?->scheduled_start_at)
                <div class="muted" style="margin-top:4px;">{{ $monthLabel }} - First class {{ $firstLesson->scheduled_start_at->format('d/m') }}</div>
            @endif
        </div>
        <div class="small muted" style="margin-top:10px;">Terms:</div>
        <ol class="small muted" style="margin-top:6px; padding-left: 18px;">
            <li>Good sold and delivered/collected are not returnable. Fees collected are not refundable.</li>
            <li>Payment by cash, e-wallet or bank transfer to our MBB A/C 5581 7265 5857.</li>
            <li>A deposit of 60% is required as confirmation of order.</li>
            <li>Please notify us within 3 working days if there is any discrepancy otherwise deemed correct.</li>
            <li>We reserve the right to impose/charge 2% interest per month for overdue accounts.</li>
            <li>Goods sold remain the property of the company until full payment is made.</li>
            <li>We reserve the rights to not conduct any classes for unpaid lessons.</li>
            <li>Kindly whatsapp/email us the transfer slip for each payment made.</li>
        </ol>
    </div>
</body>
</html>

