<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="card" style="max-width:520px;margin:0 auto;text-align:center">
  <?php if ($libur): ?>
    <h1><?= esc($libur) ?></h1><p class="muted">Tidak perlu absen hari ini.</p>
  <?php elseif (! $valid): ?>
    <div class="stat" style="justify-content:center"><div class="ic t-bad"><?= ikon('alert') ?></div></div>
    <h1 class="mt">Kode sudah kedaluwarsa</h1><p class="muted">Kode QR berganti tiap 30 detik. Scan ulang QR yang tampil sekarang di layar kiosk.</p>
  <?php elseif ($hariIni && $hariIni['jam_pulang']): ?>
    <h1>Presensi hari ini sudah lengkap</h1><p class="muted">Masuk <?= jam($hariIni['jam_masuk']) ?> · pulang <?= jam($hariIni['jam_pulang']) ?>.</p>
  <?php else: $aksi = $hariIni && $hariIni['jam_masuk'] ? 'pulang' : 'masuk'; ?>
    <div class="stat" style="justify-content:center"><div class="ic t-ok"><?= ikon('qr') ?></div></div>
    <h1 class="mt">Kode valid</h1>
    <p class="muted">Tekan tombol di bawah untuk mencatat absen <?= $aksi ?> pukul <?= date('H:i') ?>.</p>
    <form method="post" action="<?= site_url('guru/absen') ?>" class="mt">
      <?= csrf_field() ?><input type="hidden" name="metode" value="kode"><input type="hidden" name="kode" value="<?= esc($kode) ?>"><input type="hidden" name="aksi" value="<?= $aksi ?>">
      <button class="btn lg blok"><?= ikon($aksi === 'masuk' ? 'masuk' : 'keluar') ?>Absen <?= $aksi ?> sekarang</button>
    </form>
  <?php endif ?>
  <a class="btn ghost blok mt" href="<?= site_url('guru') ?>">Kembali ke beranda</a>
</div>
<?= $this->endSection() ?>
