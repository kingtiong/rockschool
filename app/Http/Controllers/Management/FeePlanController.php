<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\FeePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeePlanController extends Controller
{
    public function index(): View
    {
        return view('management.fee-plans.index', [
            'feePlans' => FeePlan::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(FeePlan $feePlan): View
    {
        return view('management.fee-plans.edit', [
            'feePlan' => $feePlan,
        ]);
    }

    public function update(Request $request, FeePlan $feePlan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cycle_fee_rm' => ['required', 'numeric', 'min:0'],
            'lessons_per_cycle' => ['required', 'integer', 'min:1', 'max:12'],
            'minutes_per_lesson_default' => ['required', 'integer', 'min:15', 'max:180'],
            'allow_half_hour' => ['nullable'],
            'active' => ['nullable'],
        ]);

        $feePlan->update([
            'name' => $validated['name'],
            'cycle_fee_cents' => (int) round(((float) $validated['cycle_fee_rm']) * 100),
            'lessons_per_cycle' => (int) $validated['lessons_per_cycle'],
            'minutes_per_lesson_default' => (int) $validated['minutes_per_lesson_default'],
            'allow_half_hour' => (bool) ($validated['allow_half_hour'] ?? false),
            'active' => (bool) ($validated['active'] ?? false),
        ]);

        return redirect()->route('management.fee-plans.index');
    }
}
