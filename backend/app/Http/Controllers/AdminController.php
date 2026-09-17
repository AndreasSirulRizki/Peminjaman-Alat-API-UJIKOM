<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian; // <-- TAMBAHAN BARU
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ======================== DASHBOARD ========================
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();

        $stats = [
            'total_alat'       => Alat::count(),
            'sedang_dipinjam'  => Peminjaman::where('status', 'dipinjam')->count(),
            'pending_request'  => Peminjaman::where('status', 'diajukan')->count(),
            'total_user'       => User::count(),
        ];

        return view('admin.dashboard', compact('logs', 'stats'));
    }

    // ======================== CRUD ALAT ========================
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $request->validate([
            'kategori_id'    => 'required|exists:kategori,id',
            'nama_alat'      => 'required|string|max:255|unique:alat,nama_alat',
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi'      => 'nullable|string',
            'gambar'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $namaFile = null;

        if ($request->hasFile('gambar')) {
            $file      = $request->file('gambar');
            $namaFile  = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('foto'), $namaFile);
        }

        Alat::create([
            'kategori_id'    => $request->kategori_id,
            'nama_alat'      => $request->nama_alat,
            'stok'           => $request->stok,
            'status_kondisi' => $request->status_kondisi,
            'deskripsi'      => $request->deskripsi,
            'gambar'         => $namaFile,
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'kategori_id'    => 'required|exists:kategori,id',
            'nama_alat'      => 'required|string|max:255|unique:alat,nama_alat,' . $id,
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi'      => 'nullable|string',
            'gambar'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['kategori_id', 'nama_alat', 'stok', 'status_kondisi', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            // Hapus file lama jika ada
            if ($alat->gambar) {
                $pathLama = public_path('foto/' . $alat->gambar);
                if (file_exists($pathLama)) {
                    unlink($pathLama);
                }
            }

            // Simpan file baru
            $file             = $request->file('gambar');
            $namaFile         = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('foto'), $namaFile);
            $data['gambar']   = $namaFile;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Cek apakah alat sedang dipinjam (status 'dipinjam' atau 'telat')
        $sedangDipinjam = \App\Models\DetailPinjam::where('alat_id', $id)
            ->whereHas('peminjaman', function ($query) {
                $query->whereIn('status', ['dipinjam', 'telat']);
            })
            ->exists();

        if ($sedangDipinjam) {
            return redirect()->route('admin.alat.index')
                ->with('error', 'Alat tidak dapat dihapus karena sedang dipinjam oleh user.');
        }

        // Hapus file gambar dari disk jika ada
        if ($alat->gambar) {
            $pathGambar = public_path('foto/' . $alat->gambar);
            if (file_exists($pathGambar)) {
                unlink($pathGambar);
            }
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Alat berhasil dihapus.');
    }

    // ======================== CRUD USER ========================
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Menambahkan user baru: ' . $request->name . ' (' . $request->role . ')',
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Memperbarui data user: ' . $user->name,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $namaUser = $user->name;
        $user->delete();

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Menghapus user: ' . $namaUser,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // ======================== CRUD KATEGORI ========================
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Menambahkan kategori baru: ' . $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Memperbarui kategori: ' . $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $namaKategori = $kategori->nama_kategori;
        $kategori->delete();

        LogAktivitas::create([
            'user_id' => auth()->user()->id,
            'aktivitas' => 'Menghapus kategori: ' . $namaKategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // ======================== CRUD PEMINJAMAN (Admin) ========================
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'tgl_pinjam'       => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id'          => 'required|array|min:1',
            'alat_id.*'        => 'required|exists:alat,id',
            'jumlah'           => 'required|array|min:1',
            'jumlah.*'         => 'required|integer|min:1',
        ], [
            'user_id.required'              => 'Peminjam wajib dipilih.',
            'user_id.exists'                => 'Peminjam yang dipilih tidak valid.',
            'tgl_pinjam.required'           => 'Tanggal pinjam wajib diisi.',
            'tgl_pinjam.date'               => 'Format tanggal pinjam tidak valid.',
            'tgl_kembali_plan.required'     => 'Rencana tanggal kembali wajib diisi.',
            'tgl_kembali_plan.date'         => 'Format rencana tanggal kembali tidak valid.',
            'tgl_kembali_plan.after_or_equal' => 'Tanggal rencana kembali harus setelah atau sama dengan tanggal pinjam.',
            'alat_id.required'              => 'Pilih minimal 1 alat untuk dipinjam.',
            'alat_id.min'                   => 'Pilih minimal 1 alat untuk dipinjam.',
            'alat_id.*.required'            => 'Alat wajib dipilih pada setiap baris.',
            'alat_id.*.exists'              => 'Alat yang dipilih tidak valid.',
            'jumlah.required'               => 'Jumlah alat wajib diisi.',
            'jumlah.*.required'             => 'Jumlah barang wajib diisi.',
            'jumlah.*.integer'              => 'Jumlah barang harus berupa angka.',
            'jumlah.*.min'                  => 'Jumlah minimal 1 barang.',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            if ($statusLama !== 'dipinjam' && $statusBaru === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    // lockForUpdate() mencegah race condition pada data stok
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } elseif ($statusLama === 'dipinjam' && $statusBaru === 'dikembalikan') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    $alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    // ===== METHOD DESTROY PEMINJAMAN (SUDAH DIPERBAIKI DENGAN TRANSACTION) =====
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Jika status sedang dipinjam, kembalikan stok dulu
            if ($peminjaman->status === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            // Hapus detail pinjaman dulu (anak)
            $peminjaman->detailPinjam()->delete();

            // Hapus data peminjaman utama (induk)
            $peminjaman->delete();

            DB::commit();

            return redirect()->route('admin.peminjaman.index')
                ->with('success', 'Data peminjaman berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ======================== CRUD PENGEMBALIAN (Admin) ========================
    // 1. Daftar semua pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('peminjaman', function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalian', 'search'));
    }

    // 2. Form tambah pengembalian (pilih peminjaman yang belum dikembalikan: 'dipinjam' ATAU 'telat')
    public function createPengembalian()
    {
        $peminjamanList = Peminjaman::whereIn('status', ['dipinjam', 'telat'])
            ->with(['user', 'detailPinjam.alat'])
            ->get();

        return view('admin.pengembalian.create', compact('peminjamanList'));
    }

    // 3. Proses simpan pengembalian
    public function storePengembalian(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'nullable|integer|min:0',
        ]);

        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($request->peminjaman_id);

        // Cek apakah peminjaman belum dikembalikan (status 'dipinjam' atau 'telat')
        if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return back()->with('error', 'Peminjaman ini tidak dapat diproses karena statusnya bukan "dipinjam" atau "telat".');
        }

        DB::beginTransaction();
        try {
            // Hitung telat (jika ada) — status akhir selalu 'dikembalikan' karena barang sudah kembali
            $tglKembaliPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $hariIni = \Carbon\Carbon::now()->startOfDay();
            $statusBaru = 'dikembalikan'; // Setelah proses pengembalian, status selalu dikembalikan

            // Simpan ke tabel pengembalian
            $pengembalian = Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(), // Admin bertindak sebagai petugas
            ]);

            // Update status peminjaman
            $peminjaman->update(['status' => $statusBaru]);

            // Kembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            DB::commit();

            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Pengembalian berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    // 4. Detail pengembalian
    public function showPengembalian($id)
    {
        $pengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->findOrFail($id);

        return view('admin.pengembalian.show', compact('pengembalian'));
    }

    // 5. Hapus pengembalian (dengan rollback stok)
    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::with('peminjaman.detailPinjam.alat')->findOrFail($id);

        DB::beginTransaction();
        try {
            $peminjaman = $pengembalian->peminjaman;

            // Kembalikan status peminjaman ke 'dipinjam'
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok lagi (karena pengembalian dibatalkan), dengan lockForUpdate()
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk rollback.");
                }
                $alat->decrement('stok', $detail->jumlah);
            }

            $pengembalian->delete();

            DB::commit();

            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Data pengembalian berhasil dihapus dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}