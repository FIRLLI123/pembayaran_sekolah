# PRD Mobile Admin Backend

## 1. Ringkasan Produk

Dokumen ini mendefinisikan kebutuhan produk untuk aplikasi mobile admin pembayaran sekolah yang akan menggunakan backend Laravel pada proyek ini.

PRD ini khusus membahas:

- tujuan produk
- aktor dan role
- ruang lingkup fitur mobile
- kontrak perilaku terhadap backend
- aturan bisnis utama
- alur data dan integrasi API
- kebutuhan non-UI
- acceptance criteria

PRD ini tidak membahas desain UI/UX visual.

## 2. Latar Belakang

Saat ini sistem pembayaran sekolah sudah tersedia dalam bentuk web admin TU. Sistem web mendukung:

- login admin/petugas
- master data siswa, kelas, user, orang tua, jenis pembayaran
- generate tagihan SPP
- generate tagihan custom
- pembayaran per tagihan
- multi-bayar
- verifikasi pembayaran pending
- kwitansi pembayaran
- dashboard ringkasan
- riwayat perpindahan/kenaikan kelas siswa

Target berikutnya adalah menyediakan versi mobile Android native yang memakai backend yang sama agar operasional admin lebih fleksibel dan bisa dilakukan dari perangkat mobile.

## 3. Tujuan

### 3.1 Tujuan Bisnis

- Memungkinkan admin TU dan petugas melakukan operasional utama tanpa harus membuka laptop.
- Mempercepat proses input transaksi dan verifikasi pembayaran.
- Menjaga konsistensi aturan bisnis antara web dan mobile.

### 3.2 Tujuan Produk

- Menyediakan aplikasi mobile admin yang memiliki cakupan fungsi inti sama dengan web admin.
- Menggunakan backend terpusat dari proyek Laravel ini.
- Menjamin output transaksi, status tagihan, dan kwitansi tetap konsisten dengan versi web.

## 4. Aktor Pengguna

### 4.1 Admin

Hak akses:

- seluruh akses master data
- generate tagihan
- pembayaran
- verifikasi pembayaran
- laporan/dashboard
- riwayat siswa
- manajemen user

### 4.2 Petugas

Hak akses minimum:

- login ke aplikasi mobile admin
- akses dashboard
- akses master sesuai kebutuhan operasional
- akses transaksi pembayaran dan tagihan

Catatan:

- implementasi hak detail per menu dapat diperdalam nanti
- saat ini backend mobile menerima role `admin` dan `petugas`

### 4.3 Role yang tidak termasuk scope aplikasi ini

- `ortu` tidak termasuk scope aplikasi mobile admin pada fase ini

## 5. Ruang Lingkup

### 5.1 In Scope

- autentikasi mobile admin berbasis token
- dashboard data
- master kelas
- master siswa
- master jenis pembayaran
- master orang tua
- master user
- list dan detail tagihan
- generate tagihan SPP
- generate tagihan custom
- hapus generated tagihan
- pembayaran per tagihan
- multi-bayar
- list pembayaran
- verifikasi pembayaran pending
- approve/reject pembayaran
- pengambilan kwitansi
- riwayat siswa
- generate kenaikan kelas

### 5.2 Out of Scope

- desain UI
- pengaturan tema dan komponen visual
- fitur push notification
- aplikasi mobile untuk orang tua
- offline mode
- payment gateway
- chat / komunikasi di dalam aplikasi

## 6. Prinsip Produk

- Logic transaksi mobile harus mengikuti aturan bisnis yang sama dengan web.
- Data sumber tetap berasal dari backend Laravel ini.
- Mobile hanya menjadi client aplikasi, bukan tempat logika inti bisnis.
- Semua operasi penting harus melalui API terautentikasi.
- Respons API harus konsisten dan mudah dikonsumsi tim Android.

## 7. Kebutuhan Fungsional

## 7.1 Autentikasi

### Deskripsi

Pengguna admin/petugas dapat login ke aplikasi mobile menggunakan email dan password.

### Kebutuhan

- login menghasilkan token bearer
- token dipakai untuk semua request berikutnya
- hanya role `admin` dan `petugas` yang boleh login
- user dapat logout
- aplikasi dapat mengambil data profil user aktif

### Endpoint terkait

- `POST /api/mobile/admin/login`
- `GET /api/mobile/admin/me`
- `POST /api/mobile/admin/logout`

