@extends('layouts.app')
@section('title','Dokumen Kerja Sama')
@section('breadcrumb','Dokumen')
@section('content')
<div class="mb-4"><div class="eyebrow">Arsip dokumen</div><h1 class="page-title h3">Dokumen kerja sama</h1><p class="muted">Dokumen dengan visibilitas publik bisa dilihat dari halaman utama dan detail PTN setelah UPT memverifikasi kerja samanya.</p></div>
@include('partials.feedback')
<div class="card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Dokumen</th><th>PTN / Kerja sama</th><th>Tanggal</th><th>Ukuran</th><th>Visibilitas</th><th class="text-end pe-3">Aksi</th></tr></thead><tbody>
@forelse($items as $doc)
<tr><td class="ps-3"><span class="badge text-bg-danger me-1">PDF</span><strong>{{ strtoupper($doc->type) }}</strong><div class="small muted">{{ $doc->number ?: $doc->original_name }}</div><div class="small muted">{{ $doc->original_name }}</div></td><td>{{ $doc->kerjasama->ptn->name }}<div class="small muted">{{ $doc->kerjasama->title }}</div></td><td>{{ $doc->document_date?->format('d/m/Y') ?: '—' }}</td><td>{{ number_format($doc->file_size/1024,0,',','.') }} KB</td><td><span class="badge {{ $doc->is_public?'text-bg-success':'text-bg-secondary' }}">{{ $doc->is_public?'Portal publik':'Internal' }}</span></td><td class="text-end pe-3"><form class="d-inline" method="POST" action="{{ route('documents.visibility',$doc) }}">@csrf @method('PATCH')<input type="hidden" name="is_public" value="{{ $doc->is_public?0:1 }}"><button class="btn btn-sm {{ $doc->is_public?'btn-outline-secondary':'btn-outline-success' }}">{{ $doc->is_public?'Jadikan internal':'Tampilkan publik' }}</button></form> <a href="{{ route('documents.download',$doc) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Unduh</a></td></tr>
@empty<tr><td colspan="6" class="text-center py-5 muted">Belum ada dokumen.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $items->links() }}</div></div>
@endsection
