<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View { return view('profile.edit', ['user' => auth()->user()]); }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)], 'current_password' => ['nullable', 'required_with:password', 'current_password'], 'password' => 'nullable|string|min:12|confirmed']);
        if ($request->filled('password')) $data['password'] = Hash::make($data['password']); else unset($data['password']);
        unset($data['current_password']);
        $user->update($data);
        $audit->record('profile.updated', $user);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
