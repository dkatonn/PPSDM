<?php

namespace App\Http\Controllers;

use App\Models\Kerjasama;
use App\Models\Ptn;
use App\Models\Upt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\CarbonInterface;

class PublicPortalController extends Controller
{
    public function index(Request $request): View
    {
        $visibleIds = Kerjasama::query()->whereIn('status', ['aktif', 'berakhir'])->verifiedByUpt()->select('ptn_id')->distinct();

        $query = Ptn::query()->whereIn('id', $visibleIds)
            ->with(['kerjasamas' => fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->verifiedByUpt()->with(['ruangLingkup', 'uptAssignments' => fn ($assignments) => $assignments->where('verification_status', 'active')->with('upt'), 'dokumen' => fn ($docs) => $docs->where('is_public', true)])->when($request->filled('type'), fn ($q) => $q->where('agreement_type', $request->query('type')))])
            ->withCount(['kerjasamas as public_agreements_count' => fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->verifiedByUpt()->when($request->filled('type'), fn ($q) => $q->where('agreement_type', $request->query('type')))]);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('province', 'like', "%{$search}%"));
        }
        if ($province = $request->query('province')) {
            $query->where('province', $province);
        }
        if ($type = $request->query('type')) {
            $query->whereHas('kerjasamas', fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->verifiedByUpt()->where('agreement_type', $type));
        }

        $ptns = $query->orderBy('name')->paginate(12)->withQueryString();
        $ptns->getCollection()->each(function (Ptn $ptn): void {
            $ptn->kerjasamas->each(function (Kerjasama $agreement): void {
                $agreement->uptAssignments->each(fn ($assignment) => $this->loadAssignmentScopes($assignment));
                $this->addPublicDates($agreement);
            });
        });
        $publicUptsQuery = Upt::query()->whereHas('kerjasamas', fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->where('kerjasama_upt.verification_status', 'active'))
            ->with(['kerjasamas' => fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->where('kerjasama_upt.verification_status', 'active')->with('ptn')->orderByDesc('starts_at')])
            ->when($request->filled('upt_q'), function ($query) use ($request) {
                $term = trim((string) $request->query('upt_q'));
                $query->where(function ($match) use ($term) {
                    $match->where('name', 'like', "%{$term}%")
                        ->orWhere('province', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhereHas('kerjasamas', fn ($agreements) => $agreements->whereIn('status', ['aktif', 'berakhir'])->where('kerjasama_upt.verification_status', 'active')->whereHas('ptn', fn ($ptns) => $ptns->where('name', 'like', "%{$term}%")));
                });
            })
            ->orderBy('name');
        $publicUpts = $publicUptsQuery->get();
        $provinceCounts = Ptn::query()->whereIn('id', $visibleIds)->selectRaw('province, count(*) as total')->groupBy('province')->orderByDesc('total')->get();

        return view('public.index', [
            'ptns' => $ptns,
            'publicUpts' => $publicUpts,
            'provinces' => Ptn::query()->whereIn('id', $visibleIds)->select('province')->distinct()->orderBy('province')->pluck('province'),
            'selectedProvince' => $request->query('province'),
            'selectedType' => $request->query('type'),
            'uptSearch' => $request->query('upt_q'),
            'totalPtn' => Ptn::query()->whereIn('id', $visibleIds)->count(),
            'activeAgreements' => Kerjasama::query()->where('status', 'aktif')->withSignedCopy()->verifiedByUpt()->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))->count(),
            'operationalUpt' => Upt::query()->where('status', 'operasional')->count(),
            'nonOperationalUpt' => Upt::query()->where('status', 'non_operasional')->count(),
            'provinceCounts' => $provinceCounts,
            'mapUpts' => Upt::query()->whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get(['id', 'name', 'province', 'city', 'work_area', 'status', 'latitude', 'longitude']),
        ]);
    }

    public function show(Ptn $ptn): View
    {
        $ptn->load(['kerjasamas' => fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->verifiedByUpt()->with(['ruangLingkup', 'uptAssignments' => fn ($assignments) => $assignments->where('verification_status', 'active')->with('upt'), 'dokumen' => fn ($docs) => $docs->where('is_public', true)])->orderByDesc('starts_at')]);
        abort_if($ptn->kerjasamas->isEmpty(), 404);
        $ptn->kerjasamas->each(function (Kerjasama $agreement): void {
            $agreement->uptAssignments->each(fn ($assignment) => $this->loadAssignmentScopes($assignment));
            $this->addPublicDates($agreement);
        });

        return view('public.show', compact('ptn'));
    }

    public function showUpt(Upt $upt): View
    {
        $upt->load(['kerjasamas' => fn ($q) => $q->whereIn('status', ['aktif', 'berakhir'])->where('kerjasama_upt.verification_status', 'active')->with(['ptn', 'uptAssignments' => fn ($assignments) => $assignments->where('upt_id', $upt->id)->where('verification_status', 'active'), 'dokumen' => fn ($docs) => $docs->where('is_public', true)])->orderByDesc('starts_at')]);
        abort_if($upt->kerjasamas->isEmpty(), 404);
        $upt->kerjasamas->each(function (Kerjasama $agreement) use ($upt): void {
            $assignment = $agreement->uptAssignments->first();
            if ($assignment) $this->loadAssignmentScopes($assignment);
            $agreement->setRelation('publicUptAssignment', $assignment);
            $this->addPublicDates($agreement);
        });

        return view('public.upt-show', compact('upt'));
    }

    private function addPublicDates(Kerjasama $agreement): void
    {
        $startsAt = $agreement->starts_at;
        $endsAt = $agreement->ends_at;

        $status = $agreement->status === 'berakhir' || ($endsAt && $endsAt->lt(today()))
            ? 'Berakhir'
            : (($startsAt && $startsAt->gt(today())) ? 'Akan datang' : 'Aktif');

        $agreement->setAttribute('public_status', $status);
        $agreement->setAttribute('duration_label', $startsAt && $endsAt ? $this->intervalLabel($startsAt->diff($endsAt)) : 'Masa berlaku belum lengkap');

        if (! $endsAt) {
            $remaining = 'Tanggal akhir belum dicatat';
        } elseif ($endsAt->lt(today())) {
            $remaining = 'Berakhir '.$this->intervalLabel($endsAt->diff(today())).' lalu';
        } elseif ($endsAt->isToday()) {
            $remaining = 'Berakhir hari ini';
        } else {
            $remaining = 'Tersisa '.$this->intervalLabel(today()->diff($endsAt));
        }

        $agreement->setAttribute('remaining_label', $remaining);
        $missing = [];
        if (! $agreement->signed_at) $missing[] = 'Tanggal tanda tangan belum dicatat';
        if (! $agreement->dokumen()->whereIn('type', ['ka', 'pks'])->exists()) $missing[] = 'Dokumen KA/PKS belum diunggah';
        if (! $startsAt || ! $endsAt) $missing[] = 'Periode kerja sama belum lengkap';
        $agreement->setAttribute('administration_missing', $missing);
        $documentsAvailable = $agreement->hasSignedCopy();
        $agreement->setAttribute('public_documents_available', $documentsAvailable);
        if (! $documentsAvailable) $agreement->setRelation('dokumen', collect());
    }

    private function intervalLabel(\DateInterval $interval): string
    {
        $parts = [];
        if ($interval->y) $parts[] = $interval->y.' tahun';
        if ($interval->m) $parts[] = $interval->m.' bulan';
        if ($interval->d) $parts[] = $interval->d.' hari';
        return $parts ? implode(' ', $parts) : 'kurang dari 1 hari';
    }

    private function loadAssignmentScopes($assignment): void
    {
        $scopeIds = \Illuminate\Support\Facades\DB::table('kerjasama_upt_ruang_lingkup')
            ->where('kerjasama_id', $assignment->kerjasama_id)
            ->where('upt_id', $assignment->upt_id)
            ->pluck('ruang_lingkup_id');
        $assignment->setRelation('ruangLingkup', \App\Models\RuangLingkup::whereIn('id', $scopeIds)->orderBy('name')->get());
    }
}
