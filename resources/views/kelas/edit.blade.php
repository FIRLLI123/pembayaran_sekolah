@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Kelas</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('kelas.update', $kelas->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="nama_kelas"
                        class="form-control @error('nama_kelas') is-invalid @enderror"
                        value="{{ old('nama_kelas', $kelas->nama_kelas) }}">
                    @error('nama_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran"
                        class="form-control @error('tahun_ajaran') is-invalid @enderror"
                        value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}">
                    @error('tahun_ajaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-right">
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button class="btn btn-primary" style="margin-left: 10px;">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection