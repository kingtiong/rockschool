@php($download = (bool) ($download ?? false))
@php($teacher = $payout->teacher)
@php($totalCents = (int) ($earnings->sum('amount_cents') ?? 0))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payout Statement #{{ $payout->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; padding: 24px; }
        .muted { color: #6B7280; }
        .row { display: flex; justify-content: space-between; gap: 24px; }
        .title { font-size: 28px; letter-spacing: 1px; font-weight: 800; }
        .small { font-size: 12px; }
        .box { border: 1px solid #E5E7EB; border-radius: 8px; padding: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; text-align: left; vertical-align: top; }
        th { background: #111827; color: white; font-weight: 600; font-size: 12px; letter-spacing: .06em; text-transform: uppercase; }
        .right { text-align: right; }
        .total { font-weight: 800; }
        .badge { background: #F3F4F6; padding: 8px 12px; display: inline-block; font-weight: 800; }
    </style>
</head>
<body>
    @unless($download)
        <div style="margin-bottom: 14px;">
            <a href="{{ route('management.payouts.index') }}" class="muted" style="text-decoration: underline;">← Back</a>
            <span style="margin-left: 12px;"></span>
            <a href="{{ route('management.payouts.download', $payout) }}" class="muted" style="text-decoration: underline;">Download</a>
        </div>
    @endunless

    <div class="row" style="align-items:flex-start;">
        <div>
            <div class="title">PAYOUT STATEMENT</div>
            <div class="muted small" style="margin-top:6px;">{{ config('app.name', 'Music School') }}</div>
        </div>
        <div class="box" style="min-width: 340px;">
            <div class="row"><div class="muted small">Payout ID</div><div class="small"><strong>#{{ $payout->id }}</strong></div></div>
            <div class="row" style="margin-top:6px;"><div class="muted small">Teacher</div><div class="small"><strong>{{ $teacher?->name ?? '—' }}</strong></div></div>
            <div class="row" style="margin-top:6px;"><div class="muted small">Status</div><div class="small">{{ ucfirst($payout->status) }}</div></div>
            <div class="row" style="margin-top:6px;"><div class="muted small">Created</div><div class="small">{{ $payout->created_at?->format('Y-m-d g:i A') ?? '—' }}</div></div>
            <div class="row" style="margin-top:6px;"><div class="muted small">Paid at</div><div class="small">{{ $payout->paid_at?->format('Y-m-d g:i A') ?? '—' }}</div></div>
            <div class="row" style="margin-top:6px;"><div class="muted small">Reference</div><div class="small">{{ $payout->reference ?? '—' }}</div></div>
            <div class="row" style="margin-top:12px; align-items:center;">
                <div class="muted small">Total (MYR)</div>
                <div class="badge">RM {{ number_format($totalCents / 100, 2) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Lesson</th>
                <th>Student</th>
                <th>Plan</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($earnings as $e)
                @php($l = $e->lesson)
                @php($plan = $l?->cycle?->enrollment?->feePlan)
                <tr>
                    <td>
                        <div><strong>{{ $l?->scheduled_start_at?->format('Y-m-d g:i A') ?? '—' }}</strong></div>
                        <div class="muted small">
                            {{ $l?->minutes ? ($l->minutes.' mins') : '' }}
                            @if($l?->cycle?->cycle_number)
                                · Cycle #{{ $l->cycle->cycle_number }}
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ $l?->student?->name ?? '—' }}
                    </td>
                    <td>
                        {{ $plan?->name ?? '—' }}
                    </td>
                    <td class="right">
                        RM {{ number_format(((int) $e->amount_cents) / 100, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No earnings linked to this payout.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right total">Total</td>
                <td class="right total">RM {{ number_format($totalCents / 100, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

