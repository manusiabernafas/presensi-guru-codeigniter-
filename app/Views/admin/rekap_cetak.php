<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title><?= esc($judul) ?></title><link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<style>@page{size:A4 landscape;margin:12mm}body{background:#fff;padding:20px}.rekap{width:100%}.rekap th,.rekap td{padding:3px;min-width:0;font-size:10.5px}.kop{display:flex;gap:14px;align-items:center;border-bottom:3px solid var(--navy);padding-bottom:10px;margin-bottom:12px}</style></head>
<body onload="setTimeout(() => print(), 400)">
<div class="kop"><div class="brand" style="padding:0"><div class="logo"><?= $set['logo'] ? '<img src="' . base_url('uploads/' . esc($set['logo'])) . '" alt="">' : 'PG' ?></div></div><div><h2><?= esc($set['nama_sekolah']) ?></h2><div class="small"><?= esc($set['alamat']) ?></div></div></div>
<h2>Rekap Presensi Guru · <?= bulan_indo($bulan) ?></h2>
<p class="small">H hadir, T terlambat, I izin, S sakit, D dinas luar, A alpa, L libur · dicetak <?= tgl_indo(date('Y-m-d')) ?></p>
<table class="rekap"><thead><tr><th>No</th><th style="text-align:left">Nama</th><?php foreach ($tanggal as $d => $j): ?><th><?= date('j', strtotime($d)) ?></th><?php endforeach ?><th>H</th><th>T</th><th>I</th><th>S</th><th>D</th><th>A</th><th>%</th></tr></thead>
<tbody><?php foreach ($baris as $i => $b): ?><tr><td><?= $i + 1 ?></td><td class="nm" style="min-width:150px"><?= esc($b['g']['nama']) ?></td><?php foreach ($b['sel'] as $v): ?><td class="<?= $v ?>"><?= $v ?></td><?php endforeach ?><?php foreach ($b['tot'] as $v): ?><td><?= $v ?></td><?php endforeach ?><td><b><?= $b['persen'] ?>%</b></td></tr><?php endforeach ?></tbody></table>
<div style="display:flex;justify-content:flex-end;margin-top:30px"><div class="center small">Kepala Sekolah,<br><br><br><br>______________________</div></div>
</body></html>
