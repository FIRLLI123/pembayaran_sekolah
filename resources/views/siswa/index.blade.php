@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data Siswa</h1>
    <p class="data-table-kicker mb-3">Manajemen data siswa sekolah</p>

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 10px;">
        <a href="{{ route('siswa.create') }}" class="btn btn-primary data-table-add-btn">
            <span class="data-table-plus-icon">+</span> Tambah Siswa
        </a>

        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalGenerateKenaikan">
            Generate Kenaikan Kelas
        </button>
    </div>

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
                            <td colspan="8" class="text-center text-muted py-4">
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

<div class="modal fade" id="modalGenerateKenaikan" tabindex="-1" role="dialog" aria-labelledby="modalGenerateKenaikanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('siswa.generateKenaikan') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGenerateKenaikanLabel">Generate Kenaikan Kelas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="filter_kelas_asal" class="font-weight-bold">Filter Kelas Asal</label>
                            <select id="filter_kelas_asal" class="form-control">
                                <option value="">-- Semua Kelas Asal --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Filter ini membantu memilih siswa lebih cepat.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kelas_baru_id" class="font-weight-bold">Naik ke Kelas</label>
                            <select name="kelas_baru_id" id="kelas_baru_id" class="form-control" required>
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center flex-wrap mb-2" style="gap: 8px;">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnPilihSemua">Pilih Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnHapusSemua">Hapus Semua</button>
                        <small class="text-muted">Terpilih: <span id="jumlahSiswaTerpilih">0</span> siswa</small>
                    </div>

                    <div class="border rounded p-2" style="max-height: 320px; overflow-y: auto;">
                        @forelse ($siswaSemua as $item)
                            <div class="form-check siswa-option py-1">
                                <input
                                    class="form-check-input siswa-checkbox"
                                    type="checkbox"
                                    name="siswa_ids[]"
                                    value="{{ $item->id }}"
                                    id="siswa_{{ $item->id }}"
                                    data-kelas-id="{{ $item->kelas_id }}">
                                <label class="form-check-label" for="siswa_{{ $item->id }}">
                                    {{ $item->nis }} - {{ $item->nama_siswa }}
                                    <span class="text-muted">(Kelas: {{ $item->kelas->nama_kelas ?? '-' }})</span>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada data siswa.</p>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Proses Kenaikan Kelas</button>
                </div>
            </form>
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

(function () {
    const filterKelasAsal = document.getElementById('filter_kelas_asal');
    const btnPilihSemua = document.getElementById('btnPilihSemua');
    const btnHapusSemua = document.getElementById('btnHapusSemua');
    const jumlahSiswaTerpilih = document.getElementById('jumlahSiswaTerpilih');

    if (!filterKelasAsal || !btnPilihSemua || !btnHapusSemua || !jumlahSiswaTerpilih) {
        return;
    }

    const getCheckboxes = () => Array.from(document.querySelectorAll('.siswa-checkbox'));

    const getVisibleCheckboxes = () => {
        return getCheckboxes().filter((checkbox) => {
            const wrapper = checkbox.closest('.siswa-option');
            return wrapper && wrapper.style.display !== 'none';
        });
    };

    const updateJumlahTerpilih = () => {
        const totalTerpilih = getCheckboxes().filter((checkbox) => checkbox.checked).length;
        jumlahSiswaTerpilih.textContent = totalTerpilih;
    };

    const applyFilterKelas = () => {
        const kelasId = filterKelasAsal.value;

        getCheckboxes().forEach((checkbox) => {
            const wrapper = checkbox.closest('.siswa-option');
            if (!wrapper) return;

            const match = !kelasId || checkbox.dataset.kelasId === kelasId;
            wrapper.style.display = match ? 'block' : 'none';

            if (!match) {
                checkbox.checked = false;
            }
        });

        updateJumlahTerpilih();
    };

    filterKelasAsal.addEventListener('change', applyFilterKelas);

    btnPilihSemua.addEventListener('click', function () {
        getVisibleCheckboxes().forEach((checkbox) => {
            checkbox.checked = true;
        });
        updateJumlahTerpilih();
    });

    btnHapusSemua.addEventListener('click', function () {
        getCheckboxes().forEach((checkbox) => {
            checkbox.checked = false;
        });
        updateJumlahTerpilih();
    });

    getCheckboxes().forEach((checkbox) => {
        checkbox.addEventListener('change', updateJumlahTerpilih);
    });

    applyFilterKelas();
})();
</script>
@endpush
