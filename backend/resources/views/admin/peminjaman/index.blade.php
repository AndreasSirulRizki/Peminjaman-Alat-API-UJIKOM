@extends('layouts.app')

@section('title', 'Kelola Peminjaman - Panel Admin')
@section('header-title', 'Manajemen Transaksi Peminjaman')

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
            <h3 class="text-lg font-bold text-white">Daftar Transaksi Peminjaman</h3>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.peminjaman.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari peminjam / status..."
                        class="w-full px-3 py-2 text-sm bg-slate-800 border border-slate-700 rounded-l-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.peminjaman.index') }}"
                            class="ml-2 bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-2 text-sm rounded-lg flex items-center transition border border-slate-700" title="Reset Pencarian">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.peminjaman.create') }}"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap shadow-lg shadow-indigo-500/20">
                    + Tambah Peminjaman
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800">Peminjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Alat Dipinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Tgl Pinjam / Rencana Kembali</th>
                        <th class="py-3 px-5 border-b border-slate-800">Status</th>
                        <th class="py-3 px-5 border-b border-slate-800 w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($peminjamans as $peminjaman)
                        <tr class="hover:bg-slate-800/40 transition align-top">
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">
                                {{ $peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <ul class="space-y-1">
                                    @foreach($peminjaman->detailPinjam as $detail)
                                        <li class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-200">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                            <span class="text-xs bg-slate-800 px-1.5 py-0.5 rounded text-slate-400">{{ $detail->jumlah }} pcs</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800 text-xs text-slate-400">
                                <span class="block">Pinjam: {{ $peminjaman->tgl_pinjam }}</span>
                                <span class="block font-semibold text-slate-300">Rencana: {{ $peminjaman->tgl_kembali_plan }}</span>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($peminjaman->status === 'diajukan') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($peminjaman->status === 'dipinjam') bg-indigo-500/10 text-indigo-400 border border-indigo-500/20
                                    @elseif($peminjaman->status === 'dikembalikan') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                    {{ ucfirst($peminjaman->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                <div class="flex flex-col gap-2">
                                    <form action="{{ route('admin.peminjaman.updateStatus', $peminjaman->id) }}" method="POST" class="flex items-center gap-1">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                            class="text-xs bg-slate-800 border border-slate-700 text-slate-200 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="diajukan" {{ $peminjaman->status === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                            <option value="dipinjam" {{ $peminjaman->status === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                            <option value="dikembalikan" {{ $peminjaman->status === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                                            <option value="telat" {{ $peminjaman->status === 'telat' ? 'selected' : '' }}>Telat</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('admin.peminjaman.destroy', $peminjaman->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data peminjaman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Belum ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800">
            {{ $peminjamans->links() }}
        </div>
    </div>
@endsection