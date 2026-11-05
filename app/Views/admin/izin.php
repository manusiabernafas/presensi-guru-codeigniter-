<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Pengajuan izin</h1><p>Setujui untuk otomatis mengisi presensi pada hari kerja di rentang tanggal.</p></div></div>
<nav class="tabs"><?php foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $k => $l): ?><a class="<?= $status === $k ? 'on' : '' ?>" href="<?= site_url('admin/izin?status=' . $k) ?>"><?= $l ?> (<?= (int) ($jumlah[$k] ?? 0) ?>)</a><?php endforeach ?></nav>
<?php if (! $daftar): ?><div class="card kosong">Tidak ada pengajuan <?= $status ?>.</div><?php endif ?>
<div class="grid g2">
<?php foreach ($daftar as $d): ?>
  <div class="card">
    <div class="card-h"><div class="orang"><div class="ava"><?= esc(inisial($d['nama'])) ?></div><div><b><?= esc($d['nama']) ?></b><div class="muted small">Diajukan <?= tgl_indo(substr($d['created_at'], 0, 10)) ?></div></div></div><span class="badge info"><?= esc($d['jenis'] === 'dinas' ? 'Dinas luar' : ucfirst($d['jenis'])) ?></span></div>
    <p style="margin:14px 0 6px"><b><?= tgl_indo($d['tgl_mulai'], true) ?><?= $d['tgl_selesai'] !== $d['tgl_mulai'] ? ' – ' . tgl_indo($d['tgl_selesai'], true) : '' ?></b></p>
    <p style="margin:0"><?= esc($d['alasan']) ?></p>
    <?php if ($d['lampiran']): ?><a class="btn sm ghost mt" href="<?= site_url('izin/' . $d['id'] . '/lampiran') ?>" target="_blank" rel="noopener"><?= ikon('surat') ?>Lihat lampiran</a><?php endif ?>
    <?php if ($d['catatan_admin']): ?><p class="muted small">Catatan: <?= esc($d['catatan_admin']) ?></p><?php endif ?>
    <?php if ($status === 'menunggu'): ?>
      <form method="post" action="<?= site_url('admin/izin/' . $d['id']) ?>" class="mt">
        <?= csrf_field() ?><div class="field"><label for="c<?= $d['id'] ?>">Catatan <span class="muted small">(wajib bila ditolak)</span></label><input id="c<?= $d['id'] ?>" name="catatan" maxlength="200"></div>
        <div class="row"><button class="btn ok" name="keputusan" value="setuju"><?= ikon('cek') ?>Setujui</button><button class="btn danger" name="keputusan" value="tolak">Tolak</button></div>
      </form>
    <?php endif ?>
  </div>
<?php endforeach ?>
</div>
<?= $this->endSection() ?>
