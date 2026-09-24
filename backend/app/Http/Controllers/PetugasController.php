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
    public function indexPeminjaman(Request $request)
    {
        $status = $request->input('status');
        $validStatus = ['diajukan', 'dipinjam', 'telat', 'dikembalikan'];

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($status && in_array($status, $validStatus), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'status'));
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

    // ========== MENOLAK PEMINJAMAN (DENGAN ALASAN) ==========
    public function tolakPeminjaman(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|min:5|max:500',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
            'alasan_tolak.min'      => 'Alasan penolakan minimal 5 karakter.',
            'alasan_tolak.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Peminjaman ini sudah diproses, tidak bisa dibatalkan.');
            }

            // Simpan alasan ke tabel peminjaman sebelum dihapus
            // (jika ingin tetap bisa dilihat di riwayat, ubah menjadi update status saja)
            $peminjaman->update(['alasan_tolak' => $request->alasan_tolak]);

            // Catat ke log aktivitas agar ada jejak audit
            \App\Models\LogAktivitas::create([
                'user_id'    => auth()->id(),
                'aktivitas'  => "Menolak peminjaman ID #{$peminjaman->id} atas nama {$peminjaman->user->name}. Alasan: {$request->alasan_tolak}",
            ]);

            // Hapus detail pinjaman dulu (anak), baru induk
            $peminjaman->detailPinjam()->delete();
            $peminjaman->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========== MEMANTAU PENGEMBALIAN (DAFTAR PEMINJAMAN AKTIF) ==========
    public function indexPengembalian(Request $request)
    {
        $status      = $request->input('status');
        $validStatus = ['diajukan', 'dipinjam', 'telat', 'dikembalikan'];

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($status && in_array($status, $validStatus), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->get();

        // ── Hitung denda otomatis per peminjaman ─────────────────────────────
        // Tarif: Rp 1.000 per hari keterlambatan.
        // Rumus: hari_telat = diffInDays(tgl_kembali_plan, hari_ini)
        //        denda_otomatis = hari_telat * 1000
        // Nilai ini hanya dipakai sebagai default prefill form; nilai final
        // yang tersimpan ke DB adalah input petugas di kolom "Denda".
        // ─────────────────────────────────────────────────────────────────────
        $tarifDendaPerHari = 1000;

        foreach ($peminjamans as $item) {
            $tglPlan = \Carbon\Carbon::parse($item->tgl_kembali_plan)->startOfDay();
            $hariIni = \Carbon\Carbon::now()->startOfDay();

            if ($hariIni->greaterThan($tglPlan)) {
                $hariTelat              = $tglPlan->diffInDays($hariIni);
                $item->denda_otomatis   = $hariTelat * $tarifDendaPerHari;
                $item->hari_telat       = $hariTelat;
            } else {
                $item->denda_otomatis   = 0;
                $item->hari_telat       = 0;
            }
        }

        return view('petugas.pengembalian.index', compact('peminjamans', 'status'));
    }

    // ========== MEMPROSES PENGEMBALIAN ==========
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        // Gunakan Validator manual agar error masuk ke named bag "pengembalian_{id}".
        // Ini penting karena halaman memiliki banyak form sekaligus — tanpa named bag,
        // error dari satu baris akan muncul di semua baris.
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'kondisi_kembali' => 'required|string|max:255',
            'denda'           => 'nullable|integer|min:0',
        ], [
            'kondisi_kembali.required' => 'Kondisi barang wajib diisi.',
            'kondisi_kembali.max'      => 'Kondisi barang maksimal 255 karakter.',
            'denda.integer'            => 'Denda harus berupa angka.',
            'denda.min'                => 'Denda tidak boleh bernilai negatif.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'pengembalian_' . $peminjamanId)
                ->withInput();
        }

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

    // ========== GENERATE LAPORAN (GET — preview di halaman index) ==========
    public function laporan(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat']);

        // Filter tanggal (opsional — kalau tidak diisi, tampil semua data)
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereBetween('tgl_pinjam', [
                $request->tgl_awal . ' 00:00:00',
                $request->tgl_akhir . ' 23:59:59',
            ]);
        }

        // Filter status (opsional)
        if ($request->filled('status')) {
            $validStatus = ['diajukan', 'dipinjam', 'telat', 'dikembalikan'];
            if (in_array($request->status, $validStatus)) {
                $query->where('status', $request->status);
            }
        }

        $peminjamans = $query->latest()->get();

        return view('petugas.laporan.index', compact('peminjamans'));
    }

    // ========== CETAK LAPORAN (POST) ==========
    public function cetakLaporan(Request $request)
    {
        // Validasi: tanggal wajib diisi & format benar
        $request->validate([
            'tgl_awal'  => 'required|date|date_format:Y-m-d',
            'tgl_akhir' => 'required|date|date_format:Y-m-d|after_or_equal:tgl_awal',
            'status'    => 'nullable|in:diajukan,dipinjam,dikembalikan,telat',
        ], [
            'tgl_awal.required'         => 'Tanggal Awal wajib diisi.',
            'tgl_awal.date'             => 'Format Tanggal Awal tidak valid.',
            'tgl_awal.date_format'      => 'Format Tanggal Awal harus YYYY-MM-DD.',
            'tgl_akhir.required'        => 'Tanggal Akhir wajib diisi.',
            'tgl_akhir.date'            => 'Format Tanggal Akhir tidak valid.',
            'tgl_akhir.date_format'     => 'Format Tanggal Akhir harus YYYY-MM-DD.',
            'tgl_akhir.after_or_equal'  => 'Tanggal Akhir harus sama dengan atau setelah Tanggal Awal.',
            'status.in'                 => 'Status tidak valid.',
        ]);

        // Query dengan filter tanggal wajib + status opsional
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->whereBetween('tgl_pinjam', [
                $request->tgl_awal  . ' 00:00:00',
                $request->tgl_akhir . ' 23:59:59',
            ])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.cetak', [
            'peminjamans' => $peminjamans,
            'tgl_awal'    => $request->tgl_awal,
            'tgl_akhir'   => $request->tgl_akhir,
            'status'      => $request->status,
        ]);
    }
}