<?php

namespace App\Http\Controllers;

use App\Models\Upt;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UptController extends Controller
{
    public function index(Request $request): View
    {
        $upts = Upt::query()->withCount('kerjasamas')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->q.'%')->orWhere('province', 'like', '%'.$request->q.'%')->orWhere('work_area', 'like', '%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('province'), fn ($q) => $q->where('province', $request->province))
            ->orderBy('name')->paginate(15)->withQueryString();
        return view('upt.index', ['upts' => $upts, 'provinces' => Upt::query()->distinct()->orderBy('province')->pluck('province'), 'mappedCount' => Upt::query()->whereNotNull('latitude')->whereNotNull('longitude')->count()]);
    }
    public function create(): View { return view('upt.form', ['upt' => new Upt]); }
    public function store(Request $request, AuditLogger $audit) { $upt = Upt::create($this->validated($request)); $audit->record('upt.created', $upt); return redirect()->route('upt.index')->with('success', 'Data UPT berhasil ditambahkan.'); }
    public function edit(Upt $upt): View { return view('upt.form', compact('upt')); }
    public function update(Request $request, Upt $upt, AuditLogger $audit) { $upt->update($this->validated($request)); $audit->record('upt.updated', $upt); return redirect()->route('upt.index')->with('success', 'Data UPT berhasil diperbarui.'); }
    public function destroy(Upt $upt, AuditLogger $audit) { if ($upt->kerjasamas()->exists() || $upt->implementasis()->exists()) return back()->withErrors(['delete' => 'UPT masih terkait dengan kerja sama atau implementasi.']); $audit->record('upt.deleted', $upt); $upt->delete(); return back()->with('success', 'Data UPT berhasil dihapus.'); }
    private function validated(Request $request): array { return $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('upt', 'name')->ignore($request->route('upt'))], 'type' => 'nullable|string|max:100', 'work_area' => 'nullable|string|max:255', 'address' => 'nullable|string|max:5000', 'province' => 'required|string|max:100', 'city' => 'nullable|string|max:100', 'contact' => 'nullable|string|max:255', 'status' => 'required|in:operasional,non_operasional', 'latitude' => 'required|numeric|between:-11.2,6.3', 'longitude' => 'required|numeric|between:95,141.1', 'notes' => 'nullable|string|max:5000']); }
}
