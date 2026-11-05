<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Guru extends BaseController
{
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $b = $this->db->table('users')->orderBy('role')->orderBy('nama');
        if ($q !== '') {
            $b->groupStart()->like('nama', $q)->orLike('nip', $q)->orLike('username', $q)->groupEnd();
        }
        return $this->render('admin/guru', ['judul' => 'Guru & staf', 'daftar' => $b->get()->getResultArray(), 'q' => $q]);
    }

    public function simpan()
    {
        $id = (int) $this->request->getPost('id');
        $rules = [
            'nama' => ['label' => 'Nama', 'rules' => 'required|min_length[3]|max_length[100]'],
            'username' => ['label' => 'Username', 'rules' => 'required|regex_match[/^[A-Za-z0-9._-]+$/]|min_length[3]|max_length[50]|is_unique[users.username,id,' . $id . ']'],
            'nip' => ['label' => 'NIP', 'rules' => 'permit_empty|max_length[30]'],
            'jabatan' => ['label' => 'Jabatan', 'rules' => 'permit_empty|max_length[80]'],
            'mapel' => ['label' => 'Mata pelajaran', 'rules' => 'permit_empty|max_length[80]'],
            'no_hp' => ['label' => 'No. HP', 'rules' => 'permit_empty|regex_match[/^[0-9+ ]{8,20}$/]'],
            'role' => ['label' => 'Peran', 'rules' => 'required|in_list[admin,guru]'],
            'password' => ['label' => 'Kata sandi', 'rules' => ($id ? 'permit_empty' : 'required') . '|min_length[8]|max_length[72]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $p = $this->request->getPost();
        $data = [
            'nama' => trim($p['nama']), 'username' => trim($p['username']), 'nip' => trim($p['nip'] ?? '') ?: null,
            'jabatan' => trim($p['jabatan'] ?? '') ?: null, 'mapel' => trim($p['mapel'] ?? '') ?: null, 'no_hp' => trim($p['no_hp'] ?? '') ?: null,
            'role' => $p['role'], 'aktif' => isset($p['aktif']) ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($id === $this->user()['id']) {
            $data['role'] = 'admin';
            $data['aktif'] = 1; // admin tidak bisa menonaktifkan/menurunkan dirinya sendiri
        }
        if (! empty($p['password'])) {
            $data['password_hash'] = password_hash($p['password'], PASSWORD_DEFAULT);
        }
        if ($id) {
            $this->db->table('users')->where('id', $id)->update($data);
        } else {
            $this->db->table('users')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
        }
        return redirect()->to(site_url('admin/guru'))->with('ok', 'Data ' . $data['nama'] . ' disimpan.');
    }

    public function hapus($id)
    {
        if ((int) $id === $this->user()['id']) {
            return redirect()->to(site_url('admin/guru'))->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }
        $this->db->table('users')->where('id', (int) $id)->delete();
        return redirect()->to(site_url('admin/guru'))->with('ok', 'Akun dihapus beserta riwayat presensinya.');
    }

    public function reset($id)
    {
        $u = $this->db->table('users')->where('id', (int) $id)->get()->getRowArray();
        if (! $u) {
            return redirect()->to(site_url('admin/guru'))->with('error', 'Akun tidak ditemukan.');
        }
        $baru = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(9))), 0, 10);
        $this->db->table('users')->where('id', $u['id'])->update(['password_hash' => password_hash($baru, PASSWORD_DEFAULT)]);
        return redirect()->to(site_url('admin/guru'))->with('ok', "Kata sandi baru {$u['nama']}: {$baru} (sampaikan ke yang bersangkutan, hanya ditampilkan sekali).");
    }
}