### Acceptance Criteria

- jika email/password benar dan role valid, login berhasil
- jika email/password salah, login ditolak
- jika role bukan admin/petugas, login ditolak

## 7.2 Dashboard

### Deskripsi

Pengguna dapat melihat data ringkasan operasional dari dashboard web dalam format mobile.

### Kebutuhan

- tampilkan total tagihan bulan ini
- tampilkan total pembayaran masuk
- tampilkan jumlah siswa belum lunas
- tampilkan jumlah siswa aktif
- tampilkan chart target vs realisasi pembayaran
- tampilkan status tagihan
- tampilkan progress pembayaran per jenis
- dukung filter siswa, kelas, periode, status

### Endpoint terkait

- `GET /api/mobile/admin/dashboard`

### Acceptance Criteria

- data dashboard mengikuti filter request
- hasil dashboard konsisten dengan perhitungan backend web

## 7.3 Master Kelas

### Deskripsi

Pengguna dapat mengelola data kelas.

### Kebutuhan

- lihat daftar kelas
- cari data kelas
- tambah kelas
- edit kelas
- hapus kelas
- lihat jumlah siswa per kelas

### Endpoint terkait

- `GET /api/mobile/admin/kelas`
- `POST /api/mobile/admin/kelas`
- `GET /api/mobile/admin/kelas/{id}`
- `PUT/PATCH /api/mobile/admin/kelas/{id}`
- `DELETE /api/mobile/admin/kelas/{id}`

## 7.4 Master Siswa

### Deskripsi

Pengguna dapat mengelola data siswa beserta foto dan relasi kelas.

### Kebutuhan

- lihat daftar siswa
- filter per kelas
- cari berdasarkan nama atau NIS
- tambah siswa
- edit siswa
- hapus siswa
- upload foto siswa

### Endpoint terkait

- `GET /api/mobile/admin/siswa`
- `POST /api/mobile/admin/siswa`
- `GET /api/mobile/admin/siswa/{id}`
- `PUT/PATCH /api/mobile/admin/siswa/{id}`
- `DELETE /api/mobile/admin/siswa/{id}`

### Acceptance Criteria

- foto siswa tersimpan dan URL foto dapat diakses
- data siswa memuat relasi kelas

## 7.5 Master Jenis Pembayaran

### Deskripsi

Pengguna dapat mengelola jenis pembayaran yang dipakai oleh tagihan dan pembayaran.

### Kebutuhan

- lihat daftar jenis pembayaran
- filter per tipe
- tambah jenis pembayaran
- edit jenis pembayaran
- hapus jenis pembayaran
- data harus mendukung `tipe` dan `periode`

### Catatan bisnis

- `tipe = rutin` digunakan untuk tagihan terjadwal
- `periode = bulanan` penting untuk generate SPP
- `tipe = insidental` digunakan untuk tagihan custom/non-rutin

### Endpoint terkait

- `GET /api/mobile/admin/jenis-pembayaran`
- `POST /api/mobile/admin/jenis-pembayaran`
- `GET /api/mobile/admin/jenis-pembayaran/{id}`
- `PUT/PATCH /api/mobile/admin/jenis-pembayaran/{id}`
- `DELETE /api/mobile/admin/jenis-pembayaran/{id}`

## 7.6 Master Orang Tua

### Deskripsi

Pengguna dapat mengelola data orang tua/wali.

### Kebutuhan

- lihat daftar orang tua
- cari data
- tambah data
- edit data
- hapus data

### Endpoint terkait

- `GET /api/mobile/admin/ortu`
- `POST /api/mobile/admin/ortu`
- `GET /api/mobile/admin/ortu/{id}`
- `PUT/PATCH /api/mobile/admin/ortu/{id}`
- `DELETE /api/mobile/admin/ortu/{id}`

## 7.7 Master User

### Deskripsi

Pengguna admin dapat mengelola akun user sistem.

### Kebutuhan

- lihat daftar user
- filter by role
- tambah user
- edit user
- reset/update password melalui edit
- hapus user
- dukung relasi ke siswa dan ortu

### Endpoint terkait

- `GET /api/mobile/admin/users`
- `POST /api/mobile/admin/users`
- `GET /api/mobile/admin/users/{id}`
- `PUT/PATCH /api/mobile/admin/users/{id}`
- `DELETE /api/mobile/admin/users/{id}`

## 7.8 Tagihan

