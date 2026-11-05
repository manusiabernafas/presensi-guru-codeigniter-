<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $hari = date('Y-m-d');
        $guru = $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->orderBy('nama')->get()->getResultArray();
        $hariIni = array_column($this->db->table('presensi_guru')->where('tanggal', $hari)->get()->getResultArray(), null, 'user_id');
        $c = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0];
        $belum = [];
        foreach ($guru as $g) {
            $r = $hariIni[$g['id']] ?? null;
            if (! $r) {
                $belum[] = $g;
            } elseif (in_array($r['status'], ['hadir', 'terlambat'], true)) {
                $c[$r['status']]++;
            } elseif ($r['status'] !== 'alpa') {
                $c['izin']++;
            }
        }
        // grafik 10 hari kerja terakhir
        $grafik = [];
        for ($t = time(); count($grafik) < 10 && $t > time() - 40 * 86400; $t -= 86400) {
            $d = date('Y-m-d', $t);
            if ($this->presensi->alasanLibur($d)) {
                continue;
            }
            $rows = $this->db->table('presensi_guru')->select('status, COUNT(*) n')->where('tanggal', $d)->groupBy('status')->get()->getResultArray();
            $n = array_column($rows, 'n', 'status');
            $grafik[] = ['tgl' => $d, 'hadir' => (int) ($n['hadir'] ?? 0), 'terlambat' => (int) ($n['terlambat'] ?? 0), 'izin' => (int) ($n['izin'] ?? 0) + (int) ($n['sakit'] ?? 0) + (int) ($n['dinas'] ?? 0)];
        }
        return $this->render('admin/dashboard', [
            'judul' => 'Dasbor',
            'total' => count($guru),
            'c' => $c,
            'belum' => $belum,
            'libur' => $this->presensi->alasanLibur($hari),
            'terlambat' => array_values(array_filter(array_map(static fn ($g) => isset($hariIni[$g['id']]) && $hariIni[$g['id']]['status'] === 'terlambat' ? $g + ['p' => $hariIni[$g['id']]] : null, $guru))),
            'grafik' => array_reverse($grafik),
            'izin' => $this->db->table('pengajuan_izin i')->select('i.*, u.nama')->join('users u', 'u.id = i.user_id')->where('i.status', 'menunggu')->orderBy('i.created_at')->limit(5)->get()->getResultArray(),
            'ekskul' => $this->db->query('SELECT e.*, u.nama pembina, (SELECT COUNT(*) FROM ekskul_pertemuan p WHERE p.ekskul_id = e.id AND p.tanggal = ?) tercatat FROM ekskul e LEFT JOIN users u ON u.id = e.pembina_id WHERE e.aktif = 1 AND e.hari = ? ORDER BY e.jam_mulai', [$hari, HARI_ID[(int) date('N')]])->getResultArray(),
            'prestasi' => $this->db->table('prestasi p')->select('p.*, e.nama ekskul')->join('ekskul e', 'e.id = p.ekskul_id')->orderBy('p.tanggal', 'DESC')->limit(4)->get()->getResultArray(),
        ]);
    }
}
