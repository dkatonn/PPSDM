<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $upt->name }} | BARANTIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body{background:#f5f8fb;color:#243b53;font-family:Inter,"Segoe UI",Arial,sans-serif}.top{background:#102a43;color:white}.panel{background:#fff;border:1px solid #e5ebf2;border-radius:14px}.doc-card{background:#f8fafc;border:1px solid #e8eef4;border-radius:10px}.pdf-preview{width:100%;height:min(65vh,700px);min-height:400px;border:1px solid #dce5ed;border-radius:8px;background:#e9eef3}summary{cursor:pointer;list-style:none}summary::-webkit-details-marker{display:none}</style>
</head>
<body>
<header class="top py-3"><div class="container"><a href="{{ route('public.index') }}#direktori-upt" class="text-white text-decoration-none"><i class="bi bi-arrow-left me-2"></i>Kembali ke direktori SIPENA</a></div></header>
<main class="container py-5">
    <div class="text-primary small fw-bold text-uppercase mb-2">Unit pelaksana teknis mitra</div>
    <h1 class="h2 fw-bold">{{ $upt->name }}</h1>
    <p class="text-secondary">{{ $upt->type ?: 'UPT BARANTIN' }}{{ $upt->city ? ' · '.$upt->city : '' }}{{ $upt->province ? ' · '.$upt->province : '' }}</p>
    @if($upt->work_area)<p class="text-secondary">Wilayah kerja: {{ $upt->work_area }}</p>@endif
    <div class="panel p-4 mt-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-end mb-3"><div><h2 class="h5 fw-bold mb-1">PTN yang bekerja sama dengan UPT ini</h2><p class="small text-secondary mb-0">Kerja sama ditampilkan setelah diverifikasi aktif oleh UPT.</p></div><span class="badge text-bg-success">{{ $upt->kerjasamas->count() }} kerja sama terverifikasi</span></div>
        @foreach($upt->kerjasamas as $item)
            @php($assignment = $item->publicUptAssignment)
            <article class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2"><div><div class="small text-primary fw-semibold">PTN MITRA</div><h3 class="h6 fw-bold mb-1"><a href="{{ route('public.ptn.show', $item->ptn) }}" class="text-decoration-none">{{ $item->ptn->name }}</a></h3><div class="fw-semibold">{{ strtoupper($item->agreement_type) }} · {{ $item->title }}</div><span class="small text-secondary">{{ $item->document_number ?: 'Nomor belum dicatat' }}</span></div><span class="badge {{ $item->public_status==='Aktif'?'text-bg-success':($item->public_status==='Akan datang'?'text-bg-primary':'text-bg-secondary') }} align-self-start">{{ $item->public_status }}</span></div>
                <div class="row g-3 mt-1"><div class="col-sm-4"><div class="small text-secondary">Tanggal mulai</div><div class="fw-semibold">{{ $item->starts_at?->format('d/m/Y') ?: 'Belum dicatat' }}</div></div><div class="col-sm-4"><div class="small text-secondary">Tanggal berakhir</div><div class="fw-semibold">{{ $item->ends_at?->format('d/m/Y') ?: 'Tidak ditentukan' }}</div></div><div class="col-sm-4"><div class="small text-secondary">Lama kerja sama</div><div class="fw-semibold">{{ $item->duration_label }}</div></div></div>
                <div class="alert alert-light border mt-3 mb-3 py-2"><i class="bi bi-hourglass-split me-1"></i>{{ $item->remaining_label }}</div>
                @if($item->administration_missing)<div class="alert alert-warning"><strong><i class="bi bi-exclamation-triangle me-1"></i>Administrasi belum lengkap.</strong> {{ implode(', ', $item->administration_missing) }}.</div>@endif
                @if($item->notes)<div class="border rounded-3 bg-light p-3 mb-3"><div class="small fw-bold text-secondary mb-1">Keterangan kerja sama</div><div>{{ $item->notes }}</div></div>@endif
                <section><h4 class="h6 fw-bold mb-2">Ruang lingkup yang ditetapkan UPT</h4><div class="d-flex flex-wrap gap-2">@forelse($assignment?->ruangLingkup ?? collect() as $scope)<span class="badge rounded-pill text-bg-light border">{{ $scope->name }}</span>@empty<span class="small text-secondary">Belum ada ruang lingkup yang dicatat.</span>@endforelse</div></section>
                <section class="mt-4"><h4 class="h6 fw-bold mb-2">Dokumen kerja sama publik</h4>@forelse($item->dokumen as $document)<details class="doc-card p-3 mb-2"><summary class="d-flex justify-content-between align-items-center gap-2"><span><i class="bi bi-file-earmark-pdf text-danger me-2"></i><strong>{{ strtoupper($document->type) }}{{ $document->number ? ' · '.$document->number : '' }}</strong><span class="d-block small text-secondary ms-4">{{ $document->original_name }} · {{ $document->document_date?->format('d/m/Y') ?: 'Tanggal tidak dicatat' }}</span></span><span class="btn btn-sm btn-outline-primary flex-shrink-0"><i class="bi bi-eye me-1"></i>Lihat</span></summary><div class="mt-3"><iframe class="pdf-preview" loading="lazy" title="Pratinjau {{ $document->original_name }}" src="{{ route('public.documents.download',$document) }}#toolbar=0&navpanes=0"></iframe><div class="small text-secondary mt-2">Pratinjau dokumen. <a href="{{ route('public.documents.download',$document) }}" target="_blank" rel="noopener">Buka PDF di tab baru</a>.</div></div></details>@empty<div class="small text-secondary bg-light border rounded-3 p-3">Belum ada dokumen yang disetujui untuk ditampilkan ke publik.</div>@endforelse</section>
            </article>
        @endforeach
    </div>
</main>
<footer class="text-center small text-secondary py-4">Data publik bersumber dari sistem SIPENA BARANTIN.</footer>
</body>
</html>
