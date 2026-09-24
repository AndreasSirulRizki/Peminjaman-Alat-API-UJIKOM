<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PeminjamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Utama (Landing Page)
Route::get('/', function () {
    return view('welcome');
});

// ============================================================
// ROUTE SEMENTARA: Salin foto ke public/foto/ + update DB
// HAPUS SETELAH FOTO BERHASIL MUNCUL!
// ============================================================
Route::get('/setup-foto', function () {
    $sumber   = base_path('foto');
    $tujuan   = public_path('foto');
    $ekstensi = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // --- STEP 1: Salin file ke public/foto/ ---
    if (!is_dir($sumber)) {
        return '<h2 style="color:red">ERROR: Folder backend/foto/ tidak ditemukan!</h2>';
    }
    if (!is_dir($tujuan)) {
        mkdir($tujuan, 0775, true);
    }

    $fileDisalin = [];
    foreach (new DirectoryIterator($sumber) as $file) {
        if ($file->isDot() || $file->isDir()) continue;
        if (!in_array(strtolower($file->getExtension()), $ekstensi)) continue;
        $src  = $sumber . '/' . $file->getFilename();
        $dest = $tujuan . '/' . $file->getFilename();
        copy($src, $dest);
        $fileDisalin[] = $file->getFilename();
    }

    // --- STEP 2: Update kolom gambar di database ---
    // Mapping: kata kunci nama alat => nama file fisik
    $mapping = [
        'Mikrotik'  => 'Router Mikrotik RB941-2nD.jpg',
        'Canon'     => 'Kamera DSLR Canon EOS 3000D.jpg',
        'Mini PC'   => 'MIni PC.jpg',
        'Tang'      => 'Tang.jpg',
        'Adapter'   => 'ADAPTER HDMI TO VGA support VGA 1080p with audio.jpg',
        'Iphone'    => 'Iphone 11 pro max.jpg',
        'iPhone'    => 'Iphone 11 pro max.jpg',
    ];

    $dbUpdated = [];
    foreach ($mapping as $keyword => $namaFile) {
        $count = \App\Models\Alat::where('nama_alat', 'like', "%{$keyword}%")
            ->update(['gambar' => $namaFile]);
        if ($count > 0) {
            $dbUpdated[] = "\"$keyword\" → $namaFile ($count baris)";
        }
    }

    // --- STEP 3: Ambil semua alat untuk preview ---
    $alats = \App\Models\Alat::select('id', 'nama_alat', 'gambar')->get();

    // --- Render HTML ---
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8">
    <title>Setup Foto</title>
    <style>
        *{box-sizing:border-box}
        body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem;margin:0}
        h2{color:#818cf8;margin-top:2rem}
        h3{color:#94a3b8;font-size:.9rem;text-transform:uppercase;letter-spacing:.1em}
        .card{background:#1e293b;border-radius:.75rem;padding:1rem;margin:.5rem 0;display:flex;align-items:center;gap:1rem}
        img{width:64px;height:64px;object-fit:cover;border-radius:.5rem;border:1px solid #334155;flex-shrink:0}
        .ok{color:#34d399;font-weight:600}
        .miss{color:#f87171;font-weight:600}
        .warn{background:#422006;color:#fb923c;padding:1rem;border-radius:.75rem;margin-top:1rem;font-size:.9rem}
        .success{background:#052e16;color:#34d399;padding:1rem;border-radius:.75rem;margin-top:1rem}
        ul{margin:.5rem 0;padding-left:1.5rem}
        li{margin:.25rem 0}
        a{color:#818cf8}
    </style></head><body>';

    $html .= '<h2>⚙️ Setup Foto Alat</h2>';

    // Step 1 result
    $html .= '<div class="success">';
    $html .= '<b>✅ Step 1 — File disalin ke public/foto/: ' . count($fileDisalin) . ' file</b><ul>';
    foreach ($fileDisalin as $f) {
        $html .= '<li>' . htmlspecialchars($f) . '</li>';
    }
    $html .= '</ul></div>';

    // Step 2 result
    $html .= '<div class="success">';
    $html .= '<b>✅ Step 2 — Database diupdate:</b><ul>';
    if (empty($dbUpdated)) {
        $html .= '<li style="color:#f87171">⚠️ Tidak ada baris yang diupdate — cek nama alat di DB</li>';
    }
    foreach ($dbUpdated as $d) {
        $html .= '<li>' . htmlspecialchars($d) . '</li>';
    }
    $html .= '</ul></div>';

    // Step 3: preview semua alat
    $html .= '<h2>🖼️ Preview Foto Alat</h2>';
    foreach ($alats as $alat) {
        $url = asset('foto/' . $alat->gambar);
        $ada = $alat->gambar && file_exists(public_path('foto/' . $alat->gambar));
        $html .= '<div class="card">';
        if ($alat->gambar) {
            $html .= '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alat->nama_alat) . '">';
        } else {
            $html .= '<div style="width:64px;height:64px;background:#0f172a;border-radius:.5rem;border:1px solid #334155;display:flex;align-items:center;justify-content:center;color:#475569;font-size:.7rem">No Foto</div>';
        }
        $html .= '<div>';
        $html .= '<div style="font-weight:600">' . htmlspecialchars($alat->nama_alat) . '</div>';
        $html .= '<div style="font-size:.8rem;color:#64748b">DB: ' . htmlspecialchars($alat->gambar ?? '(null)') . '</div>';
        $html .= '<div style="font-size:.8rem">' . ($ada ? '<span class="ok">✅ File ditemukan</span>' : '<span class="miss">❌ File tidak ada</span>') . '</div>';
        $html .= '</div></div>';
    }

    $html .= '<div class="warn">⚠️ <b>PENTING:</b> Hapus route <code>/setup-foto</code> dari <code>routes/web.php</code> setelah selesai!</div>';
    $html .= '</body></html>';

    return $html;
});

// ============================================================
// ROUTE TAMU (BELUM LOGIN)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ============================================================
// ROUTE LOGOUT (WAJIB LOGIN)
// ============================================================
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================================
// PROFIL (SEMUA ROLE — CUKUP LOGIN)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// ============================================================
// ADMIN (HAK AKSES: ADMIN)
// ============================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin - Laporan PDF
    // DINONAKTIFKAN: Fitur laporan hanya untuk Petugas (bukan Admin) — sesuai modul hal. 141-145
    // Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    // Route::post('/laporan/generate', [LaporanController::class, 'generatePDF'])->name('laporan.generate');

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // ------------------------------
    // CRUD PENGEMBALIAN (BARU)
    // ------------------------------
    Route::get('/pengembalian', [AdminController::class, 'indexPengembalian'])->name('pengembalian.index');
    Route::get('/pengembalian/create', [AdminController::class, 'createPengembalian'])->name('pengembalian.create');
    Route::post('/pengembalian', [AdminController::class, 'storePengembalian'])->name('pengembalian.store');
    Route::get('/pengembalian/{id}', [AdminController::class, 'showPengembalian'])->name('pengembalian.show');
    Route::delete('/pengembalian/{id}', [AdminController::class, 'destroyPengembalian'])->name('pengembalian.destroy');

    // ------------------------------
    // CRUD ALAT
    // ------------------------------
    Route::get('/alat', [AdminController::class, 'indexAlat'])->name('alat.index');
    Route::get('/alat/create', [AdminController::class, 'createAlat'])->name('alat.create');
    Route::post('/alat', [AdminController::class, 'storeAlat'])->name('alat.store');
    Route::get('/alat/{id}/edit', [AdminController::class, 'editAlat'])->name('alat.edit');
    Route::put('/alat/{id}', [AdminController::class, 'updateAlat'])->name('alat.update');
    Route::delete('/alat/{id}', [AdminController::class, 'destroyAlat'])->name('alat.destroy');

    // ------------------------------
    // CRUD USER
    // ------------------------------
    Route::get('/users', [AdminController::class, 'indexUser'])->name('user.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('user.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('user.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('user.destroy');

    // ------------------------------
    // CRUD KATEGORI
    // ------------------------------
    Route::get('/kategori', [AdminController::class, 'indexKategori'])->name('kategori.index');
    Route::get('/kategori/create', [AdminController::class, 'createKategori'])->name('kategori.create');
    Route::post('/kategori', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::put('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'destroyKategori'])->name('kategori.destroy');

    // ------------------------------
    // CRUD PEMINJAMAN
    // ------------------------------
    Route::get('/peminjaman', [AdminController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [AdminController::class, 'createPeminjaman'])->name('peminjaman.create');
    Route::post('/peminjaman', [AdminController::class, 'storePeminjaman'])->name('peminjaman.store');
    Route::put('/peminjaman/{id}/status', [AdminController::class, 'updateStatusPeminjaman'])->name('peminjaman.updateStatus');
    Route::delete('/peminjaman/{id}', [AdminController::class, 'destroyPeminjaman'])->name('peminjaman.destroy');
});

// ============================================================
// PETUGAS (HAK AKSES: PETUGAS & ADMIN)
// ============================================================
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {

    // Peminjaman & Persetujuan
    Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])->name('peminjaman.tolak');

    // ===== PANTAU PENGEMBALIAN & LAPORAN =====
    Route::get('/pengembalian', [PetugasController::class, 'indexPengembalian'])->name('pengembalian.index');
    
    // Route Laporan
    Route::get('/laporan', [PetugasController::class, 'laporan'])->name('laporan');
    Route::post('/laporan/cetak', [PetugasController::class, 'cetakLaporan'])->name('laporan.cetak');

    // Pengembalian & Denda
    Route::post('/pengembalian/{id}', [PetugasController::class, 'prosesPengembalian'])->name('pengembalian.proses');
});

// ============================================================
// PEMINJAM (HAK AKSES: PEMINJAM)
// ============================================================
Route::middleware(['auth', 'role:peminjam'])->prefix('peminjam')->name('peminjam.')->group(function () {

    // Katalog & Pengajuan
    Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
    Route::get('/katalog/search', [PeminjamController::class, 'searchAlat'])->name('katalog.search');
    Route::post('/peminjaman/ajukan', [PeminjamController::class, 'ajukanPeminjaman'])->name('peminjaman.ajukan');
    Route::get('/riwayat', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');

    // Kembalikan Alat
    Route::post('/pengembalian/{id}', [PeminjamController::class, 'kembalikanAlat'])->name('pengembalian.kembalikan');
});