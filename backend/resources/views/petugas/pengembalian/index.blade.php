@extends('layouts.app-simple')

@section('title', 'Pantau Pengembalian - Petugas')
@section('header-title', 'Pantau Pengembalian')

@section('content')

    {{-- Flash Messages --}}
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

    <h3 class="text-lg font-bold text-white mb-4">Daftar Peminjaman Aktif (Harus Dikembalikan)</h3>

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
                        <th class="py-3 px-5 border-b border-slate-800">Aksi Proses</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-slate-800/40 transition align-top border-b border-slate-800/30">
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
                            <td class="py-3 px-5">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                                    @if($item->status == 'dipinjam') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @elseif($item->status == 'telat') bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            {{-- Form Proses Pengembalian --}}
                            <td class="py-3 px-5">
                                <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                    @csrf
                                    <div class="flex flex-col gap-2">
                                        <input type="text" name="kondisi_kembali"
                                               placeholder="Kondisi (contoh: Baik)"
                                               class="bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" required>
                                        <input type="number" name="denda" value="0"
                                               placeholder="Denda (opsional)"
                                               class="bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                        <button type="submit"
                                                class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                                                onclick="return confirm('Proses pengembalian untuk peminjaman ini?')">
                                            Proses Kembali
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada peminjaman aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
