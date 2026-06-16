<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\RiwayatKelasSiswa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Throwable;

class ReportController extends Controller
{
    public function exportExcel(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'periode_mulai' => 'required|date_format:Y-m-d',
            'periode_selesai' => 'required|date_format:Y-m-d|after_or_equal:periode_mulai',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'siswa_id' => 'nullable|integer|exists:siswa,id',
            'jenis_pembayaran_id' => 'nullable|integer|exists:jenis_pembayaran,id',
            'status_tagihan' => 'nullable|string|in:belum_bayar,cicil,lunas',
            'include_riwayat_siswa' => 'nullable|boolean',
        ]);

        try {
            $this->assertExportEnvironment();

            $periodeMulai = $validated['periode_mulai'];
            $periodeSelesai = $validated['periode_selesai'];
            $kelasId = $request->kelas_id;
            $siswaId = $request->siswa_id;
            $jenisPembayaranId = $request->jenis_pembayaran_id;
            $statusTagihan = $request->status_tagihan;
            $includeRiwayatSiswa = $request->boolean('include_riwayat_siswa', false);

            $rangeInDays = Carbon::parse($periodeMulai)->diffInDays(Carbon::parse($periodeSelesai));
            if ($rangeInDays > 366) {
                return response()->json([
                    'message' => 'Rentang periode terlalu besar. Maksimal 12 bulan per export.',
                ], 422);
            }

            // Fetch data based on filters
            // 1. Summary details
            $siswaQuery = Siswa::query();
            if ($kelasId) {
                $siswaQuery->where('kelas_id', $kelasId);
            }
            if ($siswaId) {
                $siswaQuery->where('id', $siswaId);
            }
            $totalSiswaAktif = $siswaQuery->count();

            // Tagihan filter helper
            $applyTagihanFilter = function ($query) use ($periodeMulai, $periodeSelesai, $kelasId, $siswaId, $jenisPembayaranId, $statusTagihan) {
                $query->whereBetween('tanggal_tagihan', [$periodeMulai, $periodeSelesai]);
                if ($kelasId) {
                    $query->whereHas('siswa', function ($q) use ($kelasId) {
                        $q->where('kelas_id', $kelasId);
                    });
                }
                if ($siswaId) {
                    $query->where('siswa_id', $siswaId);
                }
                if ($jenisPembayaranId) {
                    $query->where('jenis_pembayaran_id', $jenisPembayaranId);
                }
                if ($statusTagihan) {
                    $query->where('status', $statusTagihan);
                }
                return $query;
            };

            // Pembayaran filter helper
            $applyPembayaranFilter = function ($query) use ($periodeMulai, $periodeSelesai, $kelasId, $siswaId, $jenisPembayaranId, $statusTagihan) {
                $query->whereIn('status', ['lunas', 'cicil'])
                    ->whereBetween('tanggal_bayar', [$periodeMulai, $periodeSelesai]);
                if ($kelasId) {
                    $query->whereHas('siswa', function ($q) use ($kelasId) {
                        $q->where('kelas_id', $kelasId);
                    });
                }
                if ($siswaId) {
                    $query->where('siswa_id', $siswaId);
                }
                if ($jenisPembayaranId) {
                    $query->where('jenis_pembayaran_id', $jenisPembayaranId);
                }
                if ($statusTagihan) {
                    $query->whereHas('tagihan', function ($q) use ($statusTagihan) {
                        $q->where('status', $statusTagihan);
                    });
                }
                return $query;
            };

            // Calculate totals for Summary
            $tagihanSummaryQuery = Tagihan::query();
            $applyTagihanFilter($tagihanSummaryQuery);

            $totalTagihan = (int) $tagihanSummaryQuery->sum('nominal_tagihan');
            $totalSisaTagihan = (int) $tagihanSummaryQuery->sum('sisa_tagihan');

            $pembayaranSummaryQuery = Pembayaran::query();
            $applyPembayaranFilter($pembayaranSummaryQuery);
            $totalPembayaran = (int) $pembayaranSummaryQuery->sum('nominal_bayar');

            $countLunas = (int) (clone $tagihanSummaryQuery)->where('status', 'lunas')->count();
            $countCicil = (int) (clone $tagihanSummaryQuery)->where('status', 'cicil')->count();
            $countBelumBayar = (int) (clone $tagihanSummaryQuery)->where('status', 'belum_bayar')->count();

            $jumlahSiswaMenunggak = (int) (clone $tagihanSummaryQuery)
                ->whereIn('status', ['belum_bayar', 'cicil'])
                ->where('sisa_tagihan', '>', 0)
                ->distinct('siswa_id')
                ->count('siswa_id');

            $jumlahTransaksiPembayaran = (int) (clone $pembayaranSummaryQuery)->count();

            // 2. Detail lists
            $tagihans = $applyTagihanFilter(
                Tagihan::with(['siswa.kelas', 'jenisPembayaran'])
                    ->orderBy('tanggal_tagihan')
                    ->orderBy('id')
            )->get();

            $pembayarans = $applyPembayaranFilter(
                Pembayaran::with(['siswa.kelas', 'jenisPembayaran'])
                    ->orderBy('tanggal_bayar')
                    ->orderBy('id')
            )->get();

            // 3. Tunggakan list
            $tunggakans = $applyTagihanFilter(
                Tagihan::with(['siswa.kelas', 'jenisPembayaran'])
                    ->whereIn('status', ['belum_bayar', 'cicil'])
                    ->where('sisa_tagihan', '>', 0)
                    ->orderBy('tanggal_tagihan')
                    ->orderBy('id')
            )->get();

            // 4. Rekap Jenis
            $rekapJenisData = [];
            $jenisPembayarans = JenisPembayaran::query()
                ->when($jenisPembayaranId, function ($q) use ($jenisPembayaranId) {
                    $q->where('id', $jenisPembayaranId);
                })
                ->orderBy('nama_pembayaran')
                ->get();

            foreach ($jenisPembayarans as $jp) {
                $jpTagihans = $tagihans->where('jenis_pembayaran_id', $jp->id);
                $jpPembayarans = $pembayarans->where('jenis_pembayaran_id', $jp->id);

                $sumTagihan = (int) $jpTagihans->sum('nominal_tagihan');
                $sumPembayaran = (int) $jpPembayarans->sum('nominal_bayar');
                $sumSisa = (int) $jpTagihans->sum('sisa_tagihan');

                $lunasCount = $jpTagihans->where('status', 'lunas')->count();
                $cicilCount = $jpTagihans->where('status', 'cicil')->count();
                $belumBayarCount = $jpTagihans->where('status', 'belum_bayar')->count();

                $realisasi = $sumTagihan > 0 ? round(($sumPembayaran / $sumTagihan) * 100, 2) : 0;

                $rekapJenisData[] = [
                    'nama' => $jp->nama_pembayaran,
                    'tipe' => $jp->tipe,
                    'periode' => $jp->periode ?: '-',
                    'jumlah_tagihan' => $jpTagihans->count(),
                    'total_tagihan' => $sumTagihan,
                    'total_bayar' => $sumPembayaran,
                    'total_sisa' => $sumSisa,
                    'lunas' => $lunasCount,
                    'cicil' => $cicilCount,
                    'belum_bayar' => $belumBayarCount,
                    'realisasi' => $realisasi,
                ];
            }

            // 5. Rekap Kelas
            $rekapKelasData = [];
            $kelases = Kelas::query()
                ->when($kelasId, function ($q) use ($kelasId) {
                    $q->where('id', $kelasId);
                })
                ->orderBy('nama_kelas')
                ->get();

            foreach ($kelases as $kls) {
                $klsSiswaQuery = Siswa::where('kelas_id', $kls->id);
                if ($siswaId) {
                    $klsSiswaQuery->where('id', $siswaId);
                }
                $siswaIds = $klsSiswaQuery->pluck('id')->all();

                $klsTagihans = $tagihans->whereIn('siswa_id', $siswaIds);
                $klsPembayarans = $pembayarans->whereIn('siswa_id', $siswaIds);

                $sumTagihan = (int) $klsTagihans->sum('nominal_tagihan');
                $sumPembayaran = (int) $klsPembayarans->sum('nominal_bayar');
                $sumSisa = (int) $klsTagihans->sum('sisa_tagihan');

                $siswaLunasCount = 0;
                $siswaBelumLunasCount = 0;

                foreach ($siswaIds as $sId) {
                    $sTagihans = $klsTagihans->where('siswa_id', $sId);
                    if ($sTagihans->isEmpty()) {
                        continue;
                    }

                    $sSisa = $sTagihans->sum('sisa_tagihan');
                    if ($sSisa == 0) {
                        $siswaLunasCount++;
                    } else {
                        $siswaBelumLunasCount++;
                    }
                }

                $realisasi = $sumTagihan > 0 ? round(($sumPembayaran / $sumTagihan) * 100, 2) : 0;

                $rekapKelasData[] = [
                    'kelas' => $kls->nama_kelas,
                    'tahun_ajaran' => $kls->tahun_ajaran ?: '-',
                    'jumlah_siswa' => count($siswaIds),
                    'total_tagihan' => $sumTagihan,
                    'total_bayar' => $sumPembayaran,
                    'total_sisa' => $sumSisa,
                    'siswa_lunas' => $siswaLunasCount,
                    'siswa_belum_lunas' => $siswaBelumLunasCount,
                    'realisasi' => $realisasi,
                ];
            }

            // 6. Riwayat Siswa (optional)
            $riwayats = collect();
            if ($includeRiwayatSiswa) {
                $riwayatQuery = RiwayatKelasSiswa::with(['siswa', 'kelasLama', 'kelasBaru'])
                    ->whereBetween('tanggal_pindah', [$periodeMulai . ' 00:00:00', $periodeSelesai . ' 23:59:59']);

                if ($siswaId) {
                    $riwayatQuery->where('siswa_id', $siswaId);
                } elseif ($kelasId) {
                    $riwayatQuery->where(function ($q) use ($kelasId) {
                        $q->where('kelas_lama_id', $kelasId)
                            ->orWhere('kelas_baru_id', $kelasId);
                    });
                }
                $riwayats = $riwayatQuery->orderBy('tanggal_pindah')->get();
            }

            // Now, start Excel generation
            $spreadsheet = new Spreadsheet();

            // Setup helper styles
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ];

            // SHEET 1: Summary
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Summary');
            $sheet->setShowGridlines(true);

            $sheet->setCellValue('A1', 'RINGKASAN LAPORAN KEUANGAN SEKOLAH');
            $sheet->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

            $sheet->setCellValue('A3', 'Periode Laporan:');
            $sheet->setCellValue('B3', $periodeMulai . ' s.d ' . $periodeSelesai);
            $sheet->setCellValue('A4', 'Tanggal Export:');
            $sheet->setCellValue('B4', Carbon::now()->toDateTimeString());
            $sheet->setCellValue('A5', 'Export Oleh:');
            $sheet->setCellValue('B5', auth()->user() ? auth()->user()->name : 'Admin');

            $sheet->setCellValue('A7', 'METRIK UTAMA');
            $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);

            $metrics = [
                ['Total Siswa Aktif (Filter)', $totalSiswaAktif, 'orang'],
                ['Total Tagihan Terbuat', $totalTagihan, 'currency'],
                ['Total Pembayaran Masuk', $totalPembayaran, 'currency'],
                ['Total Tunggakan (Sisa)', $totalSisaTagihan, 'currency'],
                ['Jumlah Transaksi Pembayaran', $jumlahTransaksiPembayaran, 'kali'],
                ['Jumlah Siswa Menunggak', $jumlahSiswaMenunggak, 'orang'],
                ['Tagihan Lunas', $countLunas, 'tagihan'],
                ['Tagihan Cicilan', $countCicil, 'tagihan'],
                ['Tagihan Belum Bayar', $countBelumBayar, 'tagihan'],
            ];

            $rowNum = 8;
            foreach ($metrics as $metric) {
                $sheet->setCellValue('A' . $rowNum, $metric[0]);
                $sheet->setCellValue('B' . $rowNum, $metric[1]);

                if ($metric[2] === 'currency') {
                    $sheet->getStyle('B' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                } else {
                    $sheet->setCellValue('C' . $rowNum, $metric[2]);
                }
                $rowNum++;
            }

            $sheet->getStyle('A8:B' . ($rowNum - 1))->applyFromArray($borderStyle);
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);

            // Helper function to auto-fit and style sheets
            $formatSheet = function ($sheet, $headers, $dataRows, $headerStyle, $borderStyle) {
                $sheet->setShowGridlines(true);

                $colLetter = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($colLetter . '1', $header);
                    $colLetter++;
                }
                $lastCol = chr(ord('A') + count($headers) - 1);
                $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);
                $sheet->getRowDimension(1)->setRowHeight(25);

                $row = 2;
                foreach ($dataRows as $rowData) {
                    $colLetter = 'A';
                    foreach ($rowData as $val) {
                        $sheet->setCellValue($colLetter . $row, $val);
                        $colLetter++;
                    }
                    $row++;
                }

                if ($row > 2) {
                    $sheet->getStyle('A1:' . $lastCol . ($row - 1))->applyFromArray($borderStyle);
                }

                $colLetter = 'A';
                for ($i = 0; $i < count($headers); $i++) {
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                    $colLetter++;
                }
            };

            // SHEET 2: Tagihan
            $sheetTagihan = $spreadsheet->createSheet();
            $sheetTagihan->setTitle('Tagihan');
            $headersTagihan = [
                'No', 'ID Tagihan', 'Tanggal Tagihan', 'Jatuh Tempo', 'Tahun', 'Bulan',
                'NIS', 'Nama Siswa', 'Kelas', 'Jenis Pembayaran', 'Tipe', 'Nominal Tagihan',
                'Sisa Tagihan', 'Status', 'Keterangan', 'Dibuat Oleh', 'Diupdate Oleh'
            ];
            $rowsTagihan = [];
            $no = 1;
            foreach ($tagihans as $t) {
                $rowsTagihan[] = [
                    $no++,
                    $t->id,
                    $t->tanggal_tagihan ? $t->tanggal_tagihan->toDateString() : '-',
                    $t->jatuh_tempo ? $t->jatuh_tempo->toDateString() : '-',
                    $t->periode_tahun ?: '-',
                    $t->periode_bulan ?: '-',
                    $t->siswa ? $t->siswa->nis : '-',
                    $t->siswa ? $t->siswa->nama_siswa : '-',
                    $t->siswa && $t->siswa->kelas ? $t->siswa->kelas->nama_kelas : '-',
                    $t->jenisPembayaran ? $t->jenisPembayaran->nama_pembayaran : '-',
                    $t->jenisPembayaran ? $t->jenisPembayaran->tipe : '-',
                    $t->nominal_tagihan,
                    $t->sisa_tagihan,
                    $t->status,
                    $t->keterangan ?: '-',
                    $t->created_user ?: '-',
                    $t->updated_user ?: '-',
                ];
            }
            $formatSheet($sheetTagihan, $headersTagihan, $rowsTagihan, $headerStyle, $borderStyle);
            $row = 2;
            foreach ($tagihans as $t) {
                $sheetTagihan->getStyle('L' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetTagihan->getStyle('M' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $row++;
            }

            // SHEET 3: Pembayaran
            $sheetPembayaran = $spreadsheet->createSheet();
            $sheetPembayaran->setTitle('Pembayaran');
            $headersPembayaran = [
                'No', 'ID Pembayaran', 'Tanggal Bayar', 'ID Tagihan', 'NIS', 'Nama Siswa',
                'Kelas', 'Jenis Pembayaran', 'Nominal Bayar', 'Metode Bayar', 'Status',
                'Keterangan', 'Dibuat Oleh', 'Diupdate Oleh'
            ];
            $rowsPembayaran = [];
            $no = 1;
            foreach ($pembayarans as $p) {
                $rowsPembayaran[] = [
                    $no++,
                    $p->id,
                    $p->tanggal_bayar,
                    $p->tagihan_id ?: '-',
                    $p->siswa ? $p->siswa->nis : '-',
                    $p->siswa ? $p->siswa->nama_siswa : '-',
                    $p->siswa && $p->siswa->kelas ? $p->siswa->kelas->nama_kelas : '-',
                    $p->jenisPembayaran ? $p->jenisPembayaran->nama_pembayaran : '-',
                    $p->nominal_bayar,
                    $p->metode_bayar,
                    $p->status,
                    $p->keterangan ?: '-',
                    $p->created_user ?: '-',
                    $p->updated_user ?: '-',
                ];
            }
            $formatSheet($sheetPembayaran, $headersPembayaran, $rowsPembayaran, $headerStyle, $borderStyle);
            $row = 2;
            foreach ($pembayarans as $p) {
                $sheetPembayaran->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $row++;
            }

            // SHEET 4: Tunggakan
            $sheetTunggakan = $spreadsheet->createSheet();
            $sheetTunggakan->setTitle('Tunggakan');
            $headersTunggakan = [
                'No', 'ID Tagihan', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis Pembayaran',
                'Tahun', 'Bulan', 'Tanggal Tagihan', 'Jatuh Tempo', 'Nominal Tagihan',
                'Total Sudah Dibayar', 'Sisa Tagihan', 'Status', 'Umur Tunggakan (Hari)'
            ];
            $rowsTunggakan = [];
            $no = 1;
            foreach ($tunggakans as $t) {
                $umurTunggakan = 0;
                if ($t->jatuh_tempo) {
                    $jt = Carbon::parse($t->jatuh_tempo);
                    if ($jt->isPast()) {
                        $umurTunggakan = $jt->diffInDays(Carbon::now());
                    }
                }
                $rowsTunggakan[] = [
                    $no++,
                    $t->id,
                    $t->siswa ? $t->siswa->nis : '-',
                    $t->siswa ? $t->siswa->nama_siswa : '-',
                    $t->siswa && $t->siswa->kelas ? $t->siswa->kelas->nama_kelas : '-',
                    $t->jenisPembayaran ? $t->jenisPembayaran->nama_pembayaran : '-',
                    $t->periode_tahun ?: '-',
                    $t->periode_bulan ?: '-',
                    $t->tanggal_tagihan ? $t->tanggal_tagihan->toDateString() : '-',
                    $t->jatuh_tempo ? $t->jatuh_tempo->toDateString() : '-',
                    $t->nominal_tagihan,
                    $t->total_dibayar,
                    $t->sisa_tagihan,
                    $t->status,
                    $umurTunggakan
                ];
            }
            $formatSheet($sheetTunggakan, $headersTunggakan, $rowsTunggakan, $headerStyle, $borderStyle);
            $row = 2;
            foreach ($tunggakans as $t) {
                $sheetTunggakan->getStyle('K' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetTunggakan->getStyle('L' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetTunggakan->getStyle('M' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $row++;
            }

            // SHEET 5: Rekap Jenis
            $sheetRekapJenis = $spreadsheet->createSheet();
            $sheetRekapJenis->setTitle('Rekap Jenis');
            $headersRekapJenis = [
                'No', 'Jenis Pembayaran', 'Tipe', 'Periode', 'Jumlah Tagihan',
                'Total Nominal Tagihan', 'Total Pembayaran Masuk', 'Total Sisa Tagihan',
                'Jumlah Tagihan Lunas', 'Jumlah Tagihan Cicil', 'Jumlah Tagihan Belum Bayar',
                'Persentase Realisasi (%)'
            ];
            $rowsRekapJenis = [];
            $no = 1;
            foreach ($rekapJenisData as $rj) {
                $rowsRekapJenis[] = [
                    $no++,
                    $rj['nama'],
                    $rj['tipe'],
                    $rj['periode'],
                    $rj['jumlah_tagihan'],
                    $rj['total_tagihan'],
                    $rj['total_bayar'],
                    $rj['total_sisa'],
                    $rj['lunas'],
                    $rj['cicil'],
                    $rj['belum_bayar'],
                    $rj['realisasi']
                ];
            }
            $formatSheet($sheetRekapJenis, $headersRekapJenis, $rowsRekapJenis, $headerStyle, $borderStyle);
            $row = 2;
            foreach ($rekapJenisData as $rj) {
                $sheetRekapJenis->getStyle('F' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapJenis->getStyle('G' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapJenis->getStyle('H' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapJenis->getStyle('L' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
                $row++;
            }

            // SHEET 6: Rekap Kelas
            $sheetRekapKelas = $spreadsheet->createSheet();
            $sheetRekapKelas->setTitle('Rekap Kelas');
            $headersRekapKelas = [
                'No', 'Kelas', 'Tahun Ajaran', 'Jumlah Siswa', 'Total Nominal Tagihan',
                'Total Pembayaran Masuk', 'Total Sisa Tagihan', 'Jumlah Siswa Lunas',
                'Jumlah Siswa Belum Lunas', 'Persentase Pelunasan (%)'
            ];
            $rowsRekapKelas = [];
            $no = 1;
            foreach ($rekapKelasData as $rk) {
                $rowsRekapKelas[] = [
                    $no++,
                    $rk['kelas'],
                    $rk['tahun_ajaran'],
                    $rk['jumlah_siswa'],
                    $rk['total_tagihan'],
                    $rk['total_bayar'],
                    $rk['total_sisa'],
                    $rk['siswa_lunas'],
                    $rk['siswa_belum_lunas'],
                    $rk['realisasi']
                ];
            }
            $formatSheet($sheetRekapKelas, $headersRekapKelas, $rowsRekapKelas, $headerStyle, $borderStyle);
            $row = 2;
            foreach ($rekapKelasData as $rk) {
                $sheetRekapKelas->getStyle('E' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapKelas->getStyle('F' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapKelas->getStyle('G' . $row)->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheetRekapKelas->getStyle('J' . $row)->getNumberFormat()->setFormatCode('0.00"%"');
                $row++;
            }

            // SHEET 7: Riwayat Siswa (optional)
            if ($includeRiwayatSiswa) {
                $sheetRiwayat = $spreadsheet->createSheet();
                $sheetRiwayat->setTitle('Riwayat Siswa');
                $headersRiwayat = [
                    'No', 'ID Riwayat', 'Tanggal Pindah', 'NIS', 'Nama Siswa',
                    'Kelas Lama', 'Kelas Baru', 'Dicatat Oleh'
                ];
                $rowsRiwayat = [];
                $no = 1;
                foreach ($riwayats as $rw) {
                    $rowsRiwayat[] = [
                        $no++,
                        $rw->id,
                        $rw->tanggal_pindah,
                        $rw->siswa ? $rw->siswa->nis : '-',
                        $rw->siswa ? $rw->siswa->nama_siswa : '-',
                        $rw->kelasLama ? $rw->kelasLama->nama_kelas : '-',
                        $rw->kelasBaru ? $rw->kelasBaru->nama_kelas : '-',
                        $rw->created_user ?: '-',
                    ];
                }
                $formatSheet($sheetRiwayat, $headersRiwayat, $rowsRiwayat, $headerStyle, $borderStyle);
            }

            // Generate final file
            $filename = 'laporan-keuangan-' . $periodeMulai . '_sd_' . $periodeSelesai . '-' . time() . '.xlsx';
            $filePath = storage_path('app/exports-tmp/' . $filename);


            // Ensure temp directory exists
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            // Read the file into memory, delete temp file, return clean binary response.
            // Using file_get_contents + response() avoids ob_flush/streamDownload
            // output-buffer conflicts that corrupt binary data on production servers.
            $fileContent = file_get_contents($filePath);
            @unlink($filePath);

            // Bersihkan semua output buffer yang mungkin bocor
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            return response($fileContent, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length'      => strlen($fileContent),
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);


        } catch (Throwable $e) {
            Log::error('Mobile report export failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Gagal membuat file laporan excel. Silakan cek log server.',
            ], 500);
        }
    }

    private function assertExportEnvironment(): void
    {
        if (!class_exists(Spreadsheet::class) || !class_exists(Xlsx::class)) {
            throw new \RuntimeException('PhpSpreadsheet belum tersedia di server.');
        }

        $requiredExtensions = ['zip', 'xml', 'xmlwriter', 'dom', 'mbstring'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new \RuntimeException('Extension PHP "' . $extension . '" belum aktif di server.');
            }
        }

        $exportDir = storage_path('app/public/exports');
        if (!is_dir($exportDir) && !@mkdir($exportDir, 0755, true) && !is_dir($exportDir)) {
            throw new \RuntimeException('Folder export tidak bisa dibuat di server.');
        }

        if (!is_writable($exportDir)) {
            throw new \RuntimeException('Folder export tidak writable di server.');
        }
    }
}
