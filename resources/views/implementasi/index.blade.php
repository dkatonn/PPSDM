@extends('layouts.app')
@section('title','Implementasi')
@section('breadcrumb','Implementasi')
@section('content')
@include('partials.feedback')
<div class="mb-4"><div class="eyebrow">Pelaksanaan</div><h1 class="page-title h3 mb-1">Implementasi kerja sama</h1><p class="muted">Catat kegiatan nyata oleh UPT dan PTN mitra.</p></div>
<div class="row g-3">
    <div class="col-xl-4"><form method="POST" enctype="multipart/form-data" action="{{ route('implementation.store') }}" class="card p-4">@csrf
        <h2 class="section-title">Catat kegiatan</h2>
        <label class="form-label mt-3">Kerja sama *</label><select name="kerjasama_id" class="form-select" required><option value="">Pilih kerja sama</option>@foreach($agreements as $agreement)<option value="{{ $agreement->id }}" @selected(request('kerjasama_id')==$agreement->id)>{{ $agreement->ptn->name }} · {{ $agreement->title }}</option>@endforeach</select>
        <label class="form-label mt-3">Nama kegiatan *</label><input name="name" required class="form-control">
        <label class="form-label mt-3">UPT pelaksana</label><select name="upt_id" class="form-select"><option value="">Belum ditentukan</option>@foreach($upts as $upt)<option value="{{ $upt->id }}">{{ $upt->name }}</option>@endforeach</select>
        <label class="form-label mt-3">Ruang lingkup</label><select name="ruang_lingkup_id" class="form-select"><option value="">Ikuti ruang lingkup kerja sama</option>@foreach($scopes as $scope)<option value="{{ $scope->id }}">{{ $scope->name }}</option>@endforeach</select>
        <label class="form-label mt-3">Tanggal kegiatan *</label><input name="activity_date" type="date" required class="form-control">
        <label class="form-label mt-3">Status *</label><select name="status" class="form-select"><option value="terencana">Terencana</option><option value="berjalan">Berjalan</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option></select>
        <label class="form-label mt-3">Deskripsi</label><textarea name="description" rows="3" class="form-control"></textarea>
        <label class="form-label mt-3">Dokumentasi (opsional)</label><input name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control"><div class="form-text">PDF atau gambar, maksimal 10 MB.</div>
        <button class="btn btn-primary mt-3">Simpan kegiatan</button>
    </form></div>
    <div class="col-xl-8"><div class="card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Kegiatan</th><th>PTN / UPT</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($items as $item)<tr><td class="ps-3"><strong>{{ $item->name }}</strong><div class="small muted">{{ Str::limit($item->description,90) }} · {{ $item->ruangLingkup?->name ?: '—' }}</div>@foreach($item->dokumen as $doc)<a class="small" href="{{ route('implementation.documents.download',$doc) }}">Lampiran {{ $loop->iteration }}</a>@endforeach</td><td>{{ $item->kerjasama->ptn->name }}<div class="small muted">{{ $item->upt?->name ?: '—' }}</div></td><td>{{ $item->activity_date->format('d/m/Y') }}</td><td><span class="badge text-bg-light border">{{ ucfirst($item->status) }}</span></td><td><form method="POST" action="{{ route('implementation.destroy',$item) }}" onsubmit="return confirm('Hapus kegiatan ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>@empty<tr><td colspan="5" class="text-center py-5 muted">Belum ada kegiatan implementasi.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $items->links() }}</div></div></div>
</div>
@endsection
