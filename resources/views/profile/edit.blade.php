@extends('layouts.app')
@section('title','Profil Saya')
@section('breadcrumb','Profil')
@section('content')
<div class="mb-4"><div class="eyebrow">Akun saya</div><h1 class="page-title h3">Profil pengguna</h1><p class="muted">Perbarui nama, email, atau kata sandi akun.</p></div>
@include('partials.feedback')
<form method="POST" action="{{ route('profile.update') }}" class="card p-4" style="max-width:760px">@csrf @method('PUT')
<label class="form-label">Nama *</label><input name="name" class="form-control mb-3" required value="{{ old('name',$user->name) }}">
<label class="form-label">Email *</label><input type="email" name="email" class="form-control mb-3" required value="{{ old('email',$user->email) }}">
<hr><div class="small muted mb-3">Biarkan bagian kata sandi kosong jika tidak ingin menggantinya.</div>
<label class="form-label">Kata sandi saat ini</label><input type="password" name="current_password" class="form-control mb-3" autocomplete="current-password">
<label class="form-label">Kata sandi baru (minimal 12 karakter)</label><input type="password" name="password" class="form-control mb-3" autocomplete="new-password">
<label class="form-label">Ulangi kata sandi baru</label><input type="password" name="password_confirmation" class="form-control mb-4" autocomplete="new-password">
<button class="btn btn-primary align-self-start">Simpan profil</button></form>
@endsection
