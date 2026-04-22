<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'created_user',
        'updated_user',
        'siswa_id',
        'ortu_id',
    ];

    public function siswa()
{
    return $this->belongsTo(Siswa::class);
}

public function ortu()
{
    return $this->belongsTo(Ortu::class);
}
}
