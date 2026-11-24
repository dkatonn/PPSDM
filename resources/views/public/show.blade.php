<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $ptn->name }} |  BARANTIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body{background:#f5f8fb;color:#243b53;font-family:Inter,"Segoe UI",Arial,sans-serif}
        .top{background:#102a43;color:white}.panel{background:#fff;border:1px solid #e5ebf2;border-radius:14px}
        .doc-card{background:#f8fafc;border:1px solid #e8eef4;border-radius:10px}
        .pdf-preview{width:100%;height:min(72vh,760px);min-height:480px;border:1px solid #dce5ed;border-radius:8px;background:#e9eef3}
        summary{cursor:pointer;list-style:none}summary::-webkit-details-marker{display:none}
        @media(max-width:575px){.pdf-preview{height:65vh;min-height:380px}}
    </style>
</head>
<body>
<header class="top py-3"><div class="container"><a href="{{ route('public.index') }}" class="text-white text-decoration-none"><i class="bi bi-arrow-left me-2"></i>Kembali ke direktori SIPENA</a></div></header>
<main class="container py-5">
    <div class="text-primary small fw-bold text-uppercase mb-2">Perguruan tinggi mitra</div>
    <h1 class="h2 fw-bold">{{ $ptn->name }}</h1>
    <p class="text-secondary">{{ $ptn->type }}{{ $ptn->city ? ' · '.$ptn->city : '' }}{{ $ptn->province ? ' · '.$ptn->province : '' }}</p>
    @if($ptn->website)<p><a href="{{ $ptn->website }}" target="_blank" rel="noopener">{{ $ptn->website }}</a></p>@endif

    <div class="panel p-4 mt-4">
        <h2 class="h5 fw-bold mb-3">Riwayat kerja sama</h2>
        @foreach($ptn->kerjasamas as $item)
            <article class="border rounded-3 p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <h3 class="h6 fw-bold mb-1">{{ $item->title }}</h3>
                        <span class="small text-secondary">{{ strtoupper($item->agreement_type) }}{{ $item->document_number ? ' · '.$item->document_number : '' }}</span>
                    </div>
                    <span class="badge {{ $item->public_status==='Aktif'?'text-bg-success':($item->public_status==='Akan datang'?'text-bg-primary':'text-bg-secondary') }}">{{ $item->public_status }}</span>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-sm-4"><div class="small text-secondary">Tanggal mulai</div><div class="fw-semibold">{{ $item->starts_at?->format('d/m/Y') ?: 'Belum dicatat' }}</div></div>
                    <div class="col-sm-4"><div class="small text-secondary">Tanggal berakhir</div><div class="fw-semibold">{{ $item->ends_at?->format('d/m/Y') ?: 'Tidak ditentukan' }}</div></div>
                    <div class="col-sm-4"><div class="small text-secondary">Lama kerja sama</div><div class="fw-semibold">{{ $item->duration_label }}</div></div>
                </div>
                <div class="alert alert-light border mt-3 mb-2 py-2"><i class="bi bi-hourglass-split me-1"></i>{{ $item->remaining_label }}</div>
                @if($item->administration_missing)<div class="alert alert-warning"><strong><i class="bi bi-exclamation-triangle me-1"></i>Administrasi belum lengkap.</strong> Beberapa dokumen atau informasi kerja sama masih perlu dilengkapi: {{ implode(', ', $item->administration_missing) }}.</div>@endif
                @if($item->notes)<div class="border rounded-3 bg-light p-3 mb-3"><div class="small fw-bold text-secondary mb-1">Keterangan kerja sama</div><div>{{ $item->notes }}</div></div>@endif
                <section class="mt-3"><h4 class="h6 fw-bold mb-2">UPT dan ruang lingkup terverifikasi</h4>@foreach($item->uptAssignments as $assignment)<div class="border rounded-3 p-3 mb-2"><div class="fw-semibold">{{ $assignment->upt->name }}</div><div class="small text-secondary">{{ $assignment->upt->city ? $assignment->upt->city.', ' : '' }}{{ $assignment->upt->province }}</div><div class="d-flex flex-wrap gap-2 mt-2">@forelse($assignment->ruangLingkup as $scope)<span class="badge rounded-pill text-bg-light border">{{ $scope->name }}</span>@empty<span class="small text-secondary">Ruang lingkup belum dicatat</span>@endforelse</div></div>@endforeach</section>

                <section class="mt-4" aria-label="Dokumen kerja sama publik">
                    <h4 class="h6 fw-bold mb-2">Dokumen kerja sama</h4>
                    @forelse($item->dokumen as $document)
                        @php($previewId = 'document-preview-'.$document->id)
                        <details class="doc-card p-3 mb-2">
                            <summary class="d-flex align-items-center justify-content-between gap-3">
                                <span class="d-flex align-items-start gap-2">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                                    <span><span class="d-block fw-semibold">{{ strtoupper($document->type) }}{{ $document->number ? ' · '.$document->number : '' }}</span><span class="d-block small text-secondary">{{ $document->original_name }} · {{ $document->document_date?->format('d/m/Y') ?: 'Tanggal dokumen tidak dicatat' }}</span></span>
                                </span>
                                <span class="btn btn-sm btn-outline-primary flex-shrink-0"><i class="bi bi-eye me-1"></i>Lihat PDF</span>
                            </summary>
                            <div class="mt-3">
                                <iframe id="{{ $previewId }}" class="pdf-preview" loading="lazy" title="Pratinjau {{ $document->original_name }}" src="{{ route('public.documents.download',$document) }}#toolbar=0&navpanes=0"></iframe>
                                <div class="small text-secondary mt-2">Pratinjau hanya untuk dilihat. <a href="{{ route('public.documents.download',$document) }}" target="_blank" rel="noopener">Buka PDF di tab baru</a>.</div>
                            </div>
                        </details>
                    @empty
                        <div class="small text-secondary bg-light border rounded-3 p-3">Belum ada dokumen yang disetujui untuk ditampilkan ke publik.</div>
                    @endforelse
                </section>
            </article>
        @endforeach
    </div>
</main>
<footer class="text-center small text-secondary py-4">Data publik bersumber dari sistem SIPENA BARANTIN.</footer>
</body>
</html>
