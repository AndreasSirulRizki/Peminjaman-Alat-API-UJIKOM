import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

 Penjelasan register.blade.php

  1. Struktur HTML & Head

  <!DOCTYPE html>
  <html lang="id">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Daftar Akun - Sistem Peminjaman Alat</title>
  </head>

  Standar HTML5. lang="id" = bahasa Indonesia. viewport biar tampilan responsive di HP.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  2. Notifikasi / Alert (Blade Directives)

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

  ┌────────────────────┬────────────────────────────────────────────────────────────────────────┐
  │ Kode               │ Fungsi                                                                 │
  ├────────────────────┼────────────────────────────────────────────────────────────────────────┤
  │ session('success') │ Tampilkan pesan sukses dari redirect (misal setelah register berhasil) │
  ├────────────────────┼────────────────────────────────────────────────────────────────────────┤
  │ session('error')   │ Tampilkan pesan error umum                                             │
  ├────────────────────┼────────────────────────────────────────────────────────────────────────┤
  │ $errors->any()     │ Cek apakah ada error validasi dari Laravel                             │
  ├────────────────────┼────────────────────────────────────────────────────────────────────────┤
  │ $errors->all()     │ Loop semua pesan error validasi sekaligus                              │
  └────────────────────┴────────────────────────────────────────────────────────────────────────┘

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  3. Form Register

  <form action="{{ route('register.post') }}" method="POST">
      @csrf

  - route('register.post') → mengarah ke POST /register di web.php
  - @csrf → wajib ada di setiap form POST di Laravel, untuk proteksi dari serangan CSRF (Cross-Site Request Forgery).
  Laravel akan reject form tanpa token ini.

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  4. Input Field — Per Field

  Nama Lengkap:

  <input type="text" name="name" value="{{ old('name') }}"
      class="{{ $errors->has('name') ? 'err' : '' }}" required autofocus>
  @error('name')
      <div class="msg">{{ $message }}</div>
  @enderror
  - old('name') → kalau validasi gagal & halaman di-reload, nilai yang sudah diketik tidak hilang
  - $errors->has('name') ? 'err' : '' → kasih class CSS merah kalau field ini error
  - @error('name') → tampilkan pesan error spesifik untuk field name
  - autofocus → kursor otomatis langsung ke field ini saat halaman dibuka

  Email:

  <input type="email" name="email" value="{{ old('email') }}" required>
  - type="email" → browser otomatis validasi format email di sisi klien

  No. HP (Opsional):

  <input type="text" name="no_hp" value="{{ old('no_hp') }}" maxlength="15">
  - Tidak ada required → opsional
  - maxlength="15" → batasi panjang input langsung di HTML

  Password:

  <input type="password" name="password" placeholder="Minimal 8 karakter" required>
  - type="password" → input disembunyikan (jadi titik-titik)

  Konfirmasi Password:

  <input type="password" name="password_confirmation" required>
  - Nama field harus password_confirmation (bukan bebas) — ini konvensi Laravel supaya validasi confirmed bisa bekerja
  otomatis

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  5. Tombol & Link Login

  <button type="submit" class="btn">Daftar</button>
  <a href="{{ route('login') }}">Masuk</a>
  - Tombol submit kirim form ke controller
  - Link route('login') → arahkan ke halaman /login

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  Alur Register dari Awal sampai Akhir

  User buka /register
         │
         ▼
  [web.php] Route GET /register (middleware: guest)
         │
         ▼
  AuthController::showRegisterForm()
    → return view('auth.register')
         │
         ▼
  Browser tampilkan form register.blade.php
         │
    User isi form & klik "Daftar"
         │
         ▼
  [web.php] Route POST /register (middleware: guest)
         │
         ▼
  AuthController::register(Request $request)
         │
         ├─ Validasi input:
         │    name      → required, string, max:255
         │    email     → required, email, unique di tabel users
         │    no_hp     → nullable, max:15
         │    password  → required, min:8, confirmed (cocok dengan password_confirmation)
         │
         ├─ GAGAL VALIDASI?
         │    → redirect balik ke form
         │    → $errors terisi pesan error
         │    → old() isi ulang field yang sudah diketik
         │
         └─ VALIDASI SUKSES?
              │
              ▼
         User::create([
             name, email,
             password → Hash::make() (di-hash bcrypt),
             role     → 'peminjam' (otomatis, tidak bisa dipilih user),
             no_hp
         ])
              │
              ▼
         Auth::login($user)        ← auto login setelah register
         $request->session()->regenerate()  ← buat session baru (keamanan)
              │
              ▼
         redirect()->route('peminjam.katalog')
         + flash message: "Registrasi berhasil! Selamat datang, {nama}."
              │
              ▼
         User langsung masuk ke halaman Katalog Alat

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

  Poin Penting Keamanan yang Sudah Ada

  1. @csrf — proteksi CSRF attack
  2. Hash::make() — password tidak disimpan plain text, di-hash pakai bcrypt
  3. unique:users,email — cegah duplikasi akun
  4. middleware('guest') — kalau sudah login, tidak bisa akses /register lagi (akan di-redirect)
  5. session()->regenerate() — cegah session fixation attack
  6. Role di-hardcode 'peminjam' — user tidak bisa daftar sebagai admin/petugas sendiri


  Gunanya sebagai kelas dasar (parent class) untuk controller lain di Laravel.

Dengan begitu, controller seperti UserController bisa mewarisi struktur atau fungsi umum:

class UserController extends Controller
{
    public function index()
    {
        return "Daftar user";
    }
}
abstract berarti Controller tidak dibuat menjadi objek secara langsung; ia hanya menjadi fondasi bagi controller lain. Saat ini kosong, jadi belum memberikan fungsi tambahan, tetapi tetap dipakai sebagai struktur standar Laravel.