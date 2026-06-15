# PRD Menu Generate Tagihan Mobile

## 1. Tujuan

Dokumen ini menjelaskan kebutuhan produk untuk 2 menu mobile admin:

- Generate Tagihan SPP
- Generate Tagihan Custom

Dokumen ini fokus pada:

- tujuan fitur
- alur operasional
- kebutuhan data
- integrasi API backend
- aturan bisnis
- acceptance criteria

Dokumen ini tidak membahas desain UI visual.

## 2. Status Backend

Fitur backend untuk kedua menu ini sudah tersedia.

### Endpoint Generate SPP

- `POST /api/mobile/admin/tagihan/generate-spp`

Endpoint terdaftar di:

- [routes/api.php](/c:/laragon/www/pembayaran-sekolah/routes/api.php:61)
- [TagihanController.php](/c:/laragon/www/pembayaran-sekolah/app/Http/Controllers/Api/Mobile/Admin/TagihanController.php:136)
- [TagihanService.php](/c:/laragon/www/pembayaran-sekolah/app/Services/TagihanService.php:15)

### Endpoint Generate Custom

- `POST /api/mobile/admin/tagihan/generate-custom`

Endpoint terdaftar di:

- [routes/api.php](/c:/laragon/www/pembayaran-sekolah/routes/api.php:62)
- [TagihanController.php](/c:/laragon/www/pembayaran-sekolah/app/Http/Controllers/Api/Mobile/Admin/TagihanController.php:161)
- [TagihanService.php](/c:/laragon/www/pembayaran-sekolah/app/Services/TagihanService.php:94)

## 3. Latar Belakang

Pada sistem web, admin TU dapat membuat tagihan dalam dua bentuk:

- tagihan rutin SPP berdasarkan bulan dan tahun
- tagihan custom untuk kebutuhan insidental/non-rutin

Fungsi ini perlu tersedia di mobile agar admin bisa melakukan proses generate tagihan tanpa harus membuka versi web.

## 4. Aktor

### Admin

- dapat mengakses semua fitur generate tagihan

### Petugas

- dapat mengakses fitur generate jika kebijakan sekolah mengizinkan
- kontrol akhir tetap mengikuti role backend yang diizinkan

## 5. Ruang Lingkup

### In Scope

- form generate tagihan SPP
- form generate tagihan custom
- validasi input dasar di mobile
- kirim request ke backend
- tampilkan hasil sukses / warning / gagal
- tampilkan daftar bulan yang berhasil dibuat
- tampilkan daftar siswa duplikat untuk generate custom

### Out of Scope

- desain UI visual
- approval berjenjang
- penjadwalan otomatis
- notifikasi ke orang tua

## 6. Fitur 1: Generate Tagihan SPP

## 6.1 Tujuan Fitur

Memungkinkan admin membuat tagihan SPP rutin untuk satu atau banyak bulan sekaligus, dengan cakupan seluruh siswa, per kelas, atau per siswa tertentu.

## 6.2 Kebutuhan Fungsional

User dapat:

- memilih tahun tagihan
- memilih satu atau banyak bulan
- memilih kelas tertentu atau semua kelas
- memilih siswa tertentu atau semua siswa
- mengisi nominal custom opsional
- submit generate
- menerima hasil proses dari backend

## 6.3 Input yang Dibutuhkan

- `tahun`
- `bulan[]`
- `kelas_id` opsional
- `siswa_id` opsional
- `nominal_custom` opsional

## 6.4 Aturan Bisnis

- generate SPP hanya menggunakan jenis pembayaran dengan:
  - `tipe = rutin`
  - `periode = bulanan`
- jika `kelas_id` diisi, generate hanya berlaku untuk siswa di kelas itu
- jika `siswa_id` diisi, generate hanya berlaku untuk siswa itu
- jika `nominal_custom` diisi, nominal tagihan memakai nilai custom
- jika `nominal_custom` kosong, nominal memakai `nominal_default` dari jenis pembayaran
- sistem tidak boleh membuat duplikasi tagihan rutin pada:
  - siswa yang sama
  - jenis pembayaran yang sama
  - bulan yang sama
  - tahun yang sama
