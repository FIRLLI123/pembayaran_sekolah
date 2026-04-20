@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tambah User</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select id="role" name="role"
                        class="form-control @error('role') is-invalid @enderror">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                        
                        <option value="ortu" {{ old('role')=='ortu'?'selected':'' }}>Siswa/Orang Tua Siswa</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="siswa-wrapper" style="display: none;">
                    <label>Pilih Siswa</label>
                    <select id="siswa_id" name="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach ($siswaList as $siswa)
                            <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->nama_siswa }} (NIS: {{ $siswa->nis }})
                            </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleEl = document.getElementById('role');
        const siswaWrapper = document.getElementById('siswa-wrapper');
        const siswaNative = document.getElementById('siswa_id');
        let select2Ready = false;

        function toggleSiswaField() {
            const isOrtu = roleEl.value === 'ortu';
            siswaWrapper.style.display = isOrtu ? 'block' : 'none';
            siswaNative.required = isOrtu;

            if (!isOrtu) {
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery('#siswa_id').val('').trigger('change');
                } else {
                    siswaNative.value = '';
                }
            }
        }

        function initSelect2() {
            if (!(window.jQuery && jQuery.fn.select2)) {
                return;
            }

            if (select2Ready) {
                return;
            }

            jQuery('#siswa_id').select2({
                placeholder: '-- Pilih Siswa --',
                allowClear: true,
                width: '100%'
            });
            select2Ready = true;
        }

        function loadSelect2Script() {
            if (window.jQuery && jQuery.fn.select2) {
                initSelect2();
                return;
            }

            if (!window.jQuery) {
                return;
            }

            const existing = document.querySelector('script[data-select2="1"]');
            if (existing) {
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            script.dataset.select2 = '1';
            script.onload = initSelect2;
            document.body.appendChild(script);
        }

        roleEl.addEventListener('change', toggleSiswaField);
        toggleSiswaField();
        window.addEventListener('load', loadSelect2Script);
    });
</script>
@endsection