### Deskripsi

Pengguna dapat melihat ringkasan tagihan per siswa dan detail tagihan per siswa.

### Kebutuhan

- lihat list ringkasan tagihan per siswa
- filter berdasarkan siswa dan kelas
- lihat detail tagihan siswa
- lihat total belum lunas per siswa
- lihat status generate bulanan

### Endpoint terkait

- `GET /api/mobile/admin/tagihan`
- `GET /api/mobile/admin/tagihan/{siswa}/detail`
- `GET /api/mobile/admin/tagihan/{siswa}/total-belum-lunas`
- `GET /api/mobile/admin/tagihan/status-bulan/{tahun}`

## 7.9 Generate Tagihan SPP

### Deskripsi

Pengguna dapat meng-generate tagihan SPP rutin berdasarkan bulan dan tahun.

### Kebutuhan

- pilih satu atau banyak bulan
- pilih tahun
- boleh difilter per kelas
- boleh difilter per siswa
- nominal bisa pakai default atau custom
- sistem tidak boleh menduplikasi tagihan yang sudah ada

### Endpoint terkait

- `POST /api/mobile/admin/tagihan/generate-spp`

### Aturan bisnis

- hanya jenis pembayaran `rutin` dan `bulanan` yang dipakai
- jika data siswa hasil filter kosong, proses ditolak
- bulan yang sudah pernah digenerate harus dilewati

## 7.10 Generate Tagihan Custom

### Deskripsi

Pengguna dapat membuat tagihan custom/non-rutin ke satu atau banyak siswa.

### Kebutuhan

- pilih jenis pembayaran
- pilih banyak siswa
- set jatuh tempo
- set nominal custom opsional
- deteksi duplikasi pada periode berjalan
- dukung flag konfirmasi paksa (`force`) bila user ingin lanjut

### Endpoint terkait

- `POST /api/mobile/admin/tagihan/generate-custom`

## 7.11 Hapus Generated Tagihan

### Deskripsi

Pengguna dapat menghapus hasil generate tagihan berdasarkan filter.

### Kebutuhan

- filter by bulan
- filter by tahun
- filter by kelas
- filter by siswa
- filter by jenis pembayaran

### Aturan bisnis

- penghapusan tidak boleh dilakukan jika sudah ada pembayaran pada tagihan terkait

### Endpoint terkait

- `DELETE /api/mobile/admin/tagihan/generated`

## 7.12 Pembayaran Per Tagihan

### Deskripsi

Pengguna dapat memproses pembayaran terhadap satu tagihan tertentu.

### Kebutuhan

- pilih nominal bayar
- pilih metode bayar
- isi keterangan opsional
- upload bukti opsional
- backend menghitung status `lunas` atau `cicil`

### Aturan bisnis

- nominal bayar tidak boleh melebihi sisa tagihan
- tagihan harus diperbarui setelah pembayaran valid dibuat

### Endpoint terkait

- `POST /api/mobile/admin/pembayaran/tagihan/{tagihan}/bayar`

## 7.13 Multi-Bayar

### Deskripsi

Pengguna dapat membayar banyak tagihan siswa sekaligus dengan satu total pembayaran.

### Kebutuhan

- pilih siswa
- masukkan total bayar
- pilih metode bayar
- backend membagi nominal ke tagihan belum lunas berdasarkan urutan periode

### Aturan bisnis

- alokasi dimulai dari tagihan tertua
- jika dana cukup untuk menutup tagihan, status menjadi `lunas`
- jika dana tidak cukup, status tagihan terakhir menjadi `cicil`

### Endpoint terkait

- `POST /api/mobile/admin/pembayaran/siswa/{siswa}/multi-bayar`

## 7.14 List Pembayaran

### Deskripsi

Pengguna dapat melihat daftar pembayaran yang sudah tercatat.

### Kebutuhan

- filter by siswa
- filter by kelas
- filter by status
- filter by metode bayar
- filter by tanggal mulai dan selesai

### Endpoint terkait

- `GET /api/mobile/admin/pembayaran`

## 7.15 Verifikasi Pembayaran Pending

### Deskripsi

Pengguna admin dapat memverifikasi pembayaran dengan status `pending`.

### Kebutuhan

- lihat daftar pembayaran pending
- cari berdasarkan nama siswa, NIS, atau jenis pembayaran
- filter tanggal
- lihat bukti upload
- approve pembayaran
- reject pembayaran

