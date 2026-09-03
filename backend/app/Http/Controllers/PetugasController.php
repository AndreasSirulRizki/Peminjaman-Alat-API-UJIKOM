<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    // ========== DAFTAR PENGAJUAN PEMINJAMAN ==========
    public function indexPeminjaman()
    {
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])->latest()->get();
        return view('petugas.peminjaman.index', compact('peminjamans'));
    }

    // ========== MENYETUJUI PEMINJAMAN ==========
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Peminjaman ini sudah diproses, tidak bisa disetujui.');
            }

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi (tersisa: {$alat->stok}, dibutuhkan: {$detail->jumlah}).");
                }
                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'dipinjam']);

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========== MENOLAK PEMINJAMAN (HAPUS DATA) ==========
    public function tolakPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                return redirect()->back()->with('error', 'Peminjaman ini sudah diproses, tidak bisa dibatalkan.');
            }

            $peminjaman->detailPinjam()->delete();
            $peminjaman->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak dan dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========== MEMANTAU PENGEMBALIAN (DAFTAR PEMINJAMAN AKTIF) ==========
    public function indexPengembalian()
    {
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->latest()
            ->get();

        return view('petugas.pengembalian.index', compact('peminjamans'));
    }

    // ========== MEMPROSES PENGEMBALIAN ==========
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->findOrFail($peminjamanId);

            // Guard: cegah pemrosesan ganda jika sudah dikembalikan
            if ($peminjaman->status === 'dikembalikan') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
            }

            // Hitung denda otomatis jika terlambat dan petugas tidak mengisi denda manual
            $tglKembaliPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $hariIni = \Carbon\Carbon::now()->startOfDay();
            $terlambat = $hariIni->greaterThan($tglKembaliPlan);

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            // Status selalu 'dikembalikan' setelah diproses — keterlambatan dicatat via denda
            $peminjaman->update(['status' => 'dikembalikan']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                $alat->increment('stok', $detail->jumlah);
            }

            DB::commit();

            $pesan = $terlambat
                ? 'Pengembalian berhasil dicatat (TERLAMBAT). Stok dipulihkan.'
                : 'Pengembalian berhasil dicatat dan stok dipulihkan.';

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========== GENERATE LAPORAN ==========
    public function laporan(Request $request)
    {
        $tanggalAwal  = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $status       = $request->status; // Nilai valid: diajukan|dipinjam|telat|dikembalikan|'' (semua)

        $validStatus = ['diajukan', 'dipinjam', 'telat', 'dikembalikan'];

        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($tanggalAwal && $tanggalAkhir, function ($query) use ($tanggalAwal, $tanggalAkhir) {
                return $query->whereBetween('tgl_pinjam', [$tanggalAwal, $tanggalAkhir]);
            })
            ->when($status && in_array($status, $validStatus), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.index', compact('peminjaman', 'tanggalAwal', 'tanggalAkhir', 'status'));
    }
}