@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Jenis Pembayaran</h1>
    <p class="data-table-kicker mb-3">Pengaturan jenis dan nominal pembayaran</p>

    <a href="{{ route('jenis-pembayaran.create') }}"
       class="btn btn-primary mb-3 data-table-add-btn">
        <span class="data-table-plus-icon">+</span> Tambah Jenis Pembayaran
    </a>

    <div class="card data-table-card">
        <div class="card-body">

            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Nominal Default</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="fw-semibold">
                                {{ $item->nama_pembayaran }}
                            </td>

                            <td>
                                Rp {{ number_format($item->nominal_default, 0, ',', '.') }}
                            </td>

                            <td class="text-muted">
                                {{ $item->keterangan ?? '-' }}
                            </td>

                            <td class="text-center">

                                <a href="{{ route('jenis-pembayaran.edit', $item->id) }}"
                                   class="data-table-action-btn data-table-action-edit"
                                   title="Edit">
                                    ✏️
                                </a>

                                <form action="{{ route('jenis-pembayaran.destroy', $item->id) }}"
                                      method="POST" class="d-inline"
                                      id="delete-form-{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="data-table-action-btn data-table-action-delete"
                                        onclick="confirmDelete({{ $item->id }})"
                                        title="Hapus">
                                        🗑️
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @endforeach

                        @if($data->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada data jenis pembayaran
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection



@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6c63ff',
        cancelButtonColor: '#9e9e9e',
        iconColor: '#f5a623',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush