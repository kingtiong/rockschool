<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function store(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        Room::query()->updateOrCreate(
            [
                'branch_id' => $branch->id,
                'number' => (int) $validated['number'],
            ],
            [
                'name' => $validated['name'] ?? null,
                'active' => true,
            ],
        );

        return redirect()->route('management.timetable.index', [
            'branch_id' => $branch->id,
            'day' => $request->input('day', 'monday'),
            'date' => $request->input('date'),
        ]);
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $branchId = $room->branch_id;
        $room->delete();

        return redirect()->route('management.timetable.index', [
            'branch_id' => $branchId,
            'day' => $request->input('day', 'monday'),
            'date' => $request->input('date'),
        ]);
    }
}

