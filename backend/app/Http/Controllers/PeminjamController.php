<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat (semua alat, stok 0 tetap tampil tapi disabled)
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->latest()->get();
        return view('peminjam.katalog', compact('alats'));
    }

    // Search alat via AJAX GET request — mengembalikan JSON
    public function searchAlat(Request $request)
    {
        $keyword = trim($request->input('q', ''));

        $alats = Alat::with('kategori')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_alat', 'like', "%{$keyword}%")
                      ->orWhereHas('kategori', function ($q) use ($keyword) {
                          $q->where('nama_kategori', 'like', "%{$keyword}%");
                      });
            })
            ->latest()
            ->get()
            ->map(function ($alat) {
                return [
                    'id'        => $alat->id,
                    'nama_alat' => $alat->nama_alat,
                    'kategori'  => $alat->kategori->nama_kategori ?? '-',
                    'stok'      => $alat->stok,
                    'gambar'    => $alat->gambar ? asset('foto/' . $alat->gambar) : null,
                ];
            });

        return response()->json($alats);
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Buat header peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            // Masukkan daftar alat yang dipinjam ke detail_pinjam
            foreach ($request->alat_id as $alatId) {
                $jumlahPinjam = $request->jumlah[$alatId] ?? 1;
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // ============================================================
    // KEMBALIKAN ALAT — Peminjam menginisiasi pengembalian sendiri
    // ============================================================
    public function kembalikanAlat($id)
    {
        DB::beginTransaction();
        try {
            // 1. Ambil peminjaman milik user ini, kunci baris agar tidak race condition
            $peminjaman = Peminjaman::with('detailPinjam.alat')
                ->where('id', $id)
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Hanya peminjaman berstatus 'dipinjam' yang boleh dikembalikan
            if ($peminjaman->status !== 'dipinjam') {
                DB::rollBack();
                return redirect()->route('peminjam.riwayat')
                    ->with('error', 'Peminjaman ini tidak dapat dikembalikan (status saat ini: ' . $peminjaman->status . ').');
            }

            // 3. Hitung telat atau tidak
            //    tgl_kembali_plan di-cast ke Carbon oleh model
            $today        = Carbon::today();
            $planKembali  = Carbon::parse($peminjaman->tgl_kembali_plan);
            $statusBaru   = $today->gt($planKembali) ? 'telat' : 'dikembalikan';

            // 4. Update status peminjaman
            $peminjaman->status = $statusBaru;
            $peminjaman->save();

            // 5. Kembalikan stok setiap alat yang dipinjam
            foreach ($peminjaman->detailPinjam as $detail) {
                Alat::where('id', $detail->alat_id)
                    ->lockForUpdate()
                    ->first()
                    ->increment('stok', $detail->jumlah);
            }

            // 6. Catat record pengembalian
            //    petugas_id diisi dengan id peminjam sendiri (self-service);
            //    kondisi_kembali default 'baik' — dapat diubah petugas nanti.
            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => $today->toDateString(),
                'kondisi_kembali' => 'baik',
                'denda'           => 0,
                'petugas_id'      => auth()->id(),
            ]);

            // 7. Catat log aktivitas
            $keteranganAlat = $peminjaman->detailPinjam
                ->map(fn($d) => ($d->alat->nama_alat ?? 'Alat') . ' (' . $d->jumlah . ' pcs)')
                ->join(', ');

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => 'Peminjam ' . auth()->user()->name
                    . ' mengembalikan alat: ' . $keteranganAlat
                    . '. Status: ' . $statusBaru . '.',
            ]);

            DB::commit();

            $pesan = $statusBaru === 'telat'
                ? 'Alat berhasil dikembalikan. Catatan: pengembalian melebihi batas waktu (telat). Harap konfirmasi ke petugas.'
                : 'Alat berhasil dikembalikan tepat waktu. Terima kasih!';

            return redirect()->route('peminjam.riwayat')->with('success', $pesan);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->route('peminjam.riwayat')
                ->with('error', 'Data peminjaman tidak ditemukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('peminjam.riwayat')
                ->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}