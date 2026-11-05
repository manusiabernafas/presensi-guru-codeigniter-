<?php

namespace App\Controllers;

/**
 * Kegiatan ekstrakurikuler. Admin bisa mengelola semua ekskul,
 * guru hanya ekskul yang ia bina (dicek di setiap aksi).
 */
class Ekskul extends BaseController
{
    private function ambil(int $id): ?array
    {
        $e = $this->db->table('ekskul e')->select('e.*, u.nama pembina')->join('users u', 'u.id = e.pembina_id', 'left')->where('e.id', $id)->get()->getRowArray();
        if (! $e || (! $this->isAdmin() && (int) $e['pembina_id'] !== $this->user()['id'])) {
            return null;
        }
        return $e;
    }

    private function tolak()
    {
        return redirect()->to(site_url('ekskul'))->with('error', 'Ekskul tidak ditemukan atau bukan binaan Anda.');
    }

    public function index()
    {
        $q = $this->db->table('ekskul e')->select('e.*, u.nama pembina, (SELECT COUNT(*) FROM ekskul_anggota a WHERE a.ekskul_id = e.id) anggota, (SELECT COUNT(*) FROM ekskul_pertemuan p WHERE p.ekskul_id = e.id) pertemuan, (SELECT MAX(tanggal) FROM ekskul_pertemuan p WHERE p.ekskul_id = e.id) terakhir')
            ->join('users u', 'u.id = e.pembina_id', 'left')->where('e.aktif', 1)->orderBy('e.nama');
        if (! $this->isAdmin()) {
            $q->where('e.pembina_id', $this->user()['id']);
        }
        return $this->render('ekskul/index', ['judul' => 'Ekstrakurikuler', 'daftar' => $q->get()->getResultArray()]);
    }

