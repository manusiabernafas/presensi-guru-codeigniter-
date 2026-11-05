<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Kelas extends BaseController
{
    public function index()
    {
        return $this->render('admin/kelas', [
            'judul' => 'Kelas',
            'daftar' => $this->db->query('SELECT k.*, u.nama wali, (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id AND s.aktif = 1) jumlah FROM kelas k LEFT JOIN users u ON u.id = k.wali_id ORDER BY k.tingkat, k.nama')->getResultArray(),
            'guru' => $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->orderBy('nama')->get()->getResultArray(),
        ]);
    }

    public function simpan()
    {
        $id = (int) $this->request->getPost('id');
        $rules = [
            'nama' => ['label' => 'Nama kelas', 'rules' => 'required|max_length[30]|is_unique[kelas.nama,id,' . $id . ']'],
            'tingkat' => ['label' => 'Tingkat', 'rules' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[12]'],
            'wali_id' => ['label' => 'Wali kelas', 'rules' => 'permit_empty|is_natural_no_zero'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $data = ['nama' => trim((string) $this->request->getPost('nama')), 'tingkat' => (int) $this->request->getPost('tingkat'), 'wali_id' => (int) $this->request->getPost('wali_id') ?: null];
        $id ? $this->db->table('kelas')->where('id', $id)->update($data) : $this->db->table('kelas')->insert($data);
        return redirect()->to(site_url('admin/kelas'))->with('ok', 'Kelas ' . $data['nama'] . ' disimpan.');
    }

    public function hapus($id)
    {
        $this->db->table('kelas')->where('id', (int) $id)->delete();
        return redirect()->to(site_url('admin/kelas'))->with('ok', 'Kelas dihapus. Siswanya kini tanpa kelas.');
    }
}
