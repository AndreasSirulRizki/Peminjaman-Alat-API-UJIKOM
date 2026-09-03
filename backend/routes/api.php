<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PeminjamanController;
use App\Http\Controllers\API\PengembalianController;
use App\Http\Controllers\API\LogAktivitasController;
use App\Http\Controllers\API\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES (Tidak perlu token) ====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==================== PROTECTED ROUTES (Wajib Bearer Token) ====================
Route::middleware('auth:sanctum')->group(function () {
    // ---- ROUTE UMUM (Semua role) ----
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Katalog: boleh diakses admin ATAU peminjam (otorisasi di Controller)
    Route::get('/katalog', [AlatController::class, 'katalog']);

    // Laporan: boleh diakses admin ATAU petugas
    Route::middleware('role:admin,petugas')->get('/laporan-peminjaman', [LaporanController::class, 'index']);

    // ==================== GROUP ADMIN ====================
    Route::middleware('role.admin')->group(function () {
        // Master Data
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('alat', AlatController::class);
        Route::apiResource('users', UserController::class);

        // Peminjaman (CRUD + Approve)
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);

        // Pengembalian (CRUD lengkap)
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);

        // Log Aktivitas
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
    });

    // ==================== GROUP PETUGAS ====================
    Route::middleware('role.petugas')->group(function () {
        // Pengembalian: hanya store (proses pengembalian)
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
    });

    // Approve: bisa diakses admin ATAU petugas
    Route::middleware('role:admin,petugas')->post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);

    // ==================== GROUP PEMINJAM ====================
    Route::middleware('role.peminjam')->group(function () {
        // Peminjaman: ajukan & riwayat
        Route::post('/peminjaman', [PeminjamanController::class, 'store']);
        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);

        // Peminjam TIDAK punya akses ke endpoint pengembalian (hanya lewat riwayat-pinjam)
    });
});