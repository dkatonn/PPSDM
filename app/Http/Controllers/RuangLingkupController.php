<?php

namespace App\Http\Controllers;

use App\Models\RuangLingkup;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuangLingkupController extends Controller
{
    public function index(): View { return view('scope.index', ['items' => RuangLingkup::withCount('kerjasamas')->orderBy('name')->paginate(20)]); }
    public function store(Request $request, AuditLogger $audit) { $item = RuangLingkup::create($request->validate(['name' => 'required|string|max:255|unique:ruang_lingkup,name', 'description' => 'nullable|string|max:3000'])); $audit->record('scope.created', $item); return back()->with('success', 'Ruang lingkup berhasil ditambahkan.'); }
    public function update(Request $request, RuangLingkup $ruangLingkup, AuditLogger $audit) { $ruangLingkup->update($request->validate(['name' => 'required|string|max:255|unique:ruang_lingkup,name,'.$ruangLingkup->id, 'description' => 'nullable|string|max:3000'])); $audit->record('scope.updated', $ruangLingkup); return back()->with('success', 'Ruang lingkup berhasil diperbarui.'); }
    public function destroy(RuangLingkup $ruangLingkup, AuditLogger $audit) { if ($ruangLingkup->kerjasamas()->exists()) return back()->withErrors(['delete' => 'Ruang lingkup masih digunakan oleh kerja sama.']); $audit->record('scope.deleted', $ruangLingkup); $ruangLingkup->delete(); return back()->with('success', 'Ruang lingkup berhasil dihapus.'); }
}
