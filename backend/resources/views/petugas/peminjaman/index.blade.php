@extends('layouts.app-simple')

@section('title', 'Kelola Peminjaman - Petugas')
@section('header-title', 'Kelola Peminjaman')

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

    <h3 class="text-lg font-bold text-white mb-4">Daftar Pengajuan & Transaksi Peminjaman</h3>

    <div class="bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-5 border-b border-slate-800">Peminjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Tanggal Pinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Rencana Kembali</th>
                        <th class="py-3 px-5 border-b border-slate-800">Status</th>
                        <th class="py-3 px-5 border-b border-slate-800">Alat yang Dipinjam</th>
                        <th class="py-3 px-5 border-b border-slate-800">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-5 border-b border-slate-800 font-medium text-white">
                                {{ $item->user->name ?? '-' }}
                            </td>
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
                                <ul class="list-disc pl-4 text-slate-400">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-5 border-b border-slate-800">
                                @if($item->status == 'diajukan')
                                    {{-- TOMBOL SETUJUI --}}
                                    <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400
                                                   border border-emerald-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition">
                                            Setujui
                                        </button>
                                    </form>

                                    {{-- TOMBOL TOLAK (BARU!) --}}
                                    <form action="{{ route('petugas.peminjaman.tolak', $item->id) }}" method="POST" class="inline-block ml-1">
                                        @csrf
                                        <button type="submit"
                                            class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400
                                                   border border-rose-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition"
                                            onclick="return confirm('Yakin ingin menolak pengajuan ini? Data akan dihapus.')">
                                            Tolak
                                        </button>
                                    </form>

                                @elseif($item->status == 'dipinjam')
                                    {{-- TOMBOL TERIMA KEMBALI (PROSES PENGEMBALIAN) --}}
                                    <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="kondisi_kembali" value="Baik">
                                        <input type="hidden" name="denda" value="0">
                                        <button type="submit"
                                            class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400
                                                   border border-amber-500/20 text-xs font-semibold
                                                   px-3 py-1.5 rounded-lg transition"
                                            onclick="return confirm('Proses pengembalian alat ini?')">
                                            Terima Kembali
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-xs italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection