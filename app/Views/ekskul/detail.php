<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head">
  <div><a class="small" href="<?= site_url('ekskul') ?>">&larr; Semua ekskul</a><h1 style="margin-top:6px"><?= esc($e['nama']) ?></h1>
    <p><?= esc($e['hari']) ?>, <?= jam($e['jam_mulai']) ?>–<?= jam($e['jam_selesai']) ?> · <?= esc($e['tempat'] ?: '-') ?> · Pembina <?= esc($e['pembina'] ?? '-') ?> · <?= count($anggota) ?> anggota</p></div>
  <div class="row"><a class="btn ghost" href="<?= site_url('ekskul/' . $e['id'] . '/rekap.csv') ?>"><?= ikon('unduh') ?>CSV</a><a class="btn ghost" href="<?= site_url('ekskul/' . $e['id'] . '/cetak') ?>" target="_blank"><?= ikon('cetak') ?>Cetak</a></div>
</div>
<nav class="tabs">
  <?php foreach (['pertemuan' => 'Pertemuan', 'anggota' => 'Anggota', 'nilai' => 'Nilai rapor', 'prestasi' => 'Prestasi', 'rekap' => 'Rekap kehadiran'] as $k => $l): ?>
    <a class="<?= $tab === $k ? 'on' : '' ?>" href="<?= site_url('ekskul/' . $e['id'] . '?tab=' . $k) ?>"><?= $l ?></a>
  <?php endforeach ?>
</nav>

<?php if ($tab === 'pertemuan'): ?>
<div class="grid g-side">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tanggal</th><th>Materi / kegiatan</th><th class="center">H</th><th class="center">I</th><th class="center">S</th><th class="center">A</th><th></th></tr></thead>
    <tbody>
    <?php if (! $pertemuan): ?><tr><td colspan="7" class="kosong">Belum ada pertemuan. Buat pertemuan pertama di samping.</td></tr><?php endif ?>
    <?php foreach ($pertemuan as $p): ?>
      <tr><td class="nowrap"><?= tgl_indo($p['tanggal'], true) ?></td><td><?= esc($p['materi']) ?></td>
        <td class="center"><b style="color:var(--ok)"><?= (int) $p['h'] ?></b></td><td class="center"><?= (int) $p['i'] ?></td><td class="center"><?= (int) $p['s'] ?></td><td class="center"><?= (int) $p['a'] ? '<b style="color:var(--bad)">' . (int) $p['a'] . '</b>' : 0 ?></td>
        <td><div class="aksi"><a class="btn sm ghost" href="<?= site_url('ekskul/pertemuan/' . $p['id']) ?>">Presensi</a>
          <form method="post" action="<?= site_url('ekskul/pertemuan/' . $p['id'] . '/hapus') ?>" data-konfirmasi="Hapus pertemuan ini beserta presensinya?"><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></div></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <form class="card" method="post" action="<?= site_url('ekskul/' . $e['id'] . '/pertemuan') ?>" novalidate>
    <?= csrf_field() ?><h2>Pertemuan baru</h2>
    <?php if (! $anggota): ?><div class="alert info mt">Tambahkan anggota dulu di tab Anggota.</div><?php endif ?>
    <div class="field mt"><label for="tanggal">Tanggal</label><input type="date" id="tanggal" name="tanggal" value="<?= esc(old('tanggal') ?: date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>" required></div>
    <div class="field"><label for="materi">Materi / kegiatan</label><input id="materi" name="materi" maxlength="200" value="<?= esc(old('materi')) ?>" placeholder="Contoh: Latihan baris-berbaris" required></div>
    <button class="btn blok" <?= $anggota ? '' : 'disabled' ?>><?= ikon('plus') ?>Buat & isi presensi</button>
  </form>
</div>

