@extends('layouts.app')
@section('title','Notifikasi')
@section('breadcrumb','Notifikasi')
@section('content')
<div class="mb-4"><div class="eyebrow">Pengingat</div><h1 class="page-title h3">Notifikasi kerja sama</h1><p class="muted">Pengingat dibuat otomatis {{ config('sipena.reminder_months') }} bulan sebelum tanggal kerja sama berakhir.</p></div>
<div class="card overflow-hidden"><div class="list-group list-group-flush">
@forelse($items as $item)<div class="list-group-item p-3 {{ $item->read_at?'':'bg-primary-subtle' }}"><div class="d-flex justify-content-between gap-3"><div><div class="fw-semibold">{{ $item->title }}</div><div class="small mt-1">{{ $item->message }}</div><div class="small text-secondary mt-2">Pengingat: {{ $item->reminder_on->format('d/m/Y') }} · {{ $item->created_at->diffForHumans() }}</div></div>@if(!$item->read_at)<form method="POST" action="{{ route('notifications.read',$item) }}">@csrf<button class="btn btn-sm btn-outline-primary">Lihat kerja sama</button></form>@else<a class="btn btn-sm btn-light" href="{{ route('kerjasama.show',$item->kerjasama_id) }}">Buka</a>@endif</div></div>
@empty<div class="text-center py-5 text-secondary"><i class="bi bi-bell fs-2"></i><p class="mb-0 mt-2">Belum ada notifikasi.</p></div>@endforelse
</div><div class="p-3">{{ $items->links() }}</div></div>
@endsection
