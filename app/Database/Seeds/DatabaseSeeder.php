<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Data contoh sekolah: 24 guru, 6 kelas, 180 siswa, 8 ekskul,
 * presensi guru 45 hari terakhir, pertemuan ekskul 8 minggu, nilai, dan prestasi.
 * Jalankan: php spark db:seed DatabaseSeeder  (menghapus data lama)
 */
class DatabaseSeeder extends Seeder
{
    private const DEPAN_L = ['Ahmad', 'Budi', 'Dimas', 'Fajar', 'Galih', 'Hendra', 'Irfan', 'Joko', 'Kurniawan', 'Lukman', 'Muhammad', 'Nanda', 'Rizky', 'Satria', 'Teguh', 'Wahyu', 'Yoga', 'Arif', 'Bayu', 'Dani', 'Eko', 'Gilang', 'Hafiz', 'Ilham', 'Raka', 'Reza', 'Bagas', 'Aldi', 'Farhan', 'Zaki'];
    private const DEPAN_P = ['Aisyah', 'Bunga', 'Citra', 'Dewi', 'Erna', 'Fitri', 'Gita', 'Hana', 'Indah', 'Kartika', 'Larasati', 'Maya', 'Nadia', 'Putri', 'Rahma', 'Salsabila', 'Tiara', 'Vina', 'Wulan', 'Yuni', 'Zahra', 'Anisa', 'Dinda', 'Elsa', 'Intan', 'Keisha', 'Nayla', 'Rara', 'Sinta', 'Aulia'];
    private const BELAKANG = ['Pratama', 'Saputra', 'Wijaya', 'Kusuma', 'Lestari', 'Rahmawati', 'Nugroho', 'Hidayat', 'Permata', 'Santoso', 'Setiawan', 'Purnama', 'Wulandari', 'Firmansyah', 'Maharani', 'Ramadhan', 'Utami', 'Syahputra', 'Anggraini', 'Prasetyo', 'Handayani', 'Kurniasih', 'Febriani', 'Gunawan'];

    private function nama(string $jk): string
    {
        $d = $jk === 'L' ? self::DEPAN_L : self::DEPAN_P;
        return $d[array_rand($d)] . ' ' . self::BELAKANG[array_rand(self::BELAKANG)];
    }

