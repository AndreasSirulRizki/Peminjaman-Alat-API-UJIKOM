<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan constraint UNIQUE di kolom nama_kategori dan nama_alat.
     *
     * Catatan:
     * - users.email sudah UNIQUE sejak migrasi awal, tidak perlu diulang.
     * - Jika ada data duplikat di database sebelum migrasi ini dijalankan,
     *   migrasi akan GAGAL. Bersihkan dulu dengan query di bawah ini:
     *
     *   -- Cek duplikat kategori:
     *   SELECT nama_kategori, COUNT(*) as total FROM kategori
     *   GROUP BY nama_kategori HAVING total > 1;
     *
     *   -- Cek duplikat alat:
     *   SELECT nama_alat, COUNT(*) as total FROM alat
     *   GROUP BY nama_alat HAVING total > 1;
     */
    public function up(): void
    {
        // Kategori: nama_kategori harus unik
        Schema::table('kategori', function (Blueprint $table) {
            $table->unique('nama_kategori', 'kategori_nama_kategori_unique');
        });

        // Alat: nama_alat harus unik secara global
        Schema::table('alat', function (Blueprint $table) {
            $table->unique('nama_alat', 'alat_nama_alat_unique');
        });
    }

    /**
     * Rollback: hapus constraint UNIQUE yang ditambahkan.
     */
    public function down(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->dropUnique('kategori_nama_kategori_unique');
        });

        Schema::table('alat', function (Blueprint $table) {
            $table->dropUnique('alat_nama_alat_unique');
        });
    }
};
