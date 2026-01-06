<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AdditionalCharge;
use App\Models\Cycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdditionalChargeController extends Controller
{
    public function store(Request $request, Cycle $cycle): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount_rm' => ['required', 'numeric', 'min:0'],
        ]);

        AdditionalCharge::create([
            'cycle_id' => $cycle->id,
            'description' => $validated['description'],
            'amount_cents' => (int) round(((float) $validated['amount_rm']) * 100),
            'created_by_user_id' => $user->id,
        ]);

        return redirect()->route('management.payments.index');
    }

    public function destroy(Request $request, AdditionalCharge $additionalCharge): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'management', 403);

        $additionalCharge->delete();

        return redirect()->route('management.payments.index');
    }
}

