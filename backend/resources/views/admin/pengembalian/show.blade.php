@extends('layouts.app')

@section('title', 'Detail Pengembalian - Panel Admin')
@section('header-title', 'Detail Pengembalian')

@section('content')

<div class="bg-slate-900 rounded-lg shadow-sm border border-slate-700/50 p-6 max-w-3xl">

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-slate-400">ID Pengembalian</p>
            <p class="font-semibold text-white">#{{ $pengembalian->id }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-400">Peminjam</p>
            <p class="font-semibold text-white">{{ $pengembalian->peminjaman->user->name ?? 'User Dihapus' }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-400">Tanggal Kembali</p>
            <p class="font-semibold text-white">{{ $pengembalian->tgl_kembali }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-400">Kondisi Kembali</p>
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
                $kClass = $kondisiConfig[$pengembalian->kondisi_kembali] ?? 'bg-slate-700/50 text-slate-400 border-slate-600';
                $kIcon  = $kondisiIcon[$pengembalian->kondisi_kembali]   ?? '';
            @endphp
            <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 text-xs font-semibold rounded-full border {{ $kClass }}">
                {{ $kIcon }} {{ $pengembalian->kondisi_kembali }}
            </span>
        </div>
        <div>
            <p class="text-sm text-slate-400">Denda</p>
            <p class="font-semibold text-rose-400">Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-400">Petugas</p>
            <p class="font-semibold text-white">{{ $pengembalian->petugas->name ?? 'Sistem' }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-sm text-slate-400">Status Peminjaman</p>
            <p>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border
                    {{ $pengembalian->peminjaman->status == 'dikembalikan' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/20' : '' }}
                    {{ $pengembalian->peminjaman->status == 'telat' ? 'bg-rose-500/20 text-rose-400 border-rose-500/20' : '' }}
                    {{ $pengembalian->peminjaman->status == 'dipinjam' ? 'bg-blue-500/20 text-blue-400 border-blue-500/20' : '' }}">
                    {{ ucfirst($pengembalian->peminjaman->status) }}
                </span>
            </p>
        </div>
    </div>

    <hr class="my-4 border-slate-700/50">

    <h4 class="font-semibold mb-2 text-sm text-slate-300">Detail Alat yang Dikembalikan</h4>
    <ul class="list-disc list-inside space-y-1 text-sm text-slate-300">
        @foreach($pengembalian->peminjaman->detailPinjam as $detail)
            <li>
                <span class="font-semibold text-white">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                - {{ $detail->jumlah }} pcs
            </li>
        @endforeach
    </ul>

    <div class="mt-6 flex justify-end space-x-2">
        <a href="{{ route('admin.pengembalian.index') }}"
           class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>

@endsection