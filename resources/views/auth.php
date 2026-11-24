
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login |  BARANTIN</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #eef3f8;
        }

        .login-card {
            width: 420px;
            max-width: 92%;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 45px rgba(20, 50, 80, .08);
        }

        .brand {
            color: #174477;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .subtitle {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin: 10px 0 30px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin: 18px 0 8px;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
        }

        input:focus {
            border-color: #174477;
            box-shadow: 0 0 0 3px rgba(23, 68, 119, .1);
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #174477;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: #10345c;
        }

        .error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 7px;
        }

        .footer {
            text-align: center;
            margin-top: 28px;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.7;
        }
    </style>
</head>

<body>
    <main class="login-card">

        <div class="brand"> BARANTIN</div>

        <div class="subtitle">
            Sistem Informasi Pemantauan Kerja Sama Perguruan Tinggi
            <br>
            Silakan masuk menggunakan akun yang telah terdaftar.
        </div>

        <form method="POST" action="{{ route('login.authenticate') }}">
            @csrf

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email"
                autocomplete="username"
                required
                autofocus
            >

            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
            >

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <label style="font-weight: normal;">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    style="width: auto;"
                >
                Ingat saya
            </label>

            <button type="submit">Masuk ke Sistem</button>
        </form>

        <div class="footer">
            Badan Karantina Indonesia
            <br>
            Akses terbatas untuk pengguna berwenang
        </div>

    </main>
</body>
</html>
