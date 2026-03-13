<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nama_siswa',
        'kelas_id',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'created_user',
        'updated_user'
    ];

    // Relasi: Siswa milik satu Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Relasi: Siswa punya banyak Pembayaran
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }
}
