<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tanggal_bayar' => optional($this->tanggal_bayar)->toDateString(),
            'nominal_bayar' => (int) $this->nominal_bayar,
            'metode_bayar' => $this->metode_bayar,
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'upload_foto' => $this->upload_foto,
            'upload_foto_url' => $this->upload_foto ? asset($this->upload_foto) : null,
            'created_user' => $this->created_user,
            'updated_user' => $this->updated_user,
            'siswa' => $this->whenLoaded('siswa', function () {
                return [
                    'id' => $this->siswa->id,
                    'nis' => $this->siswa->nis,
                    'nama_siswa' => $this->siswa->nama_siswa,
                    'kelas' => $this->siswa->relationLoaded('kelas') && $this->siswa->kelas ? [
                        'id' => $this->siswa->kelas->id,
                        'nama_kelas' => $this->siswa->kelas->nama_kelas,
                    ] : null,
                ];
            }),
            'jenis_pembayaran' => $this->whenLoaded('jenisPembayaran', function () {
                return [
                    'id' => $this->jenisPembayaran->id,
                    'nama_pembayaran' => $this->jenisPembayaran->nama_pembayaran,
                ];
            }),
            'tagihan' => $this->whenLoaded('tagihan', function () {
                return [
                    'id' => $this->tagihan->id,
                    'nominal_tagihan' => (int) $this->tagihan->nominal_tagihan,
                    'sisa_tagihan' => (int) $this->tagihan->sisa_tagihan,
                    'status' => $this->tagihan->status,
                    'periode_bulan' => $this->tagihan->periode_bulan,
                    'periode_tahun' => $this->tagihan->periode_tahun,
                ];
            }),
        ];
    }
}
