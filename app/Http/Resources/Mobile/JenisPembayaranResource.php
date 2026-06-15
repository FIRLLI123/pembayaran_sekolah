<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class JenisPembayaranResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama_pembayaran' => $this->nama_pembayaran,
            'nominal_default' => (int) $this->nominal_default,
            'tipe' => $this->tipe,
            'periode' => $this->periode,
            'keterangan' => $this->keterangan,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
