@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data Tagihan</h1>
    <p class="data-table-kicker mb-3">Manajemen tagihan siswa</p>
    {{-- FILTER --}}
    <form method="GET" action="{{ route('tagihan.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text"
                       name="nama_siswa"
                       class="form-control"
                       placeholder="Cari nama siswa..."
                       value="{{ request('nama_siswa') }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary">Cari</button>
                <a href="{{ route('tagihan.index') }}" class="btn btn-secondary" style="marginLeft:10px;">Reset</a>
            </div>
        </div>
    </form>

    {{-- 🔥 BUTTON GENERATE --}}
    <button class="btn btn-success" onclick="openGenerateModal()">
    ⚡ Generate SPP
</button>

<button class="btn btn-primary" style="marginLeft:10px;"
    onclick="openMultiBayarModal({{ $item->siswa_id ?? 0 }})">
    💰 Bayar Banyak Bulan
</button>

<button class="btn btn-info" style="marginLeft:10px;"    onclick="window.location.href='{{ route('tagihan.custom') }}'">
    🛠️ Generate Tagihan Custom
</button>

    {{-- 🔥 TABLE --}}
    <div class="card data-table-card">
        <div class="card-body">

            <div class="table-responsive data-table-wrap">
                @if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
                <table class="table data-table align-middle mb-0">
    <thead>
        <tr>
            <th>No</th>
            <th>Siswa</th>
            <th>Total Nominal</th>
            <th>Total Sisa</th>
            <th class="text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tagihan as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
            <td>Rp {{ number_format($item->total_nominal, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item->total_sisa, 0, ',', '.') }}</td>
            <td class="text-center">
            <button class="data-table-action-btn"
                onclick="bukaDetail({{ $item->siswa_id }}, '{{ $item->siswa->nama_siswa ?? '-' }}')"
                data-siswa-id="{{ $item->siswa_id }}"
                title="Detail">
                👁️
            </button>
</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>

    {{-- DETAIL PANEL --}}


</table>


            </div>

            {{-- PAGINATION --}}
            <div class="mt-3">
                {{ $tagihan->links() }}
            </div>

        </div>
    </div>


    <div id="detail_panel" style="display:none; padding-top:24px;" class="mt-3">
    <div class="card data-table-card">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-muted">Detail Tagihan — <span id="detail_nama_siswa"></span></h6>
                <button class="btn btn-sm btn-outline-secondary" onclick="tutupDetail()">✕ Tutup</button>
            </div>

            <div class="table-responsive">
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
                    <tbody id="detail_tbody">
                        <tr><td colspan="7" class="text-center">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

                {{-- Modal Generate Tagihan SPP --}} 
    <div class="modal fade" id="modalGenerate" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form method="POST" action="{{ route('generate.spp') }}">
      @csrf
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

        {{-- Header --}}
        <div class="modal-header px-4 py-3 border-bottom" style="background: #f8f9fa;">
          <h5 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
            <span style="color: #f59e0b;">⚡</span> Generate Tagihan SPP
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        {{-- Body --}}
        <div class="modal-body px-4 py-4" style="color: #1a1a1a;">

          {{-- Tahun --}}
          <div class="mb-4">
            <label class="form-label fw-semibold mb-2">Tahun</label>
            <input type="number" name="tahun" id="input_tahun" class="form-control form-control-lg"
                   value="{{ now()->year }}" min="2020" max="2099"
                   style="max-width: 200px;"
                   onchange="loadStatusBulan(this.value)">
          </div>

          {{-- Pilih Bulan --}}
          <div class="mb-2">
            <label class="form-label fw-semibold mb-2">Pilih Bulan</label>
            <div class="d-flex gap-2 mb-3">
              <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="checkAllBulan()">Pilih Semua</button>
              <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="uncheckAllBulan()" style="margin-left: 10px">Hapus Semua</button>
            </div>
            <div id="bulan_checklist" class="row g-3">
              {{-- diisi JS --}}
            </div>
          </div>

        </div>

        {{-- Footer --}}
        <div class="modal-footer px-4 py-3 border-top" style="background: #f8f9fa;">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success px-4 d-flex align-items-center gap-2">
            <span>⚡</span> Generate
          </button>
        </div>

      </div>
    </form>
  </div>
</div>


                {{-- Modal Bayar Tagihan Satuan--}}
    <div class="modal fade" id="modalBayar" tabindex="-1">
  <div class="modal-dialog">
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
                </div>

                <div class="mb-2">
                    <label>Nominal Bayar</label>

                    <input type="text" id="nominal_view" 
       class="form-control" 
       placeholder="Rp 0"
       onkeyup="formatInputRupiah(this)">
                    <input type="hidden" name="nominal_bayar" id="nominal_bayar">
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
                <button class="btn btn-primary" onclick="showLoading()">Bayar</button>
            </div>
        </div>
    </form>
  </div>
