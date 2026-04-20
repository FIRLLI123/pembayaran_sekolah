@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data User</h1>
    <p class="data-table-kicker mb-3">Manajemen pengguna sistem</p>

    <a href="{{ route('users.create') }}"
       class="btn btn-primary mb-3 data-table-add-btn">
        <span class="data-table-plus-icon">+</span> Tambah User
    </a>

    <div class="card data-table-card">
        <div class="card-body">

            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Siswa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="fw-semibold">
                                {{ $user->name }}
                            </td>

                            <td class="text-muted">
                                {{ $user->email }}
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                @if (in_array($user->role, ['ortu', 'viewer']) && $user->siswa)
                                    <div class="fw-semibold">
                                        {{ $user->siswa->nama_siswa }}
                                    </div>
                                    <small class="text-muted">
                                        NIS: {{ $user->siswa->nis }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">

                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="data-table-action-btn data-table-action-edit"
                                   title="Edit">
                                    ✏️
                                </a>

                                <form action="{{ route('users.destroy', $user->id) }}"
                                      method="POST" class="d-inline"
                                      id="delete-form-{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="data-table-action-btn data-table-action-delete"
                                        onclick="confirmDelete({{ $user->id }})"
                                        title="Hapus">
                                        🗑️
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @endforeach

                        @if($users->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada data user
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