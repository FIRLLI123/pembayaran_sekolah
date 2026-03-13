<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisPembayaran extends Model
{
    use HasFactory;

    protected $table = 'jenis_pembayaran';

    protected $fillable = [
        'nama_pembayaran',
        'nominal_default',
        'keterangan',
        'created_user',
        'updated_user'
    ];

    // Relasi: JenisPembayaran punya banyak Pembayaran
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'jenis_pembayaran_id');
    }
}
