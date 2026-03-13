@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Data Pembayaran</h1>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Cari nama siswa / NIS / jenis pembayaran / keterangan"
                    >
                </div>

                <div class="col-md-3 mb-2">
                    <select id="metodeFilter" class="form-control">
                        <option value="">Semua Metode Bayar</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <select id="statusFilter" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="cicil">Cicil</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 d-flex">
                    <button type="button" id="resetBtn" class="btn btn-secondary w-100">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="tanggal">
                                Tanggal Bayar <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="nama">
                                Nama Siswa <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="nis">
                                NIS <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="jenis">
                                Jenis Pembayaran <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="nominal">
                                Nominal <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="metode">
                                Metode Bayar <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-dark sort-btn" data-sort="status">
                                Status <span class="sort-icon"></span>
                            </button>
                        </th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <small class="text-muted mb-2 mb-md-0" id="tableInfo"></small>
            <nav aria-label="Pagination">
                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

@php
    $rows = $pembayaran->map(function ($item) {
        return [
            'tanggal' => optional($item->tanggal_bayar)->format('Y-m-d') ?? '',
            'tanggal_label' => optional($item->tanggal_bayar)->format('d-m-Y') ?? '-',
            'nama' => $item->siswa->nama_siswa ?? '-',
            'nis' => $item->siswa->nis ?? '-',
            'jenis' => $item->jenisPembayaran->nama_pembayaran ?? '-',
            'nominal' => (int) $item->nominal_bayar,
            'nominal_label' => 'Rp ' . number_format((int) $item->nominal_bayar, 0, ',', '.'),
            'metode' => $item->metode_bayar ?? '-',
            'status' => $item->status ?? '-',
            'keterangan' => $item->keterangan ?: '-',
        ];
    })->values();
@endphp

<script>
    (function () {
        const allRows = @json($rows);
        const state = {
            search: '',
            metode: '',
            status: '',
            sortBy: 'tanggal',
            sortDir: 'desc',
            page: 1,
            perPage: 10
        };

        const searchInput = document.getElementById('searchInput');
        const metodeFilter = document.getElementById('metodeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const resetBtn = document.getElementById('resetBtn');
        const tableBody = document.getElementById('tableBody');
        const tableInfo = document.getElementById('tableInfo');
        const pagination = document.getElementById('pagination');
        const sortButtons = document.querySelectorAll('.sort-btn');

        function normalizeText(value) {
            return String(value || '').toLowerCase();
        }

        function compareValues(a, b, field) {
            if (field === 'nominal') {
                return (a.nominal || 0) - (b.nominal || 0);
            }
            return normalizeText(a[field]).localeCompare(normalizeText(b[field]), 'id');
        }

        function getFilteredRows() {
            let rows = allRows.filter((row) => {
                const searchTarget = [
                    row.nama, row.nis, row.jenis, row.keterangan, row.metode, row.status, row.tanggal_label
                ].join(' ').toLowerCase();

                const passSearch = !state.search || searchTarget.includes(state.search);
                const passMetode = !state.metode || row.metode === state.metode;
                const passStatus = !state.status || row.status === state.status;

                return passSearch && passMetode && passStatus;
            });

            rows.sort((a, b) => {
                const result = compareValues(a, b, state.sortBy);
                return state.sortDir === 'asc' ? result : -result;
            });

            return rows;
        }

        function renderTable(rows) {
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / state.perPage));
            if (state.page > totalPages) state.page = totalPages;

            const start = (state.page - 1) * state.perPage;
            const pageRows = rows.slice(start, start + state.perPage);

            tableBody.innerHTML = '';

            if (pageRows.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center">Data pembayaran belum tersedia.</td></tr>';
            } else {
                pageRows.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${start + idx + 1}</td>
                        <td>${row.tanggal_label}</td>
                        <td>${row.nama}</td>
                        <td>${row.nis}</td>
                        <td>${row.jenis}</td>
                        <td>${row.nominal_label}</td>
                        <td class="text-capitalize">${row.metode}</td>
                        <td class="text-capitalize">${row.status}</td>
                        <td>${row.keterangan}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            }

            const from = total === 0 ? 0 : start + 1;
            const to = total === 0 ? 0 : Math.min(start + state.perPage, total);
            tableInfo.textContent = `Menampilkan ${from}-${to} dari ${total} data`;

            renderPagination(totalPages);
            updateSortIcons();
        }

        function createPageItem(label, page, disabled, active) {
            const li = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'page-link';
            btn.textContent = label;
            btn.disabled = disabled;
            btn.addEventListener('click', () => {
                if (!disabled) {
                    state.page = page;
                    render();
                }
            });

            li.appendChild(btn);
            return li;
        }

        function renderPagination(totalPages) {
            pagination.innerHTML = '';

            pagination.appendChild(createPageItem('«', state.page - 1, state.page === 1, false));

            const maxButtons = 5;
            let startPage = Math.max(1, state.page - Math.floor(maxButtons / 2));
            let endPage = startPage + maxButtons - 1;
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            for (let p = startPage; p <= endPage; p += 1) {
                pagination.appendChild(createPageItem(String(p), p, false, p === state.page));
            }

            pagination.appendChild(createPageItem('»', state.page + 1, state.page === totalPages, false));
        }

        function updateSortIcons() {
            sortButtons.forEach((btn) => {
                const icon = btn.querySelector('.sort-icon');
                const field = btn.dataset.sort;
                if (field === state.sortBy) {
                    icon.innerHTML = state.sortDir === 'asc' ? '&uarr;' : '&darr;';
                } else {
                    icon.innerHTML = '';
                }
            });
        }

        function render() {
            const rows = getFilteredRows();
            renderTable(rows);
        }

        searchInput.addEventListener('input', () => {
            state.search = normalizeText(searchInput.value.trim());
            state.page = 1;
            render();
        });

        metodeFilter.addEventListener('change', () => {
            state.metode = metodeFilter.value;
            state.page = 1;
            render();
        });

        statusFilter.addEventListener('change', () => {
            state.status = statusFilter.value;
            state.page = 1;
            render();
        });

        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            metodeFilter.value = '';
            statusFilter.value = '';
            state.search = '';
            state.metode = '';
            state.status = '';
            state.sortBy = 'tanggal';
            state.sortDir = 'desc';
            state.page = 1;
            render();
        });

        sortButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const field = btn.dataset.sort;
                if (state.sortBy === field) {
                    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortBy = field;
                    state.sortDir = field === 'tanggal' || field === 'nominal' ? 'desc' : 'asc';
                }
                state.page = 1;
                render();
            });
        });

        render();
    })();
</script>
@endsection
