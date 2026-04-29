<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran</title>
    <style>
        @page {
            margin: 12px 14px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .kwitansi {
            border: 1.5px solid #0f172a;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background: #0f172a;
            color: #fff;
            padding: 10px 12px;
        }
        .header-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        .header-subtitle {
            margin: 2px 0 0 0;
            font-size: 9px;
            opacity: 0.9;
        }
        .content {
            padding: 10px 12px 8px 12px;
        }
        .meta {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta td.label {
            width: 92px;
            color: #4b5563;
        }
        .meta td.sep {
            width: 10px;
        }
        .amount-box {
            border: 1.5px dashed #334155;
            border-radius: 6px;
            padding: 7px 9px;
            margin-bottom: 7px;
            background: #f8fafc;
        }
        .amount-title {
            font-size: 9px;
            color: #64748b;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }
        .amount-value {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }
        .amount-terbilang {
            margin-top: 6px;
            font-size: 11px;
            color: #334155;
        }
        .note {
            margin-top: 4px;
            font-size: 8.5px;
            color: #6b7280;
            font-style: italic;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .summary-table td {
            border: 1px solid #dbe3ee;
            padding: 3px 5px;
            font-size: 9px;
        }
        .summary-table td.label {
            background: #f8fafc;
            color: #334155;
            width: 62%;
        }
        .detail-title {
            margin: 6px 0 4px 0;
            font-size: 9px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .detail-table th,
        .detail-table td {
            border: 1px solid #dbe3ee;
            padding: 3px 4px;
            font-size: 8.5px;
        }
        .detail-table th {
            background: #f8fafc;
            color: #334155;
            text-align: left;
        }
        .signatures {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-title {
            font-size: 9px;
            margin-bottom: 30px;
            color: #334155;
        }
        .sign-name {
            border-top: 1px solid #111827;
            padding-top: 3px;
            font-weight: 700;
            font-size: 9px;
        }
        .footer {
            border-top: 1px solid #cbd5e1;
            margin-top: 8px;
            padding: 5px 10px 6px 10px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $nominal = (int) $pembayaran->nominal_bayar;
        $tanggal = optional($pembayaran->tanggal_bayar)->translatedFormat('d F Y') ?? '-';
        $nomorKwitansi = 'KW-' . str_pad((string) $pembayaran->id, 5, '0', STR_PAD_LEFT);
    @endphp

    <div class="kwitansi">
        <div class="header">
            <p class="header-title">KWITANSI PEMBAYARAN</p>
            <p class="header-subtitle">Bukti resmi transaksi pembayaran sekolah</p>
        </div>

        <div class="content">
            <table class="meta">
                <tr>
                    <td class="label">Nomor Kwitansi</td>
                    <td class="sep">:</td>
                    <td>{{ $nomorKwitansi }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Bayar</td>
                    <td class="sep">:</td>
                    <td>{{ $tanggal }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Siswa</td>
                    <td class="sep">:</td>
                    <td>{{ $pembayaran->siswa->nama_siswa ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NIS</td>
                    <td class="sep">:</td>
                    <td>{{ $pembayaran->siswa->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Pembayaran</td>
                    <td class="sep">:</td>
                    <td>{{ $pembayaran->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Metode Bayar</td>
                    <td class="sep">:</td>
                    <td>{{ strtoupper($pembayaran->metode_bayar ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Keterangan</td>
                    <td class="sep">:</td>
                    <td>{{ $pembayaran->keterangan ?: '-' }}</td>
                </tr>
            </table>

            <div class="amount-box">
                <p class="amount-title">Jumlah Pembayaran Diterima</p>
                <p class="amount-value">Rp {{ number_format($nominal, 0, ',', '.') }}</p>
            </div>

            <table class="summary-table">
                <tr>
                    <td class="label">Total Tagihan Awal</td>
                    <td>Rp {{ number_format((int) ($totalTagihanAwal ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Cicilan Sebelumnya</td>
                    <td>Rp {{ number_format((int) ($totalCicilanSebelumnya ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Pembayaran Kwitansi Ini</td>
                    <td>Rp {{ number_format($nominal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label"><strong>Sisa Setelah Pembayaran Ini</strong></td>
                    <td><strong>Rp {{ number_format((int) ($sisaSetelahPembayaranIni ?? 0), 0, ',', '.') }}</strong></td>
                </tr>
            </table>

            <p class="detail-title">Rincian Cicilan Sebelumnya</p>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Tanggal</th>
                        <th style="width: 35%;">Nominal</th>
                        <th style="width: 30%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($riwayatCicilanSebelumnya ?? []) as $cicilan)
                        <tr>
                            <td>{{ optional($cicilan->tanggal_bayar)->format('d-m-Y') ?? '-' }}</td>
                            <td>Rp {{ number_format((int) $cicilan->nominal_bayar, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($cicilan->status ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Belum ada cicilan sebelumnya.</td>
                        </tr>
                    @endforelse
                    @if(!empty($jumlahCicilanDisembunyikan))
                        <tr>
                            <td colspan="3">... dan {{ $jumlahCicilanDisembunyikan }} cicilan sebelumnya lainnya.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="note">Dokumen ini sah sebagai bukti pembayaran yang telah diverifikasi.</div>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="sign-title">Pihak Orang Tua / Wali /Siswa</div>
                        <div class="sign-name">{{ $pembayaran->siswa->nama_siswa ?? '(............................)' }}</div>
                    </td>
                    <td>
                        <div class="sign-title">Petugas / Admin</div>
                        <div class="sign-name">{{ $petugas }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Sistem Pembayaran Sekolah • Dicetak otomatis pada {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>
</body>
</html>
