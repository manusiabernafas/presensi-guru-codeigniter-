<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · <?= esc($set['nama_sekolah']) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="login">
  <div class="kiri">
    <div class="brand"><div class="logo"><?= $set['logo'] ? '<img src="' . base_url('uploads/' . esc($set['logo'])) . '" alt="">' : 'PG' ?></div><div><b><?= esc($set['nama_sekolah']) ?></b><small><?= esc($set['alamat']) ?></small></div></div>
    <div style="position:relative;z-index:1">
      <h1>Presensi guru & kegiatan ekskul dalam satu aplikasi.</h1>
      <p style="color:#c9d5ef;max-width:440px;margin-top:14px">Absen cukup scan QR di gerbang, izin diajukan dari HP, dan rekap bulanan siap dicetak kapan saja.</p>
    </div>
    <div class="kotak" aria-hidden="true"></div>
    <small style="color:#9fb2d8;position:relative;z-index:1">Jam masuk <?= esc($set['jam_masuk']) ?> · toleransi <?= esc($set['toleransi_menit']) ?> menit</small>
  </div>
  <div class="kanan">
    <form method="post" action="<?= site_url('login') ?>" novalidate>
      <?= csrf_field() ?>
      <h2 style="font-size:26px">Masuk</h2>
      <p class="muted" style="margin:6px 0 22px">Gunakan akun yang diberikan admin sekolah.</p>
      <?php if (session('error')): ?><div class="alert err" role="alert"><?= esc(session('error')) ?></div><?php endif ?>
      <?php if (session('ok')): ?><div class="alert ok"><?= esc(session('ok')) ?></div><?php endif ?>
      <div class="field"><label for="username">Username</label><input id="username" name="username" type="text" autocomplete="username" value="<?= esc(old('username')) ?>" required autofocus></div>
      <div class="field"><label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
      <button class="btn blok lg">Masuk</button>
      <p class="muted small center mt">Lupa kata sandi? Minta admin sekolah meresetnya.</p>
    </form>
  </div>
</div>
</body>
</html>
