@extends('layouts.app-simple')

@section('title', 'Laporan Peminjaman - Petugas')
@section('panel-title', 'Panel Petugas Lab')

@section('content')

{{-- ============================================================ --}}
{{-- FORM CETAK (POST, target _blank)                             --}}
{{-- ============================================================ --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6">

    <h3 class="text-lg font-bold text-white mb-1">Cetak Laporan Peminjaman</h3>
    <p class="text-sm text-slate-400 mb-5">Gunakan filter tanggal untuk mempersempit hasil laporan.</p>

    {{-- Alert error validasi --}}
    @if($errors->any())
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-lg text-sm">
            <div class="flex items-center gap-2 mb-1 font-medium">
                <i class="fas fa-circle-exclamation"></i>
                <span>Terjadi kesalahan:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petugas.laporan.cetak') }}" method="POST" target="_blank">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

            {{-- Tanggal Awal --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Tanggal Awal <span class="text-rose-400">*</span>
                </label>
                <input type="date" name="tgl_awal" value="{{ old('tgl_awal') }}" required
                       class="w-full px-3 py-2 bg-slate-800 border rounded-lg text-sm text-white
                              focus:outline-none focus:ring-2 focus:ring-indigo-500/50
                              {{ $errors->has('tgl_awal') ? 'border-rose-500/60' : 'border-slate-700' }}">
                @error('tgl_awal')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tanggal Akhir --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Tanggal Akhir <span class="text-rose-400">*</span>
                </label>
                <input type="date" name="tgl_akhir" value="{{ old('tgl_akhir') }}" required
                       class="w-full px-3 py-2 bg-slate-800 border rounded-lg text-sm text-white
                              focus:outline-none focus:ring-2 focus:ring-indigo-500/50
                              {{ $errors->has('tgl_akhir') ? 'border-rose-500/60' : 'border-slate-700' }}">
                @error('tgl_akhir')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Status <span class="text-slate-500 font-normal">(Opsional)</span>
                </label>
                <select name="status"
                        class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <option value="">-- Semua Status --</option>
                    <option value="diajukan"     {{ old('status') == 'diajukan'     ? 'selected' : '' }}>Diajukan</option>
                    <option value="dipinjam"     {{ old('status') == 'dipinjam'     ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ old('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="telat"        {{ old('status') == 'telat'        ? 'selected' : '' }}>Telat</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                <i class="fas fa-print"></i>
                Cetak Laporan
            </button>
        </div>
    </form>
</div>

{{-- ============================================================ --}}
{{-- TABEL PREVIEW DATA PEMINJAMAN                                --}}
{{-- ============================================================ --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">

    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-white">Daftar Peminjaman</h3>
        <span class="text-xs text-slate-400">
            Total: <span class="text-slate-200 font-semibold">{{ $peminjamans->count() }} data</span>
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="py-3 px-5 border-b border-slate-800">No</th>
                    <th class="py-3 px-5 border-b border-slate-800">Nama Peminjam</th>
                    <th class="py-3 px-5 border-b border-slate-800">Alat yang Dipinjam</th>
                    <th class="py-3 px-5 border-b border-slate-800">Tgl Pinjam</th>
                    <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                    <th class="py-3 px-5 border-b border-slate-800">Status</th>
                </tr>
            </thead>
            <tbody class="text-slate-300 text-sm">
                @forelse($peminjamans as $item)
                    <tr class="hover:bg-slate-800/40 transition align-top border-b border-slate-800/30">

                        <td class="py-3 px-5 text-slate-400">{{ $loop->iteration }}</td>

                        <td class="py-3 px-5 font-medium text-white">
                            {{ $item->user->name ?? 'User Dihapus' }}
                        </td>

                        <td class="py-3 px-5">
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach($item->detailPinjam as $detail)
                                    <li>
                                        {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                        <span class="text-xs bg-slate-700 px-1.5 py-0.5 rounded text-slate-300">
                                            {{ $detail->jumlah }} pcs
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>

                        <td class="py-3 px-5 text-slate-400">{{ $item->tgl_pinjam }}</td>
                        <td class="py-3 px-5 text-slate-400">{{ $item->tgl_kembali_plan }}</td>

                        <td class="py-3 px-5">
                            @php
                                $badgeClass = match($item->status) {
                                    'diajukan'     => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'dipinjam'     => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'telat'        => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'dikembalikan' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    default        => 'bg-slate-700/50 text-slate-300 border-slate-600',
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full border {{ $badgeClass }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-500">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            Belum ada data peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
