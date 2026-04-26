<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKelasSiswa extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kelas_siswa';

    protected $fillable = [
        'siswa_id',
        'kelas_lama_id',
        'kelas_baru_id',
        'tanggal_pindah',
        'created_user',
    ];

    // 🔗 Relasi ke siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    // 🔗 Relasi ke kelas lama
    public function kelasLama()
    {
        return $this->belongsTo(Kelas::class, 'kelas_lama_id');
    }

    // 🔗 Relasi ke kelas baru
    public function kelasBaru()
    {
        return $this->belongsTo(Kelas::class, 'kelas_baru_id');
    }
}