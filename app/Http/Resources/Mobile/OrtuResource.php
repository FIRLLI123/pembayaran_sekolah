<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class OrtuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama_ayah' => $this->nama_ayah,
            'nama_ibu' => $this->nama_ibu,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'jumlah_user' => isset($this->jumlah_user) ? (int) $this->jumlah_user : null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
