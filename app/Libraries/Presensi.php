<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Logika inti presensi guru: pengaturan, kode QR berputar, hari kerja,
 * absen masuk/pulang, dan perhitungan jarak lokasi.
 */
class Presensi
{
    public const JENDELA_DETIK = 30; // kode QR berganti tiap 30 detik

    private BaseConnection $db;
    private static ?array $cache = null;

    public const DEFAULT = [
        'nama_sekolah' => 'SMA Negeri 1 Harapan Bangsa',
        'alamat' => 'Jl. Pendidikan No. 1',
        'logo' => '',
        'jam_masuk' => '07:00',
        'toleransi_menit' => '10',
        'jam_pulang' => '14:00',
        'hari_kerja' => '1,2,3,4,5,6',
        'metode_qr' => '1',
        'metode_lokasi' => '1',
        'lat' => '-7.7956',
        'lng' => '110.3695',
        'radius_m' => '150',
        'tahun_ajaran' => '2026/2027',
        'semester' => '1',
        'qr_secret' => '',
        'kiosk_key' => '',
    ];

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function semua(): array
    {
        if (self::$cache === null) {
            $rows = $this->db->table('pengaturan')->get()->getResultArray();
            self::$cache = array_merge(self::DEFAULT, array_column($rows, 'nilai', 'kunci'));
            foreach (['qr_secret' => 32, 'kiosk_key' => 12] as $k => $len) {
                if (self::$cache[$k] === '') {
                    $this->simpan([$k => bin2hex(random_bytes($len))]);
                }
            }
        }
        return self::$cache;
    }

    public function get(string $kunci): string
    {
        return (string) ($this->semua()[$kunci] ?? '');
    }

    public function simpan(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->db->query('REPLACE INTO pengaturan (kunci, nilai) VALUES (?, ?)', [$k, (string) $v]);
        }
        self::$cache = null;
    }

    /** Kode 6 digit untuk jendela waktu tertentu (HMAC, tidak bisa ditebak tanpa secret). */
    public function kode(?int $jendela = null): string
    {
        $jendela ??= intdiv(time(), self::JENDELA_DETIK);
        $h = hash_hmac('sha256', 'qr|' . $jendela, $this->get('qr_secret'));
        return str_pad((string) (hexdec(substr($h, 0, 8)) % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Kode berlaku untuk jendela sekarang dan sebelumnya (toleransi ~30-60 detik). */
    public function kodeValid(string $kode): bool
    {
        $w = intdiv(time(), self::JENDELA_DETIK);
        return hash_equals($this->kode($w), $kode) || hash_equals($this->kode($w - 1), $kode);
    }

    public function sisaDetik(): int
    {
        return self::JENDELA_DETIK - (time() % self::JENDELA_DETIK);
    }

    /** Hari kerja = hari terdaftar di pengaturan dan bukan hari libur. Mengembalikan alasan bila bukan. */
    public function alasanLibur(string $tanggal): ?string
    {
        $hari = (int) date('N', strtotime($tanggal));
        if (! in_array((string) $hari, explode(',', $this->get('hari_kerja')), true)) {
            return 'Hari ini bukan hari kerja.';
        }
        $libur = $this->db->table('hari_libur')->where('tanggal', $tanggal)->get()->getRowArray();
        return $libur ? 'Hari libur: ' . $libur['keterangan'] : null;
    }

    public function presensiHariIni(int $userId): ?array
    {
        return $this->db->table('presensi_guru')->where(['user_id' => $userId, 'tanggal' => date('Y-m-d')])->get()->getRowArray();
    }

    /** @return array{ok: bool, pesan: string} */
    public function masuk(int $userId, string $metode, array $lokasi = []): array
    {
        $tgl = date('Y-m-d');
        if ($alasan = $this->alasanLibur($tgl)) {
            return ['ok' => false, 'pesan' => $alasan];
        }
        $ada = $this->presensiHariIni($userId);
        if ($ada) {
            return in_array($ada['status'], ['izin', 'sakit', 'dinas'], true)
                ? ['ok' => false, 'pesan' => 'Hari ini Anda tercatat ' . $ada['status'] . ', tidak perlu absen.']
                : ['ok' => false, 'pesan' => 'Anda sudah absen masuk pukul ' . substr($ada['jam_masuk'], 0, 5) . '.'];
        }
        $batas = strtotime($tgl . ' ' . $this->get('jam_masuk')) + 60 * (int) $this->get('toleransi_menit');
        $telat = max(0, (int) floor((time() - strtotime($tgl . ' ' . $this->get('jam_masuk'))) / 60));
        $terlambat = time() > $batas;
        $this->db->table('presensi_guru')->insert([
            'user_id' => $userId,
            'tanggal' => $tgl,
            'jam_masuk' => date('H:i:s'),
            'status' => $terlambat ? 'terlambat' : 'hadir',
            'menit_terlambat' => $terlambat ? $telat : 0,
            'metode' => $metode,
            'lat' => $lokasi['lat'] ?? null,
            'lng' => $lokasi['lng'] ?? null,
            'jarak_m' => $lokasi['jarak'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'pesan' => $terlambat
            ? 'Absen masuk tercatat pukul ' . date('H:i') . ' (terlambat ' . $telat . ' menit).'
            : 'Absen masuk tercatat pukul ' . date('H:i') . '. Selamat bertugas!'];
    }

    /** @return array{ok: bool, pesan: string} */
    public function pulang(int $userId): array
    {
        $ada = $this->presensiHariIni($userId);
        if (! $ada || ! $ada['jam_masuk']) {
            return ['ok' => false, 'pesan' => 'Anda belum absen masuk hari ini.'];
        }
        if ($ada['jam_pulang']) {
            return ['ok' => false, 'pesan' => 'Anda sudah absen pulang pukul ' . substr($ada['jam_pulang'], 0, 5) . '.'];
        }
        $cepat = time() < strtotime(date('Y-m-d') . ' ' . $this->get('jam_pulang'));
        $this->db->table('presensi_guru')->where('id', $ada['id'])->update([
            'jam_pulang' => date('H:i:s'),
            'keterangan' => $cepat ? 'Pulang sebelum jam pulang' : $ada['keterangan'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'pesan' => 'Absen pulang tercatat pukul ' . date('H:i') . '. Terima kasih, hati-hati di jalan!'];
    }

    /** Jarak dua koordinat dalam meter (rumus haversine). */
    public static function jarak(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return (int) round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /** Daftar tanggal kerja (bukan libur) dalam satu bulan, sampai hari ini. */
    public function hariKerjaBulan(string $ym): array
    {
        $out = [];
        $akhir = min(strtotime(date('Y-m-t', strtotime($ym . '-01'))), strtotime(date('Y-m-d')));
        for ($t = strtotime($ym . '-01'); $t <= $akhir; $t += 86400) {
            if (! $this->alasanLibur(date('Y-m-d', $t))) {
                $out[] = date('Y-m-d', $t);
            }
        }
        return $out;
    }
}
