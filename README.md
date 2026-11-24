# SIPENA BARANTIN

Sistem Informasi Pemantauan Kerja Sama Perguruan Tinggi untuk PPSDM Badan Karantina Indonesia.

## Menjalankan secara lokal

Kebutuhan: PHP 8.2+, Composer, dan MySQL 8 (sudah dijalankan pada PHP 8.5.7 / Laravel 12.73 di Laragon). Antarmuka memakai Bootstrap, Chart.js, dan Leaflet dari CDN.

1. Buat database `db_sipena` di MySQL.
2. Atur `.env` untuk MySQL, lalu isi kredensial Super Admin:

   ```dotenv
   APP_TIMEZONE=Asia/Jakarta
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_sipena
   DB_USERNAME=root
   DB_PASSWORD=
   SIPENA_SUPER_ADMIN_NAME="Super Admin SIPENA"
   SIPENA_SUPER_ADMIN_EMAIL=superadmin@barantin.go.id
   SIPENA_SUPER_ADMIN_PASSWORD=PASSWORD_KUAT_MINIMAL_12_KARAKTER
   SIPENA_REMINDER_MONTHS=6
   ```

3. Jalankan dari folder proyek:

   ```powershell
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```

4. Buka `http://127.0.0.1:8000` untuk portal publik. Login internal tersedia di `http://127.0.0.1:8000/login`.

Untuk mengaktifkan pencarian alamat/nama kantor UPT melalui Google Maps, buat API key browser di Google Cloud, aktifkan **Maps JavaScript API** dan **Places API (New)**, lalu batasi key ke domain aplikasi. Masukkan ke `.env` sebagai `GOOGLE_MAPS_BROWSER_KEY=...` dan jalankan `php artisan config:clear`. Google Maps Platform memerlukan API key dan billing aktif; tanpa konfigurasi ini, pemilihan lokasi manual di peta tetap tersedia.

Seeder membuat akun Super Admin dari `.env` dan empat ruang lingkup yang disebut di brief (Magang, Laboratorium, Perkuliahan, Pertukaran Data). Seeder tidak mengisi nama PTN/UPT fiktif. Isi 38 UPT operasional dan 2 non-operasional melalui menu Data UPT berdasarkan daftar resmi PPSDM.

Untuk menjalankan pengingat otomatis setiap hari pukul 07.00, jalankan juga:

```powershell
php artisan schedule:work
```

## Modul yang tersedia

- Portal publik read-only: daftar PTN dengan kerja sama berstatus Aktif atau Berakhir, termasuk yang tanggal mulainya di masa depan; tanggal, durasi, status waktu, dan sisa masa berlaku ikut ditampilkan. Dokumen hanya dapat dibuka publik jika admin mencentang opsi publik saat upload.
- Peta publik menampilkan pin UPT berdasarkan koordinat kantor yang dipilih saat tambah/edit UPT. Penggunaan peta memerlukan koneksi internet untuk memuat Leaflet dan peta OpenStreetMap.
- Login internal tanpa registrasi, role Admin PPSDM/Super Admin, dan akun nonaktif ditolak pada middleware.
- CRUD PTN, UPT, dan ruang lingkup; hapus PTN/UPT ditolak jika masih memiliki relasi.
- Pencatatan kerja sama KA/PKS dengan banyak UPT dan ruang lingkup per kerja sama.
- Petugas UPT memilih ruang lingkup kerja sama untuk UPT-nya sendiri (maksimal tiga) saat verifikasi; akun yang dibuat Super Admin hanya dapat mengakses penugasan UPT tersebut.
- Kerja sama publik memerlukan tanggal dan salinan TTD KA/PKS serta setidaknya satu verifikasi UPT berstatus Aktif. Dokumen lama dapat diubah visibilitasnya dari halaman Dokumen atau detail kerja sama.
- Upload PDF PKS/IA/dokumen pendukung di storage privat; download melalui route terautentikasi.
- Implementasi dan dokumentasi kegiatan, dashboard dinamis, monitoring, notifikasi pengingat, laporan filter, ekspor CSV (bisa dibuka dengan Excel), dan cetak/simpan PDF lewat dialog print browser.
- Manajemen akun khusus Super Admin dan audit log aktivitas penting.

Impor PTN menerima CSV yang diekspor atau disimpan dari Excel. Hierarki legal antara KA, PKS, dan IA belum dipaksakan di aplikasi sampai ketentuan resmi instansi dikonfirmasi.

## Menampilkan PTN di portal publik

Menambahkan PTN hanya membuat data universitas. Dari menu **Data PTN**, buka detail PTN lalu catat kerja samanya. Kerja sama dengan status **Aktif** atau **Berakhir** muncul di direktori publik, termasuk jika tanggal mulai masih akan datang atau masa kerja sama sudah lewat. Status waktunya, tanggal mulai/akhir, durasi keseluruhan, dan sisa waktunya ikut ditampilkan. Kerja sama berstatus **Draft** tetap disembunyikan.

Kerja sama baru dianggap aktif secara administratif jika tanggal tanda tangan diisi dan dokumen bertanda tangan jenis KA atau PKS sudah diunggah. Tanpa keduanya, sistem menandainya **Belum aktif bekerja sama** dan tidak memasukkannya ke direktori publik. Setelah mengunggah dokumen, centang **Tampilkan PDF ini di portal publik PTN** jika PDF boleh dilihat publik; dokumen tanpa centang tetap privat. Dashboard menampilkan dokumen terbaru dan status visibilitasnya.

Saat mendaftarkan UPT, klik posisi kantor pada peta untuk mengisi latitude/longitude. Pin akan muncul di peta publik setelah data disimpan. Peta publik dibuka pada tampilan ringkas seluruh Indonesia. UPT lama tanpa koordinat perlu diedit dan diberi lokasi.