</div>


                {{-- Modal bayar tagihan banyak bulan (multi bayar) --}}
<div class="modal fade" id="modalMultiBayar" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="formMultiBayar">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Multi Bayar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="siswa_id" class="form-control" onchange="loadTotalTagihan(this.value)">
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($siswa as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>

                <div class="mb-2 mt-2" id="info_total_tagihan" style="display:none;">
                    <label>Total Tagihan Belum Lunas</label>
                    <input type="text" id="total_tagihan_view" class="form-control" readonly>
                    <input type="hidden" id="total_tagihan_real" value="0">
                </div>

                <div class="mb-2">
                    <label>Total Bayar</label>
                    <input type="text" id="multi_nominal_view"
                        class="form-control"
                        onkeyup="formatMultiRupiah(this)">
                    <input type="hidden" name="total_bayar" id="multi_nominal_real">
                </div>

                <div class="mb-2">
                    <label>Metode</label>
                    <select name="metode_bayar" class="form-control">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="showLoading()">Bayar</button>
            </div>
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

    let aktifSiswaId = null;

function bukaDetail(siswaId, namaSiswa) {
    // kalau klik siswa yang sama → toggle tutup
    if (aktifSiswaId === siswaId) {
        tutupDetail();
        return;
    }

    aktifSiswaId = siswaId;

    // highlight row yang aktif
    document.querySelectorAll('tr.row-aktif').forEach(r => r.classList.remove('row-aktif'));
    document.querySelectorAll('[data-siswa-id]').forEach(btn => {
        if (parseInt(btn.dataset.siswaId) === siswaId) {
            btn.closest('tr').classList.add('row-aktif');
        }
    });

    // tampilkan panel & nama
    document.getElementById('detail_nama_siswa').textContent = namaSiswa;
    document.getElementById('detail_panel').style.display = 'block';
    document.getElementById('detail_tbody').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Memuat data...</td></tr>';

    // scroll ke panel
    setTimeout(() => {
        document.getElementById('detail_panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);

    // fetch data
    fetch('/tagihan/' + siswaId + '/detail-ajax')
        .then(res => res.json())
        .then(data => renderDetail(data))
        .catch(() => {
            document.getElementById('detail_tbody').innerHTML =
                '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>';
        });
}

function renderDetail(data) {
    let tbody = document.getElementById('detail_tbody');

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada tagihan</td></tr>';
        return;
    }

    const namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];

    let html = '';
    data.forEach((item, index) => {

        // periode
        let periode = '-';
        if (item.periode_bulan) {
            periode = namaBulan[item.periode_bulan] + ' ' + item.periode_tahun;
        }

        // status badge
        let badge = '';
        if (item.status === 'belum_bayar') {
            badge = '<span class="badge bg-danger">Belum</span>';
        } else if (item.status === 'cicil') {
            badge = '<span class="badge bg-warning text-dark">Cicil</span>';
        } else {
            badge = '<span class="badge bg-success">Lunas</span>';
        }

        // aksi
        let aksi = '';
        if (item.status !== 'lunas') {
            aksi = `<button class="data-table-action-btn data-table-action-edit"
                        onclick="openBayarModal(${item.id}, ${item.sisa_tagihan})"
                        title="Bayar">💰</button>`;
        } else {
            aksi = '<span class="text-muted" style="font-size:12px;">Lunas</span>';
        }

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.jenis_pembayaran?.nama_pembayaran ?? '-'}</td>
                <td>${periode}</td>
                <td>Rp ${formatAngka(item.nominal_tagihan)}</td>
                <td>Rp ${formatAngka(item.sisa_tagihan)}</td>
                <td>${badge}</td>
                <td class="text-center">${aksi}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function tutupDetail() {
    aktifSiswaId = null;
    document.getElementById('detail_panel').style.display = 'none';
    document.querySelectorAll('tr.row-aktif').forEach(r => r.classList.remove('row-aktif'));
}

function formatAngka(angka) {
    return parseInt(angka).toLocaleString('id-ID');
}

const namaBulan = [
    '', 'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
];

// status bulan mana yang sudah ter-generate (dari server)
let statusBulan = {}; // { 1: true, 2: false, ... }

function openGenerateModal() {
    let tahun = document.getElementById('input_tahun').value;
    loadStatusBulan(tahun);
    new bootstrap.Modal(document.getElementById('modalGenerate')).show();
}

function loadStatusBulan(tahun) {
    if (!tahun) return;

    fetch('/tagihan/status-bulan/' + tahun)
        .then(res => res.json())
        .then(data => {
            statusBulan = data; // { "1": true, "2": false, ... }
            renderChecklist();
        })
        .catch(() => {
            renderChecklist();
        });
}

function renderChecklist() {
    let container = document.getElementById('bulan_checklist');
    container.innerHTML = '';

    for (let i = 1; i <= 12; i++) {
        let sudahAda = statusBulan[i] === true;

        container.innerHTML += `
            <div class="col-6 col-md-4 mb-3">
                <div class="form-check border rounded px-3 py-3 ${sudahAda ? 'bg-light' : ''}">
                    <input class="form-check-input" type="checkbox"
                           name="bulan[]" value="${i}" id="bulan_${i}"
                           ${sudahAda ? 'checked' : ''}>
                    <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="bulan_${i}">
                        <span>${namaBulan[i]}</span>
                        ${sudahAda ? '<span class="badge bg-success" style="font-size:10px;">✓ Ada</span>' : ''}
                    </label>
                </div>
            </div>
        `;
    }
}

function checkAllBulan() {
    document.querySelectorAll('[name="bulan[]"]').forEach(cb => cb.checked = true);
}

function uncheckAllBulan() {
    document.querySelectorAll('[name="bulan[]"]').forEach(cb => cb.checked = false);
}


function openBayarModal(id, sisa) {
    let form = document.getElementById('formBayar');
    form.action = '/tagihan/' + id + '/bayar';

    // tampilkan sisa dalam format rupiah
    document.getElementById('sisa_tagihan_view').value = formatRupiah(sisa.toString());

    // OPTIONAL: isi default nominal = sisa
    document.getElementById('nominal_bayar').value = sisa;
    document.getElementById('nominal_view').value = formatRupiah(sisa.toString());

    let modal = new bootstrap.Modal(document.getElementById('modalBayar'));
    modal.show();
}

// Load total tagihan saat siswa dipilih
function loadTotalTagihan(siswaId) {
    if (!siswaId) {
        document.getElementById('info_total_tagihan').style.display = 'none';
        return;
    }

    fetch('/tagihan/total-belum-lunas/' + siswaId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('total_tagihan_real').value = data.total;
            document.getElementById('total_tagihan_view').value = formatRupiah(data.total.toString());
            document.getElementById('info_total_tagihan').style.display = 'block';
        });
}

// Submit dengan validasi
document.getElementById('formMultiBayar').addEventListener('submit', function(e) {
    e.preventDefault();

    let siswaId = this.querySelector('[name=siswa_id]').value;
    if (!siswaId) { alert('Pilih siswa dulu!'); return; }

    let nominal = parseInt(document.getElementById('multi_nominal_real').value) || 0;
    let totalTagihan = parseInt(document.getElementById('total_tagihan_real').value) || 0;

    // ✅ Validasi
    if (nominal > totalTagihan) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Nominal melebihi total tagihan!',
            confirmButtonColor: '#6c63ff'
        });
        return;
    }

    this.action = '/tagihan/multi-bayar/' + siswaId;
    showLoading();
    this.submit();
});

