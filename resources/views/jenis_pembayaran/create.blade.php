@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tambah Jenis Pembayaran</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('jenis-pembayaran.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Pembayaran</label>
                    <input type="text" name="nama_pembayaran" class="form-control">
                </div>

                <div class="form-group">
                    <label>Nominal Default</label>
                    <input type="number" name="nominal_default" class="form-control">
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
                </div>

                <div class="text-right">
                    <a href="{{ route('jenis-pembayaran.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection