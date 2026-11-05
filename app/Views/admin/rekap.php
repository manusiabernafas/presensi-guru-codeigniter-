<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Rekap presensi guru</h1><p><?= bulan_indo($bulan) ?> · H hadir, T terlambat, I izin, S sakit, D dinas, A alpa, L libur</p></div>
  <div class="row"><form class="row" method="get"><div class="field"><label for="bulan">Bulan</label><input type="month" id="bulan" name="bulan" value="<?= esc($bulan) ?>" max="<?= date('Y-m') ?>"></div><button class="btn ghost">Tampilkan</button></form>
    <a class="btn ghost" href="<?= site_url('admin/rekap/csv?bulan=' . $bulan) ?>"><?= ikon('unduh') ?>CSV / Excel</a><a class="btn" href="<?= site_url('admin/rekap/cetak?bulan=' . $bulan) ?>" target="_blank"><?= ikon('cetak') ?>Cetak</a></div></div>
<div class="tbl-wrap"><table class="rekap">
  <thead><tr><th style="text-align:left">Nama</th><?php foreach ($tanggal as $d => $j): ?><th><?= date('j', strtotime($d)) ?></th><?php endforeach ?><th>H</th><th>T</th><th>I</th><th>S</th><th>D</th><th>A</th><th>%</th></tr></thead>
  <tbody>
  <?php if (! $baris): ?><tr><td colspan="40" class="kosong">Belum ada guru aktif.</td></tr><?php endif ?>
  <?php foreach ($baris as $b): ?>
    <tr><td class="nm"><?= esc($b['g']['nama']) ?></td><?php foreach ($b['sel'] as $v): ?><td class="<?= $v ?>"><?= $v ?></td><?php endforeach ?>
      <?php foreach ($b['tot'] as $v): ?><td><b><?= $v ?></b></td><?php endforeach ?><td><b style="color:<?= $b['persen'] >= 90 ? 'var(--ok)' : ($b['persen'] >= 75 ? 'var(--warn)' : 'var(--bad)') ?>"><?= $b['persen'] ?>%</b></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<p class="muted small mt">Persentase = (hadir + terlambat + dinas) ÷ hari kerja yang sudah berlalu. Hari kerja tanpa data dihitung alpa.</p>
<?= $this->endSection() ?>