// Fungsi ini hanya untuk buka modal
function openMultiBayarModal() {
    let modal = new bootstrap.Modal(document.getElementById('modalMultiBayar'));
    modal.show();
}


// =========================
// LOADING
// =========================
function showLoading() {
    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang menyimpan pembayaran',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// =========================
// FORMAT RUPIAH (WAJIB DI LUAR)
// =========================
function formatRupiah(angka) {
    let number_string = angka.replace(/[^,\d]/g, '').toString();
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    return rupiah ? 'Rp ' + rupiah : '';
}

function formatInputRupiah(el) {
    let value = el.value.replace(/[^0-9]/g, '');

    document.getElementById('nominal_bayar').value = value;
    el.value = formatRupiah(value);
}

function formatMultiRupiah(el) {
    let value = el.value.replace(/[^0-9]/g, '');
    document.getElementById('multi_nominal_real').value = value;
    el.value = formatRupiah(value);
}
</script>

{{-- ✅ ALERT SUCCESS --}}
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

{{-- ❌ ALERT ERROR --}}
@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal ❌',
    text: '{{ session('error') }}',
    confirmButtonColor: '#6c63ff'
});



//multi




</script>
@endif

@endpush

@push('styles')
<style>
    tr.row-aktif { background-color: #E6F1FB !important; }

    #bulan_checklist .form-check {
  padding-top: 6px;
  padding-bottom: 6px;
}
</style>
@endpush
