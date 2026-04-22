<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'siswa_id',
        'jenis_pembayaran_id',
        'tanggal_bayar',
        'nominal_bayar',
        'metode_bayar',
        'status',
        'keterangan',
        'upload_foto',
        'created_user',
        'updated_user',
        'tagihan_id'
    ];

    protected $casts = [
        'tanggal_bayar' => 'date'
    ];

    // Relasi: Pembayaran milik satu Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi: Pembayaran milik satu Jenis Pembayaran
    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id');
    }

    // Relasi: Pembayaran milik satu Tagihan
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');  
}
}