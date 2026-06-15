# PRD Laporan Export Excel Mobile

## 1. Tujuan

Dokumen ini menjelaskan kebutuhan produk untuk fitur export laporan Excel dari aplikasi mobile admin pembayaran sekolah.

PRD ini mencakup:

- tujuan fitur
- jenis laporan yang tersedia
- struktur sheet Excel
- kolom per sheet
- filter laporan
- flow export dan download
- strategi performa agar aman dan tidak lemot
- acceptance criteria

Dokumen ini tidak membahas desain UI visual.

## 2. Latar Belakang

Client membutuhkan fitur export laporan keuangan dari mobile agar admin TU dapat:

- melihat data keuangan secara lengkap
- mengirim laporan ke atasan / bendahara / yayasan
- membuka file hasil export di laptop atau WhatsApp
- tetap mendapatkan data yang sama seperti sistem web

Karena laporan bisa besar, fitur export harus tetap:

- lengkap
- rapi
- mudah dipahami
- tidak memberatkan mobile
- tidak memberatkan backend

## 3. Prinsip Produk

- Export dilakukan oleh backend, bukan dirakit di mobile.
- Mobile hanya mengirim filter, menerima file, lalu mengunduh/preview.
- File Excel harus dibagi ke beberapa sheet agar mudah dibaca.
- User wajib memfilter periode agar file tetap masuk akal.
- Sistem harus aman untuk dataset besar dan tidak membuat aplikasi terasa lemot.

## 4. Tujuan Bisnis

- Menyediakan laporan keuangan yang lengkap untuk kebutuhan operasional dan audit ringan.
- Mengurangi ketergantungan pada web/laptop untuk mengambil laporan.
- Menyediakan format yang siap dibuka di Excel tanpa olah ulang manual.

## 5. Aktor

### Admin

- dapat mengekspor semua jenis laporan

### Petugas

- dapat mengekspor laporan sesuai hak akses operasional yang diberikan

## 6. Ruang Lingkup

### In Scope

- export Excel dari mobile
- satu file dengan banyak sheet
- filter laporan
- download file hasil export
- format data keuangan dan operasional pembayaran

### Out of Scope

- edit isi file Excel dari mobile
- export PDF laporan
- grafik visual di file Excel
- auto email report
- scheduled report

## 7. Bentuk Output yang Direkomendasikan

### Format Utama

1 file Excel dengan beberapa sheet.

Nama file contoh:

`laporan-keuangan-sekolah-2026-06-01_sd_2026-06-30.xlsx`

### Alasan memilih multi-sheet

- client merasa laporan “lengkap”
- tetap rapi dan terstruktur
- tidak membuat satu sheet terlalu panjang
- lebih mudah dipakai di Excel desktop
- lebih aman untuk analisis manual

## 8. Struktur Laporan yang Direkomendasikan

Rekomendasi 1 file berisi sheet berikut:

1. `Summary`
2. `Tagihan`
3. `Pembayaran`
4. `Tunggakan`
5. `Rekap Jenis`
6. `Rekap Kelas`
7. `Riwayat Siswa` opsional

## 9. Deskripsi Per Sheet

## 9.1 Sheet `Summary`

### Tujuan

Memberi ringkasan cepat kondisi keuangan pada periode terpilih.

### Kolom / Isi

- nama sekolah
- periode laporan
- tanggal export
- user yang export
- total siswa aktif
- total tagihan
- total pembayaran masuk
- total sisa tagihan
- total tagihan lunas
- total tagihan cicil
- total tagihan belum bayar
- jumlah siswa menunggak
- jumlah transaksi pembayaran

### Catatan

Sheet ini bukan data detail, tetapi ringkasan angka utama.

## 9.2 Sheet `Tagihan`

### Tujuan

Menampilkan seluruh data tagihan yang masuk dalam filter laporan.

### Kolom

- No
- ID Tagihan
- Tanggal Tagihan
- Jatuh Tempo
- Tahun Periode
- Bulan Periode
- NIS
- Nama Siswa
- Kelas
- Jenis Pembayaran
- Tipe Pembayaran
- Periode Pembayaran
- Nominal Tagihan
- Sisa Tagihan
- Status Tagihan
- Keterangan
- Dibuat Oleh
- Diupdate Oleh

### Kegunaan

- audit tagihan
- cek generated tagihan
- pelacakan tagihan per siswa / kelas / jenis

## 9.3 Sheet `Pembayaran`

### Tujuan

Menampilkan seluruh transaksi pembayaran yang masuk dalam filter laporan.

### Kolom

- No
- ID Pembayaran
- Tanggal Bayar
- ID Tagihan
- NIS
- Nama Siswa
- Kelas
- Jenis Pembayaran
- Tahun Periode Tagihan
- Bulan Periode Tagihan
- Nominal Bayar
- Metode Bayar
- Status Pembayaran
- Keterangan
- File Bukti Upload
- Dibuat Oleh
- Diupdate Oleh

