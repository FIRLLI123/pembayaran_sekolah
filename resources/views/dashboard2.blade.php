@extends('layouts.app')

@push('styles')
<style>
    .dashboard2-wrapper {
        padding-bottom: 1rem;
    }

    .dashboard2-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 1.75rem;
        width: 100%;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 2rem;
        animation: dashboard2FadeUp 0.4s ease both;
    }

    @keyframes dashboard2FadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dashboard2-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        min-width: 130px;
    }

    .dashboard2-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c4956a, #a0714f);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 600;
        color: #fff;
        letter-spacing: 1px;
    }

    .dashboard2-name {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a2e;
        text-align: center;
    }

    .dashboard2-stars {
        color: #f5a623;
        font-size: 13px;
        letter-spacing: 2px;
    }

    .dashboard2-rates {
        font-size: 11px;
        color: #9b9bae;
    }

    .dashboard2-trust-wrap {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
    }

    .dashboard2-trust-bar {
        width: 100px;
        height: 4px;
        background: #e8eaf0;
        border-radius: 2px;
        overflow: hidden;
    }

    .dashboard2-trust-fill {
        height: 100%;
        width: 85%;
        background: #4a90d9;
        border-radius: 2px;
        transform-origin: left;
        animation: dashboard2GrowBar 0.8s ease 0.2s both;
    }

    @keyframes dashboard2GrowBar {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    .dashboard2-trust-label {
        font-size: 11px;
        color: #9b9bae;
    }

    .dashboard2-divider-v {
        width: 1px;
        background: #eceef4;
        align-self: stretch;
        flex-shrink: 0;
    }

    .dashboard2-right {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .dashboard2-section-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #b0b3c4;
        margin-bottom: 6px;
    }

    .dashboard2-fields-row {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .dashboard2-field {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .dashboard2-field-label {
        font-size: 11px;
        color: #9b9bae;
    }

    .dashboard2-field-value {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a2e;
    }

    .dashboard2-field-link {
        font-size: 13px;
        font-weight: 500;
        color: #4a90d9;
    }

    .dashboard2-divider-h {
        border: none;
        border-top: 1px solid #eceef4;
        margin: 0;
    }

    .dashboard2-toggle {
        width: 34px;
        height: 19px;
        border-radius: 10px;
        position: relative;
        flex-shrink: 0;
        display: inline-block;
    }

    .dashboard2-toggle::after {
        content: '';
        position: absolute;
        width: 15px;
        height: 15px;
        background: #fff;
        border-radius: 50%;
        top: 2px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    }

    .dashboard2-toggle.on {
        background: #34c759;
    }

    .dashboard2-toggle.on::after {
        left: 17px;
    }

    .dashboard2-toggle.off {
        background: #d1d5e0;
    }

    .dashboard2-toggle.off::after {
        left: 2px;
    }

    @media (max-width: 767.98px) {
        .dashboard2-card {
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.25rem;
        }

        .dashboard2-divider-v {
            display: none;
        }

        .dashboard2-left {
            min-width: 100%;
        }
    }





    /* ... (styles dashboard2 yang sudah ada) ... */

    /* ===== Riwayat Pembayaran ===== */
    .pay-wrapper {
        margin-top: 1.5rem;
    }

    .pay-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 1.75rem;
        width: 100%;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        animation: dashboard2FadeUp 0.4s ease both;
    }

    .pay-section-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #b0b3c4;
        margin-bottom: 1.25rem;
    }

    .pay-timeline {
        display: flex;
        flex-direction: column;
    }

    .pay-row {
        display: flex;
        align-items: stretch;
        gap: 16px;
    }

    .pay-line-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 20px;
        flex-shrink: 0;
    }

    .pay-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 6px;
    }

    .pay-connector {
        width: 2px;
        flex: 1;
        background: #eceef4;
        min-height: 16px;
    }

    .pay-item {
        flex: 1;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .pay-item-name {
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 3px 0;
    }

    .pay-item-sub {
        font-size: 12px;
        margin: 0;
        opacity: 0.75;
    }

    .pay-item-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .pay-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .pay-arrow {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        color: #fff;
        text-decoration: none;
    }

    /* Color themes */
    .pay-theme-blue  { background: #E6F1FB; }
    .pay-theme-blue  .pay-item-name { color: #0C447C; }
    .pay-theme-blue  .pay-item-sub  { color: #185FA5; }
    .pay-theme-blue  .pay-arrow     { background: #378ADD; }
    .pay-theme-blue  .pay-badge     { background: #B5D4F4; color: #0C447C; }
    .dot-blue  { background: #378ADD; }

    .pay-theme-teal  { background: #E1F5EE; }
    .pay-theme-teal  .pay-item-name { color: #085041; }
    .pay-theme-teal  .pay-item-sub  { color: #0F6E56; }
    .pay-theme-teal  .pay-arrow     { background: #1D9E75; }
    .pay-theme-teal  .pay-badge     { background: #9FE1CB; color: #085041; }
    .dot-teal  { background: #1D9E75; }

    .pay-theme-purple { background: #EEEDFE; }
    .pay-theme-purple .pay-item-name { color: #3C3489; }
    .pay-theme-purple .pay-item-sub  { color: #534AB7; }
    .pay-theme-purple .pay-arrow     { background: #7F77DD; }
    .pay-theme-purple .pay-badge     { background: #CECBF6; color: #3C3489; }
    .dot-purple { background: #7F77DD; }

    .pay-theme-amber { background: #FAEEDA; }
    .pay-theme-amber .pay-item-name { color: #633806; }
    .pay-theme-amber .pay-item-sub  { color: #854F0B; }
    .pay-theme-amber .pay-arrow     { background: #BA7517; }
    .pay-theme-amber .pay-badge     { background: #FAC775; color: #633806; }
    .dot-amber { background: #fff; border: 2px solid #BA7517; }

    .pay-theme-red   { background: #FCEBEB; }
    .pay-theme-red   .pay-item-name { color: #791F1F; }
    .pay-theme-red   .pay-item-sub  { color: #A32D2D; }
    .pay-theme-red   .pay-arrow     { background: #E24B4A; }
    .pay-theme-red   .pay-badge     { background: #F7C1C1; color: #791F1F; }
    .dot-red   { background: #E24B4A; }

    .pay-theme-pink  { background: #FBEAF0; }
    .pay-theme-pink  .pay-item-name { color: #72243E; }
    .pay-theme-pink  .pay-item-sub  { color: #993556; }
    .pay-theme-pink  .pay-arrow     { background: #D4537E; }
    .pay-theme-pink  .pay-badge     { background: #F4C0D1; color: #72243E; }
    .dot-pink  { background: #D4537E; }
</style>
@endpush

@section('content')
<div class="dashboard2-wrapper">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Profile</h1>
    </div>

    <div class="dashboard2-card">
        <div class="dashboard2-left">
            <div class="dashboard2-avatar">OKM</div>

            {{-- nama siswa --}}
            <div class="dashboard2-name">Oka Kamarulsyah</div>
            <div class="dashboard2-stars">&starf;&starf;&starf;&starf;&starf;</div>
            <div class="dashboard2-rates">214 rates</div>
            <div class="dashboard2-trust-wrap">
                <div class="dashboard2-trust-bar">
                    <div class="dashboard2-trust-fill"></div>
                </div>
                {{-- <div class="dashboard2-trust-label">85% trust</div> --}}
            </div>
        </div>

        <div class="dashboard2-divider-v"></div>

        <div class="dashboard2-right">
            <div>
                <div class="dashboard2-section-label">User profile</div>
                <div class="dashboard2-fields-row">
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Jenis Kelamin</span>
                        <span class="dashboard2-field-value">Female</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Kelas</span>
                        <span class="dashboard2-field-value">23/02/1987</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Phone number</span>
                        <span class="dashboard2-field-link">0858856546</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Alamat</span>
                        <span class="dashboard2-field-value">Jl. Contoh No. 4</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Nama Orang Tua/Wali</span>
                        <span class="dashboard2-field-value">Santi</span>
                    </div>
                </div>
            </div>

            <hr class="dashboard2-divider-h">

            <div>
                <div class="dashboard2-section-label">Health information</div>
                <div class="dashboard2-fields-row">
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Blood type</span>
                        <span class="dashboard2-field-link">AB &rsaquo;</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Deaf / Hard of hearing</span>
                        <span class="dashboard2-toggle on"></span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Silent</span>
                        <span class="dashboard2-toggle off"></span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Blind</span>
                        <span class="dashboard2-toggle off"></span>
                    </div>
                </div>
            </div>

            <hr class="dashboard2-divider-h">

            <div>
                <div class="dashboard2-section-label">Notifications type</div>
                <div class="dashboard2-fields-row">
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Emergency</span>
                        <span class="dashboard2-field-link">8/11 &rsaquo;</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Helps</span>
                        <span class="dashboard2-field-link">4/5 &rsaquo;</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Number report</span>
                        <span class="dashboard2-field-link">5/9 &rsaquo;</span>
                    </div>
                </div>
            </div>

            <hr class="dashboard2-divider-h">

            <div>
                <div class="dashboard2-section-label">Setting</div>
                <div class="dashboard2-fields-row">
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Language</span>
                        <span class="dashboard2-field-link">Bahasa Indonesia &rsaquo;</span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Get notifications</span>
                        <span class="dashboard2-toggle on"></span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Send to family</span>
                        <span class="dashboard2-toggle off"></span>
                    </div>
                    <div class="dashboard2-field">
                        <span class="dashboard2-field-label">Range get notifications</span>
                        <span class="dashboard2-field-link">5 km &rsaquo;</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tambahkan di @section('content'), di bawah dashboard2-card --}}

<div class="pay-wrapper">
    <div class="pay-card">
        <div class="pay-section-label">Riwayat Pembayaran</div>

        @php
            $colorThemes = ['blue', 'teal', 'purple', 'amber', 'red', 'pink'];
        @endphp


@php
    $colorThemes = ['blue', 'teal', 'purple', 'amber', 'red', 'pink'];

    $pembayaran = [
        ['nama_tagihan' => 'SPP Bulan Januari',     'tanggal' => '2025-01-15', 'nominal' => 250000, 'status' => 'Lunas'],
        ['nama_tagihan' => 'SPP Bulan Februari',    'tanggal' => '2025-02-14', 'nominal' => 250000, 'status' => 'Lunas'],
        ['nama_tagihan' => 'Biaya Ujian Semester',  'tanggal' => '2025-03-03', 'nominal' => 150000, 'status' => 'Lunas'],
        ['nama_tagihan' => 'SPP Bulan Maret',       'tanggal' => '2025-03-20', 'nominal' => 250000, 'status' => 'Menunggu'],
        ['nama_tagihan' => 'Biaya Ekskul',          'tanggal' => '2025-03-25', 'nominal' => 100000, 'status' => 'Gagal'],
    ];
@endphp

        <div class="pay-timeline">
            @foreach ($pembayaran as $index => $item)
    @php
        $theme = $colorThemes[$index % count($colorThemes)];
        $isLast = $loop->last;
    @endphp

    <div class="pay-row">
        <div class="pay-line-col">
            <div class="pay-dot dot-{{ $theme }}"></div>
            @unless ($isLast)
                <div class="pay-connector"></div>
            @endunless
        </div>
        <div class="pay-item pay-theme-{{ $theme }}">
            <div>
                <p class="pay-item-name">{{ $item['nama_tagihan'] }}</p>
                <p class="pay-item-sub">
                    {{ \Carbon\Carbon::parse($item['tanggal'])->format('d M Y') }}
                    &nbsp;·&nbsp;
                    Rp {{ number_format($item['nominal'], 0, ',', '.') }}
                </p>
            </div>
            <div class="pay-item-right">
                <span class="pay-badge">{{ $item['status'] }}</span>
                <a href="#" class="pay-arrow">&#8599;</a>
            </div>
        </div>
    </div>
@endforeach
        </div>
    </div>
</div>
</div>
@endsection