    public function run()
    {
        mt_srand(2026);
        $db = $this->db;
        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['prestasi', 'ekskul_nilai', 'ekskul_presensi', 'ekskul_pertemuan', 'ekskul_anggota', 'ekskul', 'pengajuan_izin', 'presensi_guru', 'hari_libur', 'siswa', 'kelas', 'users', 'pengaturan'] as $t) {
            $db->table($t)->truncate();
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
        $now = date('Y-m-d H:i:s');

        $db->table('pengaturan')->insertBatch([
            ['kunci' => 'nama_sekolah', 'nilai' => 'SMA Negeri 1 Harapan Bangsa'],
            ['kunci' => 'alamat', 'nilai' => 'Jl. Pendidikan No. 1, Yogyakarta'],
            ['kunci' => 'qr_secret', 'nilai' => bin2hex(random_bytes(32))],
            ['kunci' => 'kiosk_key', 'nilai' => 'kiosk-' . bin2hex(random_bytes(8))],
        ]);

        // Guru & staf
        $guruData = [
            ['admin', 'Sri Wahyuni, S.Kom.', 'admin', 'Kepala Tata Usaha', null, 'admin123'],
            ['guru', 'Bambang Sutrisno, S.Pd.', 'guru', 'Guru Mapel', 'Pendidikan Jasmani', 'guru123'],
        ];
        $mapel = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Sejarah', 'Geografi', 'Ekonomi', 'Sosiologi', 'PPKn', 'Pendidikan Agama Islam', 'Seni Budaya', 'Informatika', 'Prakarya', 'BK', 'Bahasa Jawa', 'Matematika', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Informatika'];
        foreach ($mapel as $i => $m) {
            $jk = $i % 2 ? 'P' : 'L';
            $guruData[] = ['guru' . ($i + 2), $this->nama($jk) . ', S.Pd.', 'guru', $i === 0 ? 'Wakasek Kurikulum' : 'Guru Mapel', $m, 'guru123'];
        }
        $userIds = [];
        foreach ($guruData as $i => [$u, $n, $r, $jab, $mp, $pw]) {
            $db->table('users')->insert([
                'username' => $u, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'nama' => $n, 'role' => $r,
                'nip' => $r === 'guru' ? '19' . mt_rand(70, 95) . str_pad((string) mt_rand(101, 1231), 4, '0', STR_PAD_LEFT) . ' 20' . mt_rand(10, 22) . str_pad((string) mt_rand(1, 12), 2, '0', STR_PAD_LEFT) . ' 1 ' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) : null,
                'jabatan' => $jab, 'mapel' => $mp, 'no_hp' => '08' . mt_rand(1100000000, 9899999999), 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $userIds[$u] = $db->insertID();
        }
        $guruIds = array_values(array_diff_key($userIds, ['admin' => 0]));

        // Kelas & siswa
        $kelasIds = [];
        foreach (['X-1' => 10, 'X-2' => 10, 'XI IPA 1' => 11, 'XI IPS 1' => 11, 'XII IPA 1' => 12, 'XII IPS 1' => 12] as $nk => $tk) {
            $db->table('kelas')->insert(['nama' => $nk, 'tingkat' => $tk, 'wali_id' => $guruIds[count($kelasIds) + 3]]);
            $kelasIds[] = $db->insertID();
        }
        $siswaIds = [];
        $nis = 24001;
        foreach ($kelasIds as $ki => $kid) {
            $angkatan = [26, 26, 25, 25, 24, 24][$ki];
            for ($i = 0; $i < 30; $i++) {
                $jk = mt_rand(0, 1) ? 'L' : 'P';
                $db->table('siswa')->insert(['nis' => $angkatan . str_pad((string) ($nis++ % 1000), 3, '0', STR_PAD_LEFT) . $ki, 'nama' => $this->nama($jk), 'jk' => $jk, 'kelas_id' => $kid, 'aktif' => 1]);
                $siswaIds[] = $db->insertID();
            }
        }

        // Hari libur 2026
        foreach (['2026-01-01' => 'Tahun Baru Masehi', '2026-03-19' => 'Hari Suci Nyepi', '2026-03-20' => 'Idul Fitri 1447 H', '2026-03-21' => 'Idul Fitri 1447 H', '2026-04-03' => 'Wafat Yesus Kristus', '2026-05-01' => 'Hari Buruh', '2026-05-14' => 'Kenaikan Yesus Kristus', '2026-05-27' => 'Idul Adha 1447 H', '2026-06-01' => 'Hari Lahir Pancasila', '2026-08-17' => 'HUT Kemerdekaan RI', '2026-08-25' => 'Maulid Nabi Muhammad SAW', '2026-12-25' => 'Hari Raya Natal'] as $tg => $ket) {
            $db->table('hari_libur')->insert(['tanggal' => $tg, 'keterangan' => $ket]);
        }
        $libur = array_column($db->table('hari_libur')->get()->getResultArray(), 'keterangan', 'tanggal');

        // Presensi guru 45 hari terakhir (Senin-Sabtu)
        $hari = [];
        for ($t = strtotime('-45 days'); $t <= time(); $t += 86400) {
            $d = date('Y-m-d', $t);
            if (date('N', $t) <= 6 && ! isset($libur[$d])) {
                $hari[] = $d;
            }
        }
        $rows = [];
        foreach ($guruIds as $gi => $gid) {
            $rajin = $gi % 7 === 3 ? 0.70 : 0.90; // beberapa guru lebih sering terlambat
            foreach ($hari as $d) {
                $hariIni = $d === date('Y-m-d');
                if ($hariIni && (mt_rand(1, 100) > 72 || (int) date('G') < 7)) {
                    continue; // sebagian guru belum absen hari ini
                }
                $r = mt_rand(1, 1000) / 1000;
                if (! $hariIni && $r > 0.975) {
                    continue; // alpa
                }
                if (! $hariIni && $r > 0.94) {
                    $rows[] = ['user_id' => $gid, 'tanggal' => $d, 'jam_masuk' => null, 'jam_pulang' => null, 'status' => ['izin', 'sakit', 'dinas'][mt_rand(0, 2)], 'menit_terlambat' => 0, 'metode' => 'izin', 'lat' => null, 'lng' => null, 'jarak_m' => null, 'keterangan' => 'Pengajuan disetujui', 'created_at' => $now, 'updated_at' => $now];
                    continue;
                }
                $telat = mt_rand(1, 100) / 100 > $rajin;
                $menit = $telat ? mt_rand(11, 45) : mt_rand(-40, 9);
                $masuk = date('H:i:s', strtotime('07:00') + $menit * 60 + mt_rand(0, 59));
                $metode = mt_rand(1, 10) > 3 ? 'kode' : 'lokasi';
                $rows[] = [
                    'user_id' => $gid, 'tanggal' => $d, 'jam_masuk' => $masuk,
                    'jam_pulang' => $hariIni ? null : date('H:i:s', strtotime('14:00') + mt_rand(0, 70) * 60),
                    'status' => $menit > 10 ? 'terlambat' : 'hadir', 'menit_terlambat' => max(0, $menit), 'metode' => $metode,
                    'lat' => $metode === 'lokasi' ? -7.7956 + mt_rand(-50, 50) / 100000 : null, 'lng' => $metode === 'lokasi' ? 110.3695 + mt_rand(-50, 50) / 100000 : null,
                    'jarak_m' => $metode === 'lokasi' ? mt_rand(5, 90) : null, 'keterangan' => null, 'created_at' => $d . ' ' . $masuk, 'updated_at' => $d . ' ' . $masuk,
                ];
            }
        }
        foreach (array_chunk($rows, 300) as $chunk) {
            $db->table('presensi_guru')->insertBatch($chunk);
        }

        // Pengajuan izin
        $alasan = ['Mengantar orang tua berobat ke rumah sakit', 'Demam dan dianjurkan istirahat oleh dokter', 'Menghadiri workshop Kurikulum Merdeka di Dinas Pendidikan', 'Acara pernikahan saudara kandung', 'Mendampingi siswa lomba OSN tingkat kabupaten'];
        foreach ([[2, 'izin', 2, 'menunggu'], [5, 'sakit', 1, 'menunggu'], [7, 'dinas', 3, 'menunggu'], [9, 'dinas', -6, 'disetujui'], [11, 'sakit', -12, 'disetujui'], [13, 'izin', -9, 'ditolak']] as [$gi, $jenis, $off, $st]) {
            $mulai = date('Y-m-d', strtotime(($off >= 0 ? '+' : '') . $off . ' days'));
            $db->table('pengajuan_izin')->insert([
                'user_id' => $guruIds[$gi], 'jenis' => $jenis, 'tgl_mulai' => $mulai, 'tgl_selesai' => date('Y-m-d', strtotime($mulai . ' +' . ($jenis === 'dinas' ? 1 : 0) . ' days')),
                'alasan' => $alasan[array_search($jenis, ['izin', 'sakit', 'dinas'], true) + ($gi % 2)] ?? $alasan[0], 'status' => $st,
                'catatan_admin' => $st === 'ditolak' ? 'Bertepatan dengan ujian tengah semester, mohon diatur ulang.' : null,
                'diproses_oleh' => $st === 'menunggu' ? null : $userIds['admin'], 'created_at' => date('Y-m-d H:i:s', strtotime($mulai . ' -2 days 09:15')), 'updated_at' => $now,
            ]);
        }

        // Ekskul
        $ekskul = [
            ['Pramuka', 'Gugus depan penggalang dan penegak: tali-temali, sandi, kemah, dan bakti masyarakat.', $userIds['guru'], 'Jumat', '14:30', '16:30', 'Lapangan utama', '#8a5a2b', 34],
            ['Paskibra', 'Pasukan pengibar bendera sekolah: PBB, latihan fisik, dan upacara.', $userIds['guru'], 'Sabtu', '08:00', '10:30', 'Lapangan upacara', '#c0352b', 24],
            ['Futsal', 'Latihan teknik, taktik, dan persiapan turnamen antarsekolah.', $guruIds[3], 'Rabu', '15:00', '17:00', 'Lapangan futsal', '#17803d', 20],
            ['Basket', 'Latihan dasar dan tim inti untuk kompetisi pelajar.', $guruIds[5], 'Selasa', '15:00', '17:00', 'Lapangan basket', '#e0701b', 18],
            ['PMR', 'Palang Merah Remaja: pertolongan pertama dan donor darah.', $guruIds[8], 'Kamis', '14:30', '16:00', 'Ruang UKS', '#d81e5b', 22],
            ['Rohis', 'Kajian rutin, tahsin Al-Quran, dan kegiatan hari besar Islam.', $guruIds[12], 'Jumat', '13:00', '14:30', 'Masjid sekolah', '#0f766e', 28],
            ['Seni Tari', 'Tari tradisional dan kreasi untuk pentas seni dan lomba.', $guruIds[13], 'Rabu', '14:30', '16:30', 'Aula', '#7c4dcc', 16],
            ['Robotik & Coding', 'Merakit robot, pemrograman Arduino, dan persiapan lomba robotik.', $guruIds[14], 'Kamis', '14:30', '16:30', 'Lab komputer', '#1849a9', 15],
        ];
        $hariN = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];
        $materi = ['Latihan rutin dan pemanasan', 'Pengenalan materi dasar', 'Latihan teknik lanjutan', 'Simulasi dan evaluasi', 'Persiapan lomba', 'Praktik kelompok', 'Review dan permainan', 'Latihan gabungan'];
        foreach ($ekskul as $ei => [$n, $desk, $pemb, $hr, $jm, $js, $tmp, $warna, $jml]) {
            $db->table('ekskul')->insert(['nama' => $n, 'deskripsi' => $desk, 'pembina_id' => $pemb, 'hari' => $hr, 'jam_mulai' => $jm, 'jam_selesai' => $js, 'tempat' => $tmp, 'warna' => $warna, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $eid = $db->insertID();
            $pilih = (array) array_rand(array_flip($siswaIds), $jml);
            foreach ($pilih as $k => $sid) {
                $db->table('ekskul_anggota')->insert(['ekskul_id' => $eid, 'siswa_id' => $sid, 'jabatan' => $k === 0 ? 'Ketua' : ($k === 1 ? 'Sekretaris' : 'Anggota')]);
            }
            $rajin = [];
            foreach ($pilih as $sid) {
                $rajin[$sid] = mt_rand(60, 100) / 100;
            }
            // 8 pertemuan terakhir sesuai hari ekskul (kecuali yang belum terjadi)
            $t = strtotime('last ' . ['Senin' => 'monday', 'Selasa' => 'tuesday', 'Rabu' => 'wednesday', 'Kamis' => 'thursday', 'Jumat' => 'friday', 'Sabtu' => 'saturday'][$hr]);
            if ((int) date('N') === $hariN[$hr] && (int) date('G') >= 17) {
                $t = strtotime('today');
            }
            for ($w = 0; $w < 8; $w++) {
                $d = date('Y-m-d', $t - $w * 7 * 86400);
                if (isset($libur[$d])) {
                    continue;
                }
                $db->table('ekskul_pertemuan')->insert(['ekskul_id' => $eid, 'tanggal' => $d, 'materi' => $materi[($w + $ei) % count($materi)], 'catatan' => null, 'dibuat_oleh' => $pemb, 'created_at' => $d . ' ' . $js, 'updated_at' => $d . ' ' . $js]);
                $pid = $db->insertID();
                $batch = [];
                foreach ($pilih as $sid) {
                    $x = mt_rand(1, 100) / 100;
                    $batch[] = ['pertemuan_id' => $pid, 'siswa_id' => $sid, 'status' => $x <= $rajin[$sid] ? 'H' : ['I', 'S', 'A'][mt_rand(0, 2)]];
                }
                $db->table('ekskul_presensi')->insertBatch($batch);
            }
            // Nilai semester berjalan untuk 3 ekskul pertama
            if ($ei < 3) {
                foreach ($pilih as $sid) {
                    $v = $rajin[$sid] >= .9 ? 'A' : ($rajin[$sid] >= .75 ? 'B' : 'C');
                    $db->table('ekskul_nilai')->insert(['ekskul_id' => $eid, 'siswa_id' => $sid, 'tahun_ajaran' => '2026/2027', 'semester' => 1, 'nilai' => $v,
                        'deskripsi' => ['A' => 'Sangat aktif dan disiplin mengikuti kegiatan', 'B' => 'Aktif mengikuti kegiatan dengan baik', 'C' => 'Perlu meningkatkan kehadiran dan keaktifan'][$v], 'created_at' => $now, 'updated_at' => $now]);
                }
            }
        }
        $ids = array_column($db->table('ekskul')->get()->getResultArray(), 'id', 'nama');
        foreach ([
            ['Pramuka', 'Lomba Tingkat (LT) III Penggalang', 'Regu Elang', 'Kabupaten/Kota', 'Juara 1', '-20 days'],
            ['Paskibra', 'Lomba Baris-Berbaris HUT RI', 'Pasukan inti 17 orang', 'Kabupaten/Kota', 'Juara 2', '-70 days'],
            ['Futsal', 'Liga Futsal Pelajar', 'Tim SMA N 1 Harapan Bangsa', 'Provinsi', 'Juara 3', '-35 days'],
            ['Robotik & Coding', 'Kompetisi Robot Line Follower', 'Tim Garuda Bot', 'Nasional', 'Harapan 1', '-14 days'],
            ['Seni Tari', 'Festival Tari Kreasi Pelajar', 'Sanggar Tari Sekolah', 'Kabupaten/Kota', 'Penyaji Terbaik', '-48 days'],
            ['PMR', 'Jumbara PMR Wira', 'Tim PMR Wira', 'Kabupaten/Kota', 'Juara 2', '-9 days'],
        ] as [$e, $j, $p, $tk, $pr, $off]) {
            $db->table('prestasi')->insert(['ekskul_id' => $ids[$e], 'judul' => $j, 'peserta' => $p, 'tingkat' => $tk, 'peringkat' => $pr, 'tanggal' => date('Y-m-d', strtotime($off)), 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
