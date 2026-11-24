<?php

namespace App\Http\Controllers;

use App\Models\Ptn;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PtnController extends Controller
{
    public function index(Request $request): View
    {
        $ptns = Ptn::query()->withCount(['kerjasamas as kerjasamas_count' => fn ($q) => $q->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->q.'%')->orWhere('province', 'like', '%'.$request->q.'%')))
            ->when($request->filled('province'), fn ($q) => $q->where('province', $request->province))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('ptn.index', ['ptns' => $ptns, 'provinces' => Ptn::query()->distinct()->orderBy('province')->pluck('province'), 'types' => Ptn::query()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type')]);
    }

    public function create(): View { return view('ptn.form', ['ptn' => new Ptn]); }
    public function store(Request $request, AuditLogger $audit) { $ptn = Ptn::create($this->validated($request)); $audit->record('ptn.created', $ptn); return redirect()->route('ptn.index')->with('success', 'Data PTN berhasil ditambahkan.'); }
    public function show(Ptn $ptn): View { $ptn->load(['kerjasamas' => fn ($q) => $q->with(['upt', 'uptAssignments.upt', 'uptAssignments.verifiedBy', 'ruangLingkup', 'dokumen'])->latest()]); $ptn->kerjasamas->each(function ($agreement) { $agreement->uptAssignments->each(function ($assignment) { $ids = DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $assignment->kerjasama_id)->where('upt_id', $assignment->upt_id)->pluck('ruang_lingkup_id'); $assignment->setRelation('ruangLingkup', \App\Models\RuangLingkup::whereIn('id', $ids)->orderBy('name')->get()); }); }); return view('ptn.show', compact('ptn')); }
    public function edit(Ptn $ptn): View { return view('ptn.form', compact('ptn')); }
    public function update(Request $request, Ptn $ptn, AuditLogger $audit) { $ptn->update($this->validated($request)); $audit->record('ptn.updated', $ptn); return redirect()->route('ptn.index')->with('success', 'Data PTN berhasil diperbarui.'); }
    public function destroy(Ptn $ptn, AuditLogger $audit) { if ($ptn->kerjasamas()->exists()) return back()->withErrors(['delete' => 'PTN masih memiliki riwayat kerja sama.']); $audit->record('ptn.deleted', $ptn); $ptn->delete(); return back()->with('success', 'Data PTN berhasil dihapus.'); }

    public function export()
    {
        return response()->streamDownload(function () { $out = fopen('php://output', 'w'); fputcsv($out, ['Nama PTN', 'Jenis', 'Provinsi', 'Kabupaten/Kota', 'Alamat', 'Website', 'Email', 'Telepon'], ',', '"', ''); Ptn::orderBy('name')->chunk(200, function ($rows) use ($out) { foreach ($rows as $ptn) fputcsv($out, [$ptn->name, $ptn->type, $ptn->province, $ptn->city, $ptn->address, $ptn->website, $ptn->email, $ptn->phone], ',', '"', ''); }); fclose($out); }, 'data-ptn.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, AuditLogger $audit)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        if (! $headers) { fclose($handle); return back()->withErrors(['file' => 'File CSV kosong atau tidak valid.']); }
        $normalize = fn ($value) => str($value)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
        $headers = array_map($normalize, $headers);
        $aliases = ['nama_ptn' => 'name', 'nama' => 'name', 'jenis' => 'type', 'provinsi' => 'province', 'kabupaten_kota' => 'city', 'alamat' => 'address', 'surel' => 'email', 'telepon' => 'phone'];
        $rows = []; $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($v) => $v !== null && trim($v) !== '')) === 0) continue;
            if (count($rows) >= 2000) { fclose($handle); return back()->withErrors(['file' => 'Maksimal 2.000 baris per unggahan.']); }
            $row = [];
            foreach ($headers as $index => $header) { $key = $aliases[$header] ?? $header; $row[$key] = trim((string) ($values[$index] ?? '')) ?: null; }
            $validator = Validator::make($row, ['name' => 'required|string|max:255', 'type' => 'required|string|max:100', 'province' => 'required|string|max:100', 'city' => 'required|string|max:100', 'address' => 'required|string|max:5000', 'website' => 'nullable|url|max:255', 'email' => 'nullable|email|max:255', 'phone' => ['nullable','regex:/^[0-9]+$/','max:40']]);
            if ($validator->fails()) { fclose($handle); return back()->withErrors(['file' => 'Baris '.$line.': '.implode(' ', $validator->errors()->all())]); }
            $rows[] = $validator->validated();
        }
        fclose($handle);
        DB::transaction(function () use ($rows) { foreach ($rows as $row) Ptn::updateOrCreate(['name' => $row['name']], $row); });
        $audit->record('ptn.imported', null, ['rows' => count($rows)]);
        return back()->with('success', count($rows).' data PTN berhasil diimpor/diperbarui dari CSV.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('ptn', 'name')->ignore($request->route('ptn'))], 'type' => 'required|string|max:100', 'address' => 'required|string|max:5000', 'province' => 'required|string|max:100', 'city' => 'required|string|max:100', 'website' => 'nullable|url|max:255', 'email' => 'nullable|email:rfc|max:255', 'phone' => ['nullable','regex:/^[0-9]+$/','max:40'], 'notes' => 'nullable|string|max:5000']);
    }
}