    public function detail($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $tab = in_array($this->request->getGet('tab'), ['pertemuan', 'anggota', 'nilai', 'prestasi', 'rekap'], true) ? $this->request->getGet('tab') : 'pertemuan';
        $data = ['judul' => $e['nama'], 'e' => $e, 'tab' => $tab, 'anggota' => $this->anggota($e['id'])];
        if ($tab === 'pertemuan') {
            $data['pertemuan'] = $this->db->query("SELECT p.*, SUM(x.status='H') h, SUM(x.status='I') i, SUM(x.status='S') s, SUM(x.status='A') a
                FROM ekskul_pertemuan p LEFT JOIN ekskul_presensi x ON x.pertemuan_id = p.id WHERE p.ekskul_id = ? GROUP BY p.id ORDER BY p.tanggal DESC", [$e['id']])->getResultArray();
        } elseif ($tab === 'anggota') {
            $data['kelas'] = $this->db->table('kelas')->orderBy('tingkat')->orderBy('nama')->get()->getResultArray();
            $kelasId = (int) $this->request->getGet('kelas');
            $data['kelasPilih'] = $kelasId;
            $data['calon'] = $kelasId ? $this->db->query('SELECT s.id, s.nis, s.nama FROM siswa s WHERE s.kelas_id = ? AND s.aktif = 1 AND s.id NOT IN (SELECT siswa_id FROM ekskul_anggota WHERE ekskul_id = ?) ORDER BY s.nama', [$kelasId, $e['id']])->getResultArray() : [];
        } elseif ($tab === 'nilai') {
            $ta = $this->presensi->get('tahun_ajaran');
            $sm = (int) $this->presensi->get('semester');
            $data['ta'] = $ta;
            $data['sm'] = $sm;
            $data['nilai'] = array_column($this->db->table('ekskul_nilai')->where(['ekskul_id' => $e['id'], 'tahun_ajaran' => $ta, 'semester' => $sm])->get()->getResultArray(), null, 'siswa_id');
            $data['rekap'] = array_column($this->rekap($e['id']), null, 'id');
        } elseif ($tab === 'prestasi') {
            $data['prestasi'] = $this->db->table('prestasi')->where('ekskul_id', $e['id'])->orderBy('tanggal', 'DESC')->get()->getResultArray();
        } else {
            $data['rekap'] = $this->rekap($e['id']);
            $data['jmlPertemuan'] = $this->db->table('ekskul_pertemuan')->where('ekskul_id', $e['id'])->countAllResults();
        }
        return $this->render('ekskul/detail', $data);
    }

    private function anggota(int $ekskulId): array
    {
        return $this->db->table('ekskul_anggota a')->select('a.id aid, a.jabatan, s.id, s.nis, s.nama, s.jk, k.nama kelas')
            ->join('siswa s', 's.id = a.siswa_id')->join('kelas k', 'k.id = s.kelas_id', 'left')
            ->where('a.ekskul_id', $ekskulId)->orderBy('k.tingkat')->orderBy('k.nama')->orderBy('s.nama')->get()->getResultArray();
    }

    /** Rekap kehadiran per anggota: jumlah H/I/S/A dan persentase hadir. */
    private function rekap(int $ekskulId): array
    {
        $rows = $this->db->query("SELECT s.id, s.nis, s.nama, k.nama kelas,
                SUM(x.status='H') h, SUM(x.status='I') i, SUM(x.status='S') s_, SUM(x.status='A') a, COUNT(x.id) total
            FROM ekskul_anggota an JOIN siswa s ON s.id = an.siswa_id LEFT JOIN kelas k ON k.id = s.kelas_id
            LEFT JOIN ekskul_presensi x ON x.siswa_id = s.id AND x.pertemuan_id IN (SELECT id FROM ekskul_pertemuan WHERE ekskul_id = ?)
            WHERE an.ekskul_id = ? GROUP BY s.id, s.nis, s.nama, k.nama ORDER BY k.nama, s.nama", [$ekskulId, $ekskulId])->getResultArray();
        foreach ($rows as &$r) {
            $r['persen'] = $r['total'] ? (int) round(100 * $r['h'] / $r['total']) : 0;
            $r['saran'] = $r['persen'] >= 90 ? 'A' : ($r['persen'] >= 75 ? 'B' : ($r['persen'] >= 60 ? 'C' : 'D'));
        }
        return $rows;
    }

    public function buatPertemuan($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $rules = [
            'tanggal' => ['label' => 'Tanggal', 'rules' => 'required|valid_date[Y-m-d]'],
            'materi' => ['label' => 'Materi/kegiatan', 'rules' => 'required|min_length[3]|max_length[200]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $tgl = $this->request->getPost('tanggal');
        if ($tgl > date('Y-m-d')) {
            return $this->gagal('Tanggal pertemuan tidak boleh di masa depan.');
        }
        $ada = $this->db->table('ekskul_pertemuan')->where(['ekskul_id' => $e['id'], 'tanggal' => $tgl])->get()->getRowArray();
        if ($ada) {
            return redirect()->to(site_url('ekskul/pertemuan/' . $ada['id']))->with('ok', 'Pertemuan tanggal itu sudah ada, silakan perbarui presensinya.');
        }
        $this->db->table('ekskul_pertemuan')->insert(['ekskul_id' => $e['id'], 'tanggal' => $tgl, 'materi' => trim((string) $this->request->getPost('materi')), 'catatan' => null, 'dibuat_oleh' => $this->user()['id'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('ekskul/pertemuan/' . $this->db->insertID()))->with('ok', 'Pertemuan dibuat. Semua anggota otomatis ditandai Hadir, ubah yang tidak hadir lalu simpan.');
    }

    private function ambilPertemuan(int $id): ?array
    {
        $p = $this->db->table('ekskul_pertemuan')->where('id', $id)->get()->getRowArray();
        return $p && $this->ambil((int) $p['ekskul_id']) ? $p : null;
    }

    public function pertemuan($id)
    {
        $p = $this->ambilPertemuan((int) $id);
        if (! $p) {
            return $this->tolak();
        }
        $e = $this->ambil((int) $p['ekskul_id']);
        return $this->render('ekskul/pertemuan', [
            'judul' => 'Presensi ' . $e['nama'],
            'e' => $e,
            'p' => $p,
            'anggota' => $this->anggota($e['id']),
            'status' => array_column($this->db->table('ekskul_presensi')->where('pertemuan_id', $p['id'])->get()->getResultArray(), 'status', 'siswa_id'),
        ]);
    }

    public function simpanPresensi($id)
    {
        $p = $this->ambilPertemuan((int) $id);
        if (! $p) {
            return $this->tolak();
        }
        if (! $this->validate(['materi' => ['label' => 'Materi', 'rules' => 'required|max_length[200]'], 'catatan' => ['label' => 'Catatan', 'rules' => 'permit_empty|max_length[300]']])) {
            return $this->gagal();
        }
        $anggota = array_column($this->anggota((int) $p['ekskul_id']), 'id');
        $input = (array) $this->request->getPost('status');
        $this->db->transStart();
        $this->db->table('ekskul_pertemuan')->where('id', $p['id'])->update(['materi' => trim((string) $this->request->getPost('materi')), 'catatan' => trim((string) $this->request->getPost('catatan')) ?: null, 'updated_at' => date('Y-m-d H:i:s')]);
        foreach ($anggota as $sid) {
            $st = in_array($input[$sid] ?? 'H', ['H', 'I', 'S', 'A'], true) ? ($input[$sid] ?? 'H') : 'H';
            $this->db->query('INSERT INTO ekskul_presensi (pertemuan_id, siswa_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)', [$p['id'], $sid, $st]);
        }
        $this->db->transComplete();
        return redirect()->to(site_url('ekskul/' . $p['ekskul_id']))->with('ok', 'Presensi ' . count($anggota) . ' anggota disimpan.');
    }

    public function hapusPertemuan($id)
    {
        $p = $this->ambilPertemuan((int) $id);
        if (! $p) {
            return $this->tolak();
        }
        $this->db->table('ekskul_pertemuan')->where('id', $p['id'])->delete();
        return redirect()->to(site_url('ekskul/' . $p['ekskul_id']))->with('ok', 'Pertemuan dihapus.');
    }

    public function tambahAnggota($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $ids = array_filter(array_map('intval', (array) $this->request->getPost('siswa')));
        if (! $ids) {
            return $this->gagal('Centang minimal satu siswa.');
        }
        $valid = array_column($this->db->table('siswa')->select('id')->whereIn('id', $ids)->get()->getResultArray(), 'id');
        foreach ($valid as $sid) {
            $this->db->query('INSERT IGNORE INTO ekskul_anggota (ekskul_id, siswa_id, jabatan) VALUES (?, ?, ?)', [$e['id'], $sid, 'Anggota']);
        }
        return redirect()->to(site_url('ekskul/' . $e['id'] . '?tab=anggota&kelas=' . (int) $this->request->getPost('kelas')))->with('ok', count($valid) . ' siswa ditambahkan sebagai anggota.');
    }

    public function hapusAnggota($id, $siswaId)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $this->db->table('ekskul_anggota')->where(['ekskul_id' => $e['id'], 'siswa_id' => (int) $siswaId])->delete();
        return redirect()->to(site_url('ekskul/' . $e['id'] . '?tab=anggota'))->with('ok', 'Anggota dikeluarkan.');
    }

    public function simpanNilai($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $ta = $this->presensi->get('tahun_ajaran');
        $sm = (int) $this->presensi->get('semester');
        $nilai = (array) $this->request->getPost('nilai');
        $desk = (array) $this->request->getPost('deskripsi');
        $n = 0;
        foreach (array_column($this->anggota($e['id']), 'id') as $sid) {
            $v = $nilai[$sid] ?? '';
            if (! in_array($v, ['A', 'B', 'C', 'D'], true)) {
                continue;
            }
            $d = mb_substr(trim((string) ($desk[$sid] ?? '')), 0, 200) ?: null;
            $this->db->query('INSERT INTO ekskul_nilai (ekskul_id, siswa_id, tahun_ajaran, semester, nilai, deskripsi, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), deskripsi = VALUES(deskripsi), updated_at = NOW()', [$e['id'], $sid, $ta, $sm, $v, $d]);
            $n++;
        }
        return redirect()->to(site_url('ekskul/' . $e['id'] . '?tab=nilai'))->with('ok', "Nilai {$n} siswa disimpan untuk {$ta} semester {$sm}.");
    }

    public function tambahPrestasi($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $rules = [
            'judul' => ['label' => 'Nama lomba/kegiatan', 'rules' => 'required|min_length[3]|max_length[150]'],
            'peserta' => ['label' => 'Peserta', 'rules' => 'permit_empty|max_length[150]'],
            'tingkat' => ['label' => 'Tingkat', 'rules' => 'required|in_list[Sekolah,Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional]'],
            'peringkat' => ['label' => 'Peringkat', 'rules' => 'required|max_length[40]'],
            'tanggal' => ['label' => 'Tanggal', 'rules' => 'required|valid_date[Y-m-d]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $this->db->table('prestasi')->insert(['ekskul_id' => $e['id']] + array_map(static fn ($v) => trim((string) $v), $this->request->getPost(['judul', 'peserta', 'tingkat', 'peringkat', 'tanggal'])) + ['created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('ekskul/' . $e['id'] . '?tab=prestasi'))->with('ok', 'Prestasi dicatat. Selamat!');
    }

    public function hapusPrestasi($id)
    {
        $pr = $this->db->table('prestasi')->where('id', (int) $id)->get()->getRowArray();
        if (! $pr || ! $this->ambil((int) $pr['ekskul_id'])) {
            return $this->tolak();
        }
        $this->db->table('prestasi')->where('id', $pr['id'])->delete();
        return redirect()->to(site_url('ekskul/' . $pr['ekskul_id'] . '?tab=prestasi'))->with('ok', 'Prestasi dihapus.');
    }

    public function rekapCsv($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, ['NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Persen hadir', 'Saran nilai'], ';', '"', '');
        foreach ($this->rekap($e['id']) as $r) {
            fputcsv($f, [$r['nis'], $r['nama'], $r['kelas'], $r['h'], $r['i'], $r['s_'], $r['a'], $r['persen'] . '%', $r['saran']], ';', '"', '');
        }
        rewind($f);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="rekap-' . url_title($e['nama'], '-', true) . '.csv"')->setBody(stream_get_contents($f));
    }

    public function cetak($id)
    {
        $e = $this->ambil((int) $id);
        if (! $e) {
            return $this->tolak();
        }
        $ta = $this->presensi->get('tahun_ajaran');
        $sm = (int) $this->presensi->get('semester');
        return $this->render('ekskul/cetak', [
            'judul' => 'Cetak ' . $e['nama'], 'e' => $e, 'ta' => $ta, 'sm' => $sm,
            'rekap' => $this->rekap($e['id']),
            'nilai' => array_column($this->db->table('ekskul_nilai')->where(['ekskul_id' => $e['id'], 'tahun_ajaran' => $ta, 'semester' => $sm])->get()->getResultArray(), null, 'siswa_id'),
        ]);
    }
}
