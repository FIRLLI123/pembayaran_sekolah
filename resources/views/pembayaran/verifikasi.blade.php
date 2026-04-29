@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-3 text-gray-800">Verifikasi Pembayaran Pending</h1>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pembayaran.verifikasi') }}" class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama siswa / NIS / jenis pembayaran" value="{{ request('q') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('pembayaran.verifikasi') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>NIS</th>
                        <th>Jenis Pembayaran</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Bukti</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pending as $item)
                        <tr>
                            <td>{{ $pending->firstItem() + $loop->index }}</td>
                            <td>{{ optional($item->tanggal_bayar)->format('d-m-Y') }}</td>
                            <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                            <td>{{ $item->siswa->nis ?? '-' }}</td>
                            <td>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                            <td>Rp {{ number_format((int) $item->nominal_bayar, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($item->metode_bayar ?? '-') }}</td>
                            <td>
                                @if($item->upload_foto)
                                    <a href="{{ asset($item->upload_foto) }}" target="_blank" rel="noopener">Lihat Bukti</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->keterangan ?: '-' }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                <form method="POST" action="{{ route('pembayaran.approve', $item->id) }}" class="d-inline js-approve-form">
                                    @csrf
                                    <button type="button"
                                            class="btn btn-sm btn-success js-approve-btn">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('pembayaran.reject', $item->id) }}" class="d-inline ml-1 js-reject-form">
                                    @csrf
                                    <input type="hidden" name="alasan_reject" value="Ditolak oleh admin">
                                    <button type="button"
                                            class="btn btn-sm btn-danger js-reject-btn">
                                        Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada pembayaran pending.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
            <form method="GET" action="{{ route('pembayaran.verifikasi') }}" class="d-flex align-items-center" style="gap: 8px;">
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
                <select name="per_page" class="form-control form-control-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="10" {{ (string) ($perPage ?? request('per_page', '10')) === '10' ? 'selected' : '' }}>10</option>
                    <option value="20" {{ (string) ($perPage ?? request('per_page', '10')) === '20' ? 'selected' : '' }}>20</option>
                    <option value="30" {{ (string) ($perPage ?? request('per_page', '10')) === '30' ? 'selected' : '' }}>30</option>
                    <option value="all" {{ (string) ($perPage ?? request('per_page', '10')) === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
                <small class="text-muted">data</small>
            </form>
            {{ $pending->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
document.querySelectorAll('.js-approve-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
        const form = this.closest('.js-approve-form');
        Swal.fire({
            icon: 'question',
            title: 'Approve Pembayaran?',
            text: 'Pastikan nominal dan bukti pembayaran sudah sesuai.',
            showCancelButton: true,
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1D9E75'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

document.querySelectorAll('.js-reject-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
        const form = this.closest('.js-reject-form');
        Swal.fire({
            icon: 'warning',
            title: 'Tolak Pembayaran?',
            text: 'Pengajuan ini akan ditandai ditolak.',
            showCancelButton: true,
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session('success') }}',
    confirmButtonColor: '#1D9E75'
});
</script>
@endif

@if(session('error') || $errors->any())
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '{{ session('error') ?? $errors->first() }}',
    confirmButtonColor: '#D85A30'
});
</script>
@endif
@endpush
