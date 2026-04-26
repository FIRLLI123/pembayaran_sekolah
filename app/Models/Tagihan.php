<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected $fillable = [
        'siswa_id',
        'jenis_pembayaran_id',
        'tanggal_tagihan',
        'jatuh_tempo',
        'periode_bulan',
        'periode_tahun',
        'nominal_tagihan',
        'sisa_tagihan',
        'status',
        'keterangan',
        'created_user',
        'updated_user'
    ];

    protected $casts = [
        'tanggal_tagihan' => 'date',
        'jatuh_tempo' => 'date',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['siswa_id']), function (Builder $q) use ($filters) {
                $q->where('siswa_id', $filters['siswa_id']);
            })
            ->when(!empty($filters['kelas_id']), function (Builder $q) use ($filters) {
                $q->whereHas('siswa', function (Builder $siswa) use ($filters) {
                    $siswa->where('kelas_id', $filters['kelas_id']);
                });
            });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    // Tagihan milik 1 siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    // Tagihan punya 1 jenis pembayaran
    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class);
    }

    // Tagihan punya banyak pembayaran (cicilan)
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER FUNCTION (BIAR ENAK DIPAKAI)
    |--------------------------------------------------------------------------
    */

    // total sudah dibayar
    public function getTotalDibayarAttribute()
    {
        return $this->pembayaran()->sum('nominal_bayar');
    }

    // cek lunas
    public function isLunas()
    {
        return $this->status === 'lunas';
    }
}
