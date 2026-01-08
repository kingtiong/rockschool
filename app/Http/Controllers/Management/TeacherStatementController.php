<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TeacherEarning;
use App\Models\TeacherPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TeacherStatementController extends Controller
{
    public function show(Request $request, User $teacher): View
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);
        abort_unless($teacher->role === 'teacher', 404);

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? null;
        $start = null;
        $end = null;
        if ($month) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->addMonth();
        }

        $earningsQuery = TeacherEarning::query()
            ->with(['lesson.student', 'lesson.cycle.enrollment.feePlan', 'payout'])
            ->where('teacher_id', $teacher->id);

        if ($start && $end) {
            $earningsQuery->whereHas('lesson', function ($q) use ($start, $end) {
                $q->where('scheduled_start_at', '>=', $start)
                    ->where('scheduled_start_at', '<', $end);
            });
        }

        $payoutsQuery = TeacherPayout::query()
            ->where('teacher_id', $teacher->id);

        if ($start && $end) {
            $payoutsQuery->where(function ($q) use ($start, $end) {
                // Use paid_at when available; otherwise created_at for pending.
                $q->whereBetween('paid_at', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->whereNull('paid_at')->whereBetween('created_at', [$start, $end]);
                    });
            });
        }

        $earnings = $earningsQuery->get();
        $payouts = $payoutsQuery->get();

        // Month options (based on existing data)
        $minDate = collect([
            $earnings->min(fn (TeacherEarning $e) => $e->lesson?->scheduled_start_at),
            $payouts->min(fn (TeacherPayout $p) => $p->paid_at ?? $p->created_at),
        ])->filter()->min();

        $maxDate = collect([
            $earnings->max(fn (TeacherEarning $e) => $e->lesson?->scheduled_start_at),
            $payouts->max(fn (TeacherPayout $p) => $p->paid_at ?? $p->created_at),
        ])->filter()->max();

        $monthOptions = [];
        if ($minDate && $maxDate) {
            $cursor = Carbon::parse($minDate)->startOfMonth();
            $last = Carbon::parse($maxDate)->startOfMonth();
            while ($cursor->lte($last)) {
                $monthOptions[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
        }

        // Build ledger entries (earnings increase balance; PAID payouts decrease balance).
        $entries = collect();

        foreach ($earnings as $e) {
            $dt = $e->lesson?->scheduled_start_at ?? $e->calculated_at ?? $e->created_at;
            $entries->push([
                'at' => $dt,
                'type' => 'earning',
                'label' => 'Lesson earning',
                'ref' => $e->lesson?->scheduled_start_at?->format('Y-m-d g:i A'),
                'student' => $e->lesson?->student?->name,
                'plan' => $e->lesson?->cycle?->enrollment?->feePlan?->name,
                'amount_cents' => (int) $e->amount_cents, // +
                'status' => $e->status,
            ]);
        }

        foreach ($payouts as $p) {
            $dt = $p->paid_at ?? $p->created_at;
            $isPaid = $p->status === TeacherPayout::STATUS_PAID;
            $entries->push([
                'at' => $dt,
                'type' => 'payout',
                'label' => 'Teacher payout',
                'ref' => '#'.$p->id.($p->reference ? ' · '.$p->reference : ''),
                'student' => null,
                'plan' => null,
                'amount_cents' => $isPaid ? -((int) $p->amount_cents) : 0, // deduct only when paid
                'status' => $p->status,
                'pending_amount_cents' => $isPaid ? 0 : (int) $p->amount_cents,
            ]);
        }

        $entries = $entries
            ->filter(fn ($x) => $x['at'] !== null)
            ->sortBy(fn ($x) => Carbon::parse($x['at'])->timestamp)
            ->values();

        $running = 0;
        $entriesWithBalance = $entries->map(function ($x) use (&$running) {
            $running += (int) ($x['amount_cents'] ?? 0);
            $x['balance_cents'] = $running;
            return $x;
        });

        $totalEarned = (int) $earnings->sum('amount_cents');
        $totalPaidOut = (int) $payouts->where('status', TeacherPayout::STATUS_PAID)->sum('amount_cents');
        $pendingPayout = (int) $payouts->where('status', TeacherPayout::STATUS_PENDING)->sum('amount_cents');
        $balance = $totalEarned - $totalPaidOut;

        return view('management/teachers/statement', [
            'teacher' => $teacher,
            'month' => $month,
            'monthOptions' => $monthOptions,
            'entries' => $entriesWithBalance,
            'totalEarnedCents' => $totalEarned,
            'totalPaidOutCents' => $totalPaidOut,
            'pendingPayoutCents' => $pendingPayout,
            'balanceCents' => $balance,
        ]);
    }
}

