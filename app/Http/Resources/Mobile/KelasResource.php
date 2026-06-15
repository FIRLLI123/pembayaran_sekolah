<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama_kelas' => $this->nama_kelas,
            'tahun_ajaran' => $this->tahun_ajaran,
            'jumlah_siswa' => isset($this->jumlah_siswa) ? (int) $this->jumlah_siswa : null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
