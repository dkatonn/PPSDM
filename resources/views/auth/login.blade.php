<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | PPSDM BARANTIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#edf4fb 0%,#f8fafc 55%,#e5eff8 100%);font-family:Inter,"Segoe UI",Arial,sans-serif;color:#243b53}.login-wrap{min-height:100vh;display:grid;place-items:center;padding:24px}.login-card{width:100%;max-width:440px;background:#fff;border:1px solid #e3ebf2;border-radius:18px;box-shadow:0 18px 55px #102a4312;padding:38px}.brand-icon{height:48px;width:48px;display:grid;place-items:center;background:#eaf4ff;color:#1769aa;border-radius:14px;font-size:1.45rem}.login-title{font-size:1.55rem;color:#102a43;font-weight:750;letter-spacing:-.03em}.form-control{padding:.78rem .9rem;border-color:#d6e0e9}.form-control:focus{border-color:#5d9ed7;box-shadow:0 0 0 .2rem #1769aa1a}.btn-primary{background:#1769aa;border-color:#1769aa;padding:.78rem;font-weight:650}.btn-primary:hover{background:#12548b}.small-muted{color:#718096;font-size:.86rem;line-height:1.6}.footer{font-size:.75rem;color:#8293a3}
    </style>
</head>
<body>
<main class="login-wrap"><section class="login-card">
    <div class="d-flex align-items-center gap-3 mb-4"><span class="brand-icon"><i class="bi bi-shield-check"></i></span><div><div class="fw-bold" style="letter-spacing:.04em;color:#102a43"> BARANTIN</div><div class="small-muted" style="font-size:.75rem">PPSDM · Badan Karantina Indonesia</div></div></div>
    <h1 class="login-title mb-2">Selamat datang</h1>
    <p class="small-muted mb-4">Masuk menggunakan akun internal yang telah diberikan oleh Super Admin.</p>
    @if($errors->any())<div class="alert alert-danger py-2 small" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.authenticate') }}">
        @csrf
        <div class="mb-3"><label for="email" class="form-label fw-semibold small">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@barantin.go.id" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label for="password" class="form-label fw-semibold small">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required></div>
        <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="remember" name="remember" value="1"><label class="form-check-label small text-secondary" for="remember">Ingat saya di perangkat ini</label></div>
        <button class="btn btn-primary w-100" type="submit">Masuk ke <i class="bi bi-arrow-right ms-1"></i></button>
    </form>
    <div class="border-top mt-4 pt-3 text-center footer"><i class="bi bi-lock me-1"></i>Aplikasi internal · Akses terbatas untuk pengguna berwenang</div>
</section></main>
</body>
</html>
