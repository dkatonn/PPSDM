<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Ptn;
use App\Models\RuangLingkup;
use App\Models\Upt;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KerjasamaController extends Controller
{
    public function index(Request $request): View
    {
        $items = Kerjasama::with('ptn')->withCount('implementasis')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('title', 'like', '%'.$request->q.'%')->orWhereHas('ptn', fn ($p) => $p->where('name', 'like', '%'.$request->q.'%'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('agreement_type'), fn ($q) => $q->where('agreement_type', $request->agreement_type))
            ->orderByDesc('starts_at')->paginate(15)->withQueryString();
        return view('kerjasama.index', compact('items'));
    }
    public function create(): View { return view('kerjasama.form', $this->formData(new Kerjasama)); }
    public function store(Request $request, AuditLogger $audit) { $data = $this->validated($request); $item = DB::transaction(function () use ($data, $request) { $item = Kerjasama::create($data['fields'] + ['created_by' => $request->user()->id]); $this->syncUptAssignments($item, $data); return $item; }); $this->storeDocuments($item, $data['documents'], $request); if ($data['approve_public']) $this->approveAssignments($item, $data, $request); $audit->record('kerjasama.created', $item); return redirect()->route('kerjasama.show', $item)->with('success', 'Kerja sama dan dokumen berhasil dicatat.'.($data['approve_public'] ? ' Kerja sama disetujui untuk tampil di portal publik.' : '')); }
    public function show(Kerjasama $kerjasama): View { $kerjasama->load(['ptn', 'upt', 'uptAssignments.upt', 'uptAssignments.verifiedBy', 'ruangLingkup', 'dokumen', 'implementasis.upt', 'auditLogs.user']); $kerjasama->uptAssignments->each(function ($assignment) { $ids = DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $assignment->kerjasama_id)->where('upt_id', $assignment->upt_id)->pluck('ruang_lingkup_id'); $assignment->setRelation('ruangLingkup', \App\Models\RuangLingkup::whereIn('id', $ids)->orderBy('name')->get()); }); return view('kerjasama.show', compact('kerjasama')); }
    public function edit(Kerjasama $kerjasama): View { $kerjasama->load('upt', 'ruangLingkup', 'uptAssignments.upt', 'uptAssignments.verifiedBy', 'dokumen'); $kerjasama->uptAssignments->each(fn ($assignment) => $this->loadAssignmentScopes($assignment)); return view('kerjasama.form', $this->formData($kerjasama)); }
    public function update(Request $request, Kerjasama $kerjasama, AuditLogger $audit) { $data = $this->validated($request, $kerjasama); $before = $kerjasama->only(['status', 'document_number', 'starts_at', 'ends_at']); DB::transaction(function () use ($data, $kerjasama) { $kerjasama->update($data['fields']); $this->syncUptAssignments($kerjasama, $data); }); $this->storeDocuments($kerjasama, $data['documents'], $request); if ($data['approve_public']) $this->approveAssignments($kerjasama, $data, $request); $after = $kerjasama->only(['status', 'document_number', 'starts_at', 'ends_at']); $audit->record('kerjasama.updated', $kerjasama, ['before' => $before, 'after' => $after]); return redirect()->route('kerjasama.show', $kerjasama)->with('success', 'Kerja sama berhasil diperbarui.'.($data['approve_public'] ? ' Persetujuan portal publik sudah disimpan.' : '')); }
    public function destroy(Kerjasama $kerjasama, AuditLogger $audit) { $audit->record('kerjasama.deleted', $kerjasama); $kerjasama->delete(); return redirect()->route('kerjasama.index')->with('success', 'Kerja sama berhasil dihapus.'); }

    private function formData(Kerjasama $item): array
    {
        $ptns = Ptn::orderBy('name')->get();
        $scopeCounts = DB::table('kerjasama_ruang_lingkup')->join('kerjasama', 'kerjasama.id', '=', 'kerjasama_ruang_lingkup.kerjasama_id')->select('kerjasama.ptn_id')->selectRaw('count(distinct kerjasama_ruang_lingkup.ruang_lingkup_id) as total')->groupBy('kerjasama.ptn_id')->pluck('total', 'ptn_id');
        $ptns = $ptns->filter(fn (Ptn $ptn) => (int) $scopeCounts->get($ptn->id, 0) <= 3 || $ptn->is($item->ptn))->values();
        return ['item' => $item, 'ptns' => $ptns, 'upts' => Upt::orderBy('name')->get(), 'scopes' => RuangLingkup::orderBy('name')->get()];
    }

    private function loadAssignmentScopes($assignment): void
    {
        $ids = DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $assignment->kerjasama_id)->where('upt_id', $assignment->upt_id)->pluck('ruang_lingkup_id');
        $assignment->setRelation('ruangLingkup', \App\Models\RuangLingkup::whereIn('id', $ids)->orderBy('name')->get());
    }

    private function storeDocuments(Kerjasama $item, array $documents, Request $request): void
    {
        foreach ($documents as $data) {
            if (empty($data['file'])) continue;
            $file = $data['file'];
            $path = $file->store('kerjasama/'.$item->id, 'local');
            $item->dokumen()->create([
                'type' => $data['type'], 'number' => $data['number'] ?? null,
                'document_date' => $data['document_date'] ?? null,
                'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
                'original_name' => $file->getClientOriginalName(), 'path' => $path,
                'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(),
                'is_public' => (bool) ($data['is_public'] ?? false), 'uploaded_by' => $request->user()->id,
            ]);
        }
    }

    private function syncUptAssignments(Kerjasama $item, array $data): void
    {
        $item->upt()->sync($data['upt_ids']);
        $scopeRows = DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $item->id);
        if ($data['upt_ids']) $scopeRows->whereNotIn('upt_id', $data['upt_ids']);
        $scopeRows->delete();

        foreach ($data['upt_ids'] as $uptId) {
            DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $item->id)->where('upt_id', $uptId)->delete();
            foreach ($data['scope_ids'] as $scopeId) DB::table('kerjasama_upt_ruang_lingkup')->insertOrIgnore(['kerjasama_id' => $item->id, 'upt_id' => $uptId, 'ruang_lingkup_id' => $scopeId]);
        }
        $item->ruangLingkup()->sync($data['scope_ids']);

    }
    private function approveAssignments(Kerjasama $item, array $data, Request $request): void
    {
        DB::table('kerjasama_upt')->where('kerjasama_id', $item->id)->update([
            'verification_status' => 'active', 'verified_at' => now(),
            'verified_by' => $request->user()->id, 'verification_notes' => 'Disetujui admin PPSDM untuk demonstrasi portal publik.',
        ]);
    }

    private function validated(Request $request, ?Kerjasama $item = null): array
    {
        $rules = ['ptn_id' => 'required|exists:ptn,id', 'title' => 'required|string|max:255', 'agreement_type' => 'required|in:ka,pks', 'document_number' => 'nullable|string|max:255', 'signed_at' => 'nullable|date', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date', 'status' => 'required|in:draft,aktif,berakhir', 'notes' => 'nullable|string|max:5000', 'upt_ids' => 'nullable|array', 'upt_ids.*' => 'exists:upt,id|distinct', 'scope_ids' => 'nullable|array|max:3', 'scope_ids.*' => 'exists:ruang_lingkup,id|distinct', 'approve_public' => 'nullable|boolean', 'documents' => 'nullable|array', 'documents.*.type' => 'required_with:documents.*.file|in:ka,pks,ia,lainnya', 'documents.*.number' => 'nullable|string|max:255', 'documents.*.document_date' => 'nullable|date', 'documents.*.starts_at' => 'nullable|date', 'documents.*.ends_at' => 'nullable|date', 'documents.*.is_public' => 'nullable|boolean', 'documents.*.file' => 'nullable|file|mimes:pdf|max:10240'];
        if ($request->filled('starts_at')) $rules['ends_at'] .= '|after_or_equal:starts_at';
        $v = $request->validate($rules);
        $uptIds = array_map('intval', $v['upt_ids'] ?? []);
        $scopeIds = array_map('intval', $v['scope_ids'] ?? []);
        $approve = (bool) ($v['approve_public'] ?? false) && $request->user()->role === 'super_admin';
        unset($v['upt_ids'], $v['scope_ids'], $v['approve_public']);
        $documents = $v['documents'] ?? [];
        unset($v['documents']);
        if ($approve) {
            if ($v['status'] !== 'aktif' || ! $uptIds || ! $scopeIds || empty($v['starts_at']) || empty($v['ends_at'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['approve_public' => 'Agar bisa disetujui, isi status Aktif, tanggal mulai dan akhir, serta pilih UPT dan ruang lingkup. Dokumen yang belum lengkap akan ditandai di portal publik.']);
            }
        }
        return ['fields' => $v, 'upt_ids' => $uptIds, 'scope_ids' => $scopeIds, 'documents' => $documents, 'approve_public' => $approve];
    }
}
