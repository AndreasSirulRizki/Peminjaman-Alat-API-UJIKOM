<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\AlatController;

// Public Routes (Tidak perlu token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Wajib membawa Bearer Token dari Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Katalog: boleh admin ATAU peminjam (dicek manual di dalam Controller)
    Route::get('/katalog', [AlatController::class, 'katalog']);

    Route::middleware('role.admin')->group(function () {
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('alat', AlatController::class);
    });
    Route::middleware('role.petugas')->group(function () {
        // Route khusus petugas
    });
    Route::middleware('role.peminjam')->group(function () {
        // Route khusus peminjam
    });
});