- jika semua data bulan yang dipilih sudah pernah digenerate, backend tetap merespons sukses proses namun hasilnya menunjukkan tidak ada data baru
- jika sebagian bulan sudah ada, backend membuat yang belum ada dan mengembalikan daftar bulan yang dilewati

## 6.5 Endpoint

`POST /api/mobile/admin/tagihan/generate-spp`

Header:

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Contoh body:

```json
{
  "tahun": 2026,
  "bulan": [7, 8, 9],
  "kelas_id": 2,
  "siswa_id": null,
  "nominal_custom": 250000
}
```

Contoh response sukses:

```json
{
  "message": "Generate tagihan SPP selesai diproses.",
  "data": {
    "berhasil": ["Juli", "Agustus", "September"],
    "sudah_ada": [],
    "tahun": 2026
  }
}
```

Contoh response sukses sebagian:

```json
{
  "message": "Generate tagihan SPP selesai diproses.",
  "data": {
    "berhasil": ["Agustus"],
    "sudah_ada": ["Juli", "September"],
    "tahun": 2026
  }
}
```

Contoh response gagal validasi/bisnis:

```json
{
  "message": "Tidak ada siswa sesuai filter kelas/siswa yang dipilih."
}
```

## 6.6 Perilaku Mobile yang Diharapkan

- sebelum submit, mobile memastikan minimal 1 bulan dipilih
- mobile mengirim JSON body sesuai input
- jika response sukses:
  - tampilkan pesan sukses
  - tampilkan bulan yang berhasil digenerate
  - jika ada `sudah_ada`, tampilkan sebagai informasi
- jika response `422`, tampilkan pesan error dari backend

## 6.7 Acceptance Criteria

1. User bisa memilih lebih dari satu bulan.
2. User bisa generate per kelas.
3. User bisa generate per siswa.
4. Mobile menerima dan menampilkan daftar bulan `berhasil`.
5. Mobile menerima dan menampilkan daftar bulan `sudah_ada`.
6. Jika data siswa kosong berdasarkan filter, mobile menampilkan error backend.

## 7. Fitur 2: Generate Tagihan Custom

## 7.1 Tujuan Fitur

Memungkinkan admin membuat tagihan non-rutin untuk satu atau banyak siswa berdasarkan jenis pembayaran tertentu.

## 7.2 Kebutuhan Fungsional

User dapat:

- memilih jenis pembayaran
- memilih satu atau banyak siswa
- mengisi jatuh tempo opsional
- mengisi nominal custom opsional
- submit generate
- menerima warning jika ada duplikasi
- melanjutkan generate dengan konfirmasi paksa jika diperlukan

## 7.3 Input yang Dibutuhkan

- `jenis_pembayaran_id`
- `siswa_id[]`
- `jatuh_tempo` opsional
- `nominal_custom` opsional
- `force` opsional

## 7.4 Aturan Bisnis

- generate custom dipakai untuk tagihan non-rutin/insidental
- sistem akan memeriksa apakah siswa yang dipilih sudah punya tagihan dengan:
  - jenis pembayaran sama
  - bulan berjalan sama
  - tahun berjalan sama
- jika ditemukan duplikasi dan `force = false`, backend tidak langsung membuat data, tetapi mengembalikan warning
- jika user setuju lanjut, mobile mengirim ulang request dengan `force = true`
- jika `nominal_custom` kosong, nominal menggunakan `nominal_default`
- jika `jatuh_tempo` kosong, backend memakai default `now + 7 hari`

## 7.5 Endpoint

`POST /api/mobile/admin/tagihan/generate-custom`

Header:

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Contoh body awal:

```json
{
  "jenis_pembayaran_id": 5,
  "siswa_id": [10, 11, 15],
  "jatuh_tempo": "2026-07-20",
  "nominal_custom": 150000,
  "force": false
}
```

Contoh response sukses:

```json
{
  "message": "Tagihan custom berhasil dibuat.",
  "data": {
    "requires_confirmation": false,
    "message": "Tagihan custom berhasil dibuat."
  }
}
```

Contoh response warning duplicate:

