<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Riwayat presensi</h1><p><?= bulan_indo($bulan) ?> · kehadiran <?= $ringkas['persen'] ?>%</p></div>
  <form class="row" method="get"><div class="field"><label for="bulan">Bulan</label><input type="month" id="bulan" name="bulan" value="<?= esc($bulan) ?>" max="<?= date('Y-m') ?>"></div><button class="btn ghost">Tampilkan</button></form></div>
<div class="grid g4 stats mb">
  <?php foreach ([['hadir', 'Tepat waktu', 't-ok', 'cek'], ['terlambat', 'Terlambat', 't-warn', 'jam'], ['izin', 'Izin', 't-info', 'surat'], ['sakit', 'Sakit', 't-info', 'alert']] as [$k, $l, $t, $ic]): ?>
    <div class="card stat"><div class="ic <?= $t ?>"><?= ikon($ic) ?></div><div><b><?= (int) $ringkas[$k] ?></b><span><?= $l ?></span></div></div>
  <?php endforeach ?>
</div>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Cara</th><th>Keterangan</th></tr></thead>
  <tbody>
  <?php if (! $hariKerja): ?><tr><td colspan="6" class="kosong">Belum ada hari kerja di bulan ini.</td></tr><?php endif ?>
  <?php foreach (array_reverse($hariKerja) as $d): $r = $rows[$d] ?? null; ?>
    <tr><td class="nowrap"><?= tgl_indo($d, true) ?></td>
    <?php if ($r): [$lb, $cls] = status_label($r['status']); ?>
      <td><?= jam($r['jam_masuk']) ?></td><td><?= jam($r['jam_pulang']) ?></td>
      <td><span class="badge <?= $cls ?>"><?= $lb ?><?= $r['menit_terlambat'] ? ' ' . $r['menit_terlambat'] . ' mnt' : '' ?></span></td>
      <td class="muted small"><?= esc(['qr' => 'QR', 'kode' => 'Kode kiosk', 'lokasi' => 'Lokasi', 'manual' => 'Admin', 'izin' => 'Pengajuan'][$r['metode']] ?? $r['metode']) ?></td>
      <td class="muted small"><?= esc($r['keterangan'] ?? '') ?></td>
    <?php else: ?>
      <td>-</td><td>-</td><td><?= $d === date('Y-m-d') ? '<span class="badge muted">Belum absen</span>' : '<span class="badge bad">Alpa</span>' ?></td><td></td><td></td>
    <?php endif ?></tr>
  <?php endforeach ?>
  </tbody></table></div>
<?= $this->endSection() ?>
