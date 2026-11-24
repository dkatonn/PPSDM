<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\KerjasamaUpt;
use App\Models\RuangLingkup;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UptVerificationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->upt_id, 403, 'Akun ini belum ditautkan ke UPT.');

        $assignments = KerjasamaUpt::query()
            ->with(['kerjasama.ptn', 'kerjasama.dokumen'])
            ->where('upt_id', $request->user()->upt_id)
            ->latest('updated_at')
            ->paginate(15);

        $assignments->getCollection()->each(function (KerjasamaUpt $assignment): void {
            $scopeIds = DB::table('kerjasama_upt_ruang_lingkup')
                ->where('kerjasama_id', $assignment->kerjasama_id)
                ->where('upt_id', $assignment->upt_id)
                ->pluck('ruang_lingkup_id');
            $assignment->setRelation('ruangLingkup', RuangLingkup::whereIn('id', $scopeIds)->orderBy('name')->get());
        });

        return view('upt-portal.index', ['assignments' => $assignments, 'scopes' => RuangLingkup::orderBy('name')->get()]);
    }

    public function update(Request $request, Kerjasama $kerjasama, AuditLogger $audit)
    {
        $uptId = $request->user()->upt_id;
        abort_unless($uptId && DB::table('kerjasama_upt')->where('kerjasama_id', $kerjasama->id)->where('upt_id', $uptId)->exists(), 404);

        $data = $request->validate([
            'verification_status' => 'required|in:active,inactive,rejected',
            'verification_notes' => 'nullable|string|max:2000',
            'ruang_lingkup_ids' => 'nullable|array',
            'ruang_lingkup_ids.'.$kerjasama->id => 'nullable|array|max:3',
            'ruang_lingkup_ids.'.$kerjasama->id.'.*' => 'required|integer|distinct|exists:ruang_lingkup,id',
        ]);

        $scopeIds = array_values(array_unique(array_map('intval', $data['ruang_lingkup_ids'][$kerjasama->id] ?? [])));

        if ($data['verification_status'] === 'active' && count($scopeIds) === 0) {
            return back()->withErrors(['ruang_lingkup_ids' => 'Pilih minimal satu ruang lingkup untuk memverifikasi kerja sama sebagai aktif.']);
        }

        if ($data['verification_status'] === 'active' && ($kerjasama->status !== 'aktif' || ! $kerjasama->hasSignedCopy())) {
            return back()->withErrors(['verification_status' => 'Status Aktif hanya dapat dipilih jika kerja sama pusat berstatus Aktif dan tanggal TTD serta PDF KA/PKS sudah tercatat.']);
        }

        DB::transaction(function () use ($kerjasama, $uptId, $scopeIds, $data, $request): void {
            DB::table('kerjasama_upt_ruang_lingkup')->where('kerjasama_id', $kerjasama->id)->where('upt_id', $uptId)->delete();
            foreach ($scopeIds as $scopeId) {
                DB::table('kerjasama_upt_ruang_lingkup')->insert([
                    'kerjasama_id' => $kerjasama->id,
                    'upt_id' => $uptId,
                    'ruang_lingkup_id' => $scopeId,
                ]);
            }

            $scopeUnion = DB::table('kerjasama_upt_ruang_lingkup')
                ->where('kerjasama_id', $kerjasama->id)
                ->distinct()
                ->pluck('ruang_lingkup_id')
                ->all();
            $kerjasama->ruangLingkup()->sync($scopeUnion);

            DB::table('kerjasama_upt')->where('kerjasama_id', $kerjasama->id)->where('upt_id', $uptId)->update([
                'verification_status' => $data['verification_status'],
                'verification_notes' => $data['verification_notes'] ?? null,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $audit->record('upt.cooperation_verified', $kerjasama, ['upt_id' => $uptId, 'verification_status' => $data['verification_status'], 'ruang_lingkup_ids' => $scopeIds]);

        return back()->with('success', 'Verifikasi kerja sama berhasil disimpan.');
    }
}
