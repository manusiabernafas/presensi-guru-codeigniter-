# Presensi Guru & Ekskul (CodeIgniter 4 + MySQL)

Aplikasi kehadiran guru dan manajemen ekstrakurikuler sekolah. Guru absen dengan scan QR di layar kiosk gerbang (kode berganti tiap 30 detik) atau dengan lokasi GPS, mengajukan izin/sakit/dinas dari HP, dan pembina ekskul mengisi presensi latihan, nilai rapor, serta prestasi. Admin/TU memantau dasbor harian dan mencetak rekap bulanan.

## Fitur

**Presensi guru**
- Layar kiosk QR untuk TV/laptop di gerbang: QR + kode 6 angka berganti otomatis tiap 30 detik (HMAC, tidak bisa ditebak/difoto untuk dipakai belakangan), jam besar, daftar guru yang baru absen
- Guru scan QR pakai kamera HP biasa → halaman konfirmasi → absen masuk / pulang (tanpa aplikasi tambahan)
- Alternatif: ketik kode 6 angka, atau absen lokasi GPS dalam radius sekolah (jarak dihitung rumus haversine)
- Status otomatis: tepat waktu / terlambat (dengan menit terlambat) sesuai jam masuk + toleransi
- Hari kerja & hari libur nasional/sekolah: absen ditolak di hari libur
- Pengajuan izin, sakit, dinas luar dengan lampiran (surat dokter disimpan privat, hanya bisa dibuka pemilik & admin)
- Admin menyetujui/menolak; bila disetujui, presensi hari kerja dalam rentang tanggal terisi otomatis
- Koreksi manual oleh admin (lupa absen, HP rusak)

**Rekap & dasbor**
- Dasbor: hadir, terlambat, izin, belum absen hari ini, grafik 10 hari kerja, ekskul hari ini, prestasi terbaru
- Rekap bulanan berupa matriks guru × tanggal (H/T/I/S/D/A/L), total, dan persentase kehadiran
- Unduh CSV (bisa dibuka di Excel) dan cetak A4 landscape dengan kop sekolah

**Ekstrakurikuler**
- Data ekskul: pembina, hari & jam latihan, tempat, warna
- Anggota dipilih per kelas (centang banyak sekaligus)
- Presensi tiap pertemuan dengan tombol cepat H / I / S / A
- Nilai rapor A/B/C/D + deskripsi, dengan saran otomatis dari persentase kehadiran
- Catatan prestasi lomba (tingkat sekolah sampai internasional)
- Rekap kehadiran anggota, unduh CSV, dan cetak lembar nilai bertanda tangan pembina
- Pembina hanya bisa mengelola ekskul binaannya (dicek di server)

**Data sekolah**
- Guru & staf (peran admin/guru, aktif/nonaktif, reset kata sandi), kelas & wali kelas
- Siswa + impor CSV dari Excel (kelas baru dibuat otomatis)
- Pengaturan: identitas & logo sekolah, jam masuk/pulang, toleransi, hari kerja, cara absen, koordinat & radius, tahun ajaran

**Keamanan**: password hash, proteksi CSRF di semua form, pembatasan percobaan login, validasi input berbahasa Indonesia, berkas unggahan dibatasi jenis & ukurannya, folder `app/`, `writable/`, dan `.env` tidak bisa diakses dari browser.

Tampilan responsif (nyaman di HP guru), font Plus Jakarta Sans + Montserrat Bold, ikon garis, tanpa CDN (bisa dipakai di jaringan lokal sekolah).

## Kebutuhan

- PHP 8.2 atau lebih baru dengan ekstensi **intl** dan **mysqli** (XAMPP 8.2+ / Laragon)
- MySQL 5.7+ / MariaDB 10.4+
- [Composer](https://getcomposer.org)

## Cara install (XAMPP / Laragon di Windows)

1. Ekstrak ZIP lalu pindahkan folder `presensi-guru-ekskul` ke `C:\xampp\htdocs\` (atau `C:\laragon\www\`).
2. Aktifkan ekstensi **intl**: buka `C:\xampp\php\php.ini`, cari `;extension=intl`, hapus tanda `;` di depannya, simpan, lalu restart Apache.
3. Nyalakan **Apache** dan **MySQL** dari XAMPP Control Panel.
4. Klik dua kali **`setup.bat`** (atau buka *Shell* XAMPP, masuk ke folder proyek, ketik `setup.bat`). Script ini memasang dependensi, membuat database `presensi_guru`, tabel, dan data contoh.
5. Buka **http://localhost/presensi-guru-ekskul** lalu masuk:
   - Admin / TU: `admin` / `admin123`
   - Guru (pembina Pramuka & Paskibra): `guru` / `guru123`
   - Guru lain: `guru2` sampai `guru23`, sandi `guru123`

Linux/macOS: jalankan `bash setup.sh`, lalu `php spark serve` dan buka http://localhost:8080.

Database berbeda? Ubah bagian `database.default.*` di file `.env` lalu jalankan ulang `php spark migrate --all`.

## Cara memakai

1. **Pengaturan** → isi nama sekolah, logo, jam masuk, toleransi, hari kerja, dan koordinat sekolah (salin dari Google Maps).
2. Dari **Dasbor**, klik **Buka layar kiosk QR** di laptop/TV dekat gerbang. Alamat kiosk bersifat rahasia (bisa diganti di Pengaturan).
3. Guru login di HP sekali saja, lalu setiap pagi cukup arahkan kamera ke QR → tekan **Absen masuk**. Sore hari scan lagi untuk **Absen pulang**.
4. Admin memproses pengajuan izin di menu **Pengajuan izin** dan mencetak **Rekap bulanan** di akhir bulan.
5. Pembina membuka **Ekskul saya** → buat pertemuan → tandai siswa yang tidak hadir → simpan. Di akhir semester isi **Nilai rapor** (bisa diisi otomatis dari saran kehadiran) lalu **Cetak**.

> **Absen lokasi (GPS)** butuh alamat HTTPS karena aturan keamanan browser. Di jaringan lokal tanpa HTTPS gunakan QR/kode kiosk (tetap berfungsi penuh).

## Struktur singkat

- `app/Libraries/Presensi.php` — logika inti: kode QR berputar, hari kerja, absen masuk/pulang, jarak lokasi
- `app/Controllers/` — `Guru`, `Kiosk`, `Ekskul`, `Profil`, `Auth`, dan `Admin/*`
- `app/Database/Migrations/` — skema 13 tabel, `app/Database/Seeds/` — data contoh sekolah
- `app/Views/` — tampilan, `public/assets/` — CSS, font, dan pustaka QR (lokal)
- `.htaccess` di folder utama mengarahkan semua permintaan ke `public/` agar aman dipasang di `htdocs`
