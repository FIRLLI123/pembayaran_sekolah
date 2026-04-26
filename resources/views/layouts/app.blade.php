<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pembayaran Sekolah - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        html { font-size: 14px; }
        body { font-size: 0.95rem; }
        .table td, .table th { font-size: 0.9rem; }
        .form-control, .btn, .page-link { font-size: 0.9rem; }
        .h1, .h2, .h3, .h4, .h5, .h6 { margin-bottom: .75rem; }
        #content .container-fluid { padding-left: .75rem; padding-right: .75rem; }
        .sidebar .nav-item .nav-link { padding: .6rem .85rem; }
        .sidebar .nav-item .collapse .collapse-inner .collapse-item { padding: .4rem .85rem; }
        @media (min-width: 768px) {
            .sidebar .nav-item .nav-link { padding: .75rem .85rem; }
        }
    </style>

    <style>
.swal-rounded { border-radius: 16px !important; }
</style>
    @stack('styles')

</head>

<body id="page-top">

    @php
        $currentUser = auth()->user();
        $currentRole = $currentUser->role ?? null;
        $pendingVerifikasiCount = $currentRole === 'admin'
            ? \App\Models\Pembayaran::where('status', 'pending')->count()
            : 0;
    @endphp

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    {{-- <i class="fas fa-laugh-wink"></i> --}}
                </div>
                <div class="sidebar-brand-text mx-3">Sekolah TU</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            @if($currentRole !== 'ortu')
                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Interface
                </div>

                <!-- Nav Item - Pages Collapse Menu -->
                {{-- Tambahkan ortu* ke kondisi active di li dan div collapse --}}
<li class="nav-item {{ request()->is('siswa*') || request()->is('users*') || request()->is('kelas*') || request()->is('jenis-pembayaran*') || request()->is('ortu*') ? 'active' : '' }}">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
        aria-expanded="false" aria-controls="collapseTwo">
        <i class="fas fa-fw fa-cog"></i>
        <span>Master</span>
    </a>

    <div id="collapseTwo" class="collapse {{ request()->is('siswa*') || request()->is('users*') || request()->is('kelas*') || request()->is('jenis-pembayaran*') || request()->is('ortu*') ? 'show' : '' }}">
        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item {{ request()->is('users*') ? 'active' : '' }}"
                href="{{ route('users.index') }}">Data User</a>

            <a class="collapse-item {{ request()->is('siswa*') ? 'active' : '' }}"
                href="{{ route('siswa.index') }}">Siswa</a>

            <a class="collapse-item {{ request()->is('kelas*') ? 'active' : '' }}"
                href="{{ route('kelas.index') }}">Data Kelas</a>

            <a class="collapse-item {{ request()->is('jenis-pembayaran*') ? 'active' : '' }}"
                href="{{ route('jenis-pembayaran.index') }}">Jenis Pembayaran</a>

            {{-- Tambahan baru --}}
            <a class="collapse-item {{ request()->is('ortu*') ? 'active' : '' }}"
                href="{{ route('ortu.index') }}">Data Orang Tua</a>

        </div>
    </div>
</li>

                <div class="sidebar-heading">
    Transaksi
</div>

<li class="nav-item {{ request()->is('pembayaran*') || request()->is('tagihan*') ? 'active' : '' }}">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTransaksi"
        aria-expanded="false" aria-controls="collapseTransaksi">
        <i class="fas fa-fw fa-money-bill-wave"></i>
        <span>
            Transaksi
            @if($currentRole === 'admin' && $pendingVerifikasiCount > 0)
                <span class="badge badge-danger ml-1">{{ $pendingVerifikasiCount }}</span>
            @endif
        </span>
    </a>

    <div id="collapseTransaksi"
         class="collapse {{ request()->is('pembayaran*') || request()->is('tagihan*') ? 'show' : '' }}"
         data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item {{ request()->is('pembayaran*') && !request()->routeIs('pembayaran.verifikasi') ? 'active' : '' }}"
               href="{{ route('pembayaran.index') }}">
                Pembayaran
            </a>

            @if($currentRole === 'admin')
                <a class="collapse-item {{ request()->routeIs('pembayaran.verifikasi') ? 'active' : '' }}"
                   href="{{ route('pembayaran.verifikasi') }}">
                    Verifikasi Pembayaran
                    @if($pendingVerifikasiCount > 0)
                        <span class="badge badge-danger ml-1">{{ $pendingVerifikasiCount }}</span>
                    @endif
                </a>
            @endif

            <a class="collapse-item {{ request()->is('tagihan*') ? 'active' : '' }}"
               href="{{ route('tagihan.index') }}">
                Tagihan
            </a>

        </div>
    </div>
