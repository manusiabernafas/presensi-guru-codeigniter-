<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Siswa extends BaseController
{
    private const PER = 40;

    public function index()
    {
        $kelas = (int) $this->request->getGet('kelas');
        $q = trim((string) $this->request->getGet('q'));
        $hal = max(1, (int) $this->request->getGet('hal'));
        $b = $this->db->table('siswa s')->select('s.*, k.nama kelas, (SELECT GROUP_CONCAT(e.nama SEPARATOR ", ") FROM ekskul_anggota a JOIN ekskul e ON e.id = a.ekskul_id WHERE a.siswa_id = s.id) ekskul')->join('kelas k', 'k.id = s.kelas_id', 'left');
        if ($kelas) {
            $b->where('s.kelas_id', $kelas);
        }
        if ($q !== '') {
            $b->groupStart()->like('s.nama', $q)->orLike('s.nis', $q)->groupEnd();
        }
        $total = $b->countAllResults(false);
        $daftar = $b->orderBy('k.tingkat')->orderBy('k.nama')->orderBy('s.nama')->limit(self::PER, ($hal - 1) * self::PER)->get()->getResultArray();
        return $this->render('admin/siswa', [
            'judul' => 'Siswa', 'daftar' => $daftar, 'total' => $total, 'hal' => $hal, 'halMax' => max(1, (int) ceil($total / self::PER)),
            'kelas' => $this->db->table('kelas')->orderBy('tingkat')->orderBy('nama')->get()->getResultArray(), 'kelasPilih' => $kelas, 'q' => $q,
        ]);
    }

    public function simpan()
    {
        $id = (int) $this->request->getPost('id');
        $rules = [
            'nis' => ['label' => 'NIS', 'rules' => 'required|alpha_numeric|max_length[20]|is_unique[siswa.nis,id,' . $id . ']'],
            'nama' => ['label' => 'Nama', 'rules' => 'required|min_length[3]|max_length[100]'],
            'jk' => ['label' => 'Jenis kelamin', 'rules' => 'required|in_list[L,P]'],
            'kelas_id' => ['label' => 'Kelas', 'rules' => 'permit_empty|is_natural_no_zero'],
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $data = ['nis' => trim((string) $this->request->getPost('nis')), 'nama' => trim((string) $this->request->getPost('nama')), 'jk' => $this->request->getPost('jk'), 'kelas_id' => (int) $this->request->getPost('kelas_id') ?: null, 'aktif' => $this->request->getPost('aktif') ? 1 : 0];
        $id ? $this->db->table('siswa')->where('id', $id)->update($data) : $this->db->table('siswa')->insert($data);
        return redirect()->back()->with('ok', 'Data ' . $data['nama'] . ' disimpan.');
    }

    public function hapus($id)
    {
        $this->db->table('siswa')->where('id', (int) $id)->delete();
        return redirect()->back()->with('ok', 'Siswa dihapus.');
    }

    public function template()
    {
        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')->setHeader('Content-Disposition', 'attachment; filename="template-siswa.csv"')
            ->setBody("\xEF\xBB\xBFnis;nama;jk;kelas\n26001;Ahmad Fauzi;L;X IPA 1\n26002;Siti Rahmawati;P;X IPA 1\n");
    }

    /** Impor CSV (dari Excel: Simpan sebagai CSV). Pemisah ; atau , terdeteksi otomatis. */
    public function impor()
    {
        if (! $this->validate(['berkas' => ['label' => 'Berkas CSV', 'rules' => 'uploaded[berkas]|max_size[berkas,2048]|ext_in[berkas,csv,txt]']])) {
            return $this->gagal();
        }
        $isi = file_get_contents($this->request->getFile('berkas')->getTempName());
        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi);
        $baris = preg_split('/\r\n|\r|\n/', trim($isi));
        $sep = substr_count($baris[0] ?? '', ';') >= substr_count($baris[0] ?? '', ',') ? ';' : ',';
        $kelas = array_column($this->db->table('kelas')->get()->getResultArray(), 'id', 'nama');
        $baru = $ubah = 0;
        $salah = [];
        foreach ($baris as $i => $line) {
            $c = array_map('trim', str_getcsv($line, $sep, '"', ''));
            if ($i === 0 && strtolower($c[0] ?? '') === 'nis') {
                continue;
            }
            [$nis, $nama, $jk, $kls] = array_pad($c, 4, '');
            $jk = strtoupper($jk);
            if (! preg_match('/^[A-Za-z0-9]{1,20}$/', $nis) || mb_strlen($nama) < 3 || ! in_array($jk, ['L', 'P'], true)) {
                $salah[] = $i + 1;
                continue;
            }
            $kid = $kls !== '' ? ($kelas[$kls] ?? null) : null;
            if ($kls !== '' && ! $kid) {
                $this->db->table('kelas')->insert(['nama' => mb_substr($kls, 0, 30), 'tingkat' => (int) preg_replace('/\D.*/', '', strtr($kls, ['XII' => '12', 'XI' => '11', 'X' => '10', 'IX' => '9', 'VIII' => '8', 'VII' => '7'])) ?: 10]);
                $kid = $kelas[$kls] = $this->db->insertID();
            }
            $ada = $this->db->table('siswa')->where('nis', $nis)->get()->getRowArray();
            $data = ['nama' => mb_substr($nama, 0, 100), 'jk' => $jk, 'kelas_id' => $kid, 'aktif' => 1];
            if ($ada) {
                $this->db->table('siswa')->where('id', $ada['id'])->update($data);
                $ubah++;
            } else {
                $this->db->table('siswa')->insert($data + ['nis' => $nis]);
                $baru++;
            }
        }
        $pesan = "Impor selesai: {$baru} siswa baru, {$ubah} diperbarui.";
        if ($salah) {
            $pesan .= ' Baris dilewati (format salah): ' . implode(', ', array_slice($salah, 0, 15)) . (count($salah) > 15 ? ', ...' : '') . '.';
        }
        return redirect()->to(site_url('admin/siswa'))->with($salah && ! $baru && ! $ubah ? 'error' : 'ok', $pesan);
    }
}
