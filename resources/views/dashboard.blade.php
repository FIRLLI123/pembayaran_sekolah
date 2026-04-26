@extends('layouts.app')

@section('content')
@php
    $formatRupiah = function ($nominal) {
        return 'Rp ' . number_format((int) $nominal, 0, ',', '.');
    };
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Pembayaran Sekolah</h1>
    <span class="text-muted small">Update: {{ now()->translatedFormat('d F Y H:i') }}</span>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Dashboard</h6>
    </div>
    <div class="card-body pb-2">
        <form method="GET" action="{{ route('dashboard') }}" id="dashboardFilterForm">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label class="small text-muted mb-1">Per Siswa</label>
                    <select name="siswa_id" id="filterSiswa" class="form-control">
                        <option value="">Semua Siswa</option>
                        @foreach($filterSiswa as $siswa)
                            <option
                                value="{{ $siswa->id }}"
                                data-kelas-id="{{ $siswa->kelas_id }}"
                                {{ (string) ($filters['siswa_id'] ?? '') === (string) $siswa->id ? 'selected' : '' }}
                            >
                                {{ $siswa->nama_siswa }} ({{ $siswa->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small text-muted mb-1">Per Kelas</label>
                    <select name="kelas_id" id="filterKelas" class="form-control">
                        <option value="">Semua Kelas</option>
                        @foreach($filterKelas as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) ($filters['kelas_id'] ?? '') === (string) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="small text-muted mb-1">Periode Mulai</label>
                    <input type="date" name="periode_mulai" class="form-control" value="{{ $filters['periode_mulai'] ?? '' }}">
                </div>
                <div class="form-group col-md-2">
                    <label class="small text-muted mb-1">Periode Selesai</label>
                    <input type="date" name="periode_selesai" class="form-control" value="{{ $filters['periode_selesai'] ?? '' }}">
                </div>
                <div class="form-group col-md-2">
                    <label class="small text-muted mb-1">Status Tagihan</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ ($filters['status'] ?? '') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="cicil" {{ ($filters['status'] ?? '') === 'cicil' ? 'selected' : '' }}>Cicil</option>
                        <option value="belum_bayar" {{ ($filters['status'] ?? '') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    </select>
                </div>
                <div class="form-group col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Terapkan</button>
                </div>
            </div>
            <input type="hidden" name="preset_periode" id="presetPeriodeInput" value="{{ $filters['preset_periode'] ?? '' }}">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="btn-group btn-group-sm mb-2 mb-md-0" role="group">
                    <button type="button" class="btn btn-outline-primary preset-btn {{ ($filters['preset_periode'] ?? '') === 'bulan_ini' ? 'active' : '' }}" data-preset="bulan_ini">Bulan Ini</button>
                    <button type="button" class="btn btn-outline-primary preset-btn {{ ($filters['preset_periode'] ?? '') === '3_bulan' ? 'active' : '' }}" data-preset="3_bulan">3 Bulan</button>
                    <button type="button" class="btn btn-outline-primary preset-btn {{ ($filters['preset_periode'] ?? '') === '1_tahun' ? 'active' : '' }}" data-preset="1_tahun">1 Tahun</button>
                </div>
                <div class="d-flex">
                    <a
                        href="{{ route('dashboard.export', request()->query()) }}"
                        class="btn btn-success btn-sm mr-2"
                    >
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="clearPresetBtn">Clear Preset</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                </div>
            </div>
            <div class="small text-muted mt-2">
                Preset periode akan otomatis mengisi range tanggal.
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Tagihan Bulan Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $formatRupiah($totalTagihanBulanIni) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Pembayaran Masuk
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $formatRupiah($totalPembayaranMasuk) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wallet fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Jumlah Siswa Belum Lunas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($jumlahSiswaBelumLunas, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Jumlah Siswa Aktif
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($jumlahSiswaAktif, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tren Pembayaran Masuk per Bulan (Target vs Realisasi)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 380px;">
    <canvas id="paymentTrendChart"></canvas>
</div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Tagihan Keseluruhan</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2" style="height: 320px;">
    <canvas id="statusTagihanChart"></canvas>
</div>
                <div class="mt-4 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> Lunas</span>
                    <span class="mr-2"><i class="fas fa-circle text-warning"></i> Cicil</span>
                    <span class="mr-2"><i class="fas fa-circle text-danger"></i> Belum Bayar</span>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Lunas</span>
                        <strong>{{ $statusTagihan['lunas'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Cicil</span>
                        <strong>{{ $statusTagihan['cicil'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Belum Bayar</span>
                        <strong>{{ $statusTagihan['belum_bayar'] }}</strong>
                    </div>
                    <div class="text-muted mt-2">Total tagihan: {{ $totalStatusTagihan }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Realisasi Pembayaran per Jenis</h6>
            </div>
            <div class="card-body">
                @forelse($progressPerJenis as $item)
                    <h4 class="small font-weight-bold">
                        {{ $item['nama_pembayaran'] }}
                        <span class="float-right">
                            {{ number_format($item['persentase'], 2, ',', '.') }}%
                            ({{ $formatRupiah($item['total_bayar']) }} / {{ $formatRupiah($item['total_tagihan']) }})
                        </span>
                    </h4>
                    <div class="progress mb-4">
                        <div
                            class="progress-bar {{ $item['persentase'] >= 75 ? 'bg-success' : ($item['persentase'] >= 40 ? 'bg-warning' : 'bg-danger') }}"
                            role="progressbar"
                            style="width: {{ $item['persentase'] }}%"
                            aria-valuenow="{{ $item['persentase'] }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada jenis pembayaran yang bisa ditampilkan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('sbadmin2/vendor/chart.js/Chart.min.js') }}"></script>
    <script>
        (function () {
            var kelasSelect = document.getElementById('filterKelas');
            var siswaSelect = document.getElementById('filterSiswa');
            var selectedSiswa = "{{ $filters['siswa_id'] ?? '' }}";

            function filterSiswaByKelas() {
                if (!kelasSelect || !siswaSelect) return;
                var kelasId = kelasSelect.value;
                var hasSelected = false;

                for (var i = 0; i < siswaSelect.options.length; i++) {
                    var opt = siswaSelect.options[i];
                    if (!opt.value) {
                        opt.hidden = false;
                        continue;
                    }
                    var siswaKelas = opt.getAttribute('data-kelas-id');
                    var visible = !kelasId || siswaKelas === kelasId;
                    opt.hidden = !visible;

                    if (opt.value === siswaSelect.value && visible) {
                        hasSelected = true;
                    }
                }

                if (!hasSelected && siswaSelect.value) {
                    siswaSelect.value = '';
                }
            }

            if (kelasSelect) {
                kelasSelect.addEventListener('change', filterSiswaByKelas);
            }

            if (siswaSelect) {
                siswaSelect.value = selectedSiswa;
            }
            filterSiswaByKelas();
        })();

        (function () {
            var form = document.getElementById('dashboardFilterForm');
            var presetInput = document.getElementById('presetPeriodeInput');
            var presetButtons = document.querySelectorAll('.preset-btn');
            var clearPresetBtn = document.getElementById('clearPresetBtn');

            function setPreset(preset) {
                if (!presetInput) return;
                presetInput.value = preset;
                if (form) form.submit();
            }

            presetButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setPreset(btn.getAttribute('data-preset'));
                });
            });

            if (clearPresetBtn) {
                clearPresetBtn.addEventListener('click', function () {
                    if (!presetInput) return;
                    presetInput.value = '';
                    if (form) form.submit();
                });
            }
        })();

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        var trendCtx = document.getElementById('paymentTrendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Target Tagihan',
                            data: @json($chartTargetTagihan),
                            borderColor: 'rgba(78, 115, 223, 1)',
                            backgroundColor: 'rgba(78, 115, 223, 0.15)',
                            lineTension: 0.3,
                            pointRadius: 3,
                            fill: true
                        },
                        {
                            label: 'Realisasi Pembayaran',
                            data: @json($chartRealisasiPembayaran),
                            borderColor: 'rgba(28, 200, 138, 1)',
                            backgroundColor: 'rgba(28, 200, 138, 0.10)',
                            lineTension: 0.3,
                            pointRadius: 3,
                            fill: true
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                callback: function(value) {
                                    return formatRupiah(value);
                                }
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                return label + ': ' + formatRupiah(tooltipItem.yLabel);
                            }
                        }
                    },
                    legend: {
                        display: true
                    }
                }
            });
        }

        var pieCtx = document.getElementById('statusTagihanChart');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Cicil', 'Belum Bayar'],
                    datasets: [{
                        data: [
                            {{ $statusTagihan['lunas'] }},
                            {{ $statusTagihan['cicil'] }},
                            {{ $statusTagihan['belum_bayar'] }}
                        ],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 65
                }
            });
        }
    </script>
@endpush

