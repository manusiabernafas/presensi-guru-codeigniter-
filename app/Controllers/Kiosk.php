<?php

namespace App\Controllers;

use App\Libraries\Presensi;

/** Layar QR untuk TV/laptop di gerbang. Diakses dengan kunci rahasia dari menu Pengaturan. */
class Kiosk extends BaseController
{
    private function cekKunci(string $kunci): bool
    {
        return hash_equals($this->presensi->get('kiosk_key'), $kunci);
    }

    public function index(string $kunci)
    {
        if (! $this->cekKunci($kunci)) {
            return $this->response->setStatusCode(404)->setBody('Halaman tidak ditemukan.');
        }
        return view('kiosk', ['set' => $this->presensi->semua(), 'kunci' => $kunci]);
    }

    public function status(string $kunci)
    {
        if (! $this->cekKunci($kunci)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not found']);
        }
        $terakhir = $this->db->table('presensi_guru p')->select('u.nama, p.jam_masuk, p.jam_pulang, p.status')
            ->join('users u', 'u.id = p.user_id')->where('p.tanggal', date('Y-m-d'))->whereIn('p.status', ['hadir', 'terlambat'])
            ->orderBy('p.updated_at', 'DESC')->limit(6)->get()->getResultArray();
        $hadir = $this->db->table('presensi_guru')->where('tanggal', date('Y-m-d'))->whereIn('status', ['hadir', 'terlambat'])->countAllResults();
        $total = $this->db->table('users')->where(['role' => 'guru', 'aktif' => 1])->countAllResults();
        $kode = $this->presensi->kode();
        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON([
            'kode' => $kode,
            'url' => site_url('absen/qr/' . $kode),
            'sisa' => $this->presensi->sisaDetik(),
            'jendela' => Presensi::JENDELA_DETIK,
            'libur' => $this->presensi->alasanLibur(date('Y-m-d')),
            'hadir' => $hadir,
            'total' => $total,
            'terakhir' => array_map(static fn ($r) => [
                'nama' => $r['nama'],
                'teks' => $r['jam_pulang'] ? 'Pulang ' . substr($r['jam_pulang'], 0, 5) : ($r['status'] === 'terlambat' ? 'Masuk ' . substr($r['jam_masuk'], 0, 5) . ' (terlambat)' : 'Masuk ' . substr($r['jam_masuk'], 0, 5)),
            ], $terakhir),
        ]);
    }
}
