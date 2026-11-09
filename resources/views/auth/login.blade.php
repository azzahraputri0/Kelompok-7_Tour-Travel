<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Travel Tour</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/login.css') }}">
</head>
<body>
<div class="login-wrap">
    <section class="login-kiri">
        <div class="brand" style="margin:0">Travel<span>Tour</span></div>
        <div>
            <h2>Kelola jadwal, pelanggan, dan laporan perjalanan dalam satu tempat.</h2>
            <p>Masuk sesuai peran Anda: Staff, Manager, atau Driver.</p>
        </div>
        <small style="opacity:.75">© {{ date('Y') }} Travel Tour</small>
    </section>

    <section class="login-kanan">
        <form class="login-form" method="post" action="{{ url('/login') }}" novalidate>
            @csrf
            <div>
                <h1>Masuk</h1>
                <p style="margin:4px 0 0;color:var(--teks-2)">Gunakan email dan password akun Anda.</p>
            </div>

            @if ($errors->any())
                <div class="alert gagal" role="alert" style="margin:0">{{ $errors->first() }}</div>
            @endif

            <div class="field">
                <label for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn">Masuk</button>

            @unless (app()->environment('production'))
                <div class="demo">
                    <p>Akun Demo (password: password123)</p>
                    <div>
                        @foreach (['Staff' => 'staff@traveltour.test', 'Manager' => 'manager@traveltour.test', 'Driver' => 'driver@traveltour.test'] as $peran => $email)
                            <button type="button" class="btn garis kecil" data-email="{{ $email }}">{{ $peran }}</button>
                        @endforeach
                    </div>
                </div>
            @endunless
        </form>
    </section>
</div>
<script>
    document.querySelectorAll('[data-email]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('email').value = b.dataset.email;
            document.getElementById('password').value = 'password123';
            document.getElementById('password').focus();
        });
    });
</script>
</body>
</html>
