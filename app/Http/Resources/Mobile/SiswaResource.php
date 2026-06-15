<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class SiswaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama_siswa' => $this->nama_siswa,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'upload_foto' => $this->upload_foto,
            'foto_url' => $this->upload_foto ? asset('storage/' . $this->upload_foto) : null,
            'kelas' => $this->whenLoaded('kelas', function () {
                return [
                    'id' => $this->kelas->id,
                    'nama_kelas' => $this->kelas->nama_kelas,
                    'tahun_ajaran' => $this->kelas->tahun_ajaran,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
