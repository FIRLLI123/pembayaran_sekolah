<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'siswa_id' => $this->siswa_id,
            'ortu_id' => $this->ortu_id,
            'siswa' => $this->whenLoaded('siswa', function () {
                return $this->siswa ? [
                    'id' => $this->siswa->id,
                    'nis' => $this->siswa->nis,
                    'nama_siswa' => $this->siswa->nama_siswa,
                ] : null;
            }),
            'ortu' => $this->whenLoaded('ortu', function () {
                return $this->ortu ? [
                    'id' => $this->ortu->id,
                    'nama_ayah' => $this->ortu->nama_ayah,
                    'nama_ibu' => $this->ortu->nama_ibu,
                ] : null;
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
