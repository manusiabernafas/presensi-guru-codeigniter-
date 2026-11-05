<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1><?= $user['role'] === 'admin' ? 'Kegiatan ekstrakurikuler' : 'Ekskul binaan saya' ?></h1><p>Isi presensi tiap latihan, nilai rapor, dan catat prestasi.</p></div>
  <?php if ($user['role'] === 'admin'): ?><a class="btn ghost" href="<?= site_url('admin/ekskul') ?>"><?= ikon('atur') ?>Kelola ekskul</a><?php endif ?></div>
<?php if (! $daftar): ?><div class="card kosong">Belum ada ekskul<?= $user['role'] === 'admin' ? '. Tambahkan di menu Kelola ekskul.' : ' yang Anda bina.' ?></div><?php endif ?>
<div class="grid g3">
<?php foreach ($daftar as $e): ?>
  <a class="card" href="<?= site_url('ekskul/' . $e['id']) ?>" style="color:inherit;text-decoration:none;border-top:6px solid <?= esc($e['warna']) ?>">
    <h2><?= esc($e['nama']) ?></h2>
    <p class="muted small" style="margin:6px 0 14px"><?= esc($e['hari']) ?>, <?= jam($e['jam_mulai']) ?>–<?= jam($e['jam_selesai']) ?> · <?= esc($e['tempat'] ?: '-') ?></p>
    <div class="orang"><div class="ava"><?= esc(inisial($e['pembina'] ?? '?')) ?></div><div><b class="small"><?= esc($e['pembina'] ?? 'Belum ada pembina') ?></b><div class="muted small">Pembina</div></div></div>
    <div class="row mt" style="gap:8px"><span class="badge info"><?= (int) $e['anggota'] ?> anggota</span><span class="badge muted"><?= (int) $e['pertemuan'] ?> pertemuan</span><?php if ($e['terakhir']): ?><span class="badge muted">Terakhir <?= date('j/n', strtotime($e['terakhir'])) ?></span><?php endif ?></div>
  </a>
<?php endforeach ?>
</div>
<?= $this->endSection() ?>