### Kegunaan

- rekap kas masuk
- audit pembayaran
- pencocokan transaksi harian/bulanan

## 9.4 Sheet `Tunggakan`

### Tujuan

Menampilkan seluruh tagihan yang belum lunas.

### Kolom

- No
- ID Tagihan
- NIS
- Nama Siswa
- Kelas
- Jenis Pembayaran
- Tahun Periode
- Bulan Periode
- Tanggal Tagihan
- Jatuh Tempo
- Nominal Tagihan
- Total Sudah Dibayar
- Sisa Tagihan
- Status Tagihan
- Umur Tunggakan (hari)

### Kegunaan

- penagihan
- monitoring piutang
- prioritas follow-up wali murid

## 9.5 Sheet `Rekap Jenis`

### Tujuan

Menampilkan rekap keuangan berdasarkan jenis pembayaran.

### Kolom

- No
- Jenis Pembayaran
- Tipe
- Periode
- Jumlah Tagihan
- Total Nominal Tagihan
- Total Pembayaran Masuk
- Total Sisa Tagihan
- Jumlah Tagihan Lunas
- Jumlah Tagihan Cicil
- Jumlah Tagihan Belum Bayar
- Persentase Realisasi

### Kegunaan

- analisis pendapatan per jenis pembayaran
- melihat jenis pembayaran yang paling aktif / paling macet

## 9.6 Sheet `Rekap Kelas`

### Tujuan

Menampilkan ringkasan keuangan per kelas.

### Kolom

- No
- Kelas
- Tahun Ajaran
- Jumlah Siswa
- Total Nominal Tagihan
- Total Pembayaran Masuk
- Total Sisa Tagihan
- Jumlah Siswa Lunas
- Jumlah Siswa Belum Lunas
- Persentase Pelunasan

### Kegunaan

- evaluasi performa pembayaran per kelas
- cocok untuk kepala sekolah / wali kelas / TU

## 9.7 Sheet `Riwayat Siswa` Opsional

### Tujuan

Menampilkan histori perpindahan atau kenaikan kelas untuk konteks data siswa.

### Kolom

- No
- ID Riwayat
- Tanggal Pindah
- NIS
- Nama Siswa
- Kelas Lama
- Kelas Baru
- Dicatat Oleh

### Catatan

Sheet ini opsional. Bisa dimasukkan bila client memang ingin file benar-benar lengkap, atau dibuat sebagai export terpisah bila ukuran file ingin dijaga.

## 10. Filter Laporan

## 10.1 Filter Wajib

- `periode_mulai`
- `periode_selesai`

### Alasan

Filter periode wajib agar:

- file tidak terlalu besar
- query lebih cepat
- mobile tidak terasa berat
- user fokus ke laporan yang dibutuhkan

## 10.2 Filter Opsional

- `kelas_id`
- `siswa_id`
- `jenis_pembayaran_id`
- `status_tagihan`
- `status_pembayaran`
- `metode_bayar`
- `include_riwayat_siswa` boolean

## 10.3 Aturan Filter

- jika `siswa_id` diisi, laporan fokus ke siswa itu
- jika `kelas_id` diisi, data dibatasi ke kelas itu
- jika `jenis_pembayaran_id` diisi, tagihan/pembayaran hanya untuk jenis itu
- jika `include_riwayat_siswa = false`, sheet riwayat tidak dibuat

## 11. Rekomendasi Strategi Export

## 11.1 Strategi Produk

Jangan sediakan “export semua data tanpa filter”.

Yang lebih aman:

- user memilih filter dulu
- backend membuat file berdasarkan filter
- mobile mengunduh hasilnya

## 11.2 Strategi Teknis Agar Tidak Lemot

### Strategi yang direkomendasikan

- backend generate file Excel
- gunakan query yang dibatasi filter
- gunakan pagination internal/chunk saat generate file bila data besar
- hindari mengambil semua relasi yang tidak perlu
- buat sheet rekap dari query agregat, bukan dari loop manual besar

### Strategi di mobile

- user klik tombol export
- mobile memanggil endpoint export
- mobile menampilkan loading
- setelah file siap, mobile menerima URL download atau file response
- mobile menyimpan file ke local storage

## 11.3 Batas Aman yang Disarankan

### Wajib

- periode default maksimal 1 bulan saat pertama membuka filter
- tampilkan warning jika user memilih rentang terlalu besar

### Rekomendasi

- batasi export real-time maksimal 12 bulan
- jika lebih dari 12 bulan, tampilkan peringatan agar user mempersempit filter

## 12. Flow Pengguna

## 12.1 Flow Normal Export

1. User membuka menu `Laporan Export Excel`
2. User memilih filter:
   - periode mulai
   - periode selesai
   - filter tambahan opsional
