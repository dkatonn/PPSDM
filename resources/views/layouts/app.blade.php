<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') |  BARANTIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root{--navy:#102a43;--blue:#1769aa;--ink:#243b53;--muted:#718096;--canvas:#f4f7fb;--line:#e5ebf2}
        body{background:var(--canvas);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;font-size:.94rem}
        .sidebar{position:fixed;inset:0 auto 0 0;width:260px;background:var(--navy);color:#d9e6f2;z-index:1030;display:flex;flex-direction:column}
        .brand{padding:23px 22px 20px;border-bottom:1px solid #ffffff1a;color:#fff}.brand-mark{width:38px;height:38px;border-radius:11px;background:#ffffff18;display:grid;place-items:center;color:#9bd0ff;font-size:1.25rem}.brand-title{font-size:.94rem;font-weight:750;letter-spacing:.05em}.brand-subtitle{font-size:.69rem;color:#afc3d5;margin-top:2px}
        .nav-caption{font-size:.66rem;text-transform:uppercase;letter-spacing:.12em;color:#8da7bd;padding:22px 22px 8px}.side-link{display:flex;align-items:center;gap:12px;color:#c7d6e4;text-decoration:none;padding:10px 14px;margin:2px 12px;border-radius:8px;font-size:.87rem}.side-link i{font-size:1rem;width:19px;text-align:center}.side-link.active,.side-link:hover{background:#ffffff16;color:#fff}.side-link.disabled{opacity:.46;cursor:default}.sidebar-footer{margin-top:auto;padding:16px 20px;border-top:1px solid #ffffff1a;color:#a9bfd2;font-size:.75rem}
        .main{margin-left:260px;min-height:100vh}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 32px}.content{padding:30px 32px;max-width:1500px;margin:auto}.eyebrow{font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;color:var(--blue);font-weight:700}.page-title{font-weight:750;color:var(--navy);letter-spacing:-.025em}.card{border:1px solid var(--line);border-radius:13px;box-shadow:0 3px 12px #102a4308}.stat-card{padding:21px}.stat-label{color:var(--muted);font-size:.82rem}.stat-value{font-size:1.8rem;font-weight:750;color:var(--navy);line-height:1.2}.stat-icon{height:42px;width:42px;border-radius:11px;display:grid;place-items:center;background:#eaf4ff;color:var(--blue);font-size:1.15rem}.section-title{font-size:1rem;font-weight:700;color:var(--navy)}.muted{color:var(--muted)}.dropdown-menu{border-color:var(--line);box-shadow:0 8px 30px #102a4315}.dropdown-item{font-size:.88rem}
        @media(max-width:991.98px){.sidebar{width:78px}.brand{padding:18px 20px}.brand-copy,.nav-caption,.side-link span,.sidebar-footer{display:none}.side-link{justify-content:center;padding:12px 0;margin:3px 10px}.side-link i{width:auto;font-size:1.15rem}.main{margin-left:78px}.topbar{padding:0 20px}.content{padding:24px 20px}}
        @media(max-width:575.98px){.sidebar{width:58px}.brand{padding:14px 10px}.brand-mark{width:36px;height:36px}.main{margin-left:58px}.topbar{height:60px;padding:0 13px}.content{padding:20px 13px}.user-label{display:none}}
        @media print{.sidebar,.topbar,.no-print{display:none!important}.main{margin:0!important}.content{padding:0!important;max-width:none!important}.card{box-shadow:none!important;border:0!important}.table{font-size:10pt}}
    </style>
    @stack('styles')
</head>
<body>
<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="brand text-decoration-none d-flex align-items-center gap-3">
        <span class="brand-mark"><i class="bi bi-shield-check"></i></span>
        <span class="brand-copy"><span class="brand-title d-block"> BARANTIN</span><span class="brand-subtitle d-block">PPSDM · Kerja Sama PTN</span></span>
    </a>
    <nav class="py-2">
        <div class="nav-caption">Menu utama</div>
        <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>
        <div class="nav-caption">Pengelolaan data</div>
        <a class="side-link {{ request()->routeIs('ptn.*') ? 'active' : '' }}" href="{{ route('ptn.index') }}"><i class="bi bi-buildings"></i><span>Data PTN</span></a>
        <a class="side-link {{ request()->routeIs('upt.*') ? 'active' : '' }}" href="{{ route('upt.index') }}"><i class="bi bi-geo-alt"></i><span>Data UPT</span></a>
        <a class="side-link {{ request()->routeIs('scope.*') ? 'active' : '' }}" href="{{ route('scope.index') }}"><i class="bi bi-diagram-3"></i><span>Ruang Lingkup</span></a>
        <a class="side-link {{ request()->routeIs('kerjasama.*') ? 'active' : '' }}" href="{{ route('kerjasama.index') }}"><i class="bi bi-briefcase"></i><span>Kerja Sama</span></a>
        <a class="side-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}"><i class="bi bi-folder2-open"></i><span>Dokumen</span></a>
        <div class="nav-caption">Pemantauan</div>
        <a class="side-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}" href="{{ route('monitoring.index') }}"><i class="bi bi-activity"></i><span>Monitoring</span></a>
        <a class="side-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i><span>Notifikasi</span></a>
        <a class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph"></i><span>Laporan</span></a>
        <a class="side-link" href="{{ route('public.index') }}" target="_blank"><i class="bi bi-globe2"></i><span>Portal Publik</span></a>
        @if(auth()->user()->role === 'super_admin')
            <div class="nav-caption">Administrasi</div>
            <a class="side-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people"></i><span>Manajemen Pengguna</span></a>
        @endif
    </nav>
    <div class="sidebar-footer"><i class="bi bi-lock me-1"></i> Akses internal · PPSDM</div>
</aside>
<div class="main">
    <header class="topbar">
        <div class="small muted"><i class="bi bi-house-door me-1"></i><span class="d-none d-sm-inline"></span><span class="mx-2">/</span><span class="text-dark">@yield('breadcrumb', 'Dashboard')</span></div>
        <div class="d-flex align-items-center gap-2">
        <a class="btn btn-light position-relative" href="{{ route('notifications.index') }}" aria-label="Notifikasi"><i class="bi bi-bell"></i>@php($unread = \App\Models\Notifikasi::where('user_id',auth()->id())->whereNull('read_at')->count())@if($unread)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $unread }}</span>@endif</a>
        <div class="dropdown">
            <button class="btn btn-light border-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="rounded-circle d-grid place-items-center text-primary bg-primary-subtle" style="width:34px;height:34px"><i class="bi bi-person"></i></span>
                <span class="user-label text-start"><span class="d-block small fw-semibold text-dark">{{ auth()->user()->name }}</span><span class="d-block" style="font-size:.7rem;color:#718096">{{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin PPSDM' }}</span></span>
                <i class="bi bi-chevron-down small muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mt-2"><li><span class="dropdown-item-text small muted">{{ auth()->user()->email }}</span></li><li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil saya</a></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button></form></li></ul>
        </div>
        </div>
    </header>
    <main class="content">@yield('content')</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
