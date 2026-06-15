<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PembayaranService
{
    public function bayarTagihan(Tagihan $tagihan, array $payload, $actorName): Pembayaran
    {
        return DB::transaction(function () use ($tagihan, $payload, $actorName) {
            $tagihan = Tagihan::lockForUpdate()->findOrFail($tagihan->id);

            $nominalBayar = (int) $payload['nominal_bayar'];
            if ($nominalBayar > (int) $tagihan->sisa_tagihan) {
                throw new \RuntimeException('Nominal melebihi sisa tagihan.');
            }

            $sisaSetelahBayar = (int) $tagihan->sisa_tagihan - $nominalBayar;
            $statusBaru = $sisaSetelahBayar === 0 ? 'lunas' : 'cicil';
            $uploadPath = null;

            if (!empty($payload['upload_foto']) && $payload['upload_foto'] instanceof UploadedFile) {
                $uploadPath = $this->storeBuktiPembayaran($payload['upload_foto']);
            }

            $pembayaran = Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'siswa_id' => $tagihan->siswa_id,
                'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                'tanggal_bayar' => now(),
                'nominal_bayar' => $nominalBayar,
                'metode_bayar' => $payload['metode_bayar'],
                'status' => $statusBaru,
                'keterangan' => !empty($payload['keterangan']) ? $payload['keterangan'] : null,
                'upload_foto' => $uploadPath,
                'created_user' => $actorName,
            ]);

            $tagihan->sisa_tagihan = $sisaSetelahBayar;
            $tagihan->status = $statusBaru;
            $tagihan->updated_user = $actorName;
            $tagihan->save();

            return $pembayaran;
        });
    }

    public function multiBayar(Siswa $siswa, array $payload, $actorName): array
    {
        return DB::transaction(function () use ($siswa, $payload, $actorName) {
            $totalBayar = (int) $payload['total_bayar'];
            $tagihanList = Tagihan::where('siswa_id', $siswa->id)
                ->where('status', '!=', 'lunas')
                ->orderBy('periode_tahun')
                ->orderBy('periode_bulan')
                ->lockForUpdate()
                ->get();

            $createdPayments = [];
            $sisaDana = $totalBayar;

            foreach ($tagihanList as $tagihan) {
                if ($sisaDana <= 0) {
                    break;
                }

                $sisaTagihan = (int) $tagihan->sisa_tagihan;

                if ($sisaDana >= $sisaTagihan) {
                    $createdPayments[] = Pembayaran::create([
                        'tagihan_id' => $tagihan->id,
                        'siswa_id' => $siswa->id,
                        'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                        'tanggal_bayar' => now(),
                        'nominal_bayar' => $sisaTagihan,
                        'metode_bayar' => $payload['metode_bayar'],
                        'status' => 'lunas',
                        'keterangan' => 'Multi bayar',
                        'created_user' => $actorName,
                    ]);

                    $tagihan->update([
                        'sisa_tagihan' => 0,
                        'status' => 'lunas',
                        'updated_user' => $actorName,
                    ]);

                    $sisaDana -= $sisaTagihan;
                } else {
                    $createdPayments[] = Pembayaran::create([
                        'tagihan_id' => $tagihan->id,
                        'siswa_id' => $siswa->id,
                        'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                        'tanggal_bayar' => now(),
                        'nominal_bayar' => $sisaDana,
                        'metode_bayar' => $payload['metode_bayar'],
                        'status' => 'cicil',
                        'keterangan' => 'Multi bayar',
                        'created_user' => $actorName,
                    ]);

                    $tagihan->update([
                        'sisa_tagihan' => $sisaTagihan - $sisaDana,
                        'status' => 'cicil',
                        'updated_user' => $actorName,
                    ]);

                    $sisaDana = 0;
                }
            }

            return [
                'payments' => $createdPayments,
                'sisa_dana' => $sisaDana,
            ];
        });
    }

    public function approve(Pembayaran $pembayaran, $actorName): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $actorName) {
            $pembayaran = Pembayaran::with('tagihan')->lockForUpdate()->findOrFail($pembayaran->id);

            if ($pembayaran->status !== 'pending') {
                throw new \RuntimeException('Pembayaran ini sudah diproses sebelumnya.');
            }

            $tagihan = Tagihan::lockForUpdate()->findOrFail($pembayaran->tagihan_id);

            if ((int) $tagihan->sisa_tagihan <= 0 || $tagihan->status === 'lunas') {
                throw new \RuntimeException('Tagihan sudah lunas, tidak bisa approve pembayaran ini.');
            }

            if ((int) $pembayaran->nominal_bayar > (int) $tagihan->sisa_tagihan) {
                throw new \RuntimeException('Nominal pending melebihi sisa tagihan saat ini. Silakan reject pembayaran ini.');
            }

            $tagihan->sisa_tagihan = (int) $tagihan->sisa_tagihan - (int) $pembayaran->nominal_bayar;
            $tagihan->status = ((int) $tagihan->sisa_tagihan === 0) ? 'lunas' : 'cicil';
            $tagihan->updated_user = $actorName;
            $tagihan->save();

            $pembayaran->status = ((int) $tagihan->sisa_tagihan === 0) ? 'lunas' : 'cicil';
            $catatanApprove = 'Disetujui admin pada ' . now()->format('d-m-Y H:i');
            $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . $catatanApprove);
            $pembayaran->updated_user = $actorName;
            $pembayaran->save();

            return $pembayaran;
        });
    }

    public function reject(Pembayaran $pembayaran, $alasanReject, $actorName): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $alasanReject, $actorName) {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);

            if ($pembayaran->status !== 'pending') {
                throw new \RuntimeException('Pembayaran ini sudah diproses sebelumnya.');
            }

            $keteranganReject = trim((string) $alasanReject);
            if ($keteranganReject !== '') {
                $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . 'Ditolak: ' . $keteranganReject);
            } else {
                $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . 'Ditolak admin tanpa keterangan');
            }

            $pembayaran->status = 'ditolak';
            $pembayaran->updated_user = $actorName;
            $pembayaran->save();

            return $pembayaran;
        });
    }

    public function delete(Pembayaran $pembayaran, $actorName): void
    {
        DB::transaction(function () use ($pembayaran, $actorName) {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);
            $tagihan = $pembayaran->tagihan_id ? Tagihan::lockForUpdate()->find($pembayaran->tagihan_id) : null;
            $uploadPath = $pembayaran->upload_foto;
            $statusPembayaranDihapus = (string) $pembayaran->status;
            $tagihanId = $pembayaran->tagihan_id;

            $pembayaran->delete();

            if ($tagihan && in_array($statusPembayaranDihapus, ['lunas', 'cicil'], true)) {
                $totalDibayarValid = (int) Pembayaran::where('tagihan_id', $tagihanId)
                    ->whereIn('status', ['lunas', 'cicil'])
                    ->sum('nominal_bayar');

                $sisaTagihan = (int) $tagihan->nominal_tagihan - $totalDibayarValid;
                if ($sisaTagihan < 0) {
                    $sisaTagihan = 0;
                }

                if ($sisaTagihan === (int) $tagihan->nominal_tagihan) {
                    $statusTagihan = 'belum_bayar';
                } elseif ($sisaTagihan === 0) {
                    $statusTagihan = 'lunas';
                } else {
                    $statusTagihan = 'cicil';
                }

                $tagihan->sisa_tagihan = $sisaTagihan;
                $tagihan->status = $statusTagihan;
                $tagihan->updated_user = $actorName;
                $tagihan->save();
            }

            if (!empty($uploadPath)) {
                $absoluteUploadPath = public_path($uploadPath);
                if (File::exists($absoluteUploadPath)) {
                    File::delete($absoluteUploadPath);
                }
            }
        });
    }

    public function storeBuktiPembayaran(UploadedFile $file): string
    {
        $folder = public_path('uploads/pembayaran');
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $filename = 'bukti_' . now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($folder, $filename);

        return 'uploads/pembayaran/' . $filename;
    }
}
