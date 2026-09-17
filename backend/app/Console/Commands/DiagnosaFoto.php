<?php

namespace App\Console\Commands;

use App\Models\Alat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnosaFoto extends Command
{
    protected $signature   = 'foto:diagnosa {--fix : Salin file dari folder foto/ ke public/foto/}';
    protected $description = 'Diagnosa dan perbaiki masalah foto alat (path, nama file, database)';

    public function handle(): void
    {
        $this->newLine();
        $this->info('======================================');
        $this->info('  DIAGNOSA FOTO ALAT');
        $this->info('======================================');
        $this->newLine();

        // --- 1. Cek folder foto/ (sumber) ---
        $folderSumber = base_path('foto');
        $folderPublik = public_path('foto');

        $this->line('<fg=cyan>📁 Folder sumber  :</> ' . $folderSumber);
        $this->line('<fg=cyan>📁 Folder publik  :</> ' . $folderPublik);
        $this->newLine();

        // --- 2. Daftar file di folder sumber ---
        $fileSumber = [];
        if (is_dir($folderSumber)) {
            foreach (glob($folderSumber . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) as $path) {
                $fileSumber[] = basename($path);
            }
            $this->info('File di backend/foto/ (' . count($fileSumber) . ' file):');
            foreach ($fileSumber as $f) {
                $this->line('  • ' . $f);
            }
        } else {
            $this->warn('⚠️  Folder backend/foto/ tidak ditemukan!');
        }
        $this->newLine();

        // --- 3. Daftar file di public/foto/ ---
        $filePublik = [];
        if (is_dir($folderPublik)) {
            foreach (glob($folderPublik . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) as $path) {
                $filePublik[] = basename($path);
            }
            $this->info('File di public/foto/ (' . count($filePublik) . ' file):');
            foreach ($filePublik as $f) {
                $this->line('  • ' . $f);
            }
        } else {
            $this->warn('⚠️  Folder public/foto/ belum ada!');
        }
        $this->newLine();

        // --- 4. Cek database ---
        $this->info('Isi kolom `gambar` di tabel alat:');
        $alats = Alat::select('id', 'nama_alat', 'gambar')->get();

        $headers = ['ID', 'Nama Alat', 'Kolom gambar', 'File Ada di public/foto?'];
        $rows    = [];

        foreach ($alats as $alat) {
            $ada = $alat->gambar
                ? (file_exists(public_path('foto/' . $alat->gambar)) ? '✅ Ada' : '❌ Tidak Ada')
                : '— (kosong)';

            $rows[] = [
                $alat->id,
                $alat->nama_alat,
                $alat->gambar ?? '(null)',
                $ada,
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // --- 5. Opsi --fix: salin file dari foto/ ke public/foto/ ---
        if ($this->option('fix')) {
            if (!is_dir($folderSumber)) {
                $this->error('Folder sumber backend/foto/ tidak ditemukan, tidak bisa menyalin.');
                return;
            }

            if (!is_dir($folderPublik)) {
                mkdir($folderPublik, 0775, true);
                $this->line('✅ Folder public/foto/ dibuat.');
            }

            $copied  = 0;
            $skipped = 0;

            foreach ($fileSumber as $namaFile) {
                $src  = $folderSumber . '/' . $namaFile;
                $dest = $folderPublik . '/' . $namaFile;

                if (file_exists($dest)) {
                    $skipped++;
                    $this->line('<fg=yellow>  ⏭ Skip (sudah ada) :</> ' . $namaFile);
                } else {
                    copy($src, $dest);
                    $copied++;
                    $this->line('<fg=green>  ✅ Disalin         :</> ' . $namaFile);
                }
            }

            $this->newLine();
            $this->info("Selesai! $copied file disalin, $skipped file di-skip.");
            $this->newLine();

            // Tampilkan ulang tabel setelah fix
            $this->info('Status setelah --fix:');
            $rows = [];
            foreach (Alat::select('id', 'nama_alat', 'gambar')->get() as $alat) {
                $ada    = $alat->gambar
                    ? (file_exists(public_path('foto/' . $alat->gambar)) ? '✅ Ada' : '❌ Tidak Ada')
                    : '— (kosong)';
                $rows[] = [$alat->id, $alat->nama_alat, $alat->gambar ?? '(null)', $ada];
            }
            $this->table($headers, $rows);
        } else {
            $this->comment('Tip: Jalankan dengan --fix untuk otomatis menyalin file ke public/foto/');
            $this->comment('     php artisan foto:diagnosa --fix');
        }

        $this->newLine();
    }
}
