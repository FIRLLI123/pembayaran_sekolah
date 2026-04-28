@extends('layouts.app')

@push('styles')
<style>

    /* === FOTO UPLOAD === */
.avatar-wrapper {
    position: relative;
    width: 84px;
    height: 84px;
    margin: 0 auto 0.9rem auto;
    cursor: pointer;
}

.avatar-img {
    width: 84px;
    height: 84px;
    border-radius: 999px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.3);
    display: block;
}

.avatar-circle {
    margin-bottom: 0; /* override lama */
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: rgba(0,0,0,0.45);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .22s ease;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    gap: 3px;
}

.avatar-overlay svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.avatar-wrapper:hover .avatar-overlay {
    opacity: 1;
}

/* Modal foto */
.foto-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.55);
    z-index: 1060;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
}

.foto-modal-backdrop.active {
    display: flex;
}

.foto-modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 380px;
    padding: 1.75rem 1.5rem 1.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    position: relative;
    animation: modalPop .22s cubic-bezier(.34,1.56,.64,1);
}

@keyframes modalPop {
    from { transform: scale(.88); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}

.foto-modal-close {
    position: absolute;
    top: 14px;
    right: 16px;
    background: #f1f5f9;
    border: 0;
    border-radius: 999px;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
    transition: background .15s;
}

.foto-modal-close:hover { background: #e2e8f0; }

.foto-modal-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.25rem;
}

.foto-drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    padding: 2rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #f8fafc;
    position: relative;
}

.foto-drop-zone.dragover {
    border-color: #2f80ed;
    background: #eff6ff;
}

.foto-drop-zone input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.foto-drop-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #e0eaff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    color: #2f80ed;
}

.foto-drop-text {
    font-size: 0.88rem;
    color: #475569;
    margin: 0 0 0.25rem;
}

.foto-drop-hint {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
}

/* Preview setelah pilih foto */
.foto-preview-wrap {
    display: none;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
}

.foto-preview-wrap.visible {
    display: flex;
}

.foto-preview-img {
    width: 110px;
    height: 110px;
    border-radius: 999px;
    object-fit: cover;
    border: 3px solid #e0eaff;
    box-shadow: 0 4px 16px rgba(47,128,237,.15);
}

.foto-preview-name {
    font-size: 0.8rem;
    color: #64748b;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.foto-modal-actions {
    display: flex;
    gap: .75rem;
    margin-top: 1.25rem;
}

.btn-foto-cancel {
    flex: 1;
    padding: .6rem;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-weight: 600;
    font-size: .88rem;
    cursor: pointer;
    transition: background .15s;
}

