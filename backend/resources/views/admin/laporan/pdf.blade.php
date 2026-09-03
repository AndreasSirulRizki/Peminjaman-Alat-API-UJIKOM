<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            padding: 20px;
            color: #1a1a1a;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #1e293b;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }

        /* Badge status */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-diajukan    { background: #fef08a; color: #713f12; }
        .badge-dipinjam    { background: #bfdbfe; color: #1e3a8a; }
        .badge-dikembalikan { background: #bbf7d0; color: #14532d; }
        .badge-telat       { background: #fecaca; color: #7f1d1d; }

        .total {
            font-weight: bold;
            font-size: 12px;
            margin-top: 14px;
        }

        .footer {
            margin-top: 16px;
            text-align: right;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .empty-row td {
            text-align: center;
            padding: 24px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <h1>{{ $title }}</h1>
    <div class="subtitle">
        {{-- Periode tanggal --}}
        @if($start_date && $end_date)
            Periode: {{ $start_date }} s/d {{ $end_date }}
        @elseif($start_date)
            Periode: Mulai {{ $start_date }}
        @elseif($end_date)
            Periode: Sampai {{ $end_date }}
        @else
            Periode: Semua Periode
        @endif

        {{-- Filter status --}}
        @if($status)
            &nbsp;|&nbsp; Status: <strong>{{ ucfirst($status) }}</strong>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:16%">Peminjam</th>
                <th style="width:24%">Alat</th>
                <th style="width:7%" class="text-center">Jml</th>
                <th style="width:11%">Tgl Pinjam</th>
                <th style="width:13%">Rencana Kembali</th>
                <th style="width:13%">Tgl Kembali</th>
                <th style="width:12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjaman as $pinjam)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>

                    <td>{{ $pinjam->user->name ?? '<i>User dihapus</i>' }}</td>

                    {{-- Kolom alat — gabungkan semua alat dalam satu baris --}}
                    <td>
                        @foreach($pinjam->detailPinjam as $detail)
                            {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}@if(!$loop->last)<br>@endif
                        @endforeach
                    </td>

                    {{-- Kolom jumlah — sesuaikan baris dengan kolom alat --}}
                    <td class="text-center">
                        @foreach($pinjam->detailPinjam as $detail)
                            {{ $detail->jumlah }}@if(!$loop->last)<br>@endif
                        @endforeach
                    </td>

                    {{--
                        tgl_pinjam dan tgl_kembali_plan adalah Carbon object (di-cast di Model).
                        Gunakan ->format() untuk menghindari error saat ditampilkan di DomPDF.
                    --}}
                    <td>
                        {{ $pinjam->tgl_pinjam ? $pinjam->tgl_pinjam->format('d-m-Y') : '-' }}
                    </td>

                    <td>
                        {{ $pinjam->tgl_kembali_plan ? $pinjam->tgl_kembali_plan->format('d-m-Y') : '-' }}
                    </td>

                    <td>
                        {{-- tgl_kembali di model Pengembalian juga di-cast Carbon --}}
                        {{ $pinjam->pengembalian && $pinjam->pengembalian->tgl_kembali
                            ? $pinjam->pengembalian->tgl_kembali->format('d-m-Y')
                            : '-' }}
                    </td>

                    <td>
                        <span class="badge badge-{{ $pinjam->status }}">
                            {{ ucfirst($pinjam->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="8">
                        Tidak ada data peminjaman yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Total Data: {{ $total }} transaksi
    </div>

    <div class="footer">
        Dicetak pada: {{ $generated_at }}
    </div>

</body>
</html>