<?php elseif ($tab === 'anggota'): ?>
<div class="grid g-side">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th></th></tr></thead>
    <tbody>
    <?php if (! $anggota): ?><tr><td colspan="4" class="kosong">Belum ada anggota.</td></tr><?php endif ?>
    <?php foreach ($anggota as $a): ?>
      <tr><td><div class="orang"><div class="ava"><?= esc(inisial($a['nama'])) ?></div><?= esc($a['nama']) ?></div></td><td><?= esc($a['nis']) ?></td><td><?= esc($a['kelas'] ?? '-') ?></td>
        <td><form method="post" action="<?= site_url('ekskul/' . $e['id'] . '/anggota/' . $a['id'] . '/hapus') ?>" data-konfirmasi="Keluarkan <?= esc($a['nama'], 'attr') ?> dari ekskul?"><?= csrf_field() ?><button class="btn sm danger">Keluarkan</button></form></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <div class="card"><h2>Tambah anggota</h2>
    <form method="get" class="row mt"><input type="hidden" name="tab" value="anggota"><div class="field"><label for="kelas">Pilih kelas</label><select id="kelas" name="kelas" onchange="this.form.submit()"><option value="">- pilih kelas -</option><?php foreach ($kelas as $k): ?><option value="<?= $k['id'] ?>" <?= $kelasPilih == $k['id'] ? 'selected' : '' ?>><?= esc($k['nama']) ?></option><?php endforeach ?></select></div></form>
    <?php if ($kelasPilih): ?>
      <form method="post" action="<?= site_url('ekskul/' . $e['id'] . '/anggota') ?>" class="mt">
        <?= csrf_field() ?><input type="hidden" name="kelas" value="<?= (int) $kelasPilih ?>">
        <?php if (! $calon): ?><p class="muted">Semua siswa kelas ini sudah menjadi anggota.</p><?php else: ?>
          <label class="cek"><input type="checkbox" onclick="document.querySelectorAll('.calon').forEach(c => c.checked = this.checked)"> Pilih semua</label>
          <div style="max-height:320px;overflow:auto;border:1px solid var(--line);border-radius:12px;padding:8px 12px;margin:8px 0 12px">
            <?php foreach ($calon as $c): ?><label class="cek" style="display:flex"><input type="checkbox" class="calon" name="siswa[]" value="<?= $c['id'] ?>"><?= esc($c['nama']) ?> <span class="muted small"><?= esc($c['nis']) ?></span></label><?php endforeach ?>
          </div>
          <button class="btn blok"><?= ikon('plus') ?>Tambahkan</button>
        <?php endif ?>
      </form>
    <?php endif ?>
  </div>
</div>

<?php elseif ($tab === 'nilai'): ?>
<form method="post" action="<?= site_url('ekskul/' . $e['id'] . '/nilai') ?>">
  <?= csrf_field() ?>
  <div class="card-h mb"><p class="muted" style="margin:0">Tahun ajaran <b><?= esc($ta) ?></b> semester <b><?= $sm ?></b>. Saran nilai dihitung dari persentase kehadiran (A ≥ 90%, B ≥ 75%, C ≥ 60%).</p>
    <button type="button" class="btn ghost sm" onclick="document.querySelectorAll('select[data-saran]').forEach(s => { if (!s.value) s.value = s.dataset.saran; })">Isi otomatis dari saran</button></div>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Nama</th><th>Kelas</th><th>Kehadiran</th><th>Nilai</th><th>Deskripsi untuk rapor</th></tr></thead>
    <tbody>
    <?php if (! $anggota): ?><tr><td colspan="5" class="kosong">Belum ada anggota.</td></tr><?php endif ?>
    <?php foreach ($anggota as $a): $n = $nilai[$a['id']] ?? null; $rk = $rekap[$a['id']] ?? ['persen' => 0, 'saran' => 'D', 'total' => 0]; ?>
      <tr><td><?= esc($a['nama']) ?></td><td><?= esc($a['kelas'] ?? '-') ?></td>
        <td class="nowrap"><?= $rk['total'] ? $rk['persen'] . '%' : '-' ?> <span class="muted small">(saran <?= $rk['saran'] ?>)</span></td>
        <td><select name="nilai[<?= $a['id'] ?>]" data-saran="<?= $rk['saran'] ?>" aria-label="Nilai <?= esc($a['nama'], 'attr') ?>" style="width:90px"><option value="">-</option><?php foreach (['A', 'B', 'C', 'D'] as $v): ?><option <?= ($n['nilai'] ?? '') === $v ? 'selected' : '' ?>><?= $v ?></option><?php endforeach ?></select></td>
        <td><input name="deskripsi[<?= $a['id'] ?>]" maxlength="200" value="<?= esc($n['deskripsi'] ?? '') ?>" placeholder="Aktif dan disiplin mengikuti latihan" aria-label="Deskripsi <?= esc($a['nama'], 'attr') ?>"></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <?php if ($anggota): ?><button class="btn mt">Simpan nilai</button><?php endif ?>
