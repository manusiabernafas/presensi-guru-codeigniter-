<?php

namespace App\Controllers;

use App\Libraries\Presensi;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    protected $helpers = ['form', 'url', 'tanggal', 'ikon'];
    protected $db;
    protected Presensi $presensi;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = db_connect();
        $this->presensi = new Presensi();
    }

    protected function user(): array
    {
        return session('user') ?? [];
    }

    protected function isAdmin(): bool
    {
        return ($this->user()['role'] ?? '') === 'admin';
    }

    /** Kembali ke halaman sebelumnya dengan pesan error validasi + input lama. */
    protected function gagal(string $pesan = 'Periksa kembali isian yang ditandai.')
    {
        return redirect()->back()->withInput()->with('error', $pesan);
    }

    /**
     * Aturan validasi untuk input berkas opsional. Catatan: "permit_empty" pada field
     * berkas melewati SEMUA aturan (karena berkas tidak ada di POST), jadi aturan
     * hanya dipasang bila memang ada berkas yang diunggah.
     */
    protected function aturanBerkas(string $field, string $label, string $aturan): array
    {
        $f = $this->request->getFile($field);
        $ada = $f && $f->getError() !== UPLOAD_ERR_NO_FILE;
        return ['label' => $label, 'rules' => $ada ? "uploaded[{$field}]|{$aturan}" : 'permit_empty'];
    }

    protected function render(string $view, array $data = []): string
    {
        $data['user'] = $this->user();
        $data['set'] = $this->presensi->semua();
        return view($view, $data);
    }
}
