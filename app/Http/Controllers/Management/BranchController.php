<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('management.branches.index', [
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('management.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'active' => ['nullable', 'boolean'],
            'classrooms_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        Branch::create([
            'name' => $validated['name'],
            'active' => (bool) ($validated['active'] ?? false),
            'classrooms_count' => (int) $validated['classrooms_count'],
        ]);

        return redirect()->route('management.branches.index');
    }

    public function edit(Branch $branch): View
    {
        return view('management.branches.edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name,'.$branch->id],
            'active' => ['nullable', 'boolean'],
            'classrooms_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $branch->update([
            'name' => $validated['name'],
            'active' => (bool) ($validated['active'] ?? false),
            'classrooms_count' => (int) $validated['classrooms_count'],
        ]);

        return redirect()->route('management.branches.index');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('management.branches.index');
    }
}

