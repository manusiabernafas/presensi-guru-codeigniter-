<?php
$uri = trim(uri_string(), '/');
$on = static fn (string $p) => ($uri === $p || ($p !== 'admin' && $p !== 'guru' && str_starts_with($uri, $p))) ? ' on' : '';
$menu = $user['role'] === 'admin' ? [
    ['Utama', [['admin', 'home', 'Dasbor'], ['admin/presensi', 'jam', 'Presensi harian'], ['admin/rekap', 'rekap', 'Rekap bulanan'], ['admin/izin', 'surat', 'Pengajuan izin']]],
    ['Ekstrakurikuler', [['ekskul', 'bintang', 'Kegiatan ekskul'], ['admin/ekskul', 'atur', 'Kelola ekskul']]],
    ['Data sekolah', [['admin/guru', 'guru', 'Guru & staf'], ['admin/kelas', 'kelas', 'Kelas'], ['admin/siswa', 'siswa', 'Siswa'], ['admin/libur', 'libur', 'Hari libur'], ['admin/pengaturan', 'atur', 'Pengaturan']]],
] : [
    ['Presensi', [['guru', 'home', 'Absen hari ini'], ['guru/riwayat', 'kalender', 'Riwayat'], ['guru/izin', 'surat', 'Izin / sakit / dinas']]],
    ['Ekstrakurikuler', [['ekskul', 'bintang', 'Ekskul saya']]],
];
$jam = (int) date('G');
$salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul ?? 'Presensi') ?> · <?= esc($set['nama_sekolah']) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
</head>
<body>
<div class="app">
  <aside class="side" id="side">
    <div class="brand">
      <div class="logo"><?= $set['logo'] ? '<img src="' . base_url('uploads/' . esc($set['logo'])) . '" alt="">' : 'PG' ?></div>
      <div><b><?= esc($set['nama_sekolah']) ?></b><small>Presensi Guru & Ekskul</small></div>
    </div>
    <?php foreach ($menu as [$sec, $items]): ?>
      <div class="nav-sec"><?= $sec ?></div>
      <?php foreach ($items as [$href, $ic, $label]): ?>
        <a class="nav<?= $on($href) ?>" href="<?= site_url($href) ?>"><?= ikon($ic) ?><?= $label ?></a>
      <?php endforeach ?>
    <?php endforeach ?>
    <div class="bawah">
      <a class="nav<?= $on('profil') ?>" href="<?= site_url('profil') ?>"><?= ikon('guru') ?>Profil & sandi</a>
      <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button class="nav"><?= ikon('keluar', '') ?>Keluar</button></form>
    </div>
  </aside>
  <div class="main">
    <header class="top">
      <button class="menu-hp" type="button" aria-label="Buka menu" onclick="document.body.classList.toggle('buka-menu')"><?= ikon('menu') ?></button>
      <div class="sapa"><span class="muted small"><?= $salam ?>,</span><b><?= esc($user['nama']) ?></b></div>
      <span class="muted small nowrap" style="display:none" id="tgl"></span>
      <div class="ava"><?= ! empty($user['foto']) ? '<img src="' . base_url('uploads/' . esc($user['foto'])) . '" alt="">' : esc(inisial($user['nama'])) ?></div>
    </header>
    <main class="konten">
      <?php if (session('ok')): ?><div class="alert ok" role="status"><?= esc(session('ok')) ?></div><?php endif ?>
      <?php if (session('error')): ?><div class="alert err" role="alert"><?= esc(session('error')) ?>
        <?php if ($errs = session('_ci_validation_errors')): ?><ul><?php foreach ((array) $errs as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul><?php endif ?>
      </div><?php endif ?>
      <?= $this->renderSection('konten') ?>
    </main>
  </div>
</div>
<script>
document.addEventListener('click', e => { if (document.body.classList.contains('buka-menu') && !e.target.closest('#side') && !e.target.closest('.menu-hp')) document.body.classList.remove('buka-menu'); });
document.querySelectorAll('form[data-konfirmasi]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.konfirmasi)) e.preventDefault(); }));
document.querySelectorAll('[data-buka]').forEach(b => b.addEventListener('click', () => document.getElementById(b.dataset.buka).showModal()));
document.querySelectorAll('dialog [data-tutup]').forEach(b => b.addEventListener('click', () => b.closest('dialog').close()));
</script>
<?= $this->renderSection('skrip') ?>
</body>
</html>