3. User menekan tombol `Export Excel`
4. Mobile mengirim request ke backend
5. Backend memvalidasi filter
6. Backend membangun file Excel multi-sheet
7. Backend mengembalikan file / URL file
8. Mobile mengunduh file
9. User mendapatkan notifikasi berhasil
10. User bisa membuka atau membagikan file

## 12.2 Flow Jika Filter Terlalu Besar

1. User memilih rentang terlalu besar
2. Backend menolak atau memberi warning
3. Mobile menampilkan pesan:
   - periode terlalu besar
   - mohon sempitkan filter

## 12.3 Flow Jika Data Kosong

1. User memilih filter
2. Backend tidak menemukan data
3. Mobile menampilkan pesan:
   - tidak ada data untuk periode/filter ini

## 13. Endpoint yang Disarankan

Belum dibangun saat ini, tetapi rekomendasi endpoint export adalah:

### Opsi sederhana

`GET /api/mobile/admin/reports/export-excel`

Query parameter:

- `periode_mulai`
- `periode_selesai`
- `kelas_id`
- `siswa_id`
- `jenis_pembayaran_id`
- `status_tagihan`
- `status_pembayaran`
- `metode_bayar`
- `include_riwayat_siswa`

### Opsi yang lebih aman untuk mobile

`POST /api/mobile/admin/reports/export-excel`

Body JSON:

```json
{
  "periode_mulai": "2026-06-01",
  "periode_selesai": "2026-06-30",
  "kelas_id": null,
  "siswa_id": null,
  "jenis_pembayaran_id": null,
  "status_tagihan": null,
  "status_pembayaran": null,
  "metode_bayar": null,
  "include_riwayat_siswa": true
}
```

### Rekomendasi Response

Opsi 1:

- langsung download file excel

Opsi 2 yang lebih nyaman untuk mobile:

```json
{
  "message": "File laporan berhasil dibuat.",
  "data": {
    "filename": "laporan-keuangan-sekolah-2026-06.xlsx",
    "download_url": "https://domain.com/storage/exports/xxx.xlsx"
  }
}
```

Saya lebih menyarankan Opsi 2 untuk mobile.

## 14. Validasi Backend yang Disarankan

- `periode_mulai` wajib
- `periode_selesai` wajib
- `periode_mulai <= periode_selesai`
- range tanggal tidak boleh terlalu besar
- semua ID filter harus valid bila dikirim

## 15. Error Handling

### 401

Token tidak valid / tidak ada

### 403

Role tidak diizinkan

### 422

Contoh:

- periode kosong
- periode terbalik
- rentang terlalu besar
- filter tidak valid

### 404

- file download sudah tidak tersedia

### 500

- gagal generate file

## 16. Kebutuhan Performa

### Tujuan performa

- export request masih terasa wajar dari mobile
- backend tidak timeout pada dataset normal
- file yang dihasilkan tetap bisa dibuka lancar di Excel

### Rekomendasi performa backend

- gunakan query agregat untuk `Summary`, `Rekap Jenis`, `Rekap Kelas`
- gunakan streaming/chunk untuk sheet detail jika datanya besar
- hindari eager loading yang tidak perlu
- batasi kolom hanya yang benar-benar dipakai di report

### Rekomendasi performa mobile

- jangan preview isi file di dalam app sebelum download selesai
- cukup tampilkan loading dan status hasil
- setelah download, berikan opsi:
  - buka file
  - bagikan file

## 17. Acceptance Criteria

Fitur export dianggap siap jika:

1. User bisa memilih periode laporan.
2. User bisa menerapkan filter opsional.
3. Backend menghasilkan 1 file Excel multi-sheet.
4. Sheet minimum yang tersedia:
   - Summary
   - Tagihan
   - Pembayaran
   - Tunggakan
   - Rekap Jenis
   - Rekap Kelas
5. File dapat diunduh dari mobile.
6. Isi file konsisten dengan data backend pada filter yang sama.
7. Export tidak terasa berat pada penggunaan normal 1 bulan.
8. Jika data kosong, user mendapat pesan yang jelas.
9. Jika rentang terlalu besar, user mendapat warning/error yang jelas.

## 18. Rekomendasi Tahap Implementasi

### Phase 1

- buat endpoint export excel
- buat filter wajib periode
- buat sheet:
  - Summary
  - Tagihan
  - Pembayaran
  - Tunggakan

### Phase 2

- tambahkan:
  - Rekap Jenis
  - Rekap Kelas

### Phase 3

- tambahkan sheet opsional:
  - Riwayat Siswa

## 19. Rekomendasi Final

Untuk kebutuhan client yang “mau lengkap”, format terbaik adalah:

- 1 tombol export laporan keuangan
- 1 file Excel
- banyak sheet
- filter periode wajib
- filter tambahan opsional
- file dibuat oleh backend
- mobile hanya mengunduh

Ini paling aman, paling rapi, dan paling kecil risikonya untuk performa.
