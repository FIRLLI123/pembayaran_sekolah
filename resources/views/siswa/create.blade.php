@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tambah Siswa</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                        value="{{ old('nis') }}">
                    @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Nama Siswa</label>
                    <input type="text" name="nama_siswa" class="form-control @error('nama_siswa') is-invalid @enderror"
                        value="{{ old('nama_siswa') }}">
                    @error('nama_siswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} - {{ $k->tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}">
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control">{{ old('alamat') }}</textarea>
                </div>

                {{-- UPLOAD FOTO --}}
                <div class="form-group">
                    <label>Foto Siswa</label>
                    <input type="file" name="upload_foto"
                           class="form-control-file @error('upload_foto') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.webp"
                           onchange="previewFoto(this)">
                    <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                    @error('upload_foto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div id="preview-wrap" class="mt-2" style="display:none;">
                        <img id="foto-preview" src="" alt="Preview"
                             style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary" style="margin-left: 10px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    const wrap = document.getElementById('preview-wrap');
    const img = document.getElementById('foto-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            wrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        wrap.style.display = 'none';
    }
}
</script>
@endpush