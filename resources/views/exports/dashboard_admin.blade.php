<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Dashboard Admin</title>
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="2">Ringkasan Dashboard Admin</th>
        </tr>
        <tr>
            <td>Total Tagihan Bulan Ini</td>
            <td>{{ $totalTagihanBulanIni }}</td>
        </tr>
        <tr>
            <td>Total Pembayaran Masuk</td>
            <td>{{ $totalPembayaranMasuk }}</td>
        </tr>
        <tr>
            <td>Jumlah Siswa Belum Lunas</td>
            <td>{{ $jumlahSiswaBelumLunas }}</td>
        </tr>
        <tr>
            <td>Jumlah Siswa Aktif</td>
            <td>{{ $jumlahSiswaAktif }}</td>
        </tr>
    </table>

    <br>

    <table border="1">
        <tr>
            <th colspan="9">Detail Tagihan (Sesuai Filter)</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Tagihan</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jenis Pembayaran</th>
            <th>Nominal Tagihan</th>
            <th>Sisa Tagihan</th>
            <th>Status</th>
        </tr>
        @forelse($exportRows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($row->tanggal_tagihan)->format('Y-m-d') }}</td>
                <td>{{ $row->siswa->nis ?? '-' }}</td>
                <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $row->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                <td>{{ (int) $row->nominal_tagihan }}</td>
                <td>{{ (int) $row->sisa_tagihan }}</td>
                <td>{{ $row->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">Tidak ada data sesuai filter.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>

