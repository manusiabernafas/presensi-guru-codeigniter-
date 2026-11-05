<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Izin extends BaseController
{
    public function index()
    {
        $status = in_array($this->request->getGet('status'), ['menunggu', 'disetujui', 'ditolak'], true) ? $this->request->getGet('status') : 'menunggu';
        return $this->render('admin/izin', [
            'judul' => 'Pengajuan izin',
            'status' => $status,
            'daftar' => $this->db->table('pengajuan_izin i')->select('i.*, u.nama, u.nip')->join('users u', 'u.id = i.user_id')
                ->where('i.status', $status)->orderBy('i.created_at', $status === 'menunggu' ? 'ASC' : 'DESC')->limit(100)->get()->getResultArray(),
            'jumlah' => array_column($this->db->table('pengajuan_izin')->select('status, COUNT(*) n')->groupBy('status')->get()->getResultArray(), 'n', 'status'),
        ]);
    }

    public function proses($id)
    {
        $iz = $this->db->table('pengajuan_izin')->where(['id' => (int) $id, 'status' => 'menunggu'])->get()->getRowArray();
        if (! $iz) {
            return redirect()->to(site_url('admin/izin'))->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }
        $setuju = $this->request->getPost('keputusan') === 'setuju';
        $catatan = mb_substr(trim((string) $this->request->getPost('catatan')), 0, 200) ?: null;
        if (! $setuju && ! $catatan) {
            return redirect()->to(site_url('admin/izin'))->with('error', 'Tuliskan alasan penolakan agar guru tahu.');
        }
        $this->db->transStart();
        $this->db->table('pengajuan_izin')->where('id', $iz['id'])->update(['status' => $setuju ? 'disetujui' : 'ditolak', 'catatan_admin' => $catatan, 'diproses_oleh' => $this->user()['id'], 'updated_at' => date('Y-m-d H:i:s')]);
        $n = 0;
        if ($setuju) {
            for ($t = strtotime($iz['tgl_mulai']); $t <= strtotime($iz['tgl_selesai']); $t += 86400) {
                $d = date('Y-m-d', $t);
                if ($this->presensi->alasanLibur($d)) {
                    continue;
                }
                $ada = $this->db->table('presensi_guru')->where(['user_id' => $iz['user_id'], 'tanggal' => $d])->get()->getRowArray();
                if ($ada && in_array($ada['status'], ['hadir', 'terlambat'], true)) {
                    continue; // sudah hadir pada hari itu, jangan ditimpa
                }
                $row = ['status' => $iz['jenis'], 'metode' => 'izin', 'jam_masuk' => null, 'jam_pulang' => null, 'menit_terlambat' => 0, 'keterangan' => mb_substr($iz['alasan'], 0, 200), 'updated_at' => date('Y-m-d H:i:s')];
                if ($ada) {
                    $this->db->table('presensi_guru')->where('id', $ada['id'])->update($row);
                } else {
                    $this->db->table('presensi_guru')->insert($row + ['user_id' => $iz['user_id'], 'tanggal' => $d, 'created_at' => date('Y-m-d H:i:s')]);
                }
                $n++;
            }
        }
        $this->db->transComplete();
        return redirect()->to(site_url('admin/izin'))->with('ok', $setuju ? "Pengajuan disetujui, {$n} hari kerja tercatat sebagai {$iz['jenis']}." : 'Pengajuan ditolak.');
    }
}
