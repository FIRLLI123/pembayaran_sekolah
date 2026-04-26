@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Riwayat Siswa</h1>
    <p class="data-table-kicker mb-3">Informasi histori kenaikan kelas siswa</p>

    <form method="GET" action="{{ route('information.riwayat-siswa.index') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap: 10px;">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               class="form-control"
               style="max-width: 320px;"
               placeholder="Cari nama siswa...">

        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('information.riwayat-siswa.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>

    <div class="card data-table-card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive data-table-wrap">
                <table class="table data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Siswa</th>
                            <th>Kenaikan Kelas</th>
                            <th style="width: 180px;">Tanggal Pindah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayat as $item)
                            <tr>
                                <td>{{ $riwayat->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $item->siswa->nama_siswa ?? '-' }}</div>
                                    <small class="text-muted">NIS: {{ $item->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span class="badge badge-pill badge-light border px-3 py-2 text-dark">
                                            {{ $item->kelasLama->nama_kelas ?? 'Belum ada kelas' }}
                                        </span>
                                        <span class="text-primary font-weight-bold" style="font-size: 1rem;">
                                            <i class="fas fa-long-arrow-alt-right"></i>
                                        </span>
                                        <span class="badge badge-pill badge-success px-3 py-2">
                                            {{ $item->kelasBaru->nama_kelas ?? '-' }}
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Perubahan kelas dari lama ke kelas baru
                                    </small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pindah)->format('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pindah)->format('H:i') }} WIB
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada data riwayat kenaikan kelas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($riwayat->hasPages())
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <small class="text-muted">
                        Menampilkan {{ $riwayat->firstItem() }}-{{ $riwayat->lastItem() }}
                        dari {{ $riwayat->total() }} data
                    </small>
                    <div>{{ $riwayat->links() }}</div>
                </div>
            @else
                <small class="text-muted mt-2 d-block">
                    Menampilkan {{ $riwayat->total() }} data
                </small>
            @endif
        </div>
    </div>
</div>
@endsection