</li>

<div class="sidebar-heading">
    Information
</div>

<li class="nav-item {{ request()->is('information*') ? 'active' : '' }}">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInformation"
        aria-expanded="false" aria-controls="collapseInformation">
        <i class="fas fa-fw fa-info-circle"></i>
        <span>Information</span>
    </a>

    <div id="collapseInformation"
         class="collapse {{ request()->is('information*') ? 'show' : '' }}"
         data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item {{ request()->routeIs('information.riwayat-siswa.index') ? 'active' : '' }}"
               href="{{ route('information.riwayat-siswa.index') }}">
                Riwayat Siswa
            </a>
        </div>
    </div>
</li>
            @else
                <hr class="sidebar-divider">
                <div class="sidebar-heading">
                    Informasi
                </div>
                <li class="nav-item {{ request()->routeIs('ortu.riwayat') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('ortu.riwayat') }}">
                        <i class="fas fa-fw fa-history"></i>
                        <span>Riwayat Pembayaran</span>
                    </a>
                </li>
            @endif


           

            

            

            
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

            

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        

                        

            

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ $currentUser->name ?? 'User' }}</span>

    @php
        $siswa = $currentUser->siswa ?? null;
        $initials = collect(explode(' ', trim($currentUser->name ?? 'U')))
            ->filter()->take(2)
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->implode('');
    @endphp

    @if($siswa && $siswa->upload_foto)
        <img class="img-profile rounded-circle"
             src="{{ asset('storage/' . $siswa->upload_foto) }}"
             style="width:32px; height:32px; object-fit:cover;">
    @else
        <div class="img-profile rounded-circle d-inline-flex align-items-center justify-content-center"
             style="width:32px; height:32px; background:linear-gradient(135deg,#1f4e96,#2f80ed); color:#fff; font-size:12px; font-weight:700; vertical-align:middle;">
            {{ $initials }}
        </div>
    @endif
</a>
    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
        aria-labelledby="userDropdown">

        <a class="dropdown-item" href="#"
           data-toggle="modal" data-target="#modalUbahPassword">
            <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
            Ubah Password
        </a>

        <a class="dropdown-item" href="#"
           data-toggle="modal" data-target="#modalUbahInformasi">
            <i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>
            Ubah Informasi
        </a>

        <div class="dropdown-divider"></div>

        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
            Logout
        </a>
    </div>
</li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <!-- Begin Page Content -->
                <div id="app" class="container-fluid">
    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

  





{{-- ===== MODAL UBAH PASSWORD ===== --}}
<div class="modal fade" id="modalUbahPassword" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:0; border-radius:16px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg,#1f4e96,#2f80ed); border:0; padding:1.25rem 1.5rem;">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-key mr-2"></i> Ubah Password
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="{{ route('profile.ubahPassword') }}" method="POST" id="formUbahPassword">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">

                    {{-- Sukses --}}
                    @if(session('success_password'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ session('success_password') }}
                        </div>
                    @endif

                    {{-- Error global --}}
                    @if($errors->has('password_baru'))
                        <div class="alert alert-danger">
                            {{ $errors->first('password_baru') }}
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-700">Password Lama</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password_lama"
                                   id="passwordLama"
                                   class="form-control {{ $errors->has('password_lama') ? 'is-invalid' : '' }}"
                                   placeholder="Masukkan password lama">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="passwordLama">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_lama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-700">Password Baru</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password_baru"
                                   id="passwordBaru"
                                   class="form-control"
                                   placeholder="Minimal 6 karakter"
                                   oninput="checkPasswordStrength(this.value)">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="passwordBaru">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2" id="strengthWrap" style="display:none;">
                            <div style="height:5px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
                                <div id="strengthBar" style="height:100%; width:0; border-radius:99px; transition:width .3s, background .3s;"></div>
                            </div>
                            <small id="strengthText" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-gray-700">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password_baru_confirmation"
                                   id="passwordKonfirmasi"
                                   class="form-control"
                                   placeholder="Ulangi password baru">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="passwordKonfirmasi">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border:0; padding:.75rem 1.5rem 1.25rem;">
                    <button type="button" class="btn btn-light" data-dismiss="modal"
                            style="border-radius:10px; padding:.5rem 1.25rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary"
                            style="border-radius:10px; padding:.5rem 1.25rem; background:linear-gradient(135deg,#1f4e96,#2f80ed); border:0;">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL UBAH INFORMASI ===== --}}
