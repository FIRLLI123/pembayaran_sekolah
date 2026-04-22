<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ortu extends Model
{
    use HasFactory;

    protected $table = 'ortu';

    protected $fillable = [
        'nama_ayah',
        'nama_ibu',
        'no_hp',
        'alamat',
    ];

    /**
     * Relasi ke User
     * 1 ortu bisa punya banyak user (opsional, tapi fleksibel)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke Siswa
     * 1 ortu punya banyak siswa
     */
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}