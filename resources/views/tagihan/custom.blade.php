@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Generate Tagihan Custom</h1>
    <p class="text-muted mb-4">Buat tagihan non-rutin seperti study tour, ujian, dll</p>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('tagihan.custom.store') }}" onsubmit="showLoading()">
        @csrf

        <div class="row">

            {{-- LEFT --}}
            <div class="col-md-4">

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <h6 class="mb-3 fw-bold">⚙️ Pengaturan</h6>

                        {{-- Jenis Pembayaran --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis Pembayaran</label>
                            <select name="jenis_pembayaran_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                @foreach($jenis as $j)
                                    <option value="{{ $j->id }}">
                                        {{ $j->nama_pembayaran }} (Rp {{ number_format($j->nominal_default,0,',','.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelas --}}
                        <div class="mb-3">
                            <label class="form-label">Filter Kelas</label>
                            <select id="filter_kelas" class="form-control">
                                <option value="">Semua Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nominal Custom --}}
                        <div class="mb-3">
                            <label class="form-label">Nominal (Opsional)</label>
                            <input type="number" name="nominal_custom" class="form-control"
                                   placeholder="Kosongkan jika pakai default">
                        </div>

                        {{-- Jatuh Tempo --}}
                        <div class="mb-3">
                            <label class="form-label">Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" class="form-control">
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="col-md-8">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">👨‍🎓 Pilih Siswa</h6>

                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="checkAll(true)">
                                    Pilih Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="checkAll(false)">
                                    Hapus Semua
                                </button>
                            </div>
                        </div>

                        {{-- SEARCH --}}
                        <input type="text" id="search_siswa" class="form-control mb-3"
                               placeholder="🔍 Cari siswa...">

                        {{-- LIST --}}
                        <div id="list_siswa" class="row" style="max-height:400px; overflow:auto;">

                            @foreach($siswa as $s)
                                <div class="col-md-6 siswa-item mb-2"
                                     data-kelas="{{ $s->kelas_id }}"
                                     data-nama="{{ strtolower($s->nama_siswa) }}">

                                    <label class="w-100 border rounded p-2 d-flex align-items-center justify-content-between siswa-card">
                                        <div>
                                            <input type="checkbox" name="siswa_id[]" value="{{ $s->id }}">
                                            <span class="ms-2">{{ $s->nama_siswa }}</span>
                                        </div>

                                        <small class="text-muted">
                                            {{ $s->kelas->nama_kelas ?? '-' }}
                                        </small>
                                    </label>

                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>

                <div class="text-end mt-3">
                    <button class="btn btn-success px-4">
                        ⚡ Generate Tagihan
                    </button>
                </div>

            </div>

        </div>

    </form>

</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
.siswa-card {
    cursor: pointer;
    transition: 0.2s;
}
.siswa-card:hover {
    background: #f8f9fc;
    transform: scale(1.01);
}
</style>
@endpush


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>

// =====================
// CHECK ALL
// =====================
function checkAll(status) {
    document.querySelectorAll('[name="siswa_id[]"]').forEach(cb => {
        cb.checked = status;
    });
}

// =====================
// FILTER KELAS
// =====================
document.getElementById('filter_kelas')?.addEventListener('change', function() {
    let kelasId = this.value;

    document.querySelectorAll('.siswa-item').forEach(el => {
        el.style.display = (!kelasId || el.dataset.kelas == kelasId) ? 'block' : 'none';
    });
});

// =====================
// SEARCH SISWA
// =====================
document.getElementById('search_siswa')?.addEventListener('keyup', function() {
    let keyword = this.value.toLowerCase();

    document.querySelectorAll('.siswa-item').forEach(el => {
        let nama = el.dataset.nama;
        el.style.display = nama.includes(keyword) ? 'block' : 'none';
    });
});

// =====================
// CLICK CARD = CHECK
// =====================
document.querySelectorAll('.siswa-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            let cb = this.querySelector('input');
            cb.checked = !cb.checked;
        }
    });
});

// =====================
// LOADING
// =====================
function showLoading() {
    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang generate tagihan',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
}

</script>


{{-- ===================== --}}
{{-- ALERT SUCCESS --}}
{{-- ===================== --}}
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil ✅',
    text: '{{ session('success') }}',
    confirmButtonColor: '#6c63ff'
});
</script>
@endif


{{-- ===================== --}}
{{-- ALERT ERROR --}}
{{-- ===================== --}}
@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal ❌',
    text: '{{ session('error') }}',
    confirmButtonColor: '#6c63ff'
});
</script>
@endif


{{-- ===================== --}}
{{-- ALERT INFO --}}
{{-- ===================== --}}
@if(session('info'))
<script>
Swal.fire({
    icon: 'info',
    title: 'Info ℹ️',
    text: '{{ session('info') }}',
    confirmButtonColor: '#6c63ff'
});
</script>
@endif


{{-- ===================== --}}
{{-- 🚨 WARNING GENERATE --}}
{{-- ===================== --}}
@if(session('warning_generate'))
<script>
console.log('WARNING KELOAD ✅');

let data = @json(session('warning_generate'));

let list = data.list || [];
let tampil = list.slice(0,5).join(', ');

if (list.length > 5) {
    tampil += ' dan lainnya...';
}

Swal.fire({
    icon: 'warning',
    title: '⚠️ Duplikat Tagihan',
    html: `
        <p>${data.message}</p>
        <div style="max-height:120px; overflow:auto;">
            <b>${tampil}</b>
        </div>
        <p>Yakin ingin tetap generate?</p>
    `,
    showCancelButton: true,
    confirmButtonText: 'Ya, lanjutkan',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#6c63ff'
}).then((result) => {

    if (result.isConfirmed) {

        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('tagihan.custom.store') }}";

        // CSRF
        let csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        // FORCE
        let force = document.createElement('input');
        force.type = 'hidden';
        force.name = 'force';
        force.value = '1';
        form.appendChild(force);

        // OLD INPUT
        let old = data.old_input || {};

        for (let key in old) {
            if (Array.isArray(old[key])) {
                old[key].forEach(val => {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key + '[]';
                    input.value = val;
                    form.appendChild(input);
                });
            } else {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = old[key];
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endif

@endpush