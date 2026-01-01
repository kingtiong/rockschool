<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('student.cycles.index', [
            'cycles' => Cycle::query()
                ->with(['enrollment.feePlan', 'lessons'])
                ->whereHas('enrollment', fn ($q) => $q->where('student_id', $user->id))
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
