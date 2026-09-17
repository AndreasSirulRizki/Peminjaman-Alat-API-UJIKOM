@extends('layouts.app')

@section('title', 'Kelola Alat - Panel Admin')
@section('header-title', 'Manajemen Data Alat')

@section('content')
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

    <div class="bg-slate-900 rounded-xl shadow-sm overflow-hidden border border-slate-800">
        <div class="p-5 border-b border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-white">Daftar Alat</h3>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.alat.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat, kategori, kondisi..."
                        class="w-full px-3 py-2 text-sm bg-slate-800 border border-slate-700 rounded-l-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.alat.index') }}"
                            class="ml-2 bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-2 text-sm rounded-lg flex items-center transition border border-slate-700" title="Reset Pencarian">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.alat.create') }}"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap shadow-lg shadow-indigo-500/20">
                    + Tambah Alat
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800 w-20">Foto</th>
                        <th class="py-3 px-5 border-b border-slate-800">Nama Alat</th>
                        <th class="py-3 px-5 border-b border-slate-800">Kategori</th>
                        <th class="py-3 px-5 border-b border-slate-800">Stok</th>
                        <th class="py-3 px-5 border-b border-slate-800">Kondisi</th>
                        <th class="py-3 px-5 border-b border-slate-800 w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($alats as $alat)
                        <tr class="hover:bg-slate-800/40 transition">
                            {{-- Kolom Foto --}}
                            <td class="py-3 px-5 border-b border-slate-800">
                                @if($alat->gambar)
                                    <img src="{{ asset('foto/' . $alat->gambar) }}"
                                         alt="{{ $alat->nama_alat }}"
                                         class="w-12 h-12 object-cover rounded-lg border border-slate-700"
                                         onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22%3E%3Crect width=%2248%22 height=%2248%22 rx=%228%22 fill=%22%231e293b%22 stroke=%22%23334155%22 stroke-width=%221%22/%3E%3Ctext x=%2224%22 y=%2228%22 text-anchor=%22middle%22 font-size=%229%22 fill=%22%2364748b%22 font-family=%22sans-serif%22%3ENo Foto%3C/text%3E%3C/svg%3E';">
                                @else
                                    <div class="w-12 h-12 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center" title="Tidak ada foto">
                                        <i class="fas fa-image text-slate-500 text-sm"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">{{ $alat->nama_alat }}</td>
                            <td class="py-3 px-5 border-b border-slate-800">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $alat->stok > 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                    {{ $alat->stok }}
                                </span>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 text-slate-400">{{ $alat->status_kondisi }}</td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.alat.edit', $alat->id) }}"
                                        class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.alat.destroy', $alat->id) }}"
                                        method="POST" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada data alat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $alats->links() }}
        </div>
    </div>
@endsection