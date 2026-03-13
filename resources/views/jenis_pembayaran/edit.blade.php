@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Jenis Pembayaran</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('jenis-pembayaran.update', $jenis_pembayaran->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nama Pembayaran</label>
                    <input type="text" name="nama_pembayaran"
                        class="form-control"
                        value="{{ $jenis_pembayaran->nama_pembayaran }}">
                </div>

                <div class="form-group">
                    <label>Nominal Default</label>
                    <input type="number" name="nominal_default"
                        class="form-control"
                        value="{{ $jenis_pembayaran->nominal_default }}">
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan"
                        class="form-control">{{ $jenis_pembayaran->keterangan }}</textarea>
                </div>

                <div class="text-right">
                    <a href="{{ route('jenis-pembayaran.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection