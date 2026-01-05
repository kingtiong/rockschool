<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:student,teacher'],
        ]);

        $role = $validated['role'] ?? 'student';

        return view('management.users.index', [
            'role' => $role,
            'users' => User::query()
                ->where('role', $role)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createStudent(): View
    {
        return view('management.users.create', [
            'role' => 'student',
        ]);
    }

    public function createTeacher(): View
    {
        return view('management.users.create', [
            'role' => 'teacher',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:student,teacher'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // Management-created users are treated as verified for smooth first login.
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('management.users.index', [
            'role' => $validated['role'],
        ]);
    }
}

