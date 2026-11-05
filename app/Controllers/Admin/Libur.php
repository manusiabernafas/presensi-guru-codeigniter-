<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Libur extends BaseController
{
    public function index()
    {
        $th = (int) ($this->request->getGet('tahun') ?: date('Y'));
        return $this->render('admin/libur', [
            'judul' => 'Hari libur', 'tahun' => $th,
            'daftar' => $this->db->table('hari_libur')->where('tanggal >=', "{$th}-01-01")->where('tanggal <=', "{$th}-12-31")->orderBy('tanggal')->get()->getResultArray(),
        ]);
    }

    public function simpan()
    {
        $rules = [
            'mulai' => ['label' => 'Tanggal mulai', 'rules' => 'required|valid_date[Y-m-d]'],
            'selesai' => ['label' => 'Tanggal selesai', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
            'keterangan' => ['label' => 'Keterangan', 'rules' => 'required|min_length[3]|max_length[120]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $mulai = $this->request->getPost('mulai');
        $selesai = $this->request->getPost('selesai') ?: $mulai;
        if ($selesai < $mulai || (strtotime($selesai) - strtotime($mulai)) / 86400 > 60) {
            return $this->gagal('Rentang tanggal tidak valid (maksimal 60 hari).');
        }
        $n = 0;
        for ($t = strtotime($mulai); $t <= strtotime($selesai); $t += 86400) {
            $this->db->query('REPLACE INTO hari_libur (tanggal, keterangan) VALUES (?, ?)', [date('Y-m-d', $t), trim((string) $this->request->getPost('keterangan'))]);
            $n++;
        }
        return redirect()->to(site_url('admin/libur?tahun=' . substr($mulai, 0, 4)))->with('ok', "{$n} hari libur disimpan.");
    }

    public function hapus($id)
    {
        $this->db->table('hari_libur')->where('id', (int) $id)->delete();
        return redirect()->back()->with('ok', 'Hari libur dihapus.');
    }
}
