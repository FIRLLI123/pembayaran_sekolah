# Mobile Admin Blueprint

## Tujuan

Membuat versi mobile Android native Java untuk admin TU dengan backend tetap memakai proyek Laravel ini.

Target utama:

- login admin dari Android
- kelola data master
- generate tagihan rutin dan custom
- proses pembayaran seperti flow web
- verifikasi pembayaran
- lihat dan unduh kwitansi
- lihat dashboard dan laporan
- lihat riwayat siswa / kenaikan kelas

## Kondisi Proyek Saat Ini

Backend web yang sudah tersedia:

- autentikasi web berbasis session
- dashboard admin dan dashboard ortu
- master data `users`, `siswa`, `kelas`, `jenis_pembayaran`, `ortu`
- transaksi `tagihan`, `pembayaran`, `quick-pay`
- verifikasi pembayaran `pending`
- kwitansi PDF
- riwayat kenaikan kelas siswa
app
File penting:

- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/TagihanController.php`
- `app/Http/Controllers/PembayaranController.php`
- `app/Http/Controllers/QuickPayController.php`
- `app/Http/Controllers/SiswaController.php`
- `app/Http/Controllers/RiwayatKelasSiswaController.php`

## Ringkasan Flow Bisnis Web

### 1. Master

- `User`: buat akun admin, petugas, ortu
- `Kelas`: data kelas dan tahun ajaran
- `Siswa`: biodata siswa, kelas, foto, nomor HP
- `Jenis Pembayaran`: nominal default dan kategori pembayaran
- `Ortu`: data wali/orang tua

### 2. Tagihan

- generate SPP bisa banyak bulan
- generate bisa difilter per kelas atau per siswa
- nominal bisa pakai default atau custom
- ada tagihan custom untuk pembayaran insidental
- tagihan disimpan per siswa, per jenis pembayaran, per periode
- status tagihan: `belum_bayar`, `cicil`, `lunas`

### 3. Pembayaran

- bayar satu tagihan
- multi-bayar ke banyak tagihan belum lunas milik satu siswa
- metode bayar: `cash` atau `transfer`
- admin bisa langsung memotong sisa tagihan
- ortu mengajukan pembayaran dengan bukti transfer lalu masuk status `pending`

### 4. Verifikasi

- admin melihat daftar pembayaran `pending`
- admin bisa `approve` atau `reject`
- saat approve, sisa tagihan baru dikurangi
- saat reject, pembayaran diberi status `ditolak`

### 5. Kwitansi

- kwitansi hanya untuk pembayaran yang sudah valid (`lunas` atau `cicil`)
- PDF dibuat dari backend
- ada preview kwitansi pada web

### 6. Dashboard dan Laporan

- filter siswa
- filter kelas
- filter periode
- filter status tagihan
- KPI total tagihan, total pembayaran, siswa belum lunas, siswa aktif
- chart target tagihan vs realisasi pembayaran
- progress pembayaran per jenis

### 7. Information

- riwayat perpindahan / kenaikan kelas siswa

## Gap untuk Kebutuhan Mobile

Saat ini backend belum siap untuk app Android karena:

- `routes/api.php` masih hampir kosong
- autentikasi masih login web session, bukan token mobile
- response masih berbentuk view Blade, belum JSON API
- logika bisnis masih tersebar di controller web
- upload file belum seragam

## Keputusan Teknis yang Disarankan

### Backend

- tetap gunakan Laravel pada repo ini
- gunakan `Laravel Sanctum` untuk token login Android
- buat API versi baru, misalnya prefix `/api/mobile/admin`
- jangan pakai controller web langsung sebagai endpoint mobile
- pisahkan logika transaksi ke service class agar web dan mobile berbagi aturan yang sama

### Android

- Android Studio
- Java native
- Retrofit atau Volley untuk HTTP client
- token disimpan aman di `SharedPreferences` terenkripsi atau pendekatan setara

## Desain Modul API

### A. Auth API

Endpoint minimum:

- `POST /api/mobile/admin/login`
- `POST /api/mobile/admin/logout`
- `GET /api/mobile/admin/me`

Response login minimum:

```json
{
  "message": "Login berhasil",
  "data": {
    "token": "plain-text-token",
    "user": {
      "id": 1,
      "name": "Admin TU",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

Aturan:

- hanya `admin` dan `petugas` yang boleh login ke app admin
- `ortu` nanti bisa dibuat app terpisah atau mode lain

### B. Master API

Endpoint minimum:

- `GET /api/mobile/admin/kelas`
- `POST /api/mobile/admin/kelas`
- `PUT /api/mobile/admin/kelas/{id}`
- `DELETE /api/mobile/admin/kelas/{id}`
- `GET /api/mobile/admin/siswa`
- `POST /api/mobile/admin/siswa`
- `PUT /api/mobile/admin/siswa/{id}`
- `DELETE /api/mobile/admin/siswa/{id}`
- `GET /api/mobile/admin/jenis-pembayaran`
- `POST /api/mobile/admin/jenis-pembayaran`
- `PUT /api/mobile/admin/jenis-pembayaran/{id}`
- `DELETE /api/mobile/admin/jenis-pembayaran/{id}`
- `GET /api/mobile/admin/ortu`
- `POST /api/mobile/admin/ortu`
- `PUT /api/mobile/admin/ortu/{id}`
- `DELETE /api/mobile/admin/ortu/{id}`
- `GET /api/mobile/admin/users`
- `POST /api/mobile/admin/users`
- `PUT /api/mobile/admin/users/{id}`
- `DELETE /api/mobile/admin/users/{id}`

Catatan:

- `siswa` perlu dukung upload foto
- `jenis_pembayaran` perlu expose `tipe` dan `periode`

### C. Tagihan API

Endpoint minimum:

- `GET /api/mobile/admin/tagihan`
- `GET /api/mobile/admin/tagihan/{siswaId}/detail`
- `GET /api/mobile/admin/tagihan/{siswaId}/total-belum-lunas`
- `POST /api/mobile/admin/tagihan/generate-spp`
- `POST /api/mobile/admin/tagihan/generate-custom`
- `DELETE /api/mobile/admin/tagihan/generated`
- `GET /api/mobile/admin/tagihan/status-bulan/{tahun}`

Payload generate SPP yang disarankan:

```json
{
  "tahun": 2026,
  "bulan": [1, 2, 3],
  "kelas_id": 2,
  "siswa_id": null,
  "nominal_custom": 250000
}
```

Payload generate custom yang disarankan:

```json
{
  "jenis_pembayaran_id": 5,
  "siswa_id": [10, 12, 14],
  "jatuh_tempo": "2026-07-10",
  "nominal_custom": 150000
}
```

### D. Pembayaran API

Endpoint minimum:

- `GET /api/mobile/admin/pembayaran`
- `POST /api/mobile/admin/pembayaran/tagihan/{id}/bayar`
- `POST /api/mobile/admin/pembayaran/siswa/{id}/multi-bayar`
- `DELETE /api/mobile/admin/pembayaran/{id}`
- `GET /api/mobile/admin/pembayaran/verifikasi`
- `POST /api/mobile/admin/pembayaran/{id}/approve`
- `POST /api/mobile/admin/pembayaran/{id}/reject`

Payload bayar tagihan:

```json
{
  "nominal_bayar": 250000,
  "metode_bayar": "cash",
  "keterangan": "Bayar di loket"
}
```

Payload multi-bayar:

```json
{
  "total_bayar": 750000,
  "metode_bayar": "cash"
}
```

### E. Quick Pay API

Kalau ingin tetap ada mode cepat seperti web:

- `GET /api/mobile/admin/quick-pay?siswa_id=10`
- `POST /api/mobile/admin/quick-pay/tagihan/{id}/bayar`

Secara implementasi, ini bisa diarahkan ke service pembayaran yang sama dengan pembayaran biasa.

### F. Kwitansi API

Endpoint minimum:

- `GET /api/mobile/admin/pembayaran/{id}/kwitansi`

Pilihan response:

- URL file PDF
- stream PDF
- base64 PDF

Rekomendasi:

- backend menghasilkan file atau stream PDF
- mobile menampilkan PDF viewer

### G. Dashboard API

Endpoint minimum:

- `GET /api/mobile/admin/dashboard`

Parameter query:

- `siswa_id`
- `kelas_id`
- `periode_mulai`
- `periode_selesai`
- `status`
- `preset_periode`

Response minimum:

```json
{
  "data": {
    "summary": {
      "total_tagihan": 10000000,
      "total_pembayaran": 7500000,
      "jumlah_siswa_belum_lunas": 35,
      "jumlah_siswa_aktif": 420
    },
    "status_tagihan": {
      "lunas": 120,
      "cicil": 55,
      "belum_bayar": 80
    },
    "chart": {
      "labels": ["Jan 2026", "Feb 2026"],
      "target_tagihan": [2000000, 2500000],
      "realisasi_pembayaran": [1500000, 2100000]
    },
    "progress_per_jenis": []
  }
}
```

### H. Riwayat Siswa API

Endpoint minimum:

- `GET /api/mobile/admin/information/riwayat-siswa`
- `POST /api/mobile/admin/siswa/generate-kenaikan`

Payload kenaikan kelas:

```json
{
  "kelas_baru_id": 4,
  "siswa_ids": [10, 11, 12]
}
```

## Mapping Layar Android

### 1. Login

- email
- password
- simpan token

### 2. Home Dashboard

- KPI cards
- filter
- chart
- shortcut ke tagihan, pembayaran, verifikasi

### 3. Master

- daftar kelas
- daftar siswa
- daftar jenis pembayaran
- daftar orang tua
- daftar user

### 4. Tagihan

- daftar ringkasan tagihan per siswa
- detail tagihan per siswa
- generate SPP
- generate custom
- hapus generated

### 5. Pembayaran

- daftar pembayaran
- bayar tagihan
- multi-bayar
- quick pay
- lihat kwitansi

### 6. Verifikasi

- daftar pembayaran pending
- preview bukti transfer
- approve / reject

### 7. Riwayat dan Laporan

- riwayat siswa
- filter dashboard
- detail histori

## Urutan Implementasi yang Disarankan

### Phase 1: Fondasi API

- aktifkan dan rapikan auth token Sanctum
- buat base response JSON
- buat middleware role admin/petugas untuk mobile
- buat versi API untuk `me`, `login`, `logout`

### Phase 2: Master Data

- API `kelas`
- API `jenis pembayaran`
- API `siswa`
- API `ortu`
- API `user`

### Phase 3: Transaksi Inti

- API list/detail tagihan
- generate SPP
- generate custom
- bayar tagihan
- multi-bayar

### Phase 4: Verifikasi dan Kwitansi

- pending list
- approve/reject
- endpoint kwitansi

### Phase 5: Dashboard dan Information

- dashboard JSON
- riwayat siswa
- kenaikan kelas

### Phase 6: Android Java

- auth module
- dashboard module
- master module
- transaksi module
- verifikasi module
- kwitansi viewer

## Refactor Backend yang Sebaiknya Dilakukan Sebelum API Besar

- pindahkan logika pembayaran ke service class
- pindahkan logika generate tagihan ke service class
- samakan flow bayar di `TagihanController` dan `QuickPayController`
- samakan penyimpanan upload ke satu strategi
- buat `API Resource` untuk format JSON konsisten
- tambahkan validasi role per endpoint

## Risiko yang Perlu Diperhatikan

- flow pembayaran menyentuh nominal dan status, jadi wajib transaksi database
- approve pembayaran pending harus pakai locking agar tidak double proses
- generate tagihan bulanan harus menjaga unique constraint
- delete pembayaran harus menghitung ulang sisa tagihan
- PDF kwitansi untuk mobile harus dipastikan bisa dibuka tanpa view Blade

## Rekomendasi Praktis

Jangan mulai dari semua menu sekaligus.

Urutan tercepat untuk menghasilkan aplikasi yang bisa dipakai:

1. login admin
2. dashboard
3. siswa dan kelas
4. tagihan detail + generate SPP
5. pembayaran + multi-bayar
6. verifikasi pending
7. kwitansi
8. laporan dan riwayat siswa

## Catatan Referensi

Dasar teknis yang disarankan untuk implementasi API mobile:

- Laravel Sanctum untuk token auth mobile
- Laravel API Resources untuk format JSON yang konsisten

Referensi resmi:

- https://laravel.com/docs/11.x/sanctum
- https://laravel.com/docs/11.x/eloquent-resources
