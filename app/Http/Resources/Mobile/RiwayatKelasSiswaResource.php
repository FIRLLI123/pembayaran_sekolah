<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class RiwayatKelasSiswaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tanggal_pindah' => optional($this->tanggal_pindah)->toDateTimeString(),
            'created_user' => $this->created_user,
            'siswa' => $this->whenLoaded('siswa', function () {
                return $this->siswa ? [
                    'id' => $this->siswa->id,
                    'nis' => $this->siswa->nis,
                    'nama_siswa' => $this->siswa->nama_siswa,
                ] : null;
            }),
            'kelas_lama' => $this->whenLoaded('kelasLama', function () {
                return $this->kelasLama ? [
                    'id' => $this->kelasLama->id,
                    'nama_kelas' => $this->kelasLama->nama_kelas,
                ] : null;
            }),
            'kelas_baru' => $this->whenLoaded('kelasBaru', function () {
                return $this->kelasBaru ? [
                    'id' => $this->kelasBaru->id,
                    'nama_kelas' => $this->kelasBaru->nama_kelas,
                ] : null;
            }),
        ];
    }
}
