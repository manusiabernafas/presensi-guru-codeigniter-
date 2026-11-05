<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title><?= esc($judul) ?></title><link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<style>body{background:#fff;padding:24px}.kop{display:flex;gap:16px;align-items:center;border-bottom:3px solid var(--navy);padding-bottom:12px;margin-bottom:16px}.tbl td,.tbl th{padding:6px 8px}</style></head>
<body onload="setTimeout(() => print(), 400)">
<div class="kop"><div class="brand" style="padding:0;color:var(--navy)"><div class="logo"><?= $set['logo'] ? '<img src="' . base_url('uploads/' . esc($set['logo'])) . '" alt="">' : 'PG' ?></div></div>
  <div><h2><?= esc($set['nama_sekolah']) ?></h2><div class="small"><?= esc($set['alamat']) ?></div></div></div>
<h2>Rekap Ekstrakurikuler <?= esc($e['nama']) ?></h2>
<p class="small">Tahun ajaran <?= esc($ta) ?> semester <?= $sm ?> · Pembina: <?= esc($e['pembina'] ?? '-') ?> · Dicetak <?= tgl_indo(date('Y-m-d')) ?></p>
<table class="tbl" style="border:1px solid var(--line)">
  <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>H</th><th>I</th><th>S</th><th>A</th><th>%</th><th>Nilai</th><th>Deskripsi</th></tr></thead>
  <tbody><?php foreach ($rekap as $i => $r): $n = $nilai[$r['id']] ?? null; ?>
    <tr><td><?= $i + 1 ?></td><td><?= esc($r['nis']) ?></td><td><?= esc($r['nama']) ?></td><td><?= esc($r['kelas'] ?? '-') ?></td><td><?= (int) $r['h'] ?></td><td><?= (int) $r['i'] ?></td><td><?= (int) $r['s_'] ?></td><td><?= (int) $r['a'] ?></td><td><?= $r['persen'] ?>%</td><td><b><?= esc($n['nilai'] ?? '-') ?></b></td><td class="small"><?= esc($n['deskripsi'] ?? '') ?></td></tr>
  <?php endforeach ?></tbody></table>
<div style="display:flex;justify-content:flex-end;margin-top:40px"><div class="center small">Pembina ekstrakurikuler,<br><br><br><br><b><?= esc($e['pembina'] ?? '') ?></b></div></div>
</body></html>
