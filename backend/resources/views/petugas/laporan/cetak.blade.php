<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Peminjaman</title>
    <style>
        /* ── Reset & Base ─────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            padding: 24px 32px;
        }

        /* ── Header ───────────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .header p {
            font-size: 12px;
            color: #475569;
            margin-top: 2px;
        }

        /* ── Info Periode ─────────────────────────────── */
        .info-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .info-box .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .info-box .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            font-weight: 600;
        }
        .info-box .value {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
        }

        /* ── Table ────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        thead tr {
            background: #1e293b;
            color: #fff;
        }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        tbody td {
            padding: 7px 10px;
            vertical-align: top;
            color: #334155;
        }
        tbody tr:last-child {
            border-bottom: 2px solid #1e293b;
        }

        /* ── Status Badge ─────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: capitalize;
            border: 1px solid;
        }
        .badge-diajukan     { background: #fef9c3; color: #854d0e; border-color: #fde047; }
        .badge-dipinjam     { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .badge-telat        { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .badge-dikembalikan { background: #dcfce7; color: #166534; border-color: #86efac; }

        /* ── Footer ───────────────────────────────────── */
        .footer {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer .ttd {
            text-align: center;
        }
        .footer .ttd .ttd-space {
            height: 56px;
        }
        .footer .ttd .ttd-name {
            border-top: 1px solid #1e293b;
            padding-top: 4px;
            font-weight: 700;
            color: #1e293b;
            font-size: 12px;
            min-width: 160px;
        }

        /* ── Summary ──────────────────────────────────── */
        .summary {
            margin-bottom: 12px;
            font-size: 11px;
            color: #475569;
        }
        .summary strong {
            color: #1e293b;
        }

        /* ── Print ────────────────────────────────────── */
        @media print {
            body { padding: 12px 16px; }
            .no-print { display: none !important; }
            a { text-decoration: none; }
        }
    </style>
</head>
<body>

    {{-- ── Tombol Cetak (hanya muncul di layar, disembunyikan saat print) ── --}}
    <div class="no-print" style="text-align:right; margin-bottom:16px;">
        <button onclick="window.print()"
                style="background:#16a34a;color:#fff;border:none;padding:8px 18px;
                       border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
                style="background:#e2e8f0;color:#334155;border:none;padding:8px 18px;
                       border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;margin-left:8px;">
            ✕ Tutup
        </button>
    </div>

    {{-- ── Header Laporan ── --}}
    <div class="header">
        <h1>Laporan Peminjaman Alat</h1>
        <p>Sistem Informasi Peminjaman Alat — Dicetak pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    {{-- ── Info Periode & Filter ── --}}
    <div class="info-box">
        <div class="info-item">
            <span class="label">Periode</span>
            <span class="value">
                {{ \Carbon\Carbon::parse($tgl_awal)->translatedFormat('d F Y') }}
                &nbsp;s/d&nbsp;
                {{ \Carbon\Carbon::parse($tgl_akhir)->translatedFormat('d F Y') }}
            </span>
        </div>
        <div class="info-item">
            <span class="label">Filter Status</span>
            <span class="value">{{ $status ? ucfirst($status) : 'Semua Status' }}</span>
        </div>
        <div class="info-item">
            <span class="label">Total Data</span>
            <span class="value">{{ $peminjamans->count() }} peminjaman</span>
        </div>
    </div>

    {{-- ── Summary ── --}}
    @if($peminjamans->isEmpty())
        <p class="summary">Tidak ada data peminjaman untuk periode dan filter yang dipilih.</p>
    @else
        <p class="summary">
            Menampilkan <strong>{{ $peminjamans->count() }} data</strong> peminjaman
            dari tanggal <strong>{{ \Carbon\Carbon::parse($tgl_awal)->format('d/m/Y') }}</strong>
            hingga <strong>{{ \Carbon\Carbon::parse($tgl_akhir)->format('d/m/Y') }}</strong>
            @if($status)
                dengan status <strong>{{ ucfirst($status) }}</strong>
            @endif.
        </p>
    @endif

    {{-- ── Tabel Data ── --}}
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:18%">Nama Peminjam</th>
                <th style="width:28%">Alat yang Dipinjam</th>
                <th style="width:14%">Tgl Pinjam</th>
                <th style="width:14%">Rencana Kembali</th>
                <th style="width:12%">Status</th>
                <th style="width:10%">Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjamans as $item)
                @php
                    $tglPlan = \Carbon\Carbon::parse($item->tgl_kembali_plan)->startOfDay();
                    $hariIni = \Carbon\Carbon::now()->startOfDay();
                    $hariTelat = $hariIni->greaterThan($tglPlan)
                                 && in_array($item->status, ['dipinjam','telat'])
                                 ? $tglPlan->diffInDays($hariIni) : 0;
                    $dendaOtomatis = $hariTelat * 1000;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->user->name ?? 'User Dihapus' }}</td>
                    <td>
                        <ul style="list-style:disc;padding-left:14px;margin:0;">
                            @foreach($item->detailPinjam as $detail)
                                <li>
                                    {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}
                                    <span style="font-size:10px;color:#64748b;">({{ $detail->jumlah }} pcs)</span>
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $badgeClass = match($item->status) {
                                'diajukan'     => 'badge-diajukan',
                                'dipinjam'     => $hariTelat > 0 ? 'badge-telat' : 'badge-dipinjam',
                                'telat'        => 'badge-telat',
                                'dikembalikan' => 'badge-dikembalikan',
                                default        => '',
                            };
                            $labelStatus = ($hariTelat > 0 && in_array($item->status, ['dipinjam','telat']))
                                           ? 'Telat' : ucfirst($item->status);
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $labelStatus }}</span>
                    </td>
                    <td>
                        @if($dendaOtomatis > 0)
                            <span style="color:#b91c1c;font-weight:700;">
                                Rp {{ number_format($dendaOtomatis, 0, ',', '.') }}
                            </span>
                            <br><span style="font-size:10px;color:#94a3b8;">{{ $hariTelat }} hari</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">
                        Tidak ada data untuk ditampilkan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer & TTD ── --}}
    <div class="footer">
        <div style="line-height:1.6;">
            <div>Dicetak oleh: <strong>{{ auth()->user()->name ?? '-' }}</strong></div>
            <div>Tanggal cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</div>
        </div>
        <div class="ttd">
            <div class="ttd-space"></div>
            <div class="ttd-name">Petugas</div>
            <div style="font-size:10px;color:#64748b;margin-top:2px;">( {{ auth()->user()->name ?? '________________' }} )</div>
        </div>
    </div>

    {{-- Auto-trigger print dialog setelah halaman load ── --}}
    <script>
        window.addEventListener('load', function () {
            // Tunda sedikit agar rendering selesai terlebih dahulu
            setTimeout(function () { window.print(); }, 400);
        });
    </script>

</body>
</html>
