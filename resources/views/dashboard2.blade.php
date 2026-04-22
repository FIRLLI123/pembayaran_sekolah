@extends('layouts.app')

@push('styles')
<style>
    .profile-card {
        background: #fff;
        border: 1px solid #e8ecf3;
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(31, 45, 61, 0.06);
        overflow: hidden;
    }

    .profile-card-body {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 0;
    }

    .profile-left {
        background: linear-gradient(165deg, #1f4e96, #2f80ed);
        color: #fff;
        padding: 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .avatar-circle {
        width: 84px;
        height: 84px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 2px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        margin-bottom: 0.9rem;
    }

    .student-name {
        font-weight: 700;
        font-size: 1rem;
        line-height: 1.35;
        margin-bottom: 0.4rem;
    }

    .student-meta {
        font-size: 0.86rem;
        opacity: 0.95;
        margin-bottom: 0.2rem;
    }

    .profile-right {
        padding: 1.35rem 1.4rem;
    }

    .section-title {
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5a6b85;
        margin-bottom: 0.65rem;
    }

    .kv-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem 1.25rem;
    }

    .kv-item small {
        display: block;
        color: #7f8da3;
        font-size: 0.76rem;
        margin-bottom: 0.1rem;
    }

    .kv-item span {
        color: #1d2a3b;
        font-size: 0.9rem;
        font-weight: 600;
        word-break: break-word;
    }

    .section-divider {
        border: 0;
        border-top: 1px solid #e8ecf3;
        margin: 1rem 0;
    }

    .mini-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .mini-stat {
        border: 1px solid #e7edf6;
        background: #f8fbff;
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
    }

    .mini-stat small {
        display: block;
        color: #68809d;
        font-size: 0.73rem;
        margin-bottom: 0.2rem;
    }

    .mini-stat strong {
        color: #13325d;
        font-size: 0.95rem;
        line-height: 1.2;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.35rem 0.6rem;
        background: #eef4ff;
        color: #214f90;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    @media (max-width: 991.98px) {
        .profile-card-body {
            grid-template-columns: 1fr;
        }

        .mini-stats {
            grid-template-columns: 1fr;
        }

        .kv-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $formatRupiah = fn($nominal) => 'Rp ' . number_format((int) $nominal, 0, ',', '.');
    $initials = collect(explode(' ', trim($siswa->nama_siswa ?? 'Siswa')))
        ->filter()
        ->take(2)
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->implode('');
    $jkText = ($siswa->jenis_kelamin ?? null) === 'L' ? 'Laki-laki' : (($siswa->jenis_kelamin ?? null) === 'P' ? 'Perempuan' : '-');
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Profil Siswa</h1>
</div>

@if(!$siswa)
    <div class="alert alert-warning">
        Data siswa belum terhubung ke akun orang tua ini. Silakan hubungkan `siswa_id` pada akun user ortu.
    </div>
@else
    <div class="profile-card">
        <div class="profile-card-body">
            <div class="profile-left">
                <div class="avatar-circle">{{ $initials ?: 'SW' }}</div>
                <div class="student-name">{{ $siswa->nama_siswa ?? '-' }}</div>
                <div class="student-meta">{{ optional($siswa->kelas)->nama_kelas ?? '-' }}</div>
                <div class="student-meta">NIS: {{ $siswa->nis ?? '-' }}</div>
            </div>

            <div class="profile-right">
                <div class="section-title">Data Siswa</div>
                <div class="kv-grid">
                    <div class="kv-item">
                        <small>Nama Lengkap</small>
                        <span>{{ $siswa->nama_siswa ?? '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>NIS</small>
                        <span>{{ $siswa->nis ?? '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>Kelas</small>
                        <span>{{ optional($siswa->kelas)->nama_kelas ?? '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>Jenis Kelamin</small>
                        <span>{{ $jkText }}</span>
                    </div>
                    <div class="kv-item">
                        <small>Tanggal Lahir</small>
                        <span>{{ $siswa->tanggal_lahir ?? '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>Alamat</small>
                        <span>{{ $siswa->alamat ?? '-' }}</span>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="section-title">Data Orang Tua / Wali</div>
                <div class="kv-grid">
                    <div class="kv-item">
                        <small>Nama Orang Tua / Wali</small>
                        <span>{{ $namaWali ?: '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>No. HP</small>
                        <span>{{ $ortu->no_hp ?? $siswa->no_hp ?? '-' }}</span>
                    </div>
                    <div class="kv-item">
                        <small>Email</small>
                        <span>{{ $user->email ?? '-' }}</span>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="section-title">Ringkasan Pembayaran</div>
                <div class="mini-stats">
                    <div class="mini-stat">
                        <small>Total tagihan aktif bulan ini</small>
                        <strong>{{ $formatRupiah($totalTagihanAktifBulanIni) }}</strong>
                    </div>
                    <div class="mini-stat">
                        <small>Sisa tagihan belum dibayar</small>
                        <strong>{{ $formatRupiah($sisaTagihanBelumDibayar) }}</strong>
                    </div>
                    <div class="mini-stat">
                        <small>Jumlah tagihan menunggu</small>
                        <strong>{{ number_format((int) $jumlahTagihanMenunggu, 0, ',', '.') }} tagihan</strong>
                    </div>
                </div>
                <div class="status-pill">{{ $statusRingkasan }}</div>
            </div>
        </div>
    </div>
@endif
@endsection

