<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Tagihan Siswa</title>
</head>
<body>
    @php
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $labelPeriode = 'Semua Periode';
        if (!empty($filters['bulan']) && !empty($filters['tahun'])) {
            $labelPeriode = ($namaBulan[(int) $filters['bulan']] ?? 'Bulan') . ' ' . $filters['tahun'];
        } elseif (!empty($filters['tahun'])) {
            $labelPeriode = 'Tahun ' . $filters['tahun'];
        } elseif (!empty($filters['bulan'])) {
            $labelPeriode = $namaBulan[(int) $filters['bulan']] ?? 'Bulan';
        }
    @endphp

    <table border="1">
        <tr style="background:#dbeafe;">
            <th colspan="2">Ringkasan Tagihan Siswa</th>
        </tr>
        <tr>
            <td>Nama Siswa</td>
            <td>{{ $siswa->nama_siswa ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIS</td>
            <td>{{ $siswa->nis ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ $labelPeriode }}</td>
        </tr>
        <tr>
            <td>Total Nominal Tagihan</td>
            <td>{{ (int) $totalNominalTagihan }}</td>
        </tr>
        <tr>
            <td>Total Sisa Tagihan</td>
            <td>{{ (int) $totalSisaTagihan }}</td>
        </tr>
        <tr>
            <td>Total Pembayaran Masuk</td>
            <td>{{ (int) $totalPembayaran }}</td>
        </tr>
    </table>

    <br>

    <table border="1">
        <tr style="background:#dcfce7;">
            <th colspan="10">Detail Tagihan</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Tagihan</th>
            <th>Periode</th>
            <th>Jenis Pembayaran</th>
            <th>Nominal Tagihan</th>
            <th>Sisa Tagihan</th>
            <th>Total Dibayar</th>
            <th>Status</th>
            <th>Jatuh Tempo</th>
            <th>Keterangan</th>
        </tr>
        @forelse($tagihanRows as $index => $row)
            @php
                $periodeTagihan = ($row->periode_bulan && $row->periode_tahun)
                    ? ($namaBulan[(int) $row->periode_bulan] ?? $row->periode_bulan) . ' ' . $row->periode_tahun
                    : '-';
                $totalDibayar = max(0, ((int) $row->nominal_tagihan - (int) $row->sisa_tagihan));
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($row->tanggal_tagihan)->format('Y-m-d') }}</td>
                <td>{{ $periodeTagihan }}</td>
                <td>{{ $row->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                <td>{{ (int) $row->nominal_tagihan }}</td>
                <td>{{ (int) $row->sisa_tagihan }}</td>
                <td>{{ $totalDibayar }}</td>
                <td>{{ $row->status ?? '-' }}</td>
                <td>{{ optional($row->jatuh_tempo)->format('Y-m-d') }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10">Tidak ada data tagihan.</td>
            </tr>
        @endforelse
        <tr style="background:#f8fafc;">
            <td colspan="4"><strong>TOTAL TAGIHAN</strong></td>
            <td><strong>{{ (int) $totalNominalTagihan }}</strong></td>
            <td><strong>{{ (int) $totalSisaTagihan }}</strong></td>
            <td><strong>{{ max(0, (int) $totalNominalTagihan - (int) $totalSisaTagihan) }}</strong></td>
            <td colspan="3"></td>
        </tr>
    </table>

    <br>

    <table border="1">
        <tr style="background:#fee2e2;">
            <th colspan="8">Detail Pembayaran</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Bayar</th>
            <th>Jenis Pembayaran</th>
            <th>Nominal Bayar</th>
            <th>Status Pembayaran</th>
            <th>Status Tagihan</th>
            <th>Metode</th>
            <th>Keterangan</th>
        </tr>
        @forelse($pembayaranRows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($row->tanggal_bayar)->format('Y-m-d') }}</td>
                <td>{{ $row->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                <td>{{ (int) $row->nominal_bayar }}</td>
                <td>{{ $row->status ?? '-' }}</td>
                <td>{{ $row->tagihan->status ?? '-' }}</td>
                <td>{{ $row->metode_bayar ?? '-' }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Tidak ada data pembayaran.</td>
            </tr>
        @endforelse
        <tr style="background:#f8fafc;">
            <td colspan="3"><strong>TOTAL PEMBAYARAN</strong></td>
            <td><strong>{{ (int) $totalPembayaran }}</strong></td>
            <td colspan="4"></td>
        </tr>
    </table>
</body>
</html>
