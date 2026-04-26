<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Pembayaran</title>
</head>
<body>
    <table border="1">
        <tr>
            <th colspan="2">Filter Aktif</th>
        </tr>
        <tr><td>Siswa ID</td><td>{{ $filters['siswa_id'] ?: '-' }}</td></tr>
        <tr><td>Kelas ID</td><td>{{ $filters['kelas_id'] ?: '-' }}</td></tr>
        <tr><td>Tanggal Mulai</td><td>{{ $filters['tanggal_mulai'] ?: '-' }}</td></tr>
        <tr><td>Tanggal Selesai</td><td>{{ $filters['tanggal_selesai'] ?: '-' }}</td></tr>
        <tr><td>Status</td><td>{{ $filters['status'] ?: '-' }}</td></tr>
        <tr><td>Metode Bayar</td><td>{{ $filters['metode_bayar'] ?: '-' }}</td></tr>
    </table>

    <br>

    <table border="1">
        <tr>
            <th>No</th>
            <th>Tanggal Bayar</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>Kelas</th>
            <th>Jenis Pembayaran</th>
            <th>Nominal</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Keterangan</th>
        </tr>
        @forelse($pembayaran as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($item->tanggal_bayar)->format('Y-m-d') }}</td>
                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $item->siswa->nis ?? '-' }}</td>
                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                <td>{{ (int) $item->nominal_bayar }}</td>
                <td>{{ $item->metode_bayar ?? '-' }}</td>
                <td>{{ $item->status ?? '-' }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10">Tidak ada data pembayaran sesuai filter.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>

