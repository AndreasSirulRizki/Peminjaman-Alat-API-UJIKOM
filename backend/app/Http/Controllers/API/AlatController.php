<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Alat\StoreAlatRequest;
use App\Http\Requests\Alat\UpdateAlatRequest;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlatController extends Controller
{
    public function index(): JsonResponse
    {
        $perPage = request()->input('per_page', 10);
        $search  = request()->input('search');

        $alat = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Daftar alat berhasil diambil.',
            'data'    => AlatResource::collection($alat->items()),
            'meta'    => [
                'current_page' => $alat->currentPage(),
                'last_page'    => $alat->lastPage(),
                'per_page'     => $alat->perPage(),
                'total'        => $alat->total(),
            ],
            'links' => [
                'prev' => $alat->previousPageUrl(),
                'next' => $alat->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreAlatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $alat = DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('alat', 'public');
            }
            return Alat::create($data);
        });
        return response()->json([
            'message' => 'Alat berhasil ditambahkan.',
            'data' => new AlatResource($alat->load('kategori'))
        ], 201);
    }

    public function show(Alat $alat): JsonResponse
    {
        return response()->json([
            'data' => new AlatResource($alat->load('kategori'))
        ]);
    }

    public function update(UpdateAlatRequest $request, Alat $alat): JsonResponse
    {
        $data = $request->validated();
        $oldGambar = $alat->gambar; // Simpan path gambar lama lebih dahulu

        DB::transaction(function () use ($request, &$data, $alat, $oldGambar) {
            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('alat', 'public');
                // Hapus gambar lama dari server HANYA setelah gambar baru sukses masuk database
                if ($oldGambar) {
                    Storage::disk('public')->delete($oldGambar);
                }
            }
            $alat->update($data);
        });
        return response()->json([
            'message' => 'Alat berhasil diperbarui.',
            'data' => new AlatResource($alat->load('kategori'))
        ]);
    }

    public function destroy(Alat $alat): JsonResponse
    {
        DB::transaction(function () use ($alat) {
            if ($alat->gambar) {
                Storage::disk('public')->delete($alat->gambar);
            }
            $alat->delete();
        });
        return response()->json([
            'message' => 'Alat berhasil dihapus.'
        ]);
    }

    public function katalog(): JsonResponse
    {

        if (!in_array(auth()->user()->role, ['admin', 'peminjam'])) {
        return response()->json(['message' => 'Akses ditolak.'], 403);
    }

        $alat = Alat::with('kategori')->tersedia()->latest()->get();
        return response()->json([
            'message' => 'Katalog alat tersedia.',
            'data' => AlatResource::collection($alat)
        ]);
    }
}