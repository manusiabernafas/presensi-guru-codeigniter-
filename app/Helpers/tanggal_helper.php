<?php

/** Format tanggal & waktu gaya Indonesia. */
const BULAN_ID = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI_ID = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

function tgl_indo(?string $tgl, bool $hari = false): string
{
    if (! $tgl) {
        return '-';
    }
    $t = strtotime($tgl);
    return ($hari ? HARI_ID[(int) date('N', $t)] . ', ' : '') . date('j', $t) . ' ' . BULAN_ID[(int) date('n', $t)] . ' ' . date('Y', $t);
}

function bulan_indo(string $ym): string
{
    $t = strtotime($ym . '-01');
    return BULAN_ID[(int) date('n', $t)] . ' ' . date('Y', $t);
}

function jam(?string $time): string
{
    return $time ? substr($time, 0, 5) : '-';
}

/** Label & kelas warna untuk status presensi guru. */
function status_label(string $s): array
{
    return [
        'hadir' => ['Hadir', 'ok'],
        'terlambat' => ['Terlambat', 'warn'],
        'izin' => ['Izin', 'info'],
        'sakit' => ['Sakit', 'info'],
        'dinas' => ['Dinas luar', 'info'],
        'alpa' => ['Alpa', 'bad'],
    ][$s] ?? [ucfirst($s), 'muted'];
}

function inisial(string $nama): string
{
    $p = preg_split('/\s+/', trim($nama));
    return strtoupper(mb_substr($p[0] ?? '', 0, 1) . mb_substr($p[1] ?? '', 0, 1));
}
