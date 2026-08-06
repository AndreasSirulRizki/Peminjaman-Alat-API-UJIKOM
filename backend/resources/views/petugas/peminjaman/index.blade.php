<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <nav class="bg-emerald-700 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <span class="font-bold text-lg">Panel Petugas Lab</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white text-emerald-700 hover:bg-gray-100 text-sm font-semibold px-4 py-2 rounded-lg transition">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar Pengajuan & Transaksi Peminjaman</h3>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="py-3 px-4 border-b">Peminjam</th>
                            <th class="py-3 px-4 border-b">Tanggal Pinjam</th>
                            <th class="py-3 px-4 border-b">Rencana Kembali</th>
                            <th class="py-3 px-4 border-b">Status</th>
                            <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                            <th class="py-3 px-4 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        @forelse($peminjamans as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 border-b">{{ $item->user->name ?? '-' }}</td>
                                <td class="py-3 px-4 border-b">{{ $item->tgl_pinjam }}</td>
                                <td class="py-3 px-4 border-b">{{ $item->tgl_kembali_plan }}</td>
                                <td class="py-3 px-4 border-b">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                        @if($item->status == 'diajukan') bg-amber-100 text-amber-800
                                        @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-800
                                        @elseif($item->status == 'selesai') bg-emerald-100 text-emerald-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b">
                                    <ul class="list-disc pl-4">
                                        @foreach($item->detailPinjam as $detail)
                                            <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="py-3 px-4 border-b">
                                    @if($item->status == 'diajukan')
                                        <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded transition">Setujui</button>
                                        </form>
                                    @elseif($item->status == 'dipinjam')
                                        <!-- Form Sederhana Proses Pengembalian -->
                                        <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="kondisi_kembali" value="Baik">
                                            <input type="hidden" name="denda" value="0">
                                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded transition"
                                                onclick="return confirm('Proses pengembalian alat ini?')">
                                                Terima Kembali
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">Belum ada data peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>