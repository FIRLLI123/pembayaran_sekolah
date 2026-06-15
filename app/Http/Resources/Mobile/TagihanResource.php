<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tanggal_tagihan' => optional($this->tanggal_tagihan)->toDateString(),
            'jatuh_tempo' => optional($this->jatuh_tempo)->toDateString(),
            'periode_bulan' => $this->periode_bulan,
            'periode_tahun' => $this->periode_tahun,
            'nominal_tagihan' => (int) $this->nominal_tagihan,
            'sisa_tagihan' => (int) $this->sisa_tagihan,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'jenis_pembayaran' => $this->whenLoaded('jenisPembayaran', function () {
                return [
                    'id' => $this->jenisPembayaran->id,
                    'nama_pembayaran' => $this->jenisPembayaran->nama_pembayaran,
                    'nominal_default' => (int) $this->jenisPembayaran->nominal_default,
                    'tipe' => $this->jenisPembayaran->tipe ?? null,
                    'periode' => $this->jenisPembayaran->periode ?? null,
                ];
            }),
            'siswa' => $this->whenLoaded('siswa', function () {
                return [
                    'id' => $this->siswa->id,
                    'nis' => $this->siswa->nis,
                    'nama_siswa' => $this->siswa->nama_siswa,
                ];
            }),
        ];
    }
}
