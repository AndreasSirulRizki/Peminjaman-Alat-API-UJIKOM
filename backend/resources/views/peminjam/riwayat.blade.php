@extends('layouts.peminjam')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman Saya')

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <h3 class="text-lg font-bold text-white mb-4">Riwayat Peminjaman Saya</h3>

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="py-3 px-5 border-b border-slate-800">Tanggal Pinjam</th>
                    <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                    <th class="py-3 px-5 border-b border-slate-800">Status</th>
                    <th class="py-3 px-5 border-b border-slate-800">Alat</th>
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
                                @if($item->status == 'diajukan')    bg-amber-500/10  text-amber-400  border-amber-500/20
                                @elseif($item->status == 'dipinjam') bg-blue-500/10   text-blue-400   border-blue-500/20
                                @elseif($item->status == 'selesai')  bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @else                                bg-rose-500/10   text-rose-400   border-rose-500/20
                                @endif">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-5 border-b border-slate-800">
                            <ul class="pl-4 list-disc text-slate-400">
                                @foreach($item->detailPinjam as $detail)
                                    <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-500">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
