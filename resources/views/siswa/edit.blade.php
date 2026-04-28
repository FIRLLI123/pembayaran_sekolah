@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Siswa</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>NIS</label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                        value="{{ $siswa->nis }}">
                    @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Nama Siswa</label>
                    <input type="text" name="nama_siswa" class="form-control @error('nama_siswa') is-invalid @enderror"
                        value="{{ $siswa->nama_siswa }}">
                    @error('nama_siswa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}"
                                {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} - {{ $k->tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control" value="{{ $siswa->no_hp }}">
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control">{{ $siswa->alamat }}</textarea>
                </div>

                {{-- UPLOAD FOTO --}}
                <div class="form-group">
                    <label>Foto Siswa</label>

                    {{-- Tampilkan foto existing --}}
                    @if($siswa->upload_foto)
                        <div class="mb-2">
                            <img src="{{ asset('public/storage/' . $siswa->upload_foto) }}"
                                 alt="Foto Siswa"
                                 id="foto-preview"
                                 style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                        </div>
                    @else
                        <div class="mb-2" id="preview-wrap" style="display:none;">
                            <img id="foto-preview" src="" alt="Preview"
                                 style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                        </div>
                    @endif

                    <input type="file" name="upload_foto"
                           class="form-control-file @error('upload_foto') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.webp"
                           onchange="previewFoto(this)">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                    @error('upload_foto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="text-right">
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary" style="margin-left: 10px;">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    const img = document.getElementById('foto-preview');
    const wrap = document.getElementById('preview-wrap');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            if (wrap) wrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush