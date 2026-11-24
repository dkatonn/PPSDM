<?php

namespace App\Http\Controllers;

use App\Models\DokumenKerjasama;
use App\Models\Kerjasama;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DokumenKerjasamaController extends Controller
{
    public function forAgreement(Kerjasama $kerjasama): View
    {
        $kerjasama->load(['ptn', 'dokumen' => fn ($query) => $query->latest()]);
        return view('kerjasama.documents', compact('kerjasama'));
    }

    public function index(): View { return view('dokumen.index', ['items' => DokumenKerjasama::with('kerjasama.ptn')->latest()->paginate(15)]); }
    public function store(Request $request, Kerjasama $kerjasama, AuditLogger $audit)
    {
        $rules = ['type' => 'required|in:ka,pks,ia,lainnya', 'number' => 'nullable|string|max:255', 'document_date' => 'nullable|date', 'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date', 'is_public' => 'nullable|boolean', 'file' => 'required|file|mimes:pdf|max:10240'];
        if ($request->filled('starts_at')) $rules['ends_at'] .= '|after_or_equal:starts_at';
        $data = $request->validate($rules);
        $file = $data['file']; $path = $file->store('kerjasama/'.$kerjasama->id, 'local');
        $document = $kerjasama->dokumen()->create(['type' => $data['type'], 'number' => $data['number'] ?? null, 'document_date' => $data['document_date'] ?? null, 'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null, 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(), 'is_public' => (bool) ($data['is_public'] ?? false), 'uploaded_by' => $request->user()->id]);
        $audit->record('document.uploaded', $document);
        return back()->with('success', $document->is_public
            ? 'Dokumen PDF diunggah dan diset agar tampil di portal setelah UPT memverifikasi kerja sama.'
            : 'Dokumen PDF berhasil diunggah sebagai dokumen internal.');
    }
    public function download(DokumenKerjasama $dokumenKerjasama) { abort_unless(Storage::disk('local')->exists($dokumenKerjasama->path), 404); return Storage::disk('local')->download($dokumenKerjasama->path, $dokumenKerjasama->original_name); }
    public function updateVisibility(Request $request, DokumenKerjasama $dokumenKerjasama, AuditLogger $audit)
    {
        $data = $request->validate(['is_public' => 'required|boolean']);
        $dokumenKerjasama->update(['is_public' => (bool) $data['is_public']]);
        $audit->record('document.visibility_updated', $dokumenKerjasama, ['is_public' => (bool) $data['is_public']]);
        return back()->with('success', $data['is_public'] ? 'Dokumen sekarang tampil di portal publik.' : 'Dokumen sekarang hanya dapat dilihat internal.');
    }
    public function publicDownload(DokumenKerjasama $dokumenKerjasama)
    {
        abort_unless($dokumenKerjasama->is_public, 404);

        $agreement = $dokumenKerjasama->kerjasama;
        abort_unless(
            $agreement
            && in_array($agreement->status, ['aktif', 'berakhir'], true)
            && $agreement->hasSignedCopy()
            && $agreement->hasActiveUpt()
            && Storage::disk('local')->exists($dokumenKerjasama->path),
            404,
        );

        return Storage::disk('local')->response(
            $dokumenKerjasama->path,
            $dokumenKerjasama->original_name,
            ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'],
            'inline',
        );
    }
    public function uptDownload(Request $request, DokumenKerjasama $dokumenKerjasama)
    {
        abort_unless($request->user()->upt_id && DB::table('kerjasama_upt')
            ->where('kerjasama_id', $dokumenKerjasama->kerjasama_id)
            ->where('upt_id', $request->user()->upt_id)
            ->exists(), 404);
        abort_unless(Storage::disk('local')->exists($dokumenKerjasama->path), 404);
        return Storage::disk('local')->download($dokumenKerjasama->path, $dokumenKerjasama->original_name);
    }
    public function destroy(DokumenKerjasama $dokumenKerjasama, AuditLogger $audit) { $audit->record('document.deleted', $dokumenKerjasama); Storage::disk('local')->delete($dokumenKerjasama->path); $dokumenKerjasama->delete(); return back()->with('success', 'Dokumen dihapus.'); }
}
