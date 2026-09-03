@extends('layouts.app-simple')

@section('title', 'Laporan Peminjaman - Petugas')
@section('header-title', 'Laporan Peminjaman')

@section('content')

    <div class="mb-6">
        <h3 class="text-lg font-bold text-white">Laporan Peminjaman Alat</h3>
        <p class="text-sm text-slate-400 mt-1">Rekap seluruh data peminjaman. Gunakan filter tanggal untuk mempersempit hasil.</p>
    </div>

    {{-- ===== FORM FILTER ===== --}}
    <form method="GET" action="{{ route('petugas.laporan') }}"
          class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-6 flex flex-wrap gap-4 items-end">

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Tanggal Awal</label>
            <input type="date" name="tanggal_awal"
                   value="{{ $tanggalAwal }}"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white
                          placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir"
                   value="{{ $tanggalAkhir }}"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white
                          placeholder-slate-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Status</label>
            <select name="status"
                    class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white
                           focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                <option value="" {{ !$status ? 'selected' : '' }}>Semua Status</option>
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
            <a href="{{ route('petugas.laporan') }}"
               class="bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-times mr-1.5"></i> Reset
            </a>
            <button type="button" onclick="window.print()"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <i class="fas fa-print mr-1.5"></i> Cetak
            </button>
        </div>
    </form>

    {{-- ===== TABEL LAPORAN ===== --}}
    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
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
                    @forelse ($peminjaman as $item)
                        <tr class="hover:bg-slate-800/40 transition align-top border-b border-slate-800/30">
                            <td class="py-3 px-5 text-slate-400">{{ $loop->iteration }}</td>

                            <td class="py-3 px-5 font-medium text-white">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>

                            <td class="py-3 px-5">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>
                                            {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}
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
                                        'diajukan'     => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                        'dipinjam'     => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'telat'        => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                        'dikembalikan' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        default        => 'bg-slate-700 text-slate-300 border-slate-600',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border {{ $badgeClass }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada data laporan yang sesuai dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: total data --}}
        @if($peminjaman->isNotEmpty())
            <div class="px-5 py-3 border-t border-slate-800 text-xs text-slate-500">
                Total: <span class="text-slate-300 font-medium">{{ $peminjaman->count() }} data</span>
            </div>
        @endif
    </div>

@endsection
