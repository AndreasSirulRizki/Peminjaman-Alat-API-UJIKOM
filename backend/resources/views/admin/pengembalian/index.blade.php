@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Pengembalian')

@section('content')

<!-- Notifikasi -->
@if(session('success'))
    <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-lg shadow-sm text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="bg-slate-900 rounded-lg shadow-sm overflow-hidden border border-slate-700/50">

    <!-- Header -->
    <div class="p-5 border-b border-slate-700/50 flex flex-wrap justify-between items-center gap-3">
        <h3 class="text-lg font-bold text-white">Daftar Pengembalian</h3>
        <a href="{{ route('admin.pengembalian.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-1"></i> Proses Pengembalian
        </a>
    </div>

    <!-- Pencarian -->
    <div class="p-4 border-b border-slate-700/50">
        <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari berdasarkan nama peminjam atau status..."
                   class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-white placeholder-slate-500">
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

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-sm uppercase tracking-wider">
                    <th class="py-3 px-4 border-b border-slate-700/50">ID</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Peminjam</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Alat Dipinjam</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Tgl Kembali</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Kondisi</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Denda</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Petugas</th>
                    <th class="py-3 px-4 border-b border-slate-700/50">Status</th>
                    <th class="py-3 px-4 border-b border-slate-700/50 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-slate-300 text-sm">
                @forelse($pengembalian as $item)
                    <tr class="hover:bg-slate-800/30 transition align-top border-b border-slate-700/30">
                        <td class="py-3 px-4 font-medium text-white">#{{ $item->id }}</td>
                        <td class="py-3 px-4 font-medium text-white">
                            {{ $item->peminjaman->user->name ?? 'User Dihapus' }}
                        </td>
                        <td class="py-3 px-4">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($item->peminjaman->detailPinjam as $detail)
                                    <li>
                                        <span class="font-semibold text-white">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                        <span class="text-xs bg-slate-700 px-1.5 py-0.5 rounded text-slate-300">{{ $detail->jumlah }} pcs</span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-3 px-4">{{ $item->tgl_kembali }}</td>
                        <td class="py-3 px-4">
                            @php
                                $kondisiConfig = [
                                    'Baik'      => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                    'Rusak'     => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                                    'Perbaikan' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                ];
                                $kondisiIcon = [
                                    'Baik'      => '✓',
                                    'Rusak'     => '✕',
                                    'Perbaikan' => '⚠',
                                ];
                                $kondisiClass = $kondisiConfig[$item->kondisi_kembali] ?? 'bg-slate-700/50 text-slate-400 border-slate-600';
                                $kondisiIcon  = $kondisiIcon[$item->kondisi_kembali]  ?? '';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full border {{ $kondisiClass }}">
                                {{ $kondisiIcon }} {{ $item->kondisi_kembali }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-rose-400">
                            Rp {{ number_format($item->denda, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">{{ $item->petugas->name ?? 'Sistem' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                {{ $item->peminjaman->status == 'dikembalikan' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20' : '' }}
                                {{ $item->peminjaman->status == 'telat' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/20' : '' }}
                                {{ $item->peminjaman->status == 'dipinjam' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/20' : '' }}">
                                {{ ucfirst($item->peminjaman->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.pengembalian.show', $item->id) }}"
                                   class="bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-400 px-3 py-1.5 rounded text-xs font-semibold transition border border-indigo-500/20">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                <form action="{{ route('admin.pengembalian.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data pengembalian ini? Stok akan dikembalikan ke kondisi semula.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 px-3 py-1.5 rounded text-xs font-semibold transition border border-rose-500/20">
                                        <i class="fas fa-trash mr-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-6 text-center text-slate-500">Belum ada data pengembalian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 border-t border-slate-700/50 bg-slate-800/30">
        {{ $pengembalian->links() }}
    </div>
</div>

@endsection