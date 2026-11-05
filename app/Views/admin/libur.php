<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Hari libur</h1><p>Tanggal di sini tidak dihitung sebagai hari kerja dan tidak ada absen.</p></div>
  <form class="row" method="get"><div class="field"><label for="tahun">Tahun</label><input type="number" id="tahun" name="tahun" value="<?= $tahun ?>" min="2020" max="2100"></div><button class="btn ghost">Tampilkan</button></form></div>
<div class="grid g-side">
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Tanggal</th><th>Keterangan</th><th></th></tr></thead>
    <tbody>
    <?php if (! $daftar): ?><tr><td colspan="3" class="kosong">Belum ada hari libur di <?= $tahun ?>.</td></tr><?php endif ?>
    <?php foreach ($daftar as $l): ?><tr><td class="nowrap"><?= tgl_indo($l['tanggal'], true) ?></td><td><?= esc($l['keterangan']) ?></td>
      <td><form method="post" action="<?= site_url('admin/libur/' . $l['id'] . '/hapus') ?>"><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></td></tr><?php endforeach ?>
    </tbody></table></div>
  <form class="card" method="post" action="<?= site_url('admin/libur') ?>" novalidate>
    <?= csrf_field() ?><h2>Tambah libur</h2>
    <div class="row mt"><div class="field"><label for="mulai">Mulai</label><input type="date" id="mulai" name="mulai" value="<?= esc(old('mulai')) ?>" required></div><div class="field"><label for="selesai">Sampai <span class="muted small">(opsional)</span></label><input type="date" id="selesai" name="selesai" value="<?= esc(old('selesai')) ?>"></div></div>
    <div class="field mt"><label for="ket">Keterangan</label><input id="ket" name="keterangan" maxlength="120" value="<?= esc(old('keterangan')) ?>" placeholder="Libur semester ganjil" required></div>
    <button class="btn blok">Simpan</button>
  </form>
</div>
<?= $this->endSection() ?>
