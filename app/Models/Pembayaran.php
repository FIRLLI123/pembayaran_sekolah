<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
            })
            ->when(!empty($filters['status']), function (Builder $q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when(!empty($filters['metode_bayar']), function (Builder $q) use ($filters) {
                $q->where('metode_bayar', $filters['metode_bayar']);
            })
            ->when(!empty($filters['tanggal_mulai']), function (Builder $q) use ($filters) {
                $q->whereDate('tanggal_bayar', '>=', $filters['tanggal_mulai']);
            })
            ->when(!empty($filters['tanggal_selesai']), function (Builder $q) use ($filters) {
                $q->whereDate('tanggal_bayar', '<=', $filters['tanggal_selesai']);
            });
    }

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
