<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Peminjaman Alat</title>
</head>
<body>

<div class="wrap">
    <h2>Daftar Akun</h2>
    <p class="sub">Buat akun untuk meminjam alat</p>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">
            <strong>Periksa kembali isian berikut:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Nama lengkap Anda"
                class="{{ $errors->has('name') ? 'err' : '' }}"
                required
                autofocus>
            @error('name')
                <div class="msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="contoh@email.com"
                class="{{ $errors->has('email') ? 'err' : '' }}"
                required>
            @error('email')
                <div class="msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>
                No. HP
                <span class="note">(opsional)</span>
            </label>
            <input
                type="text"
                name="no_hp"
                value="{{ old('no_hp') }}"
                placeholder="08xxxxxxxxxx"
                maxlength="15"
                class="{{ $errors->has('no_hp') ? 'err' : '' }}">
            @error('no_hp')
                <div class="msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Password <span class="req">*</span></label>
            <input
                type="password"
                name="password"
                placeholder="Minimal 8 karakter"
                class="{{ $errors->has('password') ? 'err' : '' }}"
                required>
            @error('password')
                <div class="msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Ulangi Password <span class="req">*</span></label>
            <input
                type="password"
                name="password_confirmation"
                placeholder="Tulis ulang password"
                required>
        </div>

        <button type="submit" class="btn">Daftar</button>
    </form>

    <hr>

    <p class="footer-link">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
    </p>
</div>
<style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f2f4f7;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }

        .wrap {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 32px 28px;
            width: 100%;
            max-width: 440px;
        }

        .wrap h2 {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            text-align: center;
            margin-bottom: 4px;
        }

        .wrap p.sub {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
            color: #1f2937;
            background: #fff;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .form-group input.err {
            border-color: #ef4444;
        }

        .form-group .msg {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
        }

        .form-group .note {
            font-size: 11px;
            color: #9ca3af;
            font-weight: normal;
        }

        .req {
            color: #ef4444;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .alert.success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert.error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .alert ul {
            margin-top: 6px;
            padding-left: 18px;
        }

        .alert ul li {
            margin-top: 2px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #2563eb;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }

        .footer-link {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }

        .footer-link a {
            color: #2563eb;
            text-decoration: none;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>
