@extends('layouts.peminjam')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman Saya')

@section('content')

    {{-- Flash: Sukses --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Flash: Error --}}
    @if(session('error'))
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <h3 class="text-lg font-bold text-white mb-4">Riwayat Peminjaman Saya</h3>

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="py-3 px-5 border-b border-slate-800">Tanggal Pinjam</th>
                    <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                    <th class="py-3 px-5 border-b border-slate-800">Status</th>
                    <th class="py-3 px-5 border-b border-slate-800">Alat</th>
                    <th class="py-3 px-5 border-b border-slate-800 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-300 text-sm">
                @forelse($peminjamans as $item)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-5 border-b border-slate-800 text-slate-400">
                            {{ $item->tgl_pinjam }}
                        </td>
                        <td class="py-3 px-5 border-b border-slate-800 text-slate-400">
                            {{ $item->tgl_kembali_plan }}
                        </td>
                        <td class="py-3 px-5 border-b border-slate-800">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                                @if($item->status == 'diajukan')      bg-amber-500/10   text-amber-400   border-amber-500/20
                                @elseif($item->status == 'dipinjam')  bg-blue-500/10    text-blue-400    border-blue-500/20
                                @elseif($item->status == 'dikembalikan') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @elseif($item->status == 'telat')     bg-rose-500/10    text-rose-400    border-rose-500/20
                                @else                                  bg-slate-500/10   text-slate-400   border-slate-500/20
                                @endif">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-5 border-b border-slate-800">
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
                                        <span class="text-slate-300">{{ $detail->alat->nama_alat ?? 'Alat' }}
                                            <span class="text-xs text-slate-500">({{ $detail->jumlah }} pcs)</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>

                        {{-- Kolom Aksi: tombol Kembalikan hanya muncul jika status 'dipinjam' --}}
                        <td class="py-3 px-5 border-b border-slate-800 text-center">
                            @if($item->status == 'dipinjam')
                                <form action="{{ route('peminjam.pengembalian.kembalikan', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Konfirmasi pengembalian alat? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                                                   bg-emerald-500/10 hover:bg-emerald-500/20
                                                   text-emerald-400 border border-emerald-500/30
                                                   rounded-lg transition">
                                        <i class="fas fa-undo-alt"></i> Kembalikan
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

@endsection
