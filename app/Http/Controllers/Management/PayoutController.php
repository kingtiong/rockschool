<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TeacherEarning;
use App\Models\TeacherPayout;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(): View
    {
        $teachers = User::query()->where('role', 'teacher')->orderBy('name')->get();

        $unpaidTotals = TeacherEarning::query()
            ->selectRaw('teacher_id, SUM(amount_cents) as total_cents')
            ->where('status', TeacherEarning::STATUS_UNPAID)
            ->groupBy('teacher_id')
            ->pluck('total_cents', 'teacher_id');

        return view('management.payouts.index', [
            'teachers' => $teachers,
            'unpaidTotals' => $unpaidTotals,
            'payouts' => TeacherPayout::query()->with('teacher')->orderByDesc('id')->get(),
        ]);
    }

    public function payAllUnpaid(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === 'teacher', 404);

        DB::transaction(function () use ($request, $teacher): void {
            $earnings = TeacherEarning::query()
                ->where('teacher_id', $teacher->id)
                ->where('status', TeacherEarning::STATUS_UNPAID)
                ->whereNull('teacher_payout_id')
                ->lockForUpdate()
                ->get();

            $total = (int) $earnings->sum('amount_cents');
            if ($total <= 0) {
                return;
            }

            $payout = TeacherPayout::create([
                'teacher_id' => $teacher->id,
                'amount_cents' => $total,
                'status' => TeacherPayout::STATUS_PENDING,
                'created_by_user_id' => $request->user()->id,
            ]);

            TeacherEarning::query()
                ->whereIn('id', $earnings->pluck('id'))
                ->update(['teacher_payout_id' => $payout->id]);
        });

        return redirect()->route('management.payouts.index');
    }

    public function markPaid(Request $request, TeacherPayout $payout): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($payout, $validated): void {
            $payout->update([
                'status' => TeacherPayout::STATUS_PAID,
                'paid_at' => now(),
                'reference' => $validated['reference'] ?? null,
            ]);

            TeacherEarning::query()
                ->where('teacher_payout_id', $payout->id)
                ->update(['status' => TeacherEarning::STATUS_PAID]);
        });

        return redirect()->route('management.payouts.index');
    }
}
