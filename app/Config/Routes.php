<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::beranda');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::prosesLogin');
$routes->post('logout', 'Auth::logout');

// Layar kiosk QR (dibuka di TV/laptop gerbang sekolah, tanpa login, dilindungi kunci rahasia)
$routes->get('kiosk/(:segment)', 'Kiosk::index/$1');
$routes->get('kiosk/(:segment)/status', 'Kiosk::status/$1');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('profil', 'Profil::index');
    $routes->get('izin/(:num)/lampiran', 'Profil::lampiranIzin/$1');
    $routes->post('profil', 'Profil::simpan');
    $routes->post('profil/password', 'Profil::password');

    // Ekstrakurikuler: admin melihat semua, guru hanya ekskul yang ia bina
    $routes->get('ekskul', 'Ekskul::index');
    $routes->get('ekskul/(:num)', 'Ekskul::detail/$1');
    $routes->post('ekskul/(:num)/pertemuan', 'Ekskul::buatPertemuan/$1');
    $routes->get('ekskul/pertemuan/(:num)', 'Ekskul::pertemuan/$1');
    $routes->post('ekskul/pertemuan/(:num)', 'Ekskul::simpanPresensi/$1');
    $routes->post('ekskul/pertemuan/(:num)/hapus', 'Ekskul::hapusPertemuan/$1');
    $routes->post('ekskul/(:num)/anggota', 'Ekskul::tambahAnggota/$1');
    $routes->post('ekskul/(:num)/anggota/(:num)/hapus', 'Ekskul::hapusAnggota/$1/$2');
    $routes->post('ekskul/(:num)/nilai', 'Ekskul::simpanNilai/$1');
    $routes->post('ekskul/(:num)/prestasi', 'Ekskul::tambahPrestasi/$1');
    $routes->post('ekskul/prestasi/(:num)/hapus', 'Ekskul::hapusPrestasi/$1');
    $routes->get('ekskul/(:num)/rekap.csv', 'Ekskul::rekapCsv/$1');
    $routes->get('ekskul/(:num)/cetak', 'Ekskul::cetak/$1');
});

$routes->group('guru', ['filter' => 'auth:guru'], static function ($routes) {
    $routes->get('/', 'Guru::index');
    $routes->post('absen', 'Guru::absen');
    $routes->get('riwayat', 'Guru::riwayat');
    $routes->get('izin', 'Guru::izin');
    $routes->post('izin', 'Guru::ajukanIzin');
    $routes->post('izin/(:num)/batal', 'Guru::batalIzin/$1');
});
// Tautan dari QR: konfirmasi dulu (GET tidak mengubah data)
$routes->get('absen/qr/(:num)', 'Guru::konfirmasiQr/$1', ['filter' => 'auth:guru']);

$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('presensi', 'Admin\Presensi::index');
    $routes->post('presensi', 'Admin\Presensi::simpan');
    $routes->get('rekap', 'Admin\Presensi::rekap');
    $routes->get('rekap/csv', 'Admin\Presensi::rekapCsv');
    $routes->get('rekap/cetak', 'Admin\Presensi::rekapCetak');
    $routes->get('izin', 'Admin\Izin::index');
    $routes->post('izin/(:num)', 'Admin\Izin::proses/$1');
    $routes->get('guru', 'Admin\Guru::index');
    $routes->post('guru', 'Admin\Guru::simpan');
    $routes->post('guru/(:num)/hapus', 'Admin\Guru::hapus/$1');
    $routes->post('guru/(:num)/reset', 'Admin\Guru::reset/$1');
    $routes->get('kelas', 'Admin\Kelas::index');
    $routes->post('kelas', 'Admin\Kelas::simpan');
    $routes->post('kelas/(:num)/hapus', 'Admin\Kelas::hapus/$1');
    $routes->get('siswa', 'Admin\Siswa::index');
    $routes->post('siswa', 'Admin\Siswa::simpan');
    $routes->post('siswa/(:num)/hapus', 'Admin\Siswa::hapus/$1');
    $routes->post('siswa/impor', 'Admin\Siswa::impor');
    $routes->get('siswa/template', 'Admin\Siswa::template');
    $routes->get('ekskul', 'Admin\EkskulData::index');
    $routes->post('ekskul', 'Admin\EkskulData::simpan');
    $routes->post('ekskul/(:num)/hapus', 'Admin\EkskulData::hapus/$1');
    $routes->get('libur', 'Admin\Libur::index');
    $routes->post('libur', 'Admin\Libur::simpan');
    $routes->post('libur/(:num)/hapus', 'Admin\Libur::hapus/$1');
    $routes->get('pengaturan', 'Admin\Pengaturan::index');
    $routes->post('pengaturan', 'Admin\Pengaturan::simpan');
    $routes->post('pengaturan/kiosk', 'Admin\Pengaturan::gantiKunciKiosk');
});
