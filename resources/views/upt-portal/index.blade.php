<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verifikasi Kerja Sama | SIPENA BARANTIN</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><style>body{background:#f4f7fb;color:#243b53}.top{background:#102a43;color:#fff}.card{border:1px solid #e5ebf2;border-radius:14px}.muted{color:#718096}</style></head>
<body>
<header class="top py-3"><div class="container d-flex justify-content-between align-items-center"><div><div class="fw-bold">SIPENA BARANTIN</div><div class="small text-white-50">Portal verifikasi UPT</div></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Keluar</button></form></div></header>
<main class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="mb-4"><div class="text-primary small fw-bold text-uppercase">{{ auth()->user()->upt?->name ?? 'UPT belum ditentukan' }}</div><h1 class="h3 fw-bold">Verifikasi kerja sama PTN</h1><p class="muted mb-0">Pilih sendiri maksimal tiga ruang lingkup yang dijalankan UPT ini, periksa dokumen, lalu catat status kerja samanya.</p></div>
    @forelse($assignments as $assignment)
        @php($agreement = $assignment->kerjasama)
        @php($selectedScopeIds = collect(old('ruang_lingkup_ids.'.$agreement->id, $assignment->ruangLingkup->pluck('id')->all()))->map(fn($id)=>(int)$id))
        @php($statusLabel = ['pending'=>'Menunggu verifikasi','active'=>'Aktif','inactive'=>'Belum aktif','rejected'=>'Ditolak'][$assignment->verification_status] ?? 'Menunggu verifikasi')
        <section class="card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2"><div><div class="small text-secondary">{{ $agreement->ptn->name }}</div><h2 class="h5 fw-bold mb-1">{{ $agreement->title }}</h2><div class="small text-secondary">{{ strtoupper($agreement->agreement_type) }}{{ $agreement->document_number ? ' · '.$agreement->document_number : '' }} · Tanggal TTD: {{ $agreement->signed_at?->format('d/m/Y') ?: 'Belum dicatat' }}</div></div><span class="badge {{ $assignment->verification_status==='active'?'text-bg-success':($assignment->verification_status==='pending'?'text-bg-warning':'text-bg-secondary') }}">{{ $statusLabel }}</span></div>
            <div class="mt-3"><div class="small fw-semibold mb-1">Dokumen untuk pemeriksaan</div>@forelse($agreement->dokumen as $document)<a class="btn btn-sm btn-outline-primary me-1 mb-1" href="{{ route('upt.portal.documents.download',$document) }}"><i class="bi bi-file-earmark-pdf me-1"></i>{{ strtoupper($document->type) }} · {{ $document->number ?: $document->original_name }}</a>@empty<div class="small text-secondary">Belum ada dokumen yang diunggah.</div>@endforelse</div>
            <form class="border-top mt-3 pt-3 verification-form" method="POST" action="{{ route('upt.portal.verify',$agreement) }}">@csrf
                <div class="small fw-semibold mb-2">Ruang lingkup kerja UPT ini <span class="text-secondary fw-normal">(maksimal 3)</span></div>
                <div class="row g-2 mb-3">@forelse($scopes as $scope)<div class="col-sm-6 col-lg-4"><label class="form-check border rounded-3 p-2 ps-5"><input class="form-check-input scope-choice" type="checkbox" name="ruang_lingkup_ids[{{ $agreement->id }}][]" value="{{ $scope->id }}" @checked($selectedScopeIds->contains($scope->id))><span class="form-check-label">{{ $scope->name }}</span></label></div>@empty<div class="col-12 small text-danger">Belum ada pilihan ruang lingkup. Hubungi Admin PPSDM untuk menambahkannya.</div>@endforelse</div>
                <div class="row g-2 align-items-end"><div class="col-md-3"><label class="form-label small">Hasil verifikasi</label><select name="verification_status" class="form-select" required><option value="" disabled @selected($assignment->verification_status==='pending')>Pilih hasil</option><option value="active" @selected($assignment->verification_status==='active')>Sudah aktif bekerja sama</option><option value="inactive" @selected($assignment->verification_status==='inactive')>Belum aktif bekerja sama</option><option value="rejected" @selected($assignment->verification_status==='rejected')>Tidak sesuai / ditolak</option></select></div><div class="col-md-7"><label class="form-label small">Catatan verifikasi</label><input name="verification_notes" maxlength="2000" class="form-control" value="{{ $assignment->verification_notes }}" placeholder="Catatan atau alasan (opsional)"></div><div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div></div>
                @if($assignment->verified_at)<div class="small text-secondary mt-2">Diverifikasi {{ $assignment->verified_at->format('d/m/Y H:i') }}</div>@endif
            </form>
        </section>
    @empty
        <div class="card p-5 text-center"><i class="bi bi-inbox fs-2 text-secondary"></i><h2 class="h5 mt-3">Belum ada kerja sama untuk diverifikasi</h2><p class="muted mb-0">Kerja sama yang ditugaskan ke UPT ini akan tampil di halaman ini.</p></div>
    @endforelse
    <div class="mt-3">{{ $assignments->links() }}</div>
</main>
<script>document.querySelectorAll('.verification-form').forEach(form=>form.querySelectorAll('.scope-choice').forEach(input=>input.addEventListener('change',()=>{const selected=form.querySelectorAll('.scope-choice:checked');if(selected.length>3){input.checked=false;alert('Maksimal tiga ruang lingkup.')}})));</script>
</body></html>
