@extends('layouts.app')

@section('title', 'Proses Pengembalian - Panel Admin')
@section('header-title', 'Proses Pengembalian Alat')

@section('content')

<div class="max-w-2xl bg-slate-900 rounded-lg shadow-sm border border-slate-700/50 p-6">

    @if(session('error'))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.pengembalian.store') }}" method="POST">
        @csrf

        <!-- Pilih Peminjaman -->
        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-semibold mb-2">Pilih Peminjaman yang Belum Dikembalikan (Status: Dipinjam / Telat)</label>
            <select name="peminjaman_id" id="peminjaman_id" required
                    onchange="hitungDendaOtomatis(this)"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white">
                <option value="" data-tgl="">-- Pilih Peminjaman --</option>
                @foreach($peminjamanList as $pinjam)
                    @php
                        $statusLabel = $pinjam->status === 'telat' ? '🚨 TELAT' : '✅ DIPINJAM';
                    @endphp
                    <option value="{{ $pinjam->id }}"
                            data-tgl="{{ \Carbon\Carbon::parse($pinjam->tgl_kembali_plan)->format('Y-m-d') }}">
                        {{ $pinjam->user->name }} -
                        @foreach($pinjam->detailPinjam as $detail)
                            {{ $detail->alat->nama_alat }} ({{ $detail->jumlah }} pcs)
                            @if(!$loop->last), @endif
                        @endforeach
                        - Jatuh Tempo: {{ \Carbon\Carbon::parse($pinjam->tgl_kembali_plan)->format('d/m/Y') }}
                        | {{ $statusLabel }}
                    </option>
                @endforeach
            </select>
            @error('peminjaman_id')
                <span class="text-rose-400 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Kondisi Kembali -->
        <div class="mb-4">
            <label class="block text-slate-300 text-sm font-semibold mb-2">Kondisi Alat Saat Dikembalikan</label>
            <select name="kondisi_kembali" id="kondisi_kembali" required
                    onchange="updateKondisiBadge(this)"
                    class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white">
                <option value="" disabled {{ old('kondisi_kembali') ? '' : 'selected' }}>-- Pilih Kondisi Alat --</option>
                <option value="Baik"      {{ old('kondisi_kembali') == 'Baik'      ? 'selected' : '' }}>Baik</option>
                <option value="Rusak"     {{ old('kondisi_kembali') == 'Rusak'     ? 'selected' : '' }}>Rusak</option>
                <option value="Perbaikan" {{ old('kondisi_kembali') == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option>
            </select>
            <!-- Badge preview kondisi -->
            <div id="kondisi_badge" class="mt-2 hidden">
                <span id="kondisi_badge_text"
                      class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border"></span>
            </div>
            @error('kondisi_kembali')
                <span class="text-rose-400 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <script>
            const badgeConfig = {
                'Baik':      { bg: 'bg-emerald-500/20', text: 'text-emerald-400', border: 'border-emerald-500/30', icon: '✓' },
                'Rusak':     { bg: 'bg-rose-500/20',    text: 'text-rose-400',    border: 'border-rose-500/30',    icon: '✕' },
                'Perbaikan': { bg: 'bg-amber-500/20',   text: 'text-amber-400',   border: 'border-amber-500/30',   icon: '⚠' },
            };

            function updateKondisiBadge(select) {
                const val    = select.value;
                const badge  = document.getElementById('kondisi_badge');
                const span   = document.getElementById('kondisi_badge_text');
                const cfg    = badgeConfig[val];

                if (!cfg) { badge.classList.add('hidden'); return; }

                // Reset classes
                span.className = 'inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border';
                span.classList.add(cfg.bg, cfg.text, cfg.border);
                span.textContent = cfg.icon + ' ' + val;
                badge.classList.remove('hidden');
            }

            // Trigger badge jika ada old() value (validasi gagal)
            document.addEventListener('DOMContentLoaded', function () {
                const sel = document.getElementById('kondisi_kembali');
                if (sel.value) updateKondisiBadge(sel);
            });
        </script>

        <!-- Denda -->
        <div class="mb-6">
            <label class="block text-slate-300 text-sm font-semibold mb-2">Denda (Opsional)</label>

            <!-- Info keterlambatan (muncul otomatis saat pilih peminjaman) -->
            <div id="info_keterlambatan" class="hidden mb-2 px-3 py-2 rounded-lg text-xs font-medium border"></div>

            <input type="number" name="denda" id="denda" value="{{ old('denda', 0) }}" min="0"
                   placeholder="Masukkan nominal denda jika ada keterlambatan"
                   class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white placeholder-slate-500">
            <p class="text-xs text-slate-500 mt-1">
                * Denda dihitung otomatis <strong class="text-slate-400">Rp 1.000/hari</strong> jika telat — boleh diubah manual untuk keringanan.
            </p>
            @error('denda')
                <span class="text-rose-400 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <script>
            // ─── Konstanta ────────────────────────────────────────────────
            const DENDA_PER_HARI = 1000; // Rp 1.000 per hari keterlambatan

            // ─── Hitung denda otomatis saat pilih peminjaman ──────────────
            function hitungDendaOtomatis(select) {
                const option        = select.options[select.selectedIndex];
                const tglPlanStr    = option.getAttribute('data-tgl'); // format: YYYY-MM-DD
                const dendaInput    = document.getElementById('denda');
                const infoBox       = document.getElementById('info_keterlambatan');

                // Belum pilih / pilih opsi kosong
                if (!tglPlanStr) {
                    dendaInput.value = 0;
                    infoBox.classList.add('hidden');
                    return;
                }

                // Tanggal hari ini (jam 00:00:00 — supaya adil, tidak terhitung hari ini sebagai telat)
                const hariIni   = new Date();
                hariIni.setHours(0, 0, 0, 0);

                // Tanggal jatuh tempo
                const [y, m, d] = tglPlanStr.split('-').map(Number);
                const jatuhTempo = new Date(y, m - 1, d);
                jatuhTempo.setHours(0, 0, 0, 0);

                // Selisih dalam hari (1 hari = 86400000 ms)
                const selisihMs   = hariIni - jatuhTempo;
                const hariTelat   = Math.max(0, Math.floor(selisihMs / 86400000));
                const totalDenda  = hariTelat * DENDA_PER_HARI;

                // Isi field denda
                dendaInput.value = totalDenda;

                // Tampilkan info box
                infoBox.classList.remove('hidden');
                if (hariTelat > 0) {
                    infoBox.className = 'mb-2 px-3 py-2 rounded-lg text-xs font-medium border bg-rose-500/10 text-rose-400 border-rose-500/20';
                    infoBox.innerHTML =
                        '⚠ Terlambat <strong>' + hariTelat + ' hari</strong> ' +
                        '(jatuh tempo: ' + tglPlanStr.split('-').reverse().join('/') + '). ' +
                        'Denda otomatis: <strong>Rp ' + totalDenda.toLocaleString('id-ID') + '</strong>';
                } else {
                    infoBox.className = 'mb-2 px-3 py-2 rounded-lg text-xs font-medium border bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                    infoBox.innerHTML =
                        '✓ Tidak terlambat — jatuh tempo: ' + tglPlanStr.split('-').reverse().join('/') + '. Denda: <strong>Rp 0</strong>';
                }
            }
        </script>

        <!-- Tombol -->
        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.pengembalian.index') }}"
               class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <i class="fas fa-check mr-1"></i> Proses Pengembalian
            </button>
        </div>
    </form>
</div>

@endsection