<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        if (! $user || $user->role !== 'management') {
            return view('dashboard', [
                'metrics' => null,
            ]);
        }

        $totalSalesCents = (int) Cycle::query()->sum('cycle_fee_cents');
        $totalCollectedCents = (int) Payment::query()
            ->where('status', Payment::STATUS_APPROVED)
            ->sum('amount_cents');

        $pendingCycleStatuses = [
            Cycle::STATUS_AWAITING_STUDENT_PAYMENT,
            Cycle::STATUS_PAYMENT_SUBMITTED,
        ];

        $pending = Cycle::query()
            ->select([
                DB::raw("CASE
                    WHEN TIMESTAMPDIFF(DAY, created_at, NOW()) <= 30 THEN '0_30'
                    WHEN TIMESTAMPDIFF(DAY, created_at, NOW()) <= 60 THEN '31_60'
                    WHEN TIMESTAMPDIFF(DAY, created_at, NOW()) <= 90 THEN '61_90'
                    ELSE '90_plus'
                END AS bucket"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(cycle_fee_cents) as amount_cents'),
            ])
            ->whereIn('status', $pendingCycleStatuses)
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $buckets = [
            '0_30' => ['label' => '0–30 days'],
            '31_60' => ['label' => '31–60 days'],
            '61_90' => ['label' => '61–90 days'],
            '90_plus' => ['label' => '90+ days'],
        ];

        foreach ($buckets as $key => &$b) {
            $row = $pending->get($key);
            $b['count'] = (int) ($row?->count ?? 0);
            $b['amount_cents'] = (int) ($row?->amount_cents ?? 0);
        }

        $totalPendingCents = array_sum(array_map(fn ($b) => $b['amount_cents'], $buckets));

        // Renewal reminders: cycles that reached the end (4/4 or 2/2).
        $renewals = Cycle::query()
            ->with(['enrollment.student', 'enrollment.feePlan'])
            ->where('status', Cycle::STATUS_COMPLETED)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->map(function (Cycle $c) {
                return [
                    'cycle_id' => $c->id,
                    'cycle_number' => $c->cycle_number,
                    'lessons_per_cycle' => $c->lessons_per_cycle,
                    'student' => $c->enrollment?->student?->name,
                    'plan' => $c->enrollment?->feePlan?->name,
                    'completed_at' => $c->updated_at,
                ];
            });

        return view('dashboard', [
            'metrics' => [
                'total_sales_cents' => $totalSalesCents,
                'total_collected_cents' => $totalCollectedCents,
                'total_pending_cents' => $totalPendingCents,
                'pending_buckets' => $buckets,
                'renewals' => $renewals,
            ],
        ]);
    }
}

