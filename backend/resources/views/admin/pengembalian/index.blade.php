@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Pengembalian')

@section('content')

{{-- Notifikasi --}}
@if(session('success'))
    <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-lg shadow-sm text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="bg-slate-900 border border-slate-800 rounded-xl shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="p-5 border-b border-slate-800 flex flex-wrap justify-between items-center gap-3">
        <h3 class="text-lg font-bold text-white">Daftar Pengembalian</h3>
        <a href="{{ route('admin.pengembalian.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-1"></i> Proses Pengembalian
        </a>
    </div>

    {{-- Pencarian --}}
    <div class="p-4 border-b border-slate-800">
        <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari berdasarkan nama peminjam atau status..."
                   class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 text-sm font-semibold rounded-lg transition">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.pengembalian.index') }}"
                   class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 text-sm rounded-lg transition">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 border-b border-slate-800">Peminjam</th>
                    <th class="py-3 px-4 border-b border-slate-800">Alat Dipinjam</th>
                    <th class="py-3 px-4 border-b border-slate-800">Tgl Kembali</th>
                    <th class="py-3 px-4 border-b border-slate-800">Kondisi</th>
                    <th class="py-3 px-4 border-b border-slate-800">Denda</th>
                    <th class="py-3 px-4 border-b border-slate-800">Status</th>
                    <th class="py-3 px-4 border-b border-slate-800 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-300 text-sm">
                @forelse($pengembalian as $item)
                    @php
                        $detail = $item->peminjaman?->detailPinjam?->first();
                        $alat   = $detail?->alat;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition border-b border-slate-800/50">
                        {{-- Peminjam --}}
                        <td class="py-3 px-4 font-medium text-white">
                            {{ $item->peminjaman?->user?->name ?? 'User Dihapus' }}
                        </td>

                        {{-- Alat + Foto --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                @if($alat?->gambar)
                                    <img src="{{ asset('foto/' . $alat->gambar) }}"
                                         alt="{{ $alat->nama_alat }}"
                                         class="w-12 h-12 object-cover rounded-lg border border-slate-700"
                                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="w-12 h-12 bg-slate-800 rounded-lg border border-slate-700 items-center justify-center hidden">
                                        <i class="fas fa-image text-slate-500"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center">
                                        <i class="fas fa-image text-slate-500"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold {{ $alat ? 'text-white' : 'text-slate-500 italic' }}">
                                        {{ $alat?->nama_alat ?? 'Alat Dihapus' }}
                                    </p>
                                    <span class="text-xs bg-slate-700 px-2 py-0.5 rounded text-slate-300">
                                        {{ $detail?->jumlah ?? 0 }} pcs
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Tgl Kembali --}}
                        <td class="py-3 px-4 text-slate-400">{{ $item->tgl_kembali }}</td>

                        {{-- Kondisi --}}
                        <td class="py-3 px-4">{{ $item->kondisi_kembali }}</td>

                        {{-- Denda --}}
                        <td class="py-3 px-4 font-semibold text-rose-400">
                            Rp {{ number_format($item->denda, 0, ',', '.') }}
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-4">
                            @php $status = $item->peminjaman?->status ?? 'unknown'; @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                                {{ $status === 'dikembalikan' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : '' }}
                                {{ $status === 'telat'        ? 'bg-rose-500/10 text-rose-400 border-rose-500/20'         : '' }}
                                {{ $status === 'dipinjam'     ? 'bg-blue-500/10 text-blue-400 border-blue-500/20'         : '' }}
                                {{ $status === 'unknown'      ? 'bg-slate-700 text-slate-400 border-slate-600'             : '' }}">
                                {{ $status !== 'unknown' ? ucfirst($status) : 'Peminjaman Dihapus' }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.pengembalian.show', $item->id) }}"
                                   class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 px-3 py-1.5 rounded text-xs font-semibold transition">
                                    Detail
                                </a>
                                <form action="{{ route('admin.pengembalian.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus data pengembalian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-1.5 rounded text-xs font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Belum ada data pengembalian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t border-slate-800">
        {{ $pengembalian->links() }}
    </div>
</div>

@endsection