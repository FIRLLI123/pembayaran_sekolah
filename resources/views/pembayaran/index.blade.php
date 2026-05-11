@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Data Pembayaran</h1>

    {{-- FILTER CARD --}}
    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pembayaran.index') }}" id="filterFormPembayaran">
                <div class="row">
                    <div class="form-group col-12 col-md-3 mb-2">
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

                    <div class="form-group col-12 col-md-2 mb-2">
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

                    <div class="form-group col-6 col-md-2 mb-2">
                        <label class="small text-muted mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $filters['tanggal_mulai'] ?? '' }}">
                    </div>

                    <div class="form-group col-6 col-md-2 mb-2">
                        <label class="small text-muted mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $filters['tanggal_selesai'] ?? '' }}">
                    </div>

                    <div class="form-group col-6 col-md-1 mb-2">
                        <label class="small text-muted mb-1">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua</option>
                            <option value="lunas"   {{ ($filters['status'] ?? '') === 'lunas'   ? 'selected' : '' }}>Lunas</option>
                            <option value="cicil"   {{ ($filters['status'] ?? '') === 'cicil'   ? 'selected' : '' }}>Cicil</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="ditolak" {{ ($filters['status'] ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="form-group col-6 col-md-2 mb-2">
                        <label class="small text-muted mb-1">Metode Bayar</label>
                        <select name="metode_bayar" class="form-control">
                            <option value="">Semua</option>
                            <option value="cash"     {{ ($filters['metode_bayar'] ?? '') === 'cash'     ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ ($filters['metode_bayar'] ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-1" style="gap:8px;">
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter mr-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                    <a href="{{ route('pembayaran.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="card shadow">
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive d-none d-md-block">
                {{-- DESKTOP: tabel biasa --}}
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Tgl Bayar</th>
                            <th>Nama Siswa</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Jenis Pembayaran</th>
                            <th>Nominal</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th class="text-center">Kwitansi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaran as $item)
                            <tr>
                                <td>{{ $pembayaran->firstItem() + $loop->index }}</td>
                                <td>{{ optional($item->tanggal_bayar)->format('d-m-Y') ?? '-' }}</td>
                                <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                                <td>{{ $item->siswa->nis ?? '-' }}</td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                                <td>Rp {{ number_format((int) $item->nominal_bayar, 0, ',', '.') }}</td>
                                <td class="text-capitalize">{{ $item->metode_bayar ?? '-' }}</td>
                                <td>
                                    @if($item->status === 'lunas')
                                        <span class="badge badge-success">Lunas</span>
                                    @elseif($item->status === 'cicil')
                                        <span class="badge badge-warning">Cicil</span>
                                    @elseif($item->status === 'pending')
                                        <span class="badge badge-info">Pending</span>
                                    @elseif($item->status === 'ditolak')
                                        <span class="badge badge-danger">Ditolak</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->keterangan ?: '-' }}</td>
                                <td class="text-center">
                                    @if(in_array($item->status, ['lunas', 'cicil'], true))
                                        <a href="{{ route('pembayaran.kwitansi', $item->id) }}" target="_blank" rel="noopener"
                                           class="btn btn-sm btn-outline-danger" title="Lihat Kwitansi PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('pembayaran.destroy', $item->id) }}" method="POST" class="d-inline js-delete-pembayaran-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pembayaran">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">Data pembayaran belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE: card list --}}
            <div class="d-md-none px-3 pt-3">
                @forelse($pembayaran as $item)
                    <div class="card-pembayaran mb-3">
                        {{-- Header card: nama siswa + status badge --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="font-weight-bold" style="font-size:.95rem;">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                <small class="text-muted">{{ $item->siswa->nis ?? '-' }} &bull; {{ $item->siswa->kelas->nama_kelas ?? '-' }}</small>
                            </div>
                            <div>
                                @if($item->status === 'lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @elseif($item->status === 'cicil')
                                    <span class="badge badge-warning">Cicil</span>
                                @elseif($item->status === 'pending')
                                    <span class="badge badge-info">Pending</span>
                                @elseif($item->status === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-secondary">{{ $item->status }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Detail rows --}}
                        <div class="card-pembayaran-row">
                            <span class="card-pembayaran-label">Jenis</span>
                            <span>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</span>
                        </div>
                        <div class="card-pembayaran-row">
                            <span class="card-pembayaran-label">Tgl Bayar</span>
                            <span>{{ optional($item->tanggal_bayar)->format('d-m-Y') ?? '-' }}</span>
                        </div>
                        <div class="card-pembayaran-row">
                            <span class="card-pembayaran-label">Nominal</span>
                            <span class="font-weight-bold text-success">Rp {{ number_format((int) $item->nominal_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="card-pembayaran-row">
                            <span class="card-pembayaran-label">Metode</span>
                            <span class="text-capitalize">{{ $item->metode_bayar ?? '-' }}</span>
                        </div>
                        @if($item->keterangan)
                        <div class="card-pembayaran-row">
                            <span class="card-pembayaran-label">Keterangan</span>
                            <span>{{ $item->keterangan }}</span>
                        </div>
                        @endif

                        {{-- Action buttons --}}
                        <div class="d-flex mt-2" style="gap:8px;">
                            @if(in_array($item->status, ['lunas', 'cicil'], true))
                                <a href="{{ route('pembayaran.kwitansi', $item->id) }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-danger flex-fill text-center">
                                    <i class="fas fa-file-pdf mr-1"></i> Kwitansi
                                </a>
                            @endif
                            <form action="{{ route('pembayaran.destroy', $item->id) }}" method="POST"
                                  class="js-delete-pembayaran-form {{ in_array($item->status, ['lunas','cicil']) ? '' : 'flex-fill' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted py-4">Data pembayaran belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap:8px;">
            <form method="GET" action="{{ route('pembayaran.index') }}" class="d-flex align-items-center" style="gap:8px;">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $arrayValue)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <small class="text-muted">Tampilkan</small>
                <select name="per_page" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
                    <option value="10"  {{ (string) ($perPage ?? request('per_page','10')) === '10'  ? 'selected' : '' }}>10</option>
                    <option value="20"  {{ (string) ($perPage ?? request('per_page','10')) === '20'  ? 'selected' : '' }}>20</option>
                    <option value="30"  {{ (string) ($perPage ?? request('per_page','10')) === '30'  ? 'selected' : '' }}>30</option>
                    <option value="all" {{ (string) ($perPage ?? request('per_page','10')) === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
                <small class="text-muted">data</small>
            </form>
            <small class="text-muted">
                Menampilkan {{ $pembayaran->firstItem() ?? 0 }} - {{ $pembayaran->lastItem() ?? 0 }} dari {{ $pembayaran->total() }} data
            </small>
            {{ $pembayaran->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var kelasSelect = document.getElementById('filterKelas');
        var siswaSelect = document.getElementById('filterSiswa');

        function filterSiswaByKelas() {
            if (!kelasSelect || !siswaSelect) return;
            var kelasId = kelasSelect.value;
            var hasSelected = false;
            for (var i = 0; i < siswaSelect.options.length; i++) {
                var opt = siswaSelect.options[i];
                if (!opt.value) { opt.hidden = false; continue; }
                var visible = !kelasId || opt.getAttribute('data-kelas-id') === kelasId;
                opt.hidden = !visible;
                if (opt.selected && visible) hasSelected = true;
            }
            if (!hasSelected && siswaSelect.value) siswaSelect.value = '';
        }

        if (kelasSelect) kelasSelect.addEventListener('change', filterSiswaByKelas);
        filterSiswaByKelas();

        document.querySelectorAll('.js-delete-pembayaran-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm('Yakin ingin menghapus pembayaran ini? Aksi ini tidak dapat dibatalkan.')) {
                    e.preventDefault();
                }
            });
        });
    })();
</script>
@endpush

@push('styles')
<style>
    /* === CARD PEMBAYARAN (Mobile) === */
    .card-pembayaran {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .card-pembayaran-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.875rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .card-pembayaran-row:last-of-type { border-bottom: none; }
    .card-pembayaran-label {
        color: #6b7280;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        flex-shrink: 0;
        margin-right: 8px;
    }

    /* === FILTER: tanggal berdampingan di mobile === */
    @media (max-width: 767.98px) {
        .card-body { padding: 1rem !important; }
        .btn-sm { font-size: .85rem; padding: .4rem .75rem; }
    }
</style>
@endpush

