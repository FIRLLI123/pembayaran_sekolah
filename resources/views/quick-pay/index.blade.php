@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-0 text-gray-800 font-weight-bold">⚡ Bayar Tagihan</h1>
            <small class="text-muted">Pilih siswa → cek tagihan → bayar</small>
        </div>
        @if($role === 'admin')
            <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-table mr-1"></i><span class="d-none d-md-inline">Tabel Lengkap</span>
            </a>
        @endif
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            @if(session('pembayaran_baru_id'))
                &nbsp;
                <a href="{{ route('pembayaran.kwitansi', session('pembayaran_baru_id')) }}"
                   target="_blank" rel="noopener"
                   class="btn btn-sm btn-success ml-2">
                    <i class="fas fa-file-pdf mr-1"></i> Download Kwitansi
                </a>
            @endif
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- PILIH SISWA (Admin only) --}}
    @if($role === 'admin')
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('quick-pay.index') }}" id="formPilihSiswa">
                <label class="small font-weight-bold text-muted mb-1 d-block">Cari Siswa</label>
                <div class="d-flex" style="gap:8px;">
                    <select name="siswa_id" id="selectSiswa" class="form-control flex-fill">
                        <option value="">-- Pilih atau ketik nama siswa --</option>
                        @foreach($siswaList as $s)
                            <option value="{{ $s->id }}"
                                {{ $siswaTerpilih && $siswaTerpilih->id == $s->id ? 'selected' : '' }}>
                                {{ $s->nama_siswa }}{{ $s->kelas ? ' — Kelas ' . $s->kelas->nama_kelas : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- INFO SISWA TERPILIH --}}
    @if($siswaTerpilih)
    <div class="d-flex align-items-center mb-3 px-1" style="gap:12px;">
        <div class="rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-white"
             style="width:44px;height:44px;background:linear-gradient(135deg,#4e73df,#224abe);font-size:1.1rem;flex-shrink:0;">
            {{ strtoupper(substr($siswaTerpilih->nama_siswa, 0, 1)) }}
        </div>
        <div>
            <div class="font-weight-bold" style="font-size:1rem;">{{ $siswaTerpilih->nama_siswa }}</div>
            <small class="text-muted">
                NIS: {{ $siswaTerpilih->nis ?? '-' }}
                @if($siswaTerpilih->kelas)
                    &bull; Kelas {{ $siswaTerpilih->kelas->nama_kelas }}
                @endif
            </small>
        </div>
    </div>
    @endif

    {{-- DAFTAR TAGIHAN --}}
    @if($siswaTerpilih)
        @if($tagihanList->isEmpty())
            <div class="text-center py-5">
                <div style="font-size:3rem;">🎉</div>
                <div class="font-weight-bold text-success mt-2">Semua tagihan sudah lunas!</div>
                <small class="text-muted">Tidak ada tagihan yang perlu dibayar.</small>
            </div>
        @else
            <div class="mb-2 d-flex justify-content-between align-items-center px-1">
                <span class="small text-muted font-weight-bold">
                    {{ $tagihanList->count() }} tagihan belum lunas
                </span>
                <span class="small text-danger font-weight-bold">
                    Total: Rp {{ number_format($tagihanList->sum('sisa_tagihan'), 0, ',', '.') }}
                </span>
            </div>

            @foreach($tagihanList as $tagihan)
            @php
                $periode = '-';
                if ($tagihan->periode_bulan && $tagihan->periode_tahun) {
                    $bulanNama = ['','Januari','Februari','Maret','April','Mei','Juni',
                                  'Juli','Agustus','September','Oktober','November','Desember'];
                    $periode = ($bulanNama[$tagihan->periode_bulan] ?? '-') . ' ' . $tagihan->periode_tahun;
                }
                $isCicil = $tagihan->status === 'cicil';
            @endphp
            <div class="card-tagihan mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="font-weight-bold" style="font-size:.95rem;">
                            {{ $tagihan->jenisPembayaran->nama_pembayaran ?? 'Tagihan' }}
                        </div>
                        <small class="text-muted">{{ $periode }}</small>
                    </div>
                    <span class="badge {{ $isCicil ? 'badge-warning' : 'badge-danger' }}">
                        {{ $isCicil ? 'Cicil' : 'Belum Bayar' }}
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted d-block">Total Tagihan</small>
                        <span class="text-muted" style="font-size:.9rem;">
                            Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-right">
                        <small class="text-muted d-block">Sisa Bayar</small>
                        <span class="font-weight-bold text-danger" style="font-size:1.1rem;">
                            Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-block"
                        onclick="bukaModalBayar(
                            {{ $tagihan->id }},
                            {{ $tagihan->sisa_tagihan }},
                            '{{ addslashes($tagihan->jenisPembayaran->nama_pembayaran ?? 'Tagihan') }}',
                            '{{ $periode }}'
                        )">
                    <i class="fas fa-money-bill-wave mr-2"></i> Bayar Sekarang
                </button>
            </div>
            @endforeach
        @endif

    @elseif($role !== 'ortu')
        <div class="text-center py-5 text-muted">
            <div style="font-size:3rem;">👆</div>
            <div class="mt-2">Pilih siswa untuk melihat tagihan</div>
        </div>
    @endif

