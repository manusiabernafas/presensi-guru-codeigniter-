<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Dasbor presensi</h1><p><?= tgl_indo(date('Y-m-d'), true) ?><?= $libur ? ' · ' . esc($libur) : '' ?></p></div>
  <div class="row"><a class="btn kuning" href="<?= site_url('kiosk/' . $set['kiosk_key']) ?>" target="_blank" rel="noopener"><?= ikon('monitor') ?>Buka layar kiosk QR</a><a class="btn ghost" href="<?= site_url('admin/rekap') ?>"><?= ikon('rekap') ?>Rekap bulanan</a></div></div>
<div class="grid g4 stats">
  <div class="card stat"><div class="ic t-ok"><?= ikon('cek') ?></div><div><b><?= $c['hadir'] ?></b><span>Hadir tepat waktu</span></div></div>
  <div class="card stat"><div class="ic t-warn"><?= ikon('jam') ?></div><div><b><?= $c['terlambat'] ?></b><span>Terlambat</span></div></div>
  <div class="card stat"><div class="ic t-info"><?= ikon('surat') ?></div><div><b><?= $c['izin'] ?></b><span>Izin / sakit / dinas</span></div></div>
  <div class="card stat"><div class="ic t-bad"><?= ikon('alert') ?></div><div><b><?= $libur ? 0 : count($belum) ?></b><span>Belum absen dari <?= $total ?></span></div></div>
</div>
<div class="grid g-side mt">
  <div class="card">
    <div class="card-h"><h2>Kehadiran 10 hari kerja terakhir</h2><span class="muted small"><?= $total ?> guru aktif</span></div>
    <?php $max = max(1, $total); ?>
    <div class="bars" role="img" aria-label="Grafik kehadiran guru 10 hari kerja terakhir">
      <?php foreach ($grafik as $g): ?>
        <div class="b" title="<?= tgl_indo($g['tgl']) ?>: hadir <?= $g['hadir'] ?>, terlambat <?= $g['terlambat'] ?>, izin <?= $g['izin'] ?>">
          <div class="stack"><i style="height:<?= 100 * $g['hadir'] / $max ?>%;background:var(--ok)"></i><i style="height:<?= 100 * $g['terlambat'] / $max ?>%;background:var(--kuning)"></i><i style="height:<?= 100 * $g['izin'] / $max ?>%;background:#8fb0ec"></i></div>
          <small><?= date('j/n', strtotime($g['tgl'])) ?></small>
        </div>
      <?php endforeach ?>
    </div>
    <div class="legend"><span><i style="background:var(--ok)"></i>Tepat waktu</span><span><i style="background:var(--kuning)"></i>Terlambat</span><span><i style="background:#8fb0ec"></i>Izin/sakit/dinas</span></div>
  </div>
  <div class="card">
    <div class="card-h"><h2>Belum absen hari ini</h2><a class="small" href="<?= site_url('admin/presensi') ?>">Kelola</a></div>
    <?php if ($libur): ?><p class="muted"><?= esc($libur) ?></p>
    <?php elseif (! $belum): ?><p class="muted">Semua guru sudah tercatat. Mantap!</p>
    <?php else: foreach (array_slice($belum, 0, 8) as $g): ?>
      <div class="orang" style="padding:6px 0"><div class="ava"><?= esc(inisial($g['nama'])) ?></div><div><b class="small"><?= esc($g['nama']) ?></b><div class="muted small"><?= esc($g['mapel'] ?: $g['jabatan']) ?></div></div></div>
    <?php endforeach; if (count($belum) > 8): ?><p class="muted small">+<?= count($belum) - 8 ?> lainnya</p><?php endif; endif ?>
  </div>
</div>
<div class="grid g3 mt">
  <div class="card"><div class="card-h"><h2>Pengajuan menunggu</h2><a class="small" href="<?= site_url('admin/izin') ?>">Semua</a></div>
    <?php if (! $izin): ?><p class="muted">Tidak ada pengajuan baru.</p><?php endif ?>
    <?php foreach ($izin as $i): ?><div style="padding:8px 0;border-bottom:1px solid var(--line)"><b class="small"><?= esc($i['nama']) ?></b> <span class="badge info"><?= esc($i['jenis']) ?></span><div class="muted small"><?= tgl_indo($i['tgl_mulai']) ?><?= $i['tgl_selesai'] !== $i['tgl_mulai'] ? ' – ' . tgl_indo($i['tgl_selesai']) : '' ?></div></div><?php endforeach ?>
  </div>
  <div class="card"><h2>Ekskul hari ini</h2>
    <?php if (! $ekskul): ?><p class="muted mt">Tidak ada jadwal ekskul hari ini.</p><?php endif ?>
    <?php foreach ($ekskul as $e): ?><div class="card-h" style="padding:8px 0;border-bottom:1px solid var(--line)"><div><b class="small"><?= esc($e['nama']) ?></b><div class="muted small"><?= jam($e['jam_mulai']) ?> · <?= esc($e['pembina'] ?? '-') ?></div></div><?= $e['tercatat'] ? '<span class="badge ok">Tercatat</span>' : '<span class="badge muted">Belum</span>' ?></div><?php endforeach ?>
  </div>
  <div class="card"><h2>Prestasi terbaru</h2>
    <?php if (! $prestasi): ?><p class="muted mt">Belum ada prestasi.</p><?php endif ?>
    <?php foreach ($prestasi as $p): ?><div style="padding:8px 0;border-bottom:1px solid var(--line)"><b class="small"><?= esc($p['peringkat']) ?> · <?= esc($p['judul']) ?></b><div class="muted small"><?= esc($p['ekskul']) ?> · <?= esc($p['tingkat']) ?></div></div><?php endforeach ?>
  </div>
</div>
<?php if ($terlambat): ?>
<div class="card mt"><h2>Terlambat hari ini</h2>
  <div class="tbl-wrap mt"><table class="tbl"><thead><tr><th>Nama</th><th>Jam masuk</th><th>Terlambat</th></tr></thead><tbody>
  <?php foreach ($terlambat as $t): ?><tr><td><?= esc($t['nama']) ?></td><td><?= jam($t['p']['jam_masuk']) ?></td><td><span class="badge warn"><?= (int) $t['p']['menit_terlambat'] ?> menit</span></td></tr><?php endforeach ?>
  </tbody></table></div></div>
<?php endif ?>
<?= $this->endSection() ?>
