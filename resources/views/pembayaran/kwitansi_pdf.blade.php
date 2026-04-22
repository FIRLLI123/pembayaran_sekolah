<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .kwitansi {
            border: 2px solid #0f172a;
            border-radius: 10px;
            overflow: hidden;
        }
        .header {
            background: #0f172a;
            color: #fff;
            padding: 14px 16px;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        .header-subtitle {
            margin: 4px 0 0 0;
            font-size: 11px;
            opacity: 0.9;
        }
        .content {
            padding: 14px 16px 10px 16px;
        }
        .meta {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .meta td {
            padding: 3px 0;
            vertical-align: top;
        }
        .meta td.label {
            width: 115px;
            color: #4b5563;
        }
        .meta td.sep {
            width: 10px;
        }
        .amount-box {
            border: 1.5px dashed #334155;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #f8fafc;
        }
        .amount-title {
            font-size: 11px;
            color: #64748b;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }
        .amount-value {
            font-size: 18px;
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
            margin-top: 6px;
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
        }
        .signatures {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-title {
            font-size: 11px;
            margin-bottom: 48px;
            color: #334155;
        }
        .sign-name {
            border-top: 1px solid #111827;
            padding-top: 4px;
            font-weight: 700;
            font-size: 11px;
        }
        .footer {
            border-top: 1px solid #cbd5e1;
            margin-top: 12px;
            padding: 8px 16px 10px 16px;
            font-size: 10px;
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

            <div class="note">Dokumen ini sah sebagai bukti pembayaran yang telah diverifikasi.</div>

            <table class="signatures">
                <tr>
                    <td>
                        <div class="sign-title">Pihak Orang Tua / Wali</div>
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