</div>

{{-- MODAL BAYAR --}}
<div class="modal fade" id="modalQuickBayar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formQuickBayar" enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">💰 Bayar Tagihan</h5>
                        <small class="text-muted" id="qp_info_tagihan"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info py-2 mb-3 d-flex justify-content-between align-items-center">
                        <span class="small">Sisa tagihan:</span>
                        <strong id="qp_sisa_label">Rp 0</strong>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nominal Bayar <span class="text-danger">*</span></label>
                        <input type="text" id="qp_nominal_view" class="form-control form-control-lg"
                               placeholder="Rp 0" autocomplete="off">
                        <input type="hidden" name="nominal_bayar" id="qp_nominal_real">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Metode Bayar <span class="text-danger">*</span></label>
                        <div class="d-flex" style="gap:10px;">
                            <label class="qp-metode-btn flex-fill text-center" id="btn_cash">
                                <input type="radio" name="metode_bayar" value="cash" class="d-none">
                                <span>💵 Cash</span>
                            </label>
                            <label class="qp-metode-btn flex-fill text-center" id="btn_transfer">
                                <input type="radio" name="metode_bayar" value="transfer" class="d-none">
                                <span>🏦 Transfer</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Upload Struk <span class="text-muted font-weight-normal">(opsional)</span></label>
                        <label class="qp-upload-label d-block">
                            <input type="file" name="upload_foto" id="qp_upload_input"
                                   accept="image/*" class="d-none">
                            <div id="qp_upload_placeholder">
                                <i class="fas fa-camera" style="font-size:1.5rem; color:#9ca3af;"></i>
                                <div class="small text-muted mt-1">Tap untuk upload foto struk</div>
                                <div class="small text-muted">(maks 2MB)</div>
                            </div>
                            <img id="qp_upload_preview" src="" alt="Preview"
                                 class="d-none img-fluid rounded" style="max-height:150px;">
                        </label>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"
                                  placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>

                </div>

                <div class="modal-footer" style="gap:8px;">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary flex-fill" id="qp_submit_btn">
                        <i class="fas fa-paper-plane mr-1"></i> Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- ======= STYLES ======= --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
