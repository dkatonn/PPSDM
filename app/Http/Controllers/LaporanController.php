<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Ptn;
use App\Models\Upt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'ptn_id' => 'nullable|exists:ptn,id', 'upt_id' => 'nullable|exists:upt,id', 'scope_id' => 'nullable|exists:ruang_lingkup,id', 'province' => 'nullable|string|max:100']);
        $query = Kerjasama::with(['ptn', 'upt', 'ruangLingkup'])
            ->when($request->filled('ptn_id'), fn ($q) => $q->where('ptn_id', $request->ptn_id))
            ->when($request->filled('upt_id'), fn ($q) => $q->whereHas('upt', fn ($sub) => $sub->whereKey($request->upt_id)))
            ->when($request->filled('scope_id'), fn ($q) => $q->whereHas('ruangLingkup', fn ($sub) => $sub->whereKey($request->scope_id)))
            ->when($request->filled('province'), fn ($q) => $q->whereHas('ptn', fn ($sub) => $sub->where('province', $request->province)))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('starts_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('starts_at', '<=', $request->to));
        return view('laporan.index', ['items' => $query->latest('starts_at')->paginate(15)->withQueryString(), 'ptns' => Ptn::orderBy('name')->get(), 'upts' => Upt::orderBy('name')->get(), 'scopes' => \App\Models\RuangLingkup::orderBy('name')->get(), 'provinces' => Ptn::query()->distinct()->orderBy('province')->pluck('province')]);
    }

    public function export(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'ptn_id' => 'nullable|exists:ptn,id', 'upt_id' => 'nullable|exists:upt,id', 'scope_id' => 'nullable|exists:ruang_lingkup,id', 'province' => 'nullable|string|max:100']);
        $items = Kerjasama::with(['ptn', 'upt', 'ruangLingkup'])
            ->when($request->filled('ptn_id'), fn ($q) => $q->where('ptn_id', $request->ptn_id))
            ->when($request->filled('upt_id'), fn ($q) => $q->whereHas('upt', fn ($sub) => $sub->whereKey($request->upt_id)))
            ->when($request->filled('scope_id'), fn ($q) => $q->whereHas('ruangLingkup', fn ($sub) => $sub->whereKey($request->scope_id)))
            ->when($request->filled('province'), fn ($q) => $q->whereHas('ptn', fn ($sub) => $sub->where('province', $request->province)))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('starts_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('starts_at', '<=', $request->to))->get();
        return response()->streamDownload(function () use ($items) { $out = fopen('php://output', 'w'); fputcsv($out, ['PTN', 'Provinsi', 'Kerja Sama', 'Jenis', 'Nomor', 'Tanggal Mulai', 'Tanggal Berakhir', 'Status', 'UPT', 'Ruang Lingkup'], ',', '"', ''); foreach ($items as $item) fputcsv($out, [$item->ptn->name, $item->ptn->province, $item->title, strtoupper($item->agreement_type), $item->document_number, $item->starts_at?->format('Y-m-d'), $item->ends_at?->format('Y-m-d'), $item->status, $item->upt->pluck('name')->implode('; '), $item->ruangLingkup->pluck('name')->implode('; ')], ',', '"', ''); fclose($out); }, 'laporan-kerja-sama.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
