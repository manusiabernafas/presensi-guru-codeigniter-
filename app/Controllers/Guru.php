<?php

namespace App\Controllers;

use App\Libraries\Presensi;

class Guru extends BaseController
{
    public function index()
    {
        $uid = $this->user()['id'];
        $ym = date('Y-m');
        return $this->render('guru/index', [
            'judul' => 'Absen hari ini',
            'hariIni' => $this->presensi->presensiHariIni($uid),
            'libur' => $this->presensi->alasanLibur(date('Y-m-d')),
            'ringkas' => $this->ringkasan($uid, $ym),
            'terbaru' => $this->db->table('presensi_guru')->where('user_id', $uid)->orderBy('tanggal', 'DESC')->limit(7)->get()->getResultArray(),
            'izinMenunggu' => $this->db->table('pengajuan_izin')->where(['user_id' => $uid, 'status' => 'menunggu'])->countAllResults(),
            'ekskulHariIni' => $this->db->table('ekskul')->where(['pembina_id' => $uid, 'aktif' => 1, 'hari' => HARI_ID[(int) date('N')]])->get()->getResultArray(),
        ]);
    }

    /** Ringkasan bulan: jumlah per status dan persentase kehadiran terhadap hari kerja. */
    private function ringkasan(int $uid, string $ym): array
    {
        $rows = $this->db->table('presensi_guru')->select('status, COUNT(*) n')->where('user_id', $uid)
            ->where('tanggal >=', $ym . '-01')->where('tanggal <=', date('Y-m-t', strtotime($ym . '-01')))->groupBy('status')->get()->getResultArray();
        $c = array_column($rows, 'n', 'status') + ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'dinas' => 0, 'alpa' => 0];
        $kerja = count($this->presensi->hariKerjaBulan($ym));
        $masuk = (int) $c['hadir'] + (int) $c['terlambat'] + (int) $c['dinas'];
        return $c + ['kerja' => $kerja, 'persen' => $kerja ? round(100 * min($masuk, $kerja) / $kerja) : 0];
    }

    public function absen()
    {
        $uid = $this->user()['id'];
        $aksi = $this->request->getPost('aksi') === 'pulang' ? 'pulang' : 'masuk';
        $metode = $this->request->getPost('metode');
        $throttler = service('throttler');
        if ($throttler->check('absen-' . $uid, 10, MINUTE) === false) {
            return redirect()->to(site_url('guru'))->with('error', 'Terlalu banyak percobaan. Tunggu sebentar.');
        }

        if ($metode === 'kode') {
            if ($this->presensi->get('metode_qr') !== '1') {
                return redirect()->to(site_url('guru'))->with('error', 'Absen dengan QR/kode sedang dinonaktifkan admin.');
            }
            $kode = preg_replace('/\D/', '', (string) $this->request->getPost('kode'));
            if (strlen($kode) !== 6 || ! $this->presensi->kodeValid($kode)) {
                return redirect()->to(site_url('guru'))->with('error', 'Kode tidak valid atau sudah kedaluwarsa. Lihat kode terbaru di layar kiosk.');
            }
            $hasil = $aksi === 'masuk' ? $this->presensi->masuk($uid, 'kode') : $this->presensi->pulang($uid);
        } elseif ($metode === 'lokasi') {
            if ($this->presensi->get('metode_lokasi') !== '1') {
                return redirect()->to(site_url('guru'))->with('error', 'Absen dengan lokasi sedang dinonaktifkan admin.');
            }
            $lat = (float) $this->request->getPost('lat');
            $lng = (float) $this->request->getPost('lng');
            if (! $lat || ! $lng || abs($lat) > 90 || abs($lng) > 180) {
                return redirect()->to(site_url('guru'))->with('error', 'Lokasi belum terbaca. Izinkan akses lokasi di browser lalu coba lagi.');
            }
            $jarak = Presensi::jarak($lat, $lng, (float) $this->presensi->get('lat'), (float) $this->presensi->get('lng'));
            $radius = (int) $this->presensi->get('radius_m');
            if ($jarak > $radius) {
                return redirect()->to(site_url('guru'))->with('error', "Anda berada {$jarak} m dari sekolah. Absen lokasi hanya bisa dalam radius {$radius} m.");
            }
            $hasil = $aksi === 'masuk' ? $this->presensi->masuk($uid, 'lokasi', ['lat' => $lat, 'lng' => $lng, 'jarak' => $jarak]) : $this->presensi->pulang($uid);
        } else {
            return redirect()->to(site_url('guru'))->with('error', 'Pilih cara absen.');
        }
        return redirect()->to(site_url('guru'))->with($hasil['ok'] ? 'ok' : 'error', $hasil['pesan']);
    }

    /** Dibuka dari kamera HP setelah memindai QR di kiosk. */
    public function konfirmasiQr($kode)
    {
        $valid = $this->presensi->kodeValid((string) $kode);
        return $this->render('guru/konfirmasi_qr', [
            'judul' => 'Konfirmasi absen',
            'kode' => $kode,
            'valid' => $valid,
            'hariIni' => $this->presensi->presensiHariIni($this->user()['id']),
            'libur' => $this->presensi->alasanLibur(date('Y-m-d')),
        ]);
    }

    public function riwayat()
    {
        $uid = $this->user()['id'];
        $ym = preg_match('/^\d{4}-\d{2}$/', (string) $this->request->getGet('bulan')) ? $this->request->getGet('bulan') : date('Y-m');
        $rows = $this->db->table('presensi_guru')->where('user_id', $uid)->where('tanggal >=', $ym . '-01')
            ->where('tanggal <=', date('Y-m-t', strtotime($ym . '-01')))->orderBy('tanggal')->get()->getResultArray();
        return $this->render('guru/riwayat', [
            'judul' => 'Riwayat presensi',
            'bulan' => $ym,
            'rows' => array_column($rows, null, 'tanggal'),
            'hariKerja' => $this->presensi->hariKerjaBulan($ym),
            'ringkas' => $this->ringkasan($uid, $ym),
        ]);
    }

    public function izin()
    {
        return $this->render('guru/izin', [
            'judul' => 'Izin, sakit, dan dinas luar',
            'daftar' => $this->db->table('pengajuan_izin')->where('user_id', $this->user()['id'])->orderBy('created_at', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function ajukanIzin()
    {
        $rules = [
            'jenis' => ['label' => 'Jenis', 'rules' => 'required|in_list[izin,sakit,dinas]'],
            'tgl_mulai' => ['label' => 'Tanggal mulai', 'rules' => 'required|valid_date[Y-m-d]'],
            'tgl_selesai' => ['label' => 'Tanggal selesai', 'rules' => 'required|valid_date[Y-m-d]'],
            'alasan' => ['label' => 'Alasan', 'rules' => 'required|min_length[5]|max_length[300]'],
            'lampiran' => $this->aturanBerkas('lampiran', 'Lampiran', 'max_size[lampiran,2048]|ext_in[lampiran,jpg,jpeg,png,pdf]|mime_in[lampiran,image/jpeg,image/png,application/pdf]'),
        ];
        if (! $this->validate($rules)) {
            return $this->gagal();
        }
        $mulai = $this->request->getPost('tgl_mulai');
        $selesai = $this->request->getPost('tgl_selesai');
        if ($selesai < $mulai) {
            return $this->gagal('Tanggal selesai tidak boleh sebelum tanggal mulai.');
        }
        if ((strtotime($selesai) - strtotime($mulai)) / 86400 > 30) {
            return $this->gagal('Pengajuan maksimal 31 hari. Pecah menjadi beberapa pengajuan.');
        }
        $nama = null;
        $file = $this->request->getFile('lampiran');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Disimpan di writable/ (bukan public/) karena bisa berisi surat dokter
            $nama = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/izin', $nama);
        }
        $this->db->table('pengajuan_izin')->insert([
            'user_id' => $this->user()['id'],
            'jenis' => $this->request->getPost('jenis'),
            'tgl_mulai' => $mulai,
            'tgl_selesai' => $selesai,
            'alasan' => trim((string) $this->request->getPost('alasan')),
            'lampiran' => $nama,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('guru/izin'))->with('ok', 'Pengajuan terkirim. Admin akan memprosesnya.');
    }

    public function batalIzin($id)
    {
        $this->db->table('pengajuan_izin')->where(['id' => (int) $id, 'user_id' => $this->user()['id'], 'status' => 'menunggu'])->delete();
        return redirect()->to(site_url('guru/izin'))->with('ok', 'Pengajuan dibatalkan.');
    }
}
