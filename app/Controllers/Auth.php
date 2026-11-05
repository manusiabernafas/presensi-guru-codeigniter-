<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function beranda()
    {
        $u = $this->user();
        return redirect()->to(site_url($u ? ($u['role'] === 'admin' ? 'admin' : 'guru') : 'login'));
    }

    public function login()
    {
        if ($this->user()) {
            return $this->beranda();
        }
        return view('auth/login', ['set' => $this->presensi->semua()]);
    }

    public function prosesLogin()
    {
        // Batasi percobaan login: 5 kali per menit per (IP + username), supaya guru
        // yang memakai Wi-Fi sekolah yang sama tidak saling terblokir di jam masuk.
        $throttler = service('throttler');
        $kunci = 'login-' . md5($this->request->getIPAddress() . '|' . strtolower(trim((string) $this->request->getPost('username'))));
        if ($throttler->check($kunci, 5, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan. Tunggu sekitar ' . $throttler->getTokenTime() . ' detik lalu coba lagi.');
        }
        if (! $this->validate(['username' => 'required|max_length[50]', 'password' => 'required|max_length[100]'])) {
            return $this->gagal('Isi username dan kata sandi.');
        }
        $u = $this->db->table('users')->where('username', trim((string) $this->request->getPost('username')))->get()->getRowArray();
        if (! $u || ! password_verify((string) $this->request->getPost('password'), $u['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau kata sandi salah.');
        }
        if (! $u['aktif']) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda dinonaktifkan. Hubungi admin sekolah.');
        }
        session()->regenerate(true);
        session()->set('user', ['id' => (int) $u['id'], 'nama' => $u['nama'], 'role' => $u['role'], 'foto' => $u['foto']]);
        $tujuan = session('setelah_login');
        session()->remove('setelah_login');
        return redirect()->to($tujuan ?: site_url($u['role'] === 'admin' ? 'admin' : 'guru'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))->with('ok', 'Anda sudah keluar.');
    }
}
