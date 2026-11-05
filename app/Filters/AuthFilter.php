<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Wajib login. Argumen opsional: role yang diizinkan, mis. 'auth:admin'. */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        if (! $user) {
            if (strtolower($request->getMethod()) === 'get') {
                session()->set('setelah_login', current_url(true)->__toString());
            }
            return redirect()->to(site_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if ($arguments && ! in_array($user['role'], $arguments, true)) {
            return redirect()->to(site_url($user['role'] === 'admin' ? 'admin' : 'guru'))->with('error', 'Halaman itu bukan untuk akun Anda.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
