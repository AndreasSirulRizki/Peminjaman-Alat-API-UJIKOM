<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    /**
     * Tampilkan halaman form filter laporan.
     */
    public function index()
    {
        return view('admin.laporan.index');
    }

    /**
     * Generate PDF berdasarkan filter tanggal dan status.
     *
     * Bug sebelumnya:
     * 1. Filter tanggal hanya aktif jika KEDUANYA (start & end) diisi.
     * 2. whereBetween di SQLite kadang tidak konsisten dengan kolom yang di-cast Carbon.
     * 3. Variabel tanggal dikirim ke view sebagai string mentah, bisa null.
     *
     * Perbaikan:
     * - Filter start_date dan end_date diproses secara INDEPENDEN dengan whereDate().
     * - Tanggal dikonversi ke Carbon agar format tampilan di PDF konsisten.
     * - Validasi menggunakan $request->validate() agar redirect otomatis dengan error.
     */
    public function generatePDF(Request $request)
    {
        // Validasi — jika gagal otomatis redirect back + withErrors + withInput
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status'     => 'nullable|in:diajukan,dipinjam,dikembalikan,telat',
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'status.in'               => 'Status tidak valid.',
        ]);

        // Bangun query dengan eager loading (hindari N+1)
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        // Filter: tanggal awal (>= start_date)
        // Menggunakan whereDate() agar perbandingan terjadi di level DATE,
        // tidak terpengaruh oleh cast Carbon atau format timestamp.
        if ($request->filled('start_date')) {
            $query->whereDate('tgl_pinjam', '>=', $request->start_date);
        }

        // Filter: tanggal akhir (<= end_date)
        if ($request->filled('end_date')) {
            $query->whereDate('tgl_pinjam', '<=', $request->end_date);
        }

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Urutkan dari yang paling lama ke terbaru (kronologis di laporan)
        $peminjaman = $query->orderBy('tgl_pinjam', 'asc')->get();

        // Format tanggal untuk tampilan subtitle di PDF
        // Konversi Carbon agar selalu tampil dalam format d-m-Y
        $startFormatted = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->format('d-m-Y')
            : null;

        $endFormatted = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->format('d-m-Y')
            : null;

        // Kirim data ke view PDF
        $data = [
            'title'        => 'Laporan Peminjaman Alat',
            'start_date'   => $startFormatted,
            'end_date'     => $endFormatted,
            'status'       => $request->status,
            'peminjaman'   => $peminjaman,
            'total'        => $peminjaman->count(),
            'generated_at' => now()->format('d-m-Y H:i:s'),
        ];

        // Generate dan stream PDF langsung di browser (bukan download)
        // Gunakan ->download() jika ingin langsung di-download
        $pdf = Pdf::loadView('admin.laporan.pdf', $data)
            ->setPaper('A4', 'landscape');

        return $pdf->stream('laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf');
    }
}