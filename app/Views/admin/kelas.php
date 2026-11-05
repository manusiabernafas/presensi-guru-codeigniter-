<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Kelas</h1><p><?= count($daftar) ?> kelas</p></div><button type="button" class="btn" data-buka="dlg" onclick="isi({})"><?= ikon('plus') ?>Tambah kelas</button></div>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Kelas</th><th>Tingkat</th><th>Wali kelas</th><th>Jumlah siswa</th><th></th></tr></thead>
  <tbody>
  <?php if (! $daftar): ?><tr><td colspan="5" class="kosong">Belum ada kelas.</td></tr><?php endif ?>
  <?php foreach ($daftar as $k): ?>
    <tr><td><b><?= esc($k['nama']) ?></b></td><td><?= (int) $k['tingkat'] ?></td><td><?= esc($k['wali'] ?? '-') ?></td><td><a href="<?= site_url('admin/siswa?kelas=' . $k['id']) ?>"><?= (int) $k['jumlah'] ?> siswa</a></td>
      <td><div class="aksi"><button type="button" class="btn sm ghost" data-buka="dlg" onclick='isi(<?= json_encode(['id' => $k['id'], 'nama' => $k['nama'], 'tingkat' => $k['tingkat'], 'wali_id' => $k['wali_id']], JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><?= ikon('edit') ?>Ubah</button>
        <form method="post" action="<?= site_url('admin/kelas/' . $k['id'] . '/hapus') ?>" data-konfirmasi="Hapus kelas <?= esc($k['nama'], 'attr') ?>? Siswanya tidak terhapus."><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></div></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<dialog id="dlg"><form class="dlg" method="post" action="<?= site_url('admin/kelas') ?>" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" id="fId">
  <div class="dlg-h"><h2>Data kelas</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <div class="row"><div class="field"><label for="fNama">Nama kelas</label><input id="fNama" name="nama" maxlength="30" placeholder="X IPA 1" required></div><div class="field"><label for="fTk">Tingkat</label><input type="number" id="fTk" name="tingkat" min="1" max="12" required></div></div>
  <div class="field mt"><label for="fWali">Wali kelas</label><select id="fWali" name="wali_id"><option value="">- belum ada -</option><?php foreach ($guru as $g): ?><option value="<?= $g['id'] ?>"><?= esc($g['nama']) ?></option><?php endforeach ?></select></div>
  <button class="btn blok">Simpan</button>
</form></dialog>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>function isi(k) { fId.value = k.id || ''; fNama.value = k.nama || ''; fTk.value = k.tingkat || 10; fWali.value = k.wali_id || ''; }</script>
<?= $this->endSection() ?>
