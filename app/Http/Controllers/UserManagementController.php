<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Models\Upt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View { return view('users.index', ['users' => User::query()->with('upt')->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')))->orderBy('name')->paginate(15)->withQueryString(), 'upts' => Upt::orderBy('name')->get()]); }
    public function store(Request $request, AuditLogger $audit) { $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => 'required|string|min:12', 'role' => 'required|in:admin,super_admin,upt', 'upt_id' => 'nullable|required_if:role,upt|exists:upt,id']); if ($data['role'] !== 'upt') $data['upt_id'] = null; $data['password'] = Hash::make($data['password']); $data['is_active'] = true; $user = User::create($data); $audit->record('user.created', $user); return back()->with('success', 'Pengguna berhasil dibuat.'); }
    public function update(Request $request, User $user, AuditLogger $audit) { $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email,'.$user->id, 'password' => 'nullable|string|min:12', 'role' => 'required|in:admin,super_admin,upt', 'upt_id' => 'nullable|required_if:role,upt|exists:upt,id', 'is_active' => 'required|boolean']); if ($data['role'] !== 'upt') $data['upt_id'] = null; if ($request->filled('password')) $data['password'] = Hash::make($data['password']); else unset($data['password']); if ($user->is(auth()->user()) && (! $data['is_active'] || $data['role'] !== 'super_admin')) return back()->withErrors(['user' => 'Super Admin tidak dapat menonaktifkan atau menurunkan role akunnya sendiri.']); $user->update($data); $audit->record('user.updated', $user); return back()->with('success', 'Pengguna berhasil diperbarui.'); }
}