</form>

<?php elseif ($tab === 'prestasi'): ?>
<div class="grid g-side">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tanggal</th><th>Lomba / kegiatan</th><th>Tingkat</th><th>Peringkat</th><th></th></tr></thead>
    <tbody>
    <?php if (! $prestasi): ?><tr><td colspan="5" class="kosong">Belum ada prestasi tercatat.</td></tr><?php endif ?>
    <?php foreach ($prestasi as $p): ?>
      <tr><td class="nowrap"><?= tgl_indo($p['tanggal']) ?></td><td><b><?= esc($p['judul']) ?></b><?php if ($p['peserta']): ?><div class="muted small"><?= esc($p['peserta']) ?></div><?php endif ?></td><td><?= esc($p['tingkat']) ?></td><td><span class="badge warn"><?= ikon('trofi') ?><?= esc($p['peringkat']) ?></span></td>
        <td><form method="post" action="<?= site_url('ekskul/prestasi/' . $p['id'] . '/hapus') ?>" data-konfirmasi="Hapus prestasi ini?"><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <form class="card" method="post" action="<?= site_url('ekskul/' . $e['id'] . '/prestasi') ?>" novalidate>
    <?= csrf_field() ?><h2>Catat prestasi</h2>
    <div class="field mt"><label for="judul">Nama lomba / kegiatan</label><input id="judul" name="judul" maxlength="150" value="<?= esc(old('judul')) ?>" required></div>
    <div class="field"><label for="peserta">Peserta</label><input id="peserta" name="peserta" maxlength="150" value="<?= esc(old('peserta')) ?>" placeholder="Nama siswa / tim"></div>
    <div class="row"><div class="field"><label for="tingkat">Tingkat</label><select id="tingkat" name="tingkat"><?php foreach (['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'] as $t): ?><option <?= old('tingkat') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach ?></select></div>
      <div class="field"><label for="peringkat">Peringkat</label><input id="peringkat" name="peringkat" maxlength="40" value="<?= esc(old('peringkat')) ?>" placeholder="Juara 1" required></div></div>
    <div class="field mt"><label for="tgl">Tanggal</label><input type="date" id="tgl" name="tanggal" value="<?= esc(old('tanggal') ?: date('Y-m-d')) ?>" required></div>
    <button class="btn blok"><?= ikon('trofi') ?>Simpan prestasi</button>
  </form>
</div>

<?php else: ?>
<p class="muted">Total <?= $jmlPertemuan ?> pertemuan.</p>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nama</th><th>Kelas</th><th class="center">Hadir</th><th class="center">Izin</th><th class="center">Sakit</th><th class="center">Alpa</th><th>Persentase</th></tr></thead>
  <tbody>
  <?php if (! $rekap): ?><tr><td colspan="7" class="kosong">Belum ada data.</td></tr><?php endif ?>
  <?php foreach ($rekap as $r): ?>
    <tr><td><?= esc($r['nama']) ?></td><td><?= esc($r['kelas'] ?? '-') ?></td><td class="center"><?= (int) $r['h'] ?></td><td class="center"><?= (int) $r['i'] ?></td><td class="center"><?= (int) $r['s_'] ?></td><td class="center"><?= (int) $r['a'] ?></td>
      <td style="min-width:160px"><div style="display:flex;align-items:center;gap:8px"><div style="flex:1;height:8px;background:#eef1f6;border-radius:99px;overflow:hidden"><i style="display:block;height:100%;width:<?= $r['persen'] ?>%;background:<?= $r['persen'] >= 75 ? 'var(--ok)' : ($r['persen'] >= 60 ? 'var(--warn)' : 'var(--bad)') ?>"></i></div><b class="small"><?= $r['persen'] ?>%</b></div></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<?php endif ?>
<?= $this->endSection() ?>
