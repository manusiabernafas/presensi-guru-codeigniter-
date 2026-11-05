<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Izin, sakit, dan dinas luar</h1><p>Ajukan dari HP. Setelah disetujui admin, presensi hari itu terisi otomatis.</p></div></div>
<div class="grid g-side">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tanggal</th><th>Jenis</th><th>Alasan</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (! $daftar): ?><tr><td colspan="5" class="kosong">Belum ada pengajuan.</td></tr><?php endif ?>
    <?php foreach ($daftar as $d): ?>
      <tr><td class="nowrap"><?= tgl_indo($d['tgl_mulai']) ?><?= $d['tgl_selesai'] !== $d['tgl_mulai'] ? '<br><span class="muted small">s.d. ' . tgl_indo($d['tgl_selesai']) . '</span>' : '' ?></td>
        <td><?= esc(ucfirst($d['jenis'] === 'dinas' ? 'dinas luar' : $d['jenis'])) ?></td>
        <td><?= esc($d['alasan']) ?><?php if ($d['lampiran']): ?><br><a class="small" href="<?= site_url('izin/' . $d['id'] . '/lampiran') ?>" target="_blank" rel="noopener">Lihat lampiran</a><?php endif ?>
          <?php if ($d['catatan_admin']): ?><div class="small muted">Catatan admin: <?= esc($d['catatan_admin']) ?></div><?php endif ?></td>
        <td><span class="badge <?= ['menunggu' => 'warn', 'disetujui' => 'ok', 'ditolak' => 'bad'][$d['status']] ?>"><?= ucfirst($d['status']) ?></span></td>
        <td><?php if ($d['status'] === 'menunggu'): ?><form method="post" action="<?= site_url('guru/izin/' . $d['id'] . '/batal') ?>" data-konfirmasi="Batalkan pengajuan ini?"><?= csrf_field() ?><button class="btn danger sm">Batal</button></form><?php endif ?></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <form class="card" method="post" action="<?= site_url('guru/izin') ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?><h2>Pengajuan baru</h2>
    <div class="field mt"><label for="jenis">Jenis</label><select id="jenis" name="jenis"><?php foreach (['izin' => 'Izin', 'sakit' => 'Sakit', 'dinas' => 'Dinas luar'] as $v => $l): ?><option value="<?= $v ?>" <?= old('jenis') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select></div>
    <div class="row"><div class="field"><label for="tgl_mulai">Mulai</label><input type="date" id="tgl_mulai" name="tgl_mulai" value="<?= esc(old('tgl_mulai') ?: date('Y-m-d')) ?>" required></div>
      <div class="field"><label for="tgl_selesai">Sampai</label><input type="date" id="tgl_selesai" name="tgl_selesai" value="<?= esc(old('tgl_selesai') ?: date('Y-m-d')) ?>" required></div></div>
    <div class="field mt"><label for="alasan">Alasan</label><textarea id="alasan" name="alasan" maxlength="300" required placeholder="Contoh: Mengantar anak periksa ke puskesmas"><?= esc(old('alasan')) ?></textarea></div>
    <div class="field"><label for="lampiran">Lampiran <span class="muted small">(opsional)</span></label><input type="file" id="lampiran" name="lampiran" accept=".jpg,.jpeg,.png,.pdf"><span class="hint">Surat dokter / surat tugas. JPG, PNG, atau PDF maks 2 MB.</span></div>
    <button class="btn blok">Kirim pengajuan</button>
  </form>
</div>
<?= $this->endSection() ?>