### Endpoint terkait

- `GET /api/mobile/admin/pembayaran/verifikasi`
- `POST /api/mobile/admin/pembayaran/{pembayaran}/approve`
- `POST /api/mobile/admin/pembayaran/{pembayaran}/reject`

### Aturan bisnis

- hanya pembayaran `pending` yang bisa diproses
- approve akan mengurangi sisa tagihan
- reject tidak mengurangi sisa tagihan
- alasan reject bersifat opsional

## 7.16 Hapus Pembayaran

### Deskripsi

Pengguna admin dapat menghapus data pembayaran.

### Kebutuhan

- hapus satu pembayaran
- backend harus menghitung ulang status dan sisa tagihan jika pembayaran yang dihapus sebelumnya valid

### Endpoint terkait

- `DELETE /api/mobile/admin/pembayaran/{pembayaran}`

## 7.17 Kwitansi

### Deskripsi

Pengguna dapat meminta kwitansi pembayaran dari backend.

### Kebutuhan

- kwitansi hanya tersedia untuk pembayaran valid
- backend mengembalikan file PDF dalam bentuk base64
- mobile akan memutuskan cara preview/download

### Endpoint terkait

- `GET /api/mobile/admin/pembayaran/{pembayaran}/kwitansi`

### Aturan bisnis

- kwitansi hanya untuk status `lunas` atau `cicil`

## 7.18 Riwayat Siswa

### Deskripsi

Pengguna dapat melihat riwayat perpindahan atau kenaikan kelas siswa.

### Kebutuhan

- lihat daftar riwayat
- cari berdasarkan nama siswa atau NIS
- tampilkan kelas lama dan kelas baru

### Endpoint terkait

- `GET /api/mobile/admin/information/riwayat-siswa`

## 7.19 Generate Kenaikan Kelas

### Deskripsi

Pengguna dapat memproses kenaikan kelas untuk banyak siswa sekaligus.

### Kebutuhan

- pilih kelas baru
- pilih banyak siswa
- catat riwayat kelas
- update kelas siswa aktif

### Endpoint terkait

- `POST /api/mobile/admin/siswa/generate-kenaikan`

### Aturan bisnis

- siswa yang sudah berada di kelas tujuan tidak ikut diproses
- jika semua siswa sudah ada di kelas tujuan, request ditolak

## 8. Aturan Bisnis Umum

- Semua request selain login harus memakai bearer token.
- Role yang diterima backend mobile adalah `admin` dan `petugas`.
- Semua transaksi penting harus menggunakan database transaction.
- Status tagihan yang valid:
  - `belum_bayar`
  - `cicil`
  - `lunas`
- Status pembayaran yang valid:
  - `pending`
  - `ditolak`
  - `cicil`
  - `lunas`
- Perhitungan nominal harus dilakukan di backend.
- Mobile tidak boleh menghitung status tagihan sendiri.

## 9. Integrasi Backend

### Base Path

`/api/mobile/admin`

### Format Header

- `Accept: application/json`
- `Authorization: Bearer {token}` untuk endpoint terproteksi

### Format Umum Response

Respon sukses:

```json
{
  "message": "Pesan sukses",
  "data": {}
}
```

Respon list umumnya:

```json
{
  "message": "Pesan sukses",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 3
  }
}
```

Respon error umum:

```json
{
  "message": "Pesan error"
}
```

## 10. Error Handling yang Harus Diantisipasi Tim Mobile

### 401 Unauthenticated

Terjadi jika:

- token tidak dikirim
- token tidak valid
- token sudah logout

### 403 Forbidden

Terjadi jika:

- role user tidak diizinkan mengakses aplikasi mobile admin

### 409 Conflict

Digunakan pada generate tagihan custom jika ditemukan duplikasi dan backend meminta konfirmasi sebelum force generate.

### 422 Validation / Business Rule Error

Contoh:

- nominal bayar melebihi sisa tagihan
- semua siswa sudah di kelas tujuan
- data hasil filter kosong
- pembayaran pending sudah diproses
- kwitansi diminta untuk status invalid

### 500 Server Error

Harus dianggap sebagai kegagalan backend dan ditampilkan sebagai error umum.

## 11. Non-Functional Requirements

### 11.1 Keamanan

- token harus disimpan aman di sisi mobile
- endpoint mobile admin wajib token-based auth
- operasi sensitif harus dibatasi by role

