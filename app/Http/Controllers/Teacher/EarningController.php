<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherEarning;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EarningController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $earnings = TeacherEarning::query()
            ->with(['lesson.student', 'lesson.cycle.enrollment.feePlan', 'payout'])
            ->where('teacher_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return view('teacher.earnings.index', [
            'earnings' => $earnings,
            'unpaidTotalCents' => (int) $earnings->where('status', TeacherEarning::STATUS_UNPAID)->sum('amount_cents'),
            'paidTotalCents' => (int) $earnings->where('status', TeacherEarning::STATUS_PAID)->sum('amount_cents'),
        ]);
    }
}
