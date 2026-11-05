<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Presensi extends BaseController
{
    public function index()
    {
        $tgl = $this->tanggal($this->request->getGet('tanggal'));
        $guru = $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->orderBy('nama')->get()->getResultArray();
        return $this->render('admin/presensi', [
            'judul' => 'Presensi harian',
            'tgl' => $tgl,
            'guru' => $guru,
            'rows' => array_column($this->db->table('presensi_guru')->where('tanggal', $tgl)->get()->getResultArray(), null, 'user_id'),
            'libur' => $this->presensi->alasanLibur($tgl),
        ]);
    }

    private function tanggal($v): string
    {
        return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : date('Y-m-d');
    }

    /** Koreksi manual oleh admin (mis. guru lupa absen atau HP rusak). */
    public function simpan()
    {
        $rules = [
            'user_id' => ['label' => 'Guru', 'rules' => 'required|is_natural_no_zero'],
            'tanggal' => ['label' => 'Tanggal', 'rules' => 'required|valid_date[Y-m-d]'],
            'status' => ['label' => 'Status', 'rules' => 'required|in_list[hadir,terlambat,izin,sakit,dinas,alpa,hapus]'],
            'jam_masuk' => ['label' => 'Jam masuk', 'rules' => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]'],
            'jam_pulang' => ['label' => 'Jam pulang', 'rules' => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]'],
            'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[200]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $p = $this->request->getPost();
        $uid = (int) $p['user_id'];
        if ($p['tanggal'] > date('Y-m-d')) {
            return $this->gagal('Tidak bisa mengisi presensi untuk tanggal yang akan datang.');
        }
        $tbl = $this->db->table('presensi_guru');
        if ($p['status'] === 'hapus') {
            $tbl->where(['user_id' => $uid, 'tanggal' => $p['tanggal']])->delete();
            return redirect()->to(site_url('admin/presensi?tanggal=' . $p['tanggal']))->with('ok', 'Data presensi dihapus.');
        }
        $masuk = in_array($p['status'], ['hadir', 'terlambat'], true);
        if ($masuk && empty($p['jam_masuk'])) {
            return $this->gagal('Isi jam masuk untuk status hadir/terlambat.');
        }
        $telat = 0;
        if ($masuk) {
            $telat = max(0, (int) floor((strtotime($p['tanggal'] . ' ' . $p['jam_masuk']) - strtotime($p['tanggal'] . ' ' . $this->presensi->get('jam_masuk'))) / 60));
        }
        $data = [
            'status' => $p['status'],
            'jam_masuk' => $masuk ? $p['jam_masuk'] . ':00' : null,
            'jam_pulang' => $masuk && ! empty($p['jam_pulang']) ? $p['jam_pulang'] . ':00' : null,
            'menit_terlambat' => $p['status'] === 'terlambat' ? $telat : 0,
            'metode' => 'manual',
            'keterangan' => trim((string) ($p['keterangan'] ?? '')) ?: 'Dikoreksi admin',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($tbl->where(['user_id' => $uid, 'tanggal' => $p['tanggal']])->countAllResults()) {
            $this->db->table('presensi_guru')->where(['user_id' => $uid, 'tanggal' => $p['tanggal']])->update($data);
        } else {
            $this->db->table('presensi_guru')->insert($data + ['user_id' => $uid, 'tanggal' => $p['tanggal'], 'created_at' => date('Y-m-d H:i:s')]);
        }
        return redirect()->to(site_url('admin/presensi?tanggal=' . $p['tanggal']))->with('ok', 'Presensi diperbarui.');
    }

    /** Matriks guru × tanggal: H, T, I, S, D, A, L (libur), - (belum berlalu). */
    private function dataRekap(string $ym): array
    {
        $awal = $ym . '-01';
        $akhir = date('Y-m-t', strtotime($awal));
        $guru = $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->orderBy('nama')->get()->getResultArray();
        $semua = $this->db->table('presensi_guru')->where('tanggal >=', $awal)->where('tanggal <=', $akhir)->get()->getResultArray();
        $map = [];
        foreach ($semua as $r) {
            $map[$r['user_id']][$r['tanggal']] = $r;
        }
        $tanggal = [];
        for ($t = strtotime($awal); $t <= strtotime($akhir); $t += 86400) {
            $d = date('Y-m-d', $t);
            $tanggal[$d] = $this->presensi->alasanLibur($d) ? 'libur' : ($d > date('Y-m-d') ? 'nanti' : 'kerja');
        }
        $kode = ['hadir' => 'H', 'terlambat' => 'T', 'izin' => 'I', 'sakit' => 'S', 'dinas' => 'D', 'alpa' => 'A'];
        $baris = [];
        foreach ($guru as $g) {
            $sel = [];
            $tot = ['H' => 0, 'T' => 0, 'I' => 0, 'S' => 0, 'D' => 0, 'A' => 0];
            foreach ($tanggal as $d => $jenis) {
                if ($jenis === 'libur') {
                    $sel[$d] = 'L';
                    continue;
                }
                if (isset($map[$g['id']][$d])) {
                    $k = $kode[$map[$g['id']][$d]['status']];
                } elseif ($jenis === 'nanti' || $d === date('Y-m-d')) {
                    $sel[$d] = '';
                    continue;
                } else {
                    $k = 'A'; // hari kerja yang sudah lewat tanpa data = alpa
                }
                $sel[$d] = $k;
                $tot[$k]++;
            }
            $kerja = count(array_filter($sel, static fn ($v) => $v !== 'L' && $v !== ''));
            $baris[] = ['g' => $g, 'sel' => $sel, 'tot' => $tot, 'persen' => $kerja ? round(100 * ($tot['H'] + $tot['T'] + $tot['D']) / $kerja) : 0];
        }
        return ['tanggal' => $tanggal, 'baris' => $baris];
    }

    private function bulan(): string
    {
        $b = (string) $this->request->getGet('bulan');
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $b) ? $b : date('Y-m');
    }

    public function rekap()
    {
        $ym = $this->bulan();
        return $this->render('admin/rekap', ['judul' => 'Rekap bulanan', 'bulan' => $ym] + $this->dataRekap($ym));
    }

    public function rekapCetak()
    {
        $ym = $this->bulan();
        return $this->render('admin/rekap_cetak', ['judul' => 'Rekap ' . bulan_indo($ym), 'bulan' => $ym] + $this->dataRekap($ym));
    }

    public function rekapCsv()
    {
        $ym = $this->bulan();
        $d = $this->dataRekap($ym);
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, array_merge(['NIP', 'Nama'], array_map(static fn ($t) => date('j', strtotime($t)), array_keys($d['tanggal'])), ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Dinas', 'Alpa', 'Persen']), ';', '"', '');
        foreach ($d['baris'] as $b) {
            fputcsv($f, array_merge([$b['g']['nip'], $b['g']['nama']], array_values($b['sel']), array_values($b['tot']), [$b['persen'] . '%']), ';', '"', '');
        }
        rewind($f);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="rekap-presensi-guru-' . $ym . '.csv"')->setBody(stream_get_contents($f));
    }
}