### 11.2 Konsistensi Data

- transaksi pembayaran tidak boleh menyebabkan sisa tagihan negatif
- generate tagihan tidak boleh menggandakan tagihan rutin yang sama
- hapus pembayaran harus menghitung ulang tagihan

### 11.3 Performa

- list data utama harus mendukung pagination
- filter harus dilakukan di backend

### 11.4 Kompatibilitas

- response harus mudah dikonsumsi Android native Java
- format harus stabil antar endpoint

## 12. Ketergantungan

- backend Laravel proyek ini
- database existing
- Laravel Sanctum untuk token auth
- DomPDF untuk kwitansi

## 13. Risiko

- Mismatch environment PHP lokal dan requirement composer dapat memicu perilaku berbeda antar environment.
- Relasi `ortu`, `siswa`, dan `user` masih perlu disiplin penggunaan data agar tidak ambigu.
- Upload file berbeda lokasi penyimpanan antara beberapa flow lama web dan flow API baru.
- Logic web lama dan API baru harus tetap dijaga sinkron saat ada perubahan bisnis berikutnya.

## 14. Acceptance Criteria Utama

Backend dianggap siap untuk dipakai tim mobile jika:

1. Admin/petugas bisa login dan mendapatkan token.
2. Tim mobile bisa mengambil dashboard.
3. Tim mobile bisa CRUD master utama:
   - kelas
   - siswa
   - jenis pembayaran
   - ortu
   - users
4. Tim mobile bisa melihat dan memproses tagihan:
   - list
   - detail
   - generate SPP
   - generate custom
   - hapus generated
5. Tim mobile bisa memproses pembayaran:
   - bayar per tagihan
   - multi-bayar
   - list pembayaran
   - verifikasi pending
   - approve/reject
   - hapus pembayaran
   - minta kwitansi
6. Tim mobile bisa melihat riwayat siswa dan memproses kenaikan kelas.
7. Semua endpoint mengembalikan JSON yang konsisten.

## 15. Rekomendasi Fase Implementasi Tim Mobile

### Fase 1

- login
- simpan token
- dashboard

### Fase 2

- kelas
- siswa
- jenis pembayaran

### Fase 3

- tagihan
- generate SPP
- generate custom

### Fase 4

- pembayaran
- multi-bayar
- verifikasi
- kwitansi

### Fase 5

- ortu
- users
- riwayat siswa
- kenaikan kelas

## 16. Lampiran Endpoint Group

### Auth

- `POST /api/mobile/admin/login`
- `GET /api/mobile/admin/me`
- `POST /api/mobile/admin/logout`

### Dashboard

- `GET /api/mobile/admin/dashboard`

### Master

- `GET|POST|PUT|DELETE /api/mobile/admin/kelas`
- `GET|POST|PUT|DELETE /api/mobile/admin/siswa`
- `GET|POST|PUT|DELETE /api/mobile/admin/jenis-pembayaran`
- `GET|POST|PUT|DELETE /api/mobile/admin/ortu`
- `GET|POST|PUT|DELETE /api/mobile/admin/users`

### Tagihan

- `GET /api/mobile/admin/tagihan`
- `GET /api/mobile/admin/tagihan/{siswa}/detail`
- `GET /api/mobile/admin/tagihan/{siswa}/total-belum-lunas`
- `GET /api/mobile/admin/tagihan/status-bulan/{tahun}`
- `POST /api/mobile/admin/tagihan/generate-spp`
- `POST /api/mobile/admin/tagihan/generate-custom`
- `DELETE /api/mobile/admin/tagihan/generated`

### Pembayaran

- `GET /api/mobile/admin/pembayaran`
- `POST /api/mobile/admin/pembayaran/tagihan/{tagihan}/bayar`
- `POST /api/mobile/admin/pembayaran/siswa/{siswa}/multi-bayar`
- `GET /api/mobile/admin/pembayaran/verifikasi`
- `POST /api/mobile/admin/pembayaran/{pembayaran}/approve`
- `POST /api/mobile/admin/pembayaran/{pembayaran}/reject`
- `DELETE /api/mobile/admin/pembayaran/{pembayaran}`
- `GET /api/mobile/admin/pembayaran/{pembayaran}/kwitansi`

### Information

- `GET /api/mobile/admin/information/riwayat-siswa`
- `POST /api/mobile/admin/siswa/generate-kenaikan`
