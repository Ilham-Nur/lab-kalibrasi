<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Lab Kalibration') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('template-admin-2026/style.css') }}">
</head>
<body class="auth-page">
    <main class="login-shell">
        <section class="login-panel">
            <div class="login-brand">
                <div class="brand-icon">
                    <i class="bi bi-hexagon-fill"></i>
                </div>
                <div>
                    <h1>Lab Kalibration</h1>
                    <p>Masuk ke sistem manajemen dokumen.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login.process') }}" class="login-form" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        autofocus
                    >
                    @error('username')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Masukkan password"
                    >
                    @error('password')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <label class="form-check login-remember">
                    <input type="checkbox" name="remember" class="check-input" value="1">
                    <span class="check-label">Ingat saya</span>
                </label>

                <button type="submit" class="btn btn-primary login-submit">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk
                </button>
            </form>
        </section>
    </main>
</body>
</html>
