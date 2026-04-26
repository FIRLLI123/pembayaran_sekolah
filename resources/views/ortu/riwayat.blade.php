@extends('layouts.app')

@push('styles')
<style>
    .ortu-wrapper {
        padding-bottom: 1rem;
    }

    .ortu-header-card,
    .pay-card,
    .tagihan-card,
    .filter-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        border: 0;
    }

    .ortu-header-card {
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }

    .ortu-title {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #8f95aa;
        margin-bottom: .4rem;
    }

    .ortu-student-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #16203a;
        margin: 0;
    }

    .ortu-subtext {
        color: #697188;
        font-size: .9rem;
        margin: .2rem 0 0 0;
    }

    .filter-card {
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }

    .pay-card,
    .tagihan-card {
        padding: 1.25rem;
    }

    .pay-section-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #8f95aa;
        margin-bottom: 1rem;
    }

    .pay-timeline {
        display: flex;
        flex-direction: column;
    }

    .pay-row {
        display: flex;
        align-items: stretch;
        gap: 14px;
    }

    .pay-line-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 16px;
        flex-shrink: 0;
    }

    .pay-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 7px;
        flex-shrink: 0;
    }

    .pay-connector {
        width: 2px;
        flex: 1;
        min-height: 14px;
        background: #e8ebf3;
    }

    .pay-item {
        flex: 1;
        border-radius: 12px;
        padding: .8rem 1rem;
        margin-bottom: .65rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
    }

    .pay-item-name {
        margin: 0 0 3px 0;
        font-size: .93rem;
        font-weight: 700;
    }

    .pay-item-sub {
        margin: 0;
        font-size: .82rem;
        opacity: .8;
    }

    .pay-badge {
        font-size: .72rem;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .pay-theme-blue  { background: #E6F1FB; }
    .pay-theme-blue  .pay-item-name { color: #0C447C; }
    .pay-theme-blue  .pay-item-sub  { color: #185FA5; }
    .pay-theme-blue  .pay-badge     { background: #B5D4F4; color: #0C447C; }
    .dot-blue  { background: #378ADD; }

    .pay-theme-teal  { background: #E1F5EE; }
    .pay-theme-teal  .pay-item-name { color: #085041; }
    .pay-theme-teal  .pay-item-sub  { color: #0F6E56; }
    .pay-theme-teal  .pay-badge     { background: #9FE1CB; color: #085041; }
    .dot-teal  { background: #1D9E75; }

    .pay-theme-amber { background: #FAEEDA; }
    .pay-theme-amber .pay-item-name { color: #633806; }
    .pay-theme-amber .pay-item-sub  { color: #854F0B; }
    .pay-theme-amber .pay-badge     { background: #FAC775; color: #633806; }
    .dot-amber { background: #BA7517; }

    .pay-theme-red   { background: #FCEBEB; }
    .pay-theme-red   .pay-item-name { color: #791F1F; }
    .pay-theme-red   .pay-item-sub  { color: #A32D2D; }
    .pay-theme-red   .pay-badge     { background: #F7C1C1; color: #791F1F; }
    .dot-red   { background: #E24B4A; }

    .pay-theme-purple { background: #EEEDFE; }
    .pay-theme-purple .pay-item-name { color: #3C3489; }
    .pay-theme-purple .pay-item-sub  { color: #534AB7; }
    .pay-theme-purple .pay-badge     { background: #CECBF6; color: #3C3489; }
    .dot-purple { background: #7F77DD; }

    .table thead th {
        white-space: nowrap;
    }

    .receipt-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0f172a;
        color: #fff;
        text-decoration: none;
        margin-left: 6px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid ortu-wrapper">
    <h1 class="h3 mb-3 text-gray-800">Riwayat Pembayaran & Tagihan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="ortu-header-card">
        <div class="ortu-title">Data Siswa</div>
        <p class="ortu-student-name">{{ $siswa->nama_siswa }}</p>
        <p class="ortu-subtext">
            NIS: {{ $siswa->nis ?? '-' }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
        </p>
    </div>

    <form method="GET" action="{{ route('ortu.riwayat') }}" class="filter-card">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="small text-muted mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="small text-muted mb-1">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
            </div>
            <div class="col-md-4 mb-2">
                <label class="small text-muted mb-1">Jenis Pembayaran</label>
                <select name="jenis_pembayaran_id" class="form-control">
                    <option value="">Semua Jenis Pembayaran</option>
                    @foreach($jenisPembayaran as $jenis)
                        <option value="{{ $jenis->id }}" {{ (string) request('jenis_pembayaran_id') === (string) $jenis->id ? 'selected' : '' }}>
                            {{ $jenis->nama_pembayaran }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button class="btn btn-primary mr-2">Filter</button>
                <a href="{{ route('ortu.riwayat') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="pay-card mb-3">
        <div class="pay-section-label">Riwayat Pembayaran</div>

        @php
            $colorThemes = ['blue', 'teal', 'amber', 'red', 'purple'];
        @endphp

        <div class="pay-timeline">
            @forelse ($pembayaran as $index => $item)
                @php
                    $theme = $colorThemes[$index % count($colorThemes)];
                    $isLast = $loop->last;
                    if ($item->status === 'pending') {
                        $statusLabel = 'Pending';
                    } elseif ($item->status === 'lunas') {
                        $statusLabel = 'Lunas';
                    } elseif ($item->status === 'ditolak') {
                        $statusLabel = 'Ditolak';
                    } else {
                        $statusLabel = 'Cicil';
                    }

                    if ($item->status === 'lunas' || $item->status === 'cicil') {
                        $keteranganVerifikasi = $item->keterangan ?: 'Pembayaran telah di-approve admin.';
                    } elseif ($item->status === 'ditolak') {
                        $keteranganVerifikasi = $item->keterangan ?: 'Pembayaran ditolak admin.';
                    } else {
                        $keteranganVerifikasi = $item->keterangan ?: 'Menunggu verifikasi admin.';
                    }
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
                            <p class="pay-item-name">{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</p>
                            <p class="pay-item-sub">
                                {{ optional($item->tanggal_bayar)->format('d M Y') ?? '-' }}
                                | Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                                | {{ strtoupper($item->metode_bayar ?? '-') }}
                                @if($item->upload_foto)
                                    | <a href="{{ asset($item->upload_foto) }}" target="_blank" rel="noopener">Bukti</a>
                                @endif
                            </p>
                            <p class="pay-item-sub mb-0">
                                Keterangan: {{ $keteranganVerifikasi }}
                            </p>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="pay-badge">{{ $statusLabel }}</span>
                            @if(in_array($item->status, ['lunas', 'cicil'], true))
                                <a href="{{ route('pembayaran.kwitansi', $item->id) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="receipt-link"
                                    title="Lihat Kwitansi">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">Belum ada riwayat pembayaran untuk filter yang dipilih.</div>
            @endforelse
        </div>

        <div class="mt-2">
            {{ $pembayaran->links() }}
        </div>
    </div>

    <div class="tagihan-card">
        <div class="pay-section-label">Detail Tagihan Siswa</div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihan as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                            <td>
                                @if($item->periode_bulan && $item->periode_tahun)
                                    {{ \Carbon\Carbon::create()->month($item->periode_bulan)->translatedFormat('F') }} {{ $item->periode_tahun }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->nominal_tagihan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->sisa_tagihan, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status === 'belum_bayar')
                                    <span class="badge badge-danger">Belum Bayar</span>
                                @elseif($item->status === 'cicil')
                                    <span class="badge badge-warning">Cicil</span>
                                @else
                                    <span class="badge badge-success">Lunas</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status !== 'lunas')
                                    <button type="button"
                                            class="btn btn-sm btn-primary"
                                            onclick="openBayarOrtuModal({{ $item->id }}, {{ $item->sisa_tagihan }})">
                                        Bayar
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $tagihan->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="modalBayarOrtu" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formBayarOrtu" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajukan Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Sisa Tagihan</label>
                        <input type="text" id="ortu_sisa_view" class="form-control" readonly>
                        <input type="hidden" id="ortu_sisa_real" value="0">
                    </div>

                    <div class="mb-2">
                        <label>Nominal Bayar</label>
                        <input type="text"
                               id="ortu_nominal_view"
                               class="form-control"
                               placeholder="Rp 0"
                               onkeyup="formatOrtuRupiah(this)"
                               required>
                        <input type="hidden" name="nominal_bayar" id="ortu_nominal_real">
                    </div>

                    <div class="mb-2">
                        <label>Metode Bayar</label>
                        <select name="metode_bayar" class="form-control" required>
                            <option value="transfer">Transfer</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Upload Bukti Bayar</label>
                        <input type="file"
                               name="upload_foto"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp"
                               required>
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maksimal 2 MB.</small>
                    </div>

                    <div class="mb-2">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatRupiahOrtu(angka) {
    let numberString = angka.replace(/[^,\d]/g, '').toString();
    let split = numberString.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    return rupiah ? 'Rp ' + rupiah : '';
}

function formatOrtuRupiah(el) {
    let value = el.value.replace(/[^0-9]/g, '');
    document.getElementById('ortu_nominal_real').value = value;
    el.value = formatRupiahOrtu(value);
}

function openBayarOrtuModal(tagihanId, sisaTagihan) {
    const form = document.getElementById('formBayarOrtu');
    form.action = '/ortu/tagihan/' + tagihanId + '/bayar';

    document.getElementById('ortu_sisa_real').value = sisaTagihan;
    document.getElementById('ortu_sisa_view').value = formatRupiahOrtu(String(sisaTagihan));
    document.getElementById('ortu_nominal_real').value = sisaTagihan;
    document.getElementById('ortu_nominal_view').value = formatRupiahOrtu(String(sisaTagihan));

    new bootstrap.Modal(document.getElementById('modalBayarOrtu')).show();
}

document.getElementById('formBayarOrtu').addEventListener('submit', function (e) {
    const nominal = parseInt(document.getElementById('ortu_nominal_real').value || '0', 10);
    const sisa = parseInt(document.getElementById('ortu_sisa_real').value || '0', 10);

    if (!nominal || nominal <= 0) {
        e.preventDefault();
        alert('Nominal bayar harus diisi.');
        return;
    }

    if (nominal > sisa) {
        e.preventDefault();
        alert('Nominal bayar tidak boleh melebihi sisa tagihan.');
    }
});
</script>
@endpush