<style>
    .card-tagihan {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
    }
    .qp-metode-btn {
        border: 2px solid #d1d5db;
        border-radius: 12px;
        padding: 12px 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: .9rem;
        transition: all .15s;
        margin: 0;
        user-select: none;
    }
    .qp-metode-btn.active {
        border-color: #4e73df;
        background: #eef2ff;
        color: #1e40af;
    }
    .qp-upload-label {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s;
    }
    .qp-upload-label:hover { border-color: #4e73df; }
    .modal-footer { display: flex; }

    /* Select2 size fix for Bootstrap 4 */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + .75rem + 2px) !important;
        padding: .375rem .75rem;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding: 0;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .75rem + 2px) !important;
    }
    .select2-search__field { font-size: 1rem !important; padding: 8px !important; }
    .select2-results__option { padding: 10px 14px; font-size: .9rem; }
    .select2-dropdown { box-shadow: 0 4px 16px rgba(0,0,0,.12); }

    @media (max-width: 767.98px) {
        .modal-dialog { margin: 8px; }
        .btn-block { font-size: .95rem; padding: .65rem; }
    }
</style>
@endpush

{{-- ======= SCRIPTS: Load Select2 dulu ======= --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {

    // ===== Select2 init =====
    if ($('#selectSiswa').length) {
        $('#selectSiswa').select2({
            placeholder: '🔍 Ketik nama siswa...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4',
            language: {
                noResults: function() { return 'Siswa tidak ditemukan'; },
                searching:  function() { return 'Mencari...'; }
            }
        });

        // Auto-submit saat pilih siswa
        $('#selectSiswa').on('select2:select', function() {
            $('#formPilihSiswa').submit();
        });

        // Clear → kembali ke halaman awal
        $('#selectSiswa').on('select2:clear', function() {
            window.location.href = '{{ route('quick-pay.index') }}';
        });
    }

    // ===== Format nominal rupiah =====
    $('#qp_nominal_view').on('input', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        $('#qp_nominal_real').val(val);
        $(this).val(val ? 'Rp ' + parseInt(val).toLocaleString('id-ID') : '');
    });

    // ===== Toggle metode bayar =====
    $('[name="metode_bayar"]').on('change', function() {
        $('.qp-metode-btn').removeClass('active');
        $(this).closest('.qp-metode-btn').addClass('active');
    });

    // ===== Preview upload foto =====
    $('#qp_upload_input').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#qp_upload_preview').attr('src', e.target.result).removeClass('d-none');
            $('#qp_upload_placeholder').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // ===== Validasi form bayar =====
    $('#formQuickBayar').on('submit', function(e) {
        const nominal = parseInt($('#qp_nominal_real').val()) || 0;
        const metode  = $('[name="metode_bayar"]:checked').val();

        if (nominal <= 0) {
            e.preventDefault();
            alert('Masukkan nominal bayar!');
            return;
        }
        if (!metode) {
            e.preventDefault();
            alert('Pilih metode bayar terlebih dahulu!');
            return;
        }

        $('#qp_submit_btn').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
    });
});

// ===== Buka modal bayar =====
function bukaModalBayar(id, sisa, namaTagihan, periode) {
    // Set action form
    $('#formQuickBayar').attr('action', '/quick-pay/' + id + '/bayar');

    // Info
    $('#qp_info_tagihan').text(namaTagihan + ' — ' + periode);
    $('#qp_sisa_label').text('Rp ' + parseInt(sisa).toLocaleString('id-ID'));

    // Default nominal = sisa
    $('#qp_nominal_real').val(sisa);
    $('#qp_nominal_view').val('Rp ' + parseInt(sisa).toLocaleString('id-ID'));

    // Reset file
    $('#qp_upload_input').val('');
    $('#qp_upload_preview').addClass('d-none');
    $('#qp_upload_placeholder').removeClass('d-none');

    // Reset metode
    $('[name="metode_bayar"]').prop('checked', false);
    $('.qp-metode-btn').removeClass('active');

    // Reset submit btn
    $('#qp_submit_btn').prop('disabled', false)
        .html('<i class="fas fa-paper-plane mr-1"></i> Bayar');

    $('#modalQuickBayar').modal('show');
}
</script>
@endpush