```json
{
  "message": "Beberapa siswa sudah memiliki tagihan ini di bulan ini.",
  "data": {
    "requires_confirmation": true,
    "message": "Beberapa siswa sudah memiliki tagihan ini di bulan ini.",
    "duplicates": ["Ahmad Fauzi", "Budi Santoso"]
  }
}
```

Contoh body konfirmasi lanjut:

```json
{
  "jenis_pembayaran_id": 5,
  "siswa_id": [10, 11, 15],
  "jatuh_tempo": "2026-07-20",
  "nominal_custom": 150000,
  "force": true
}
```

## 7.6 Perilaku Mobile yang Diharapkan

- mobile harus mendukung multi select siswa
- mobile harus menampilkan warning duplicate dari backend
- jika `requires_confirmation = true`, mobile menampilkan konfirmasi ke user
- jika user setuju, mobile mengirim request ulang dengan `force = true`
- jika user batal, tidak ada request lanjutan
- jika response sukses, tampilkan pesan berhasil

## 7.7 Acceptance Criteria

1. User dapat memilih lebih dari satu siswa.
2. User dapat mengirim nominal custom opsional.
3. Jika backend mengembalikan duplicate warning, mobile bisa menampilkan nama siswa duplicate.
4. User bisa melanjutkan generate dengan `force = true`.
5. Jika generate sukses, mobile menampilkan pesan berhasil.

## 8. Data Dependensi untuk Tim Mobile

Sebelum menu generate bisa dipakai dengan baik, mobile memerlukan data berikut:

### Untuk Generate SPP

- daftar kelas
- daftar siswa
- daftar jenis pembayaran rutin bulanan bila ingin ditampilkan untuk pengecekan/informasi

Endpoint pendukung:

- `GET /api/mobile/admin/kelas`
- `GET /api/mobile/admin/siswa`
- `GET /api/mobile/admin/jenis-pembayaran?tipe=rutin`

### Untuk Generate Custom

- daftar jenis pembayaran
- daftar siswa

Endpoint pendukung:

- `GET /api/mobile/admin/jenis-pembayaran`
- `GET /api/mobile/admin/siswa`

## 9. Error Handling

### 401

Token tidak valid / tidak ada.

### 403

Role user tidak diizinkan.

### 409

Khusus generate custom saat duplicate ditemukan dan backend meminta konfirmasi.

### 422

Contoh:

- bulan kosong
- tahun kosong
- siswa tidak ditemukan
- jenis pembayaran tidak tersedia
- filter siswa kosong

### 500

Error internal backend.

## 10. Logging dan Monitoring yang Disarankan

Untuk implementasi mobile:

- log request generate SPP
- log request generate custom
- log response duplicate warning
- log response gagal bisnis

Tujuannya agar saat ada komplain “tagihan tidak muncul” tim bisa telusuri request yang dikirim dari mobile.

## 11. Acceptance Criteria Umum

Menu generate tagihan mobile dianggap siap jika:

1. User bisa membuka data dependensi untuk form.
2. User bisa submit generate SPP.
3. User bisa submit generate custom.
4. Mobile bisa menangani response sukses penuh.
5. Mobile bisa menangani response sukses sebagian.
6. Mobile bisa menangani response duplicate warning pada generate custom.
7. Mobile bisa kirim ulang request dengan `force = true`.
8. Pesan error backend tampil jelas ke user.

## 12. Catatan Implementasi untuk Tim Mobile

- Untuk generate SPP, komponen pemilihan bulan harus mendukung multi select.
- Untuk generate custom, komponen pemilihan siswa harus mendukung multi select.
- Jika tidak ada `kelas_id` dan `siswa_id`, backend akan memproses seluruh siswa sesuai logic backend.
- Mobile tidak perlu menghitung duplikasi sendiri.
- Mobile cukup mengikuti response backend dan menampilkan hasilnya.

## 13. Ringkasan Endpoint

### Generate SPP

- `POST /api/mobile/admin/tagihan/generate-spp`

### Generate Custom

- `POST /api/mobile/admin/tagihan/generate-custom`

### Data Pendukung

- `GET /api/mobile/admin/kelas`
- `GET /api/mobile/admin/siswa`
- `GET /api/mobile/admin/jenis-pembayaran`
