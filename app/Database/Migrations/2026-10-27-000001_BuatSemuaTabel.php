<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Semua tabel aplikasi Presensi Guru & Ekskul dalam satu migrasi
 * supaya mudah dipelajari dan dipasang ulang (php spark migrate:refresh).
 */
class BuatSemuaTabel extends Migration
{
    public function up()
    {
        $f = $this->forge;
        $id = ['id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true]];
        $ts = [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        $f->addField([
            'kunci' => ['type' => 'VARCHAR', 'constraint' => 60],
            'nilai' => ['type' => 'TEXT', 'null' => true],
        ]);
        $f->addPrimaryKey('kunci');
        $f->createTable('pengaturan');

        $f->addField($id + [
            'username' => ['type' => 'VARCHAR', 'constraint' => 50],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100],
            'role' => ['type' => 'ENUM', 'constraint' => ['admin', 'guru'], 'default' => 'guru'],
            'nip' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'jabatan' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'mapel' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'no_hp' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'foto' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('username');
        $f->createTable('users');

        $f->addField($id + [
            'nama' => ['type' => 'VARCHAR', 'constraint' => 30],
            'tingkat' => ['type' => 'TINYINT', 'constraint' => 2],
            'wali_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('nama');
        $f->createTable('kelas');

        $f->addField($id + [
            'nis' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100],
            'jk' => ['type' => 'ENUM', 'constraint' => ['L', 'P']],
            'kelas_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('nis');
        $f->addKey('kelas_id');
        $f->addForeignKey('kelas_id', 'kelas', 'id', 'CASCADE', 'SET NULL');
        $f->createTable('siswa');

        $f->addField($id + [
            'tanggal' => ['type' => 'DATE'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 120],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('tanggal');
        $f->createTable('hari_libur');

        $f->addField($id + [
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'jam_masuk' => ['type' => 'TIME', 'null' => true],
            'jam_pulang' => ['type' => 'TIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['hadir', 'terlambat', 'izin', 'sakit', 'dinas', 'alpa'], 'default' => 'hadir'],
            'menit_terlambat' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'metode' => ['type' => 'ENUM', 'constraint' => ['qr', 'kode', 'lokasi', 'manual', 'izin'], 'default' => 'qr'],
            'lat' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'lng' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'jarak_m' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addUniqueKey(['user_id', 'tanggal']);
        $f->addKey('tanggal');
        $f->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('presensi_guru');

        $f->addField($id + [
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'jenis' => ['type' => 'ENUM', 'constraint' => ['izin', 'sakit', 'dinas']],
            'tgl_mulai' => ['type' => 'DATE'],
            'tgl_selesai' => ['type' => 'DATE'],
            'alasan' => ['type' => 'VARCHAR', 'constraint' => 300],
            'lampiran' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['menunggu', 'disetujui', 'ditolak'], 'default' => 'menunggu'],
            'catatan_admin' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'diproses_oleh' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addKey(['status', 'tgl_mulai']);
        $f->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('pengajuan_izin');

        $f->addField($id + [
            'nama' => ['type' => 'VARCHAR', 'constraint' => 80],
            'deskripsi' => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'pembina_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'hari' => ['type' => 'VARCHAR', 'constraint' => 10],
            'jam_mulai' => ['type' => 'TIME'],
            'jam_selesai' => ['type' => 'TIME'],
            'tempat' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'warna' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#1849a9'],
            'aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addForeignKey('pembina_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $f->createTable('ekskul');

        $f->addField($id + [
            'ekskul_id' => ['type' => 'INT', 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'unsigned' => true],
            'jabatan' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Anggota'],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey(['ekskul_id', 'siswa_id']);
        $f->addForeignKey('ekskul_id', 'ekskul', 'id', 'CASCADE', 'CASCADE');
        $f->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('ekskul_anggota');

        $f->addField($id + [
            'ekskul_id' => ['type' => 'INT', 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'materi' => ['type' => 'VARCHAR', 'constraint' => 200],
            'catatan' => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'dibuat_oleh' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addUniqueKey(['ekskul_id', 'tanggal']);
        $f->addForeignKey('ekskul_id', 'ekskul', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('ekskul_pertemuan');

        $f->addField($id + [
            'pertemuan_id' => ['type' => 'INT', 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['H', 'I', 'S', 'A'], 'default' => 'H'],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey(['pertemuan_id', 'siswa_id']);
        $f->addForeignKey('pertemuan_id', 'ekskul_pertemuan', 'id', 'CASCADE', 'CASCADE');
        $f->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('ekskul_presensi');

        $f->addField($id + [
            'ekskul_id' => ['type' => 'INT', 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'unsigned' => true],
            'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => 9],
            'semester' => ['type' => 'TINYINT', 'constraint' => 1],
            'nilai' => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D']],
            'deskripsi' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addUniqueKey(['ekskul_id', 'siswa_id', 'tahun_ajaran', 'semester']);
        $f->addForeignKey('ekskul_id', 'ekskul', 'id', 'CASCADE', 'CASCADE');
        $f->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('ekskul_nilai');

        $f->addField($id + [
            'ekskul_id' => ['type' => 'INT', 'unsigned' => true],
            'judul' => ['type' => 'VARCHAR', 'constraint' => 150],
            'peserta' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tingkat' => ['type' => 'ENUM', 'constraint' => ['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional']],
            'peringkat' => ['type' => 'VARCHAR', 'constraint' => 40],
            'tanggal' => ['type' => 'DATE'],
        ] + $ts);
        $f->addPrimaryKey('id');
        $f->addForeignKey('ekskul_id', 'ekskul', 'id', 'CASCADE', 'CASCADE');
        $f->createTable('prestasi');
    }

    public function down()
    {
        foreach (['prestasi', 'ekskul_nilai', 'ekskul_presensi', 'ekskul_pertemuan', 'ekskul_anggota', 'ekskul', 'pengajuan_izin', 'presensi_guru', 'hari_libur', 'siswa', 'kelas', 'users', 'pengaturan'] as $t) {
            $this->forge->dropTable($t, true);
        }
    }
}
