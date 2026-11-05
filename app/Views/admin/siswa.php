<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Siswa</h1><p><?= $total ?> siswa ditemukan</p></div>
  <div class="row"><button type="button" class="btn ghost" data-buka="dlgImpor"><?= ikon('unduh') ?>Impor CSV</button><button type="button" class="btn" data-buka="dlg" onclick="isi({})"><?= ikon('plus') ?>Tambah siswa</button></div></div>
<form class="row mb" method="get">
  <div class="field"><label for="kelas">Kelas</label><select id="kelas" name="kelas"><option value="">Semua kelas</option><?php foreach ($kelas as $k): ?><option value="<?= $k['id'] ?>" <?= $kelasPilih == $k['id'] ? 'selected' : '' ?>><?= esc($k['nama']) ?></option><?php endforeach ?></select></div>
  <div class="field"><label for="q">Cari</label><input type="search" id="q" name="q" value="<?= esc($q) ?>" placeholder="Nama atau NIS"></div><button class="btn ghost">Tampilkan</button></form>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>NIS</th><th>Nama</th><th>L/P</th><th>Kelas</th><th>Ekskul</th><th></th></tr></thead>
  <tbody>
  <?php if (! $daftar): ?><tr><td colspan="6" class="kosong">Tidak ada siswa.</td></tr><?php endif ?>
  <?php foreach ($daftar as $s): ?>
    <tr><td><?= esc($s['nis']) ?></td><td><b><?= esc($s['nama']) ?></b><?= $s['aktif'] ? '' : ' <span class="badge muted">Nonaktif</span>' ?></td><td><?= $s['jk'] ?></td><td><?= esc($s['kelas'] ?? '-') ?></td><td class="small muted"><?= esc($s['ekskul'] ?? '-') ?></td>
      <td><div class="aksi"><button type="button" class="btn sm ghost" data-buka="dlg" onclick='isi(<?= json_encode(array_intersect_key($s, array_flip(['id', 'nis', 'nama', 'jk', 'kelas_id', 'aktif'])), JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><?= ikon('edit') ?>Ubah</button>
        <form method="post" action="<?= site_url('admin/siswa/' . $s['id'] . '/hapus') ?>" data-konfirmasi="Hapus <?= esc($s['nama'], 'attr') ?>?"><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></div></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<?php if ($halMax > 1): ?><div class="row mt" style="justify-content:center">
  <?php for ($i = 1; $i <= $halMax; $i++): ?><a class="btn sm <?= $i === $hal ? '' : 'ghost' ?>" href="?<?= http_build_query(['kelas' => $kelasPilih ?: null, 'q' => $q ?: null, 'hal' => $i]) ?>"><?= $i ?></a><?php endfor ?></div><?php endif ?>
<dialog id="dlg"><form class="dlg" method="post" action="<?= site_url('admin/siswa') ?>" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" id="fId">
  <div class="dlg-h"><h2>Data siswa</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <div class="row"><div class="field"><label for="fNis">NIS</label><input id="fNis" name="nis" maxlength="20" required></div><div class="field"><label for="fJk">Jenis kelamin</label><select id="fJk" name="jk"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div></div>
  <div class="field mt"><label for="fNama">Nama lengkap</label><input id="fNama" name="nama" maxlength="100" required></div>
  <div class="field"><label for="fKelas">Kelas</label><select id="fKelas" name="kelas_id"><option value="">- tanpa kelas -</option><?php foreach ($kelas as $k): ?><option value="<?= $k['id'] ?>"><?= esc($k['nama']) ?></option><?php endforeach ?></select></div>
  <label class="cek"><input type="checkbox" name="aktif" id="fAktif" checked> Siswa aktif</label>
  <button class="btn blok mt">Simpan</button>
</form></dialog>
<dialog id="dlgImpor"><form class="dlg" method="post" action="<?= site_url('admin/siswa/impor') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?><div class="dlg-h"><h2>Impor siswa dari CSV</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <p class="muted" style="margin-top:0">Kolom: <b>nis; nama; jk (L/P); kelas</b>. Dari Excel pilih <i>Simpan sebagai</i> CSV. NIS yang sudah ada akan diperbarui, kelas baru dibuat otomatis.</p>
  <a class="btn sm ghost" href="<?= site_url('admin/siswa/template') ?>"><?= ikon('unduh') ?>Unduh contoh</a>
  <div class="field mt"><label for="berkas">Berkas CSV</label><input type="file" id="berkas" name="berkas" accept=".csv,.txt" required></div>
  <button class="btn blok">Impor</button>
</form></dialog>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>function isi(s) { fId.value = s.id || ''; fNis.value = s.nis || ''; fNama.value = s.nama || ''; fJk.value = s.jk || 'L'; fKelas.value = s.kelas_id || '<?= (int) $kelasPilih ?: '' ?>'; fAktif.checked = s.id ? s.aktif == 1 : true; }</script>
<?= $this->endSection() ?>
