@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data Orang Tua</h1>
    <p class="data-table-kicker mb-3">Manajemen data orang tua siswa</p>

    <a href="{{ route('ortu.create') }}" class="btn btn-primary mb-3 data-table-add-btn">
        <span class="data-table-plus-icon">+</span> Tambah Orang Tua
    </a>

    <form method="GET" action="{{ route('ortu.index') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap: 10px;">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               class="form-control"
               style="max-width: 250px;"
               placeholder="Cari nama / no hp...">

        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('ortu.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card data-table-card">
        <div class="card-body">
            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Ayah</th>
                            <th>Nama Ibu</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ortu as $item)
                        <tr>
                            <td>{{ $ortu->firstItem() + $loop->index }}</td>
                            <td>{{ $item->nama_ayah }}</td>
                            <td>{{ $item->nama_ibu }}</td>
                            <td>{{ $item->no_hp ?? '-' }}</td>
                            <td>{{ $item->alamat ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('ortu.edit', $item->id) }}"
                                   class="data-table-action-btn data-table-action-edit"
                                   title="Edit">✏️</a>

                                <form action="{{ route('ortu.destroy', $item->id) }}"
                                      method="POST" class="d-inline"
                                      id="delete-form-{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="data-table-action-btn data-table-action-delete"
                                        onclick="confirmDelete({{ $item->id }})"
                                        title="Hapus">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data orang tua
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($ortu->hasPages())
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                <small class="text-muted">
                    Menampilkan {{ $ortu->firstItem() }}–{{ $ortu->lastItem() }}
                    dari {{ $ortu->total() }} data
                </small>
                <div>
                    {{ $ortu->links() }}
                </div>
            </div>
            @else
            <small class="text-muted mt-2 d-block">
                Menampilkan {{ $ortu->total() }} data
            </small>
            @endif

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