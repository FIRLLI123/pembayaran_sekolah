@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Orang Tua</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('ortu.update', $ortu->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-control @error('nama_ayah') is-invalid @enderror"
                        value="{{ $ortu->nama_ayah }}">
                    @error('nama_ayah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="form-control @error('nama_ibu') is-invalid @enderror"
                        value="{{ $ortu->nama_ibu }}">
                    @error('nama_ibu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control"
                        value="{{ $ortu->no_hp }}">
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3">{{ $ortu->alamat }}</textarea>
                </div>

                <div class="text-right">
                    <a href="{{ route('ortu.index') }}" class="btn btn-secondary">
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