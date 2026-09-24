<?php
// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require '/var/www/vendor/autoload.php';
$app = require_once '/var/www/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Cek stok semua alat
$alats = \App\Models\Alat::select('id', 'nama_alat', 'stok')->orderBy('id')->get();
echo "=== DATA ALAT DI DATABASE ===\n";
foreach ($alats as $a) {
    echo sprintf("ID: %3d | Stok: %6d | %s\n", $a->id, $a->stok, $a->nama_alat);
}

// Cek transaksi peminjaman terbaru
echo "\n=== 5 PEMINJAMAN TERBARU ===\n";
$pinjams = \App\Models\Peminjaman::with('detailPinjam.alat')
    ->latest()->limit(5)->get();
foreach ($pinjams as $p) {
    echo "ID: {$p->id} | Status: {$p->status} | User: {$p->user_id} | {$p->created_at}\n";
    foreach ($p->detailPinjam as $d) {
        echo "  -> Alat: " . ($d->alat->nama_alat ?? '?') . " | Jumlah: {$d->jumlah}\n";
    }
}
