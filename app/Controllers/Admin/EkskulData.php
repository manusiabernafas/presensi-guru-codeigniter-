<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/** Master data ekskul (nama, pembina, jadwal). Kegiatan harian ada di controller Ekskul. */
class EkskulData extends BaseController
{
    public function index()
    {
        return $this->render('admin/ekskul', [
            'judul' => 'Kelola ekskul',
            'daftar' => $this->db->query('SELECT e.*, u.nama pembina, (SELECT COUNT(*) FROM ekskul_anggota a WHERE a.ekskul_id = e.id) anggota FROM ekskul e LEFT JOIN users u ON u.id = e.pembina_id ORDER BY e.aktif DESC, e.nama')->getResultArray(),
            'guru' => $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->orderBy('nama')->get()->getResultArray(),
        ]);
    }

    public function simpan()
    {
        $id = (int) $this->request->getPost('id');
        $rules = [
            'nama' => ['label' => 'Nama ekskul', 'rules' => 'required|min_length[3]|max_length[80]'],
            'deskripsi' => ['label' => 'Deskripsi', 'rules' => 'permit_empty|max_length[300]'],
            'pembina_id' => ['label' => 'Pembina', 'rules' => 'required|is_natural_no_zero'],
            'hari' => ['label' => 'Hari', 'rules' => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu]'],
            'jam_mulai' => ['label' => 'Jam mulai', 'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]'],
            'jam_selesai' => ['label' => 'Jam selesai', 'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]'],
            'tempat' => ['label' => 'Tempat', 'rules' => 'permit_empty|max_length[80]'],
            'warna' => ['label' => 'Warna', 'rules' => 'required|regex_match[/^#[0-9a-fA-F]{6}$/]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $p = $this->request->getPost();
        if ($p['jam_selesai'] <= $p['jam_mulai']) {
            return $this->gagal('Jam selesai harus setelah jam mulai.');
        }
        $data = ['nama' => trim($p['nama']), 'deskripsi' => trim($p['deskripsi'] ?? '') ?: null, 'pembina_id' => (int) $p['pembina_id'], 'hari' => $p['hari'], 'jam_mulai' => $p['jam_mulai'], 'jam_selesai' => $p['jam_selesai'], 'tempat' => trim($p['tempat'] ?? '') ?: null, 'warna' => $p['warna'], 'aktif' => isset($p['aktif']) ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')];
        $id ? $this->db->table('ekskul')->where('id', $id)->update($data) : $this->db->table('ekskul')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(site_url('admin/ekskul'))->with('ok', 'Ekskul ' . $data['nama'] . ' disimpan.');
    }

    public function hapus($id)
    {
        $this->db->table('ekskul')->where('id', (int) $id)->delete();
        return redirect()->to(site_url('admin/ekskul'))->with('ok', 'Ekskul dihapus beserta anggota & riwayatnya.');
    }
}
