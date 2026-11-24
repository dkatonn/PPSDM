<?php

namespace App\Http\Controllers;

use App\Models\Implementasi;
use App\Models\Kerjasama;
use App\Models\Upt;
use App\Models\RuangLingkup;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImplementasiController extends Controller
{
    public function index(): View { return view('implementasi.index', ['items' => Implementasi::with('kerjasama.ptn', 'upt', 'ruangLingkup', 'dokumen')->latest('activity_date')->paginate(15), 'agreements' => Kerjasama::with('ptn')->orderBy('title')->get(), 'upts' => Upt::orderBy('name')->get(), 'scopes' => RuangLingkup::orderBy('name')->get()]); }
    public function store(Request $request, AuditLogger $audit)
    {
        $data = $request->validate(['kerjasama_id' => 'required|exists:kerjasama,id', 'upt_id' => 'nullable|exists:upt,id', 'ruang_lingkup_id' => 'nullable|exists:ruang_lingkup,id', 'name' => 'required|string|max:255', 'activity_date' => 'required|date', 'description' => 'nullable|string|max:5000', 'status' => 'required|in:terencana,berjalan,selesai,dibatalkan', 'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $file = $data['file'] ?? null; unset($data['file']); $data['created_by'] = $request->user()->id;
        $item = Implementasi::create($data);
        if ($file) { $path = $file->store('implementasi/'.$item->id, 'local'); $item->dokumen()->create(['original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(), 'uploaded_by' => $request->user()->id]); }
        $audit->record('implementation.created', $item);
        return back()->with('success', 'Implementasi berhasil dicatat.');
    }
    public function download(\App\Models\ImplementasiDokumen $implementasiDokumen) { abort_unless(Storage::disk('local')->exists($implementasiDokumen->path), 404); return Storage::disk('local')->download($implementasiDokumen->path, $implementasiDokumen->original_name); }
    public function destroy(Implementasi $implementasi, AuditLogger $audit) { $audit->record('implementation.deleted', $implementasi); foreach ($implementasi->dokumen as $file) Storage::disk('local')->delete($file->path); $implementasi->delete(); return back()->with('success', 'Implementasi dihapus.'); }
}