.btn-foto-cancel:hover { background: #f1f5f9; }

.btn-foto-save {
    flex: 1;
    padding: .6rem;
    border-radius: 10px;
    border: 0;
    background: linear-gradient(135deg, #1f4e96, #2f80ed);
    color: #fff;
    font-weight: 600;
    font-size: .88rem;
    cursor: pointer;
    opacity: .5;
    pointer-events: none;
    transition: opacity .2s;
}

.btn-foto-save.ready {
    opacity: 1;
    pointer-events: auto;
}

.btn-foto-save.loading {
    opacity: .7;
    pointer-events: none;
}

/* Toast notif */
.foto-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: #1e293b;
    color: #fff;
    padding: .6rem 1.2rem;
    border-radius: 999px;
    font-size: .85rem;
    font-weight: 500;
    opacity: 0;
    transition: all .3s ease;
    z-index: 9999;
    white-space: nowrap;
}

.foto-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.foto-toast.success { background: #166534; }
.foto-toast.error   { background: #991b1b; }
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
        grid-template-columns: repeat(4, minmax(0, 1fr));
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

    .pay-card {
        margin-top: 1.1rem;
        background: #fff;
        border: 1px solid #e8ecf3;
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(31, 45, 61, 0.06);
        padding: 1.15rem 1.2rem;
    }

    .pay-section-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5a6b85;
        margin-bottom: 0.95rem;
    }

    .pay-timeline {
        display: flex;
        flex-direction: column;
    }

    .pay-row {
        display: flex;
        align-items: stretch;
        gap: 12px;
    }

    .pay-line-col {
        width: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
    }

    .pay-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        margin-top: 7px;
    }

    .pay-connector {
        width: 2px;
        min-height: 16px;
        background: #e6ebf3;
        flex: 1;
    }

    .pay-item {
        flex: 1;
        border-radius: 12px;
        padding: 0.8rem 0.95rem;
        margin-bottom: 0.65rem;
        display: flex;
        justify-content: space-between;
        gap: 0.6rem;
        align-items: center;
    }

    .pay-item-name {
        margin: 0 0 2px 0;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .pay-item-sub {
        margin: 0;
        font-size: 0.8rem;
        opacity: 0.86;
    }

    .pay-badge {
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 9px;
        white-space: nowrap;
    }

    .pay-theme-green { background: #e8f8f1; }
    .pay-theme-green .pay-item-name { color: #0b5e40; }
    .pay-theme-green .pay-item-sub { color: #0f7550; }
    .pay-theme-green .pay-badge { background: #bcebd7; color: #0b5e40; }
    .dot-green { background: #1cc88a; }

    .pay-theme-orange { background: #fff3e6; }
    .pay-theme-orange .pay-item-name { color: #7d4a0b; }
    .pay-theme-orange .pay-item-sub { color: #9c5a06; }
    .pay-theme-orange .pay-badge { background: #ffd7ad; color: #7d4a0b; }
    .dot-orange { background: #f6a12a; }

    .pay-theme-red { background: #fdecec; }
    .pay-theme-red .pay-item-name { color: #7a2020; }
    .pay-theme-red .pay-item-sub { color: #9b2a2a; }
    .pay-theme-red .pay-badge { background: #f6c4c4; color: #7a2020; }
    .dot-red { background: #e74a3b; }

    .pay-empty {
        color: #7f8da3;
        font-size: 0.9rem;
    }

    @media (max-width: 991.98px) {
        .profile-card-body {
            grid-template-columns: 1fr;
        }

        .mini-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .kv-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .mini-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $formatRupiah = fn($nominal) => 'Rp ' . number_format((int) $nominal, 0, ',', '.');
    $namaBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
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
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('dashboard') }}" class="form-row align-items-end">
                <div class="form-group col-md-3 mb-2">
                    <label class="small text-muted mb-1">Filter Bulan</label>
                    <select name="bulan" class="form-control">
                        <option value="">Semua Bulan</option>
                        @foreach($namaBulan as $nomor => $label)
                            <option value="{{ $nomor }}" {{ (string) ($filters['bulan'] ?? '') === (string) $nomor ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="small text-muted mb-1">Filter Tahun</label>
                    <select name="tahun" class="form-control">
                        <option value="">Semua Tahun</option>
                        @foreach($filterTahunOptions as $tahun)
                            <option value="{{ $tahun }}" {{ (string) ($filters['tahun'] ?? '') === (string) $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6 mb-2 d-flex" style="gap:8px;">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('dashboard2.export', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i> Download Excel
                    </a>
                </div>
            </form>
            <small class="text-muted">Periode data aktif: {{ $labelPeriodeTagihan }}</small>
        </div>
    </div>

    <div class="profile-card">
        <div class="profile-card-body">
            <div class="profile-left">
    {{-- Avatar klik untuk ganti foto --}}
    <div class="avatar-wrapper" onclick="openFotoModal()">
        @if($siswa->upload_foto)
            <img src="{{ asset('public/storage/' . $siswa->upload_foto) }}"
                 alt="Foto {{ $siswa->nama_siswa }}"
                 class="avatar-img"
                 id="avatarImg">
        @else
            <div class="avatar-circle" id="avatarImg" style="width:84px;height:84px;border-radius:999px;background:rgba(255,255,255,0.16);border:2px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;font-size:1.65rem;font-weight:700;">
                {{ $initials ?: 'SW' }}
            </div>
        @endif

        <div class="avatar-overlay">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            Ganti Foto
        </div>
    </div>

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
                        <small>Total tagihan periode {{ strtolower($labelPeriodeTagihan) }}</small>
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
                    <div class="mini-stat">
                        <small>Total pembayaran masuk</small>
                        <strong>{{ $formatRupiah($totalPembayaranMasuk) }}</strong>
                    </div>
                </div>
                <div class="status-pill">{{ $statusRingkasan }}</div>
            </div>
        </div>
    </div>

    <div class="pay-card">
        <div class="pay-section-label">Riwayat Tagihan ({{ $labelPeriodeTagihan }})</div>
        <div class="pay-timeline">
            @forelse($riwayatTagihan as $item)
                @php
                    $isLast = $loop->last;
                    $status = $item->status ?? 'belum_bayar';
                    $theme = $status === 'lunas' ? 'green' : ($status === 'cicil' ? 'orange' : 'red');
                    $statusLabel = $status === 'lunas' ? 'Lunas' : ($status === 'cicil' ? 'Cicil' : 'Belum Bayar');
                    $periode = ($item->periode_bulan && $item->periode_tahun)
                        ? \Carbon\Carbon::create()->month($item->periode_bulan)->translatedFormat('F') . ' ' . $item->periode_tahun
                        : '-';
                @endphp
                <div class="pay-row">
                    <div class="pay-line-col">
                        <div class="pay-dot dot-{{ $theme }}"></div>
                        @unless($isLast)
                            <div class="pay-connector"></div>
                        @endunless
                    </div>
                    <div class="pay-item pay-theme-{{ $theme }}">
                        <div>
                            <p class="pay-item-name">{{ $item->jenisPembayaran->nama_pembayaran ?? 'Tagihan' }}</p>
                            <p class="pay-item-sub">
                                {{ optional($item->tanggal_tagihan)->format('d M Y') ?? '-' }}
                                | Periode {{ $periode }}
                                | Tagihan {{ $formatRupiah($item->nominal_tagihan) }}
                                | Sisa {{ $formatRupiah($item->sisa_tagihan) }}
                            </p>
                        </div>
                        <div>
                            <span class="pay-badge">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="pay-empty">Belum ada data tagihan.</div>
            @endforelse
        </div>

        @if($riwayatTagihan)
            <div class="mt-2">
                {{ $riwayatTagihan->links() }}
            </div>
        @endif
    </div>
@endif


{{-- MODAL GANTI FOTO --}}
<div class="foto-modal-backdrop" id="fotoModalBackdrop" onclick="handleBackdropClick(event)">
    <div class="foto-modal">
        <button class="foto-modal-close" onclick="closeFotoModal()">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="1" y1="1" x2="13" y2="13"/><line x1="13" y1="1" x2="1" y2="13"/>
            </svg>
        </button>

        <div class="foto-modal-title">Ganti Foto Profil</div>

        <form id="fotoForm" action="{{ route('siswa.updateFoto') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="foto-drop-zone" id="dropZone">
                <input type="file" name="upload_foto" id="fotoInput"
                       accept=".jpg,.jpeg,.png,.webp"
                       onchange="handleFotoChange(this)">
                <div class="foto-drop-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <p class="foto-drop-text">Klik atau seret foto ke sini</p>
                <p class="foto-drop-hint">JPG, JPEG, PNG, WEBP · Maks. 2 MB</p>
            </div>

            <div class="foto-preview-wrap" id="fotoPreviewWrap">
                <img src="" alt="Preview" class="foto-preview-img" id="fotoPreviewImg">
                <span class="foto-preview-name" id="fotoPreviewName"></span>
            </div>

            <div class="foto-modal-actions">
                <button type="button" class="btn-foto-cancel" onclick="closeFotoModal()">Batal</button>
                <button type="submit" class="btn-foto-save" id="btnFotoSave">Simpan Foto</button>
            </div>
        </form>
    </div>
</div>

{{-- TOAST --}}
<div class="foto-toast" id="fotoToast"></div>
@endsection



@push('scripts')
<script>
// ── Modal ──────────────────────────────────────────
function openFotoModal() {
    document.getElementById('fotoModalBackdrop').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeFotoModal() {
    document.getElementById('fotoModalBackdrop').classList.remove('active');
    document.body.style.overflow = '';
    resetFotoForm();
}

function handleBackdropClick(e) {
    if (e.target === document.getElementById('fotoModalBackdrop')) closeFotoModal();
}

function resetFotoForm() {
    document.getElementById('fotoInput').value = '';
    document.getElementById('fotoPreviewWrap').classList.remove('visible');
    document.getElementById('btnFotoSave').classList.remove('ready');
}

// ── File pick / drag ───────────────────────────────
function handleFotoChange(input) {
    const file = input.files[0];
    if (!file) return;

    // Validasi ukuran
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran file maksimal 2 MB.', 'error');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('fotoPreviewImg').src = e.target.result;
        document.getElementById('fotoPreviewName').textContent = file.name;
        document.getElementById('fotoPreviewWrap').classList.add('visible');
        document.getElementById('btnFotoSave').classList.add('ready');
    };
    reader.readAsDataURL(file);
}

// Drag & drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('fotoInput');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        handleFotoChange(input);
    }
});

// ── Submit ─────────────────────────────────────────
document.getElementById('fotoForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnFotoSave');
    btn.classList.add('loading');
    btn.textContent = 'Menyimpan...';
});

// ── Toast ──────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.getElementById('fotoToast');
    t.textContent = msg;
    t.className = 'foto-toast ' + type;
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => t.classList.remove('show'), 3000);
}

// Tampilkan toast dari session Laravel
@if(session('success'))
    window.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));
@endif
@if(session('error'))
    window.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));
@endif

// ESC untuk tutup modal
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFotoModal(); });
</script>
@endpush
