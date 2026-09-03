<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Http\Requests\Pengembalian\UpdatePengembalianRequest;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class PengembalianController extends Controller
{
    // ========== INDEX ==========
    public function index(): JsonResponse
    {
        $user    = auth()->user();
        $perPage = request()->input('per_page', 10);

        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas']);

        if ($user->role === 'peminjam') {
            $query->whereHas('peminjaman', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $pengembalian = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Riwayat pengembalian berhasil diambil.',
            'data'    => $pengembalian->items(),
            'meta'    => [
                'current_page' => $pengembalian->currentPage(),
                'last_page'    => $pengembalian->lastPage(),
                'per_page'     => $pengembalian->perPage(),
                'total'        => $pengembalian->total(),
            ],
            'links' => [
                'prev' => $pengembalian->previousPageUrl(),
                'next' => $pengembalian->nextPageUrl(),
            ],
        ]);
    }

    // ========== STORE ==========
    public function store(StorePengembalianRequest $request): JsonResponse
    {
        try {
            $pengembalian = DB::transaction(function () use ($request) {
                // Kunci baris peminjaman
                $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->find($request->peminjaman_id);

                if ($peminjaman->status !== 'dipinjam') {
                    throw new Exception("Data ditolak. Peminjaman ini berstatus '{$peminjaman->status}', bukan 'dipinjam'.");
                }

                // Cek telat pake Carbon
                $tglKembaliPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                $hariIni = Carbon::now()->startOfDay();
                $statusPeminjamanBaru = $hariIni->greaterThan($tglKembaliPlan) ? 'telat' : 'dikembalikan';

                // Insert pengembalian
                $pengembalian = Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => now()->toDateTimeString(),
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $request->denda ?? 0,
                    'petugas_id' => auth()->id(),
                ]);

                // Update status peminjaman
                $peminjaman->update(['status' => $statusPeminjamanBaru]);

                // Kembalikan stok
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::lockForUpdate()->find($detail->alat_id);
                    $alat->increment('stok', $detail->jumlah);
                }

                return $pengembalian->load(['peminjaman.user', 'petugas']);
            });

            return response()->json([
                'message' => 'Proses pengembalian alat berhasil diselesaikan.',
                'data' => $pengembalian
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ========== SHOW ==========
    public function show(Pengembalian $pengembalian): JsonResponse
    {
        $user = auth()->user();
        $pengembalian->load(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas']);

        if ($user->role === 'peminjam' && $pengembalian->peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'message' => 'Detail pengembalian berhasil diambil.',
            'data' => $pengembalian
        ]);
    }

    // ========== UPDATE ==========
    public function update(UpdatePengembalianRequest $request, Pengembalian $pengembalian): JsonResponse
    {
        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda ?? $pengembalian->denda,
        ]);

        return response()->json([
            'message' => 'Data pengembalian berhasil diperbarui.',
            'data' => $pengembalian->load(['peminjaman.user', 'petugas'])
        ]);
    }

    // ========== DESTROY (BATALKAN PENGEMBALIAN) ==========
    public function destroy(Pengembalian $pengembalian): JsonResponse
    {
        try {
            DB::transaction(function () use ($pengembalian) {
                $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->findOrFail($pengembalian->peminjaman_id);

                // Kurangi stok lagi (karena pengembalian dibatalkan)
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception("Gagal membatalkan pengembalian. Stok alat '{$alat->nama_alat}' saat ini tidak mencukupi.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }

                // Balikin status ke 'dipinjam'
                $peminjaman->update(['status' => 'dipinjam']);

                // Log
                auth()->user()->logAktivitas()->create([
                    'aktivitas' => "Membatalkan pengembalian ID: #{$pengembalian->id}"
                ]);

                $pengembalian->delete();
            });

            return response()->json([
                'message' => 'Data pengembalian berhasil dihapus. Stok dan status peminjaman telah dikembalikan ke kondisi semula.'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}