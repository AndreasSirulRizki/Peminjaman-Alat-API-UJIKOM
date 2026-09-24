@extends('layouts.app-simple')

@section('title', 'Pantau Pengembalian - Petugas')
@section('header-title', 'Pantau Pengembalian')

@section('content')

    {{-- ============================================================ --}}
    {{-- FLASH MESSAGES                                               --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <h3 class="text-lg font-bold text-white mb-1">
        @if($status == 'dikembalikan')
            Riwayat Pengembalian
        @elseif($status == 'telat')
            Peminjaman Telat
        @elseif($status == 'dipinjam')
            Peminjaman Sedang Dipinjam
        @elseif($status == 'diajukan')
            Pengajuan Peminjaman
        @else
            Semua Peminjaman
        @endif
    </h3>
    <p class="text-sm text-slate-400 mb-4">
        Gunakan filter untuk melihat status tertentu.
    </p>

    {{-- ===== FORM FILTER STATUS ===== --}}
    <form method="GET" action="{{ route('petugas.pengembalian.index') }}"
          class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-6 flex flex-wrap gap-4 items-end">

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Status</label>
            <select name="status"
                    class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white
                           focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                <option value=""             {{ !$status ? 'selected' : '' }}>Semua Status</option>
                <option value="diajukan"     {{ $status == 'diajukan'     ? 'selected' : '' }}>Diajukan</option>
                <option value="dipinjam"     {{ $status == 'dipinjam'     ? 'selected' : '' }}>Dipinjam</option>
                <option value="telat"        {{ $status == 'telat'        ? 'selected' : '' }}>Telat</option>
                <option value="dikembalikan" {{ $status == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-filter mr-1.5"></i> Terapkan Filter
            </button>
            <a href="{{ route('petugas.pengembalian.index') }}"
               class="bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-times mr-1.5"></i> Reset
            </a>
        </div>

        {{-- Indikator filter aktif --}}
        @if($status)
            <p class="text-xs text-slate-500 self-end pb-2">
                Menampilkan: <span class="text-indigo-400 font-medium">{{ ucfirst($status) }}</span>
                &nbsp;·&nbsp; {{ $peminjamans->count() }} data
            </p>
        @endif
    </form>

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800">Peminjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Alat & Jumlah</th>
                        <th class="py-3 px-5 border-b border-slate-800">Tgl Pinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                        <th class="py-3 px-5 border-b border-slate-800">Status</th>
                        <th class="py-3 px-5 border-b border-slate-800">Denda</th>
                        <th class="py-3 px-5 border-b border-slate-800">Aksi Proses</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($peminjamans as $item)

                        {{-- ============================================================ --}}
                        {{-- Gunakan named error bag "pengembalian_{id}" agar error       --}}
                        {{-- hanya muncul di baris yang gagal validasi, bukan semua baris --}}
                        {{-- ============================================================ --}}
                        @php $bag = $errors->getBag('pengembalian_' . $item->id); @endphp

                        <tr class="hover:bg-slate-800/40 transition align-top border-b border-slate-800/30
                                   {{ $bag->isNotEmpty() ? 'ring-1 ring-rose-500/30' : '' }}">

                            {{-- Peminjam --}}
                            <td class="py-3 px-5 font-medium text-white">
                                {{ $item->user->name ?? '-' }}
                            </td>

                            {{-- Alat & Jumlah --}}
                            <td class="py-3 px-5">
                                <ul class="space-y-2">
                                    @foreach($item->detailPinjam as $detail)
                                        <li class="flex items-center gap-2">
                                            @if($detail->alat && $detail->alat->gambar)
                                                <img src="{{ asset('foto/' . $detail->alat->gambar) }}"
                                                     alt="{{ $detail->alat->nama_alat }}"
                                                     class="w-9 h-9 object-cover rounded-md border border-slate-700 flex-shrink-0"
                                                     onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2236%22 height=%2236%22%3E%3Crect width=%2236%22 height=%2236%22 rx=%226%22 fill=%22%231e293b%22 stroke=%22%23334155%22/%3E%3C/svg%3E';">
                                            @else
                                                <div class="w-9 h-9 bg-slate-800 rounded-md border border-slate-700 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-image text-slate-600 text-xs"></i>
                                                </div>
                                            @endif
                                            <span class="text-slate-300">{{ $detail->alat->nama_alat ?? 'Alat dihapus' }}
                                                <span class="text-xs bg-slate-700 px-1.5 py-0.5 rounded text-slate-300 ml-1">
                                                    {{ $detail->jumlah }} pcs
                                                </span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            {{-- Tgl Pinjam --}}
                            <td class="py-3 px-5 text-slate-400">{{ $item->tgl_pinjam }}</td>

                            {{-- Rencana Kembali --}}
                            <td class="py-3 px-5 text-slate-400">{{ $item->tgl_kembali_plan }}</td>

                            {{-- Status --}}
                            {{-- Deteksi telat real-time: jika hari ini > tgl_kembali_plan → tandai Telat --}}
                            <td class="py-3 px-5">
                                @php
                                    $tglPlanStatus = \Carbon\Carbon::parse($item->tgl_kembali_plan)->startOfDay();
                                    $hariIniStatus = \Carbon\Carbon::now()->startOfDay();
                                    $isTelat       = $hariIniStatus->greaterThan($tglPlanStatus)
                                                     && in_array($item->status, ['dipinjam', 'telat']);
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                                    @if($isTelat)
                                        bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @elseif($item->status == 'dipinjam')
                                        bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @elseif($item->status == 'dikembalikan')
                                        bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @elseif($item->status == 'diajukan')
                                        bg-yellow-500/10 text-yellow-400 border-yellow-500/20
                                    @else
                                        bg-slate-700 text-slate-400 border-slate-600
                                    @endif">
                                    {{ $isTelat ? 'Telat' : ucfirst($item->status) }}
                                </span>
                            </td>

                            {{-- ============================================================ --}}
                            {{-- AKSI: tampilkan form proses hanya jika belum dikembalikan   --}}
                            {{-- ============================================================ --}}

                            {{-- Denda Otomatis --}}
                            <td class="py-3 px-5">
                                @if($item->denda_otomatis > 0)
                                    <div class="flex flex-col">
                                        <span class="text-rose-400 font-bold text-sm">
                                            Rp {{ number_format($item->denda_otomatis, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            Telat {{ $item->hari_telat }} hari
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-500 text-sm">Rp 0</span>
                                @endif
                            </td>

                            <td class="py-3 px-5">
                                @if(in_array($item->status, ['dipinjam', 'telat']))
                                <form action="{{ route('petugas.pengembalian.proses', $item->id) }}"
                                      method="POST">
                                    @csrf

                                    {{-- Kondisi Barang --}}
                                    <div class="mb-2">
                                        <input type="text"
                                               name="kondisi_kembali"
                                               value="{{ old('kondisi_kembali') }}"
                                               placeholder="Kondisi (contoh: Baik)"
                                               maxlength="255"
                                               required
                                               oninvalid="this.setCustomValidity('Kondisi barang wajib diisi.')"
                                               oninput="this.setCustomValidity('')"
                                               class="w-full bg-slate-800 border rounded-lg px-2 py-1 text-xs text-white
                                                      placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition
                                                      {{ $bag->has('kondisi_kembali') ? 'border-rose-500/60' : 'border-slate-700' }}">
                                        @if($bag->has('kondisi_kembali'))
                                            <span class="text-rose-400 text-xs mt-0.5 block">
                                                {{ $bag->first('kondisi_kembali') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Denda ─────────────────────────────────────────────
                                         PERUBAHAN:
                                         • Atribut min="0" DIHAPUS dari HTML agar browser tidak
                                           memunculkan tooltip bawaan bahasa Inggris
                                           ("Value must be greater than or equal to 0.").
                                         • Validasi nilai negatif tetap ditangani backend
                                           (Validator::make, rule: min:0).
                                         • oninvalid / oninput pada kondisi_kembali
                                           memastikan pesan custom muncul jika browser
                                           constraint HTML5 aktif (required).
                                         • value: prefill dengan denda_otomatis dari controller,
                                           petugas tetap bisa mengubah angkanya secara manual.
                                    ─────────────────────────────────────────────────────── --}}
                                    <div class="mb-2">
                                        <input type="number"
                                               name="denda"
                                               value="{{ old('denda', $item->denda_otomatis) }}"
                                               placeholder="Denda (opsional)"
                                               class="w-full bg-slate-800 border rounded-lg px-2 py-1 text-xs text-white
                                                      placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition
                                                      {{ $bag->has('denda') ? 'border-rose-500/60' : 'border-slate-700' }}">
                                        @if($bag->has('denda'))
                                            <span class="text-rose-400 text-xs mt-0.5 block">
                                                {{ $bag->first('denda') }}
                                            </span>
                                        @endif
                                    </div>

                                    <button type="submit"
                                            class="w-full bg-amber-500/10 hover:bg-amber-500/20 text-amber-400
                                                   border border-amber-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition"
                                            onclick="return confirm('Proses pengembalian untuk peminjaman ini?')">
                                        Proses Kembali
                                    </button>
                                </form>
                                @else
                                    {{-- Status dikembalikan — tidak ada aksi --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold
                                                 rounded-full border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                        <i class="fas fa-check text-[10px]"></i> Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                @if($status)
                                    Tidak ada data peminjaman dengan status
                                    <span class="text-slate-400 font-medium">{{ ucfirst($status) }}</span>.
                                @else
                                    Tidak ada peminjaman aktif saat ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
