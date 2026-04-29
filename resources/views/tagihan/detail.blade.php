@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-outline-secondary">← Kembali</a>
        <h1 class="h3 mb-0 text-gray-800">Detail Tagihan</h1>
    </div>
    <p class="data-table-kicker mb-3">{{ $siswa->nama_siswa }}</p>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('tagihan.detail', $siswa->id) }}" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="cicil"       {{ request('status') == 'cicil'       ? 'selected' : '' }}>Cicil</option>
                    <option value="lunas"       {{ request('status') == 'lunas'       ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bulan" class="form-control">
                    <option value="">-- Bulan --</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="tahun" class="form-control"
                       placeholder="Tahun" value="{{ request('tahun') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('tagihan.detail', $siswa->id) }}" class="btn btn-secondary ms-2">Reset</a>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card data-table-card">
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
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
                        @forelse ($detail as $item)
                        <tr>
                            <td>{{ $detail->firstItem() + $loop->index }}</td>
                            <td>{{ $item->jenisPembayaran->nama_pembayaran ?? '-' }}</td>
                            <td>
                                @if($item->periode_bulan)
                                    {{ \Carbon\Carbon::create()->month($item->periode_bulan)->translatedFormat('F') }}
                                    {{ $item->periode_tahun }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->nominal_tagihan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->sisa_tagihan, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status == 'belum_bayar')
                                    <span class="status-pill status-pill-belum">Belum Bayar</span>
                                @elseif($item->status == 'cicil')
                                    <span class="status-pill status-pill-cicil">Cicil</span>
                                @else
                                    <span class="status-pill status-pill-lunas">Lunas</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status != 'lunas')
                                    <button class="data-table-action-btn data-table-action-edit"
                                        onclick="openBayarModal({{ $item->id }}, {{ $item->sisa_tagihan }})"
                                        title="Bayar">
                                        💰
                                    </button>
                                @else
                                    <span class="text-muted" style="font-size:12px;">Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                <form method="GET" action="{{ route('tagihan.detail', $siswa->id) }}" class="d-flex align-items-center" style="gap: 8px;">
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
                <div>
                    {{ $detail->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL BAYAR -- copy dari index, cukup modal bayar saja --}}
    <div class="modal fade" id="modalBayar" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formBayar">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Bayar Tagihan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label>Sisa Tagihan</label>
                <input type="text" id="sisa_tagihan_view" class="form-control" readonly>
                <input type="hidden" id="nominal_bayar_sisa" value="0">
              </div>
              <div class="mb-2">
                <label>Nominal Bayar</label>
                <input type="text" id="nominal_view" class="form-control"
                       placeholder="Rp 0" oninput="formatInputRupiah(this)">
                <input type="hidden" name="nominal_bayar" id="nominal_bayar">
                <small class="text-danger d-none" id="error_nominal">Nominal melebihi sisa tagihan</small>
              </div>
              <div class="mb-2">
                <label>Metode Bayar</label>
                <select name="metode_bayar" class="form-control" required>
                  <option value="cash">Cash</option>
                  <option value="transfer">Transfer</option>
                </select>
              </div>
              <div class="mb-2">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Bayar</button>
            </div>
          </div>
        </form>
      </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.status-pill {
    display: inline-block;
    min-width: 90px;
    padding: 0.38rem 0.7rem;
    border-radius: 999px;
    text-align: center;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.2px;
    line-height: 1.1;
}
.status-pill-belum {
    background: #f8d7da;
    color: #7f1d1d;
    border: 1px solid #f1aeb5;
}
.status-pill-cicil {
    background: #fff3cd;
    color: #7a4b00;
    border: 1px solid #ffe08a;
}
.status-pill-lunas {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #8ce7be;
}
</style>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
function formatRupiah(angka) {
    let number_string = angka.replace(/[^,\d]/g, '').toString();
    let split  = number_string.split(',');
    let sisa   = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        rupiah += (sisa ? '.' : '') + ribuan.join('.');
    }
    return rupiah ? 'Rp ' + rupiah : '';
}

function formatInputRupiah(el) {
    let value = el.value.replace(/[^0-9]/g, '');
    document.getElementById('nominal_bayar').value = value;
    el.value = formatRupiah(value);
    let sisa = parseInt(document.getElementById('nominal_bayar_sisa').value) || 0;
    document.getElementById('error_nominal').classList.toggle('d-none', parseInt(value) <= sisa);
}

function openBayarModal(id, sisa) {
    document.getElementById('formBayar').action = '/tagihan/' + id + '/bayar';
    document.getElementById('sisa_tagihan_view').value  = formatRupiah(sisa.toString());
    document.getElementById('nominal_bayar_sisa').value = sisa;
    document.getElementById('nominal_bayar').value      = sisa;
    document.getElementById('nominal_view').value       = formatRupiah(sisa.toString());
    document.getElementById('error_nominal').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalBayar')).show();
}

document.getElementById('formBayar').addEventListener('submit', function(e) {
    let nominal = parseInt(document.getElementById('nominal_bayar').value) || 0;
    let sisa    = parseInt(document.getElementById('nominal_bayar_sisa').value) || 0;
    if (nominal <= 0) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Isi nominal bayar dulu!', confirmButtonColor: '#378ADD' });
        return;
    }
    if (nominal > sisa) {
        e.preventDefault();
        document.getElementById('error_nominal').classList.remove('d-none');
        return;
    }
    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
});
</script>

@if(session('success'))
<script>
Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session('success') }}', confirmButtonColor: '#1D9E75' });
</script>
@endif
@if(session('error'))
<script>
Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ session('error') }}', confirmButtonColor: '#D85A30' });
</script>
@endif



@if(session('warning_generate'))
<script>
console.log('WARNING MASUK ✅'); // debug

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
        <p>Yakin lanjut?</p>
    `,
    showCancelButton: true,
    confirmButtonText: 'Ya',
    cancelButtonText: 'Batal'
}).then((result) => {
    if (result.isConfirmed) {

        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('tagihan.custom.store') }}";

        let csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        let force = document.createElement('input');
        force.type = 'hidden';
        force.name = 'force';
        force.value = '1';
        form.appendChild(force);

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

