<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Pengaturan extends BaseController
{
    public function index()
    {
        return $this->render('admin/pengaturan', ['judul' => 'Pengaturan']);
    }

    public function simpan()
    {
        $rules = [
            'nama_sekolah' => ['label' => 'Nama sekolah', 'rules' => 'required|max_length[100]'],
            'alamat' => ['label' => 'Alamat', 'rules' => 'permit_empty|max_length[200]'],
            'jam_masuk' => ['label' => 'Jam masuk', 'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]'],
            'toleransi_menit' => ['label' => 'Toleransi', 'rules' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[120]'],
            'jam_pulang' => ['label' => 'Jam pulang', 'rules' => 'required|regex_match[/^\d{2}:\d{2}$/]'],
            'lat' => ['label' => 'Latitude', 'rules' => 'required|decimal|greater_than_equal_to[-90]|less_than_equal_to[90]'],
            'lng' => ['label' => 'Longitude', 'rules' => 'required|decimal|greater_than_equal_to[-180]|less_than_equal_to[180]'],
            'radius_m' => ['label' => 'Radius', 'rules' => 'required|integer|greater_than_equal_to[20]|less_than_equal_to[5000]'],
            'tahun_ajaran' => ['label' => 'Tahun ajaran', 'rules' => 'required|regex_match[/^\d{4}\/\d{4}$/]'],
            'semester' => ['label' => 'Semester', 'rules' => 'required|in_list[1,2]'],
            'logo' => $this->aturanBerkas('logo', 'Logo', 'max_size[logo,1024]|is_image[logo]|ext_in[logo,png,jpg,jpeg,webp]'),
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $p = $this->request->getPost();
        $hari = array_values(array_intersect(array_map('strval', (array) ($p['hari_kerja'] ?? [])), ['1', '2', '3', '4', '5', '6', '7']));
        if (! $hari) {
            return $this->gagal('Pilih minimal satu hari kerja.');
        }
        if ($p['jam_pulang'] <= $p['jam_masuk']) {
            return $this->gagal('Jam pulang harus setelah jam masuk.');
        }
        $qr = isset($p['metode_qr']) ? '1' : '0';
        $lok = isset($p['metode_lokasi']) ? '1' : '0';
        if ($qr === '0' && $lok === '0') {
            return $this->gagal('Aktifkan minimal satu cara absen.');
        }
        $data = [
            'nama_sekolah' => trim($p['nama_sekolah']), 'alamat' => trim($p['alamat'] ?? ''), 'jam_masuk' => $p['jam_masuk'], 'toleransi_menit' => (int) $p['toleransi_menit'],
            'jam_pulang' => $p['jam_pulang'], 'lat' => $p['lat'], 'lng' => $p['lng'], 'radius_m' => (int) $p['radius_m'], 'hari_kerja' => implode(',', $hari),
            'metode_qr' => $qr, 'metode_lokasi' => $lok, 'tahun_ajaran' => $p['tahun_ajaran'], 'semester' => $p['semester'],
        ];
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $nama = 'logo-' . $logo->getRandomName();
            $logo->move(FCPATH . 'uploads', $nama);
            $data['logo'] = $nama;
        }
        $this->presensi->simpan($data);
        return redirect()->to(site_url('admin/pengaturan'))->with('ok', 'Pengaturan disimpan.');
    }

    public function gantiKunciKiosk()
    {
        $this->presensi->simpan(['kiosk_key' => bin2hex(random_bytes(12))]);
        return redirect()->to(site_url('admin/pengaturan'))->with('ok', 'Alamat kiosk diganti. Buka alamat baru di layar kiosk.');
    }
}
