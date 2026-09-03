@extends('layouts.peminjam')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat Tersedia')

@section('content')

    {{-- Flash messages --}}
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

    {{-- Validasi errors --}}
    @if($errors->any())
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-sm">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h3 class="text-lg font-bold text-white mb-4">Katalog Alat Tersedia</h3>

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
        @csrf

        {{-- Pilih tanggal rencana kembali --}}
        <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 p-5 mb-5">
            <label class="block text-slate-300 text-sm font-medium mb-2">Rencana Tanggal Kembali</label>
            <input type="date" name="tgl_kembali_plan" required
                class="w-full md:w-64 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg
                       text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        {{-- Tabel katalog alat --}}
        <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden mb-5">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800 w-16 text-center">Pilih</th>
                        <th class="py-3 px-5 border-b border-slate-800">Nama Alat</th>
                        <th class="py-3 px-5 border-b border-slate-800">Kategori</th>
                        <th class="py-3 px-5 border-b border-slate-800">Stok Tersedia</th>
                        <th class="py-3 px-5 border-b border-slate-800 w-40">Jumlah Pinjam</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($alats as $alat)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-5 border-b border-slate-800 text-center">
                                <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}"
                                    class="w-4 h-4 accent-indigo-500"
                                    onchange="toggleJumlah(this)">
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">
                                {{ $alat->nama_alat }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 text-slate-400">
                                {{ $alat->kategori->nama_kategori ?? '-' }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold
                                             bg-emerald-500/10 text-emerald-400">
                                    {{ $alat->stok }}
                                </span>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <input type="number" name="jumlah[{{ $alat->id }}]"
                                    value="1" min="1" max="{{ $alat->stok }}"
                                    class="w-24 px-2 py-1.5 bg-slate-800 border border-slate-700
                                           rounded-lg text-slate-100 text-sm focus:outline-none
                                           focus:ring-2 focus:ring-indigo-500">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                Tidak ada alat yang tersedia saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit"
            class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold px-5 py-2.5
                   rounded-lg transition shadow-lg shadow-indigo-500/20">
            Ajukan Peminjaman
        </button>
    </form>

@endsection