<div class="modal fade" id="modalUbahInformasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:0; border-radius:16px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg,#1f4e96,#2f80ed); border:0; padding:1.25rem 1.5rem;">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-user-edit mr-2"></i> Ubah Informasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="{{ route('profile.ubahInformasi') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">

                    <p class="small font-weight-bold text-gray-600 mb-3" style="text-transform:uppercase; letter-spacing:.06em;">
                        Akun
                    </p>

                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-700">Nama</label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name', $currentUser->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-gray-700">Email</label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email', $currentUser->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if($currentUser->ortu_id)
                        <hr style="border-color:#e8ecf3;">
                        <p class="small font-weight-bold text-gray-600 mb-3" style="text-transform:uppercase; letter-spacing:.06em;">
                            Data Orang Tua
                        </p>

                        <div class="form-group">
                            <label class="small font-weight-bold text-gray-700">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="form-control"
                                   value="{{ old('nama_ayah', $currentUser->ortu->nama_ayah ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-gray-700">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="form-control"
                                   value="{{ old('nama_ibu', $currentUser->ortu->nama_ibu ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold text-gray-700">No HP</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="{{ old('no_hp', $currentUser->ortu->no_hp ?? '') }}">
                        </div>

                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-gray-700">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $currentUser->ortu->alamat ?? '') }}</textarea>
                        </div>
                    @endif

                </div>
                <div class="modal-footer" style="border:0; padding:.75rem 1.5rem 1.25rem;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius:10px; padding:.5rem 1.25rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" style="border-radius:10px; padding:.5rem 1.25rem; background:linear-gradient(135deg,#1f4e96,#2f80ed); border:0;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

  <!-- Bootstrap core JavaScript-->
<script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- Core plugin JavaScript-->
<script src="{{ asset('sbadmin2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

<!-- Custom scripts for all pages-->
<script src="{{ asset('sbadmin2/js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>

<!-- SweetAlert2 — harus sebelum script yang pakai Swal -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// Toggle show/hide password
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// Password strength
function checkPasswordStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');

    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 6)          score++;
    if (val.length >= 10)         score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%',  bg: '#ef4444', label: 'Sangat lemah' },
        { w: '40%',  bg: '#f97316', label: 'Lemah'        },
        { w: '60%',  bg: '#eab308', label: 'Cukup'        },
        { w: '80%',  bg: '#22c55e', label: 'Kuat'         },
        { w: '100%', bg: '#16a34a', label: 'Sangat kuat'  },
    ];

    const lvl = levels[Math.min(score - 1, 4)] || levels[0];
    bar.style.width      = lvl.w;
    bar.style.background = lvl.bg;
    text.textContent     = lvl.label;
    text.style.color     = lvl.bg;
}

$(document).ready(function () {

    // Auto-buka modal jika ada error password
    @if($errors->has('password_lama') || $errors->has('password_baru') || session('modal_password'))
        $('#modalUbahPassword').modal('show');
    @endif

    // Auto-buka modal jika ada error informasi
    @if($errors->has('name') || $errors->has('email'))
        $('#modalUbahInformasi').modal('show');
    @endif

    // Notifikasi sukses ubah password
    @if(session('success_password'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: @json(session('success_password')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#2f80ed',
            customClass: { popup: 'swal-rounded' }
        });
    @endif

    // Notifikasi sukses ubah informasi — pakai key khusus, bukan 'success'
    @if(session('success_informasi'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: @json(session('success_informasi')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#2f80ed',
            customClass: { popup: 'swal-rounded' }
        });
    @endif

});
</script>

@stack('scripts')
</body>

</html>
