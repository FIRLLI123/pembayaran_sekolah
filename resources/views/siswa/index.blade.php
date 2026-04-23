@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data Siswa</h1>
    <p class="data-table-kicker mb-3">Manajemen data siswa sekolah</p>

    <a href="{{ route('siswa.create') }}" class="btn btn-primary mb-3 data-table-add-btn">
        <span class="data-table-plus-icon">+</span> Tambah Siswa
    </a>

    <form method="GET" action="{{ route('siswa.index') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap: 10px;">
    <input type="text"
           name="search"
           value="{{ request('search') }}"
           class="form-control"
           style="max-width: 250px;"
           placeholder="Cari nama / NIS...">

    <select name="kelas_id" class="form-control" style="max-width: 200px;">
        <option value="">-- Semua Kelas --</option>
        @foreach ($kelas as $k)
            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                {{ $k->nama_kelas }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">Reset</a>
</form>

    <div class="card data-table-card">
        <div class="card-body">
            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>JK</th>
                            <th>No HP</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($siswa as $item)
                        <tr>
                            <td>{{ $siswa->firstItem() + $loop->index }}</td>
                            <td>
                                    @if($item->upload_foto)
                                        <img src="{{ asset('storage/' . $item->upload_foto) }}"
                                            alt="Foto"
                                            style="width:40px; height:40px; object-fit:cover; border-radius:6px;">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                            </td>
                            <td>{{ $item->nis }}</td>
                            <td>{{ $item->nama_siswa }}</td>
                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $item->jenis_kelamin }}</td>
                            <td>{{ $item->no_hp ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('siswa.edit', $item->id) }}"
                                   class="data-table-action-btn data-table-action-edit"
                                   title="Edit">✏️</a>

                                <form action="{{ route('siswa.destroy', $item->id) }}"
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
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada data siswa
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
                @if ($siswa->hasPages())
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <small class="text-muted">
                        Menampilkan {{ $siswa->firstItem() }}–{{ $siswa->lastItem() }}
                        dari {{ $siswa->total() }} data
                    </small>
                    <div>
                        {{ $siswa->links() }}
                    </div>
                </div>
                @else
                <small class="text-muted mt-2 d-block">
                    Menampilkan {{ $siswa->total() }} data
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