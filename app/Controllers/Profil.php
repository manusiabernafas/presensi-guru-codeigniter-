<?php

namespace App\Controllers;

class Profil extends BaseController
{
    public function index()
    {
        return $this->render('profil', [
            'judul' => 'Profil & kata sandi',
            'u' => $this->db->table('users')->where('id', $this->user()['id'])->get()->getRowArray(),
        ]);
    }

    public function simpan()
    {
        $rules = [
            'nama' => ['label' => 'Nama', 'rules' => 'required|min_length[3]|max_length[100]'],
            'no_hp' => ['label' => 'No. HP', 'rules' => 'permit_empty|regex_match[/^[0-9+ ]{8,20}$/]'],
            'foto' => $this->aturanBerkas('foto', 'Foto', 'max_size[foto,1024]|is_image[foto]|ext_in[foto,jpg,jpeg,png,webp]'),
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $data = ['nama' => trim((string) $this->request->getPost('nama')), 'no_hp' => trim((string) $this->request->getPost('no_hp')) ?: null, 'updated_at' => date('Y-m-d H:i:s')];
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $nama = 'foto/' . $file->getRandomName();
            $file->move(FCPATH . 'uploads/foto', basename($nama));
            $data['foto'] = $nama;
        }
        $this->db->table('users')->where('id', $this->user()['id'])->update($data);
        $sess = $this->user();
        $sess['nama'] = $data['nama'];
        $sess['foto'] = $data['foto'] ?? $sess['foto'];
        session()->set('user', $sess);
        return redirect()->to(site_url('profil'))->with('ok', 'Profil disimpan.');
    }

    public function password()
    {
        $rules = [
            'lama' => ['label' => 'Kata sandi lama', 'rules' => 'required'],
            'baru' => ['label' => 'Kata sandi baru', 'rules' => 'required|min_length[8]|max_length[72]'],
            'ulang' => ['label' => 'Ulangi kata sandi', 'rules' => 'required|matches[baru]'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $u = $this->db->table('users')->where('id', $this->user()['id'])->get()->getRowArray();
        if (! password_verify((string) $this->request->getPost('lama'), $u['password_hash'])) {
            return $this->gagal('Kata sandi lama salah.');
        }
        $this->db->table('users')->where('id', $u['id'])->update(['password_hash' => password_hash((string) $this->request->getPost('baru'), PASSWORD_DEFAULT)]);
        return redirect()->to(site_url('profil'))->with('ok', 'Kata sandi berhasil diganti.');
    }

    /** Lampiran izin hanya untuk pemilik pengajuan atau admin. */
    public function lampiranIzin($id)
    {
        $iz = $this->db->table('pengajuan_izin')->where('id', (int) $id)->get()->getRowArray();
        if (! $iz || ! $iz['lampiran'] || (! $this->isAdmin() && (int) $iz['user_id'] !== $this->user()['id'])) {
            return $this->response->setStatusCode(404)->setBody('Lampiran tidak ditemukan.');
        }
        $path = WRITEPATH . 'uploads/izin/' . basename($iz['lampiran']);
        if (! is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('Lampiran tidak ditemukan.');
        }
        return $this->response->download($path, null)->inline();
    }
}
