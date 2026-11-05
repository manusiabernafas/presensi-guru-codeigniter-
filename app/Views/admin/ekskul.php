<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Kelola ekstrakurikuler</h1><p>Atur nama, pembina, dan jadwal. Kegiatan harian diisi pembina di menu Kegiatan ekskul.</p></div><button type="button" class="btn" data-buka="dlg" onclick="isi({})"><?= ikon('plus') ?>Tambah ekskul</button></div>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Ekskul</th><th>Pembina</th><th>Jadwal</th><th>Anggota</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (! $daftar): ?><tr><td colspan="6" class="kosong">Belum ada ekskul.</td></tr><?php endif ?>
  <?php foreach ($daftar as $e): ?>
    <tr><td><div class="orang"><span style="width:12px;height:36px;border-radius:6px;background:<?= esc($e['warna']) ?>"></span><div><b><?= esc($e['nama']) ?></b><div class="muted small"><?= esc($e['tempat'] ?: '-') ?></div></div></div></td>
      <td><?= esc($e['pembina'] ?? '-') ?></td><td class="nowrap"><?= esc($e['hari']) ?>, <?= jam($e['jam_mulai']) ?>–<?= jam($e['jam_selesai']) ?></td><td><a href="<?= site_url('ekskul/' . $e['id'] . '?tab=anggota') ?>"><?= (int) $e['anggota'] ?></a></td>
      <td><?= $e['aktif'] ? '<span class="badge ok">Aktif</span>' : '<span class="badge muted">Nonaktif</span>' ?></td>
      <td><div class="aksi"><a class="btn sm ghost" href="<?= site_url('ekskul/' . $e['id']) ?>">Buka</a><button type="button" class="btn sm ghost" data-buka="dlg" onclick='isi(<?= json_encode(array_intersect_key($e, array_flip(['id', 'nama', 'deskripsi', 'pembina_id', 'hari', 'jam_mulai', 'jam_selesai', 'tempat', 'warna', 'aktif'])), JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><?= ikon('edit') ?>Ubah</button>
        <form method="post" action="<?= site_url('admin/ekskul/' . $e['id'] . '/hapus') ?>" data-konfirmasi="Hapus <?= esc($e['nama'], 'attr') ?> beserta anggota, presensi, nilai, dan prestasinya?"><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form></div></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<dialog id="dlg"><form class="dlg" method="post" action="<?= site_url('admin/ekskul') ?>" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" id="fId">
  <div class="dlg-h"><h2>Data ekskul</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <div class="row"><div class="field" style="flex:3 1 200px"><label for="fNama">Nama ekskul</label><input id="fNama" name="nama" maxlength="80" required></div><div class="field" style="flex:1 1 80px"><label for="fWarna">Warna</label><input type="color" id="fWarna" name="warna" value="#1849a9" style="height:44px;padding:4px"></div></div>
  <div class="field mt"><label for="fDesk">Deskripsi</label><input id="fDesk" name="deskripsi" maxlength="300"></div>
  <div class="field"><label for="fPemb">Pembina</label><select id="fPemb" name="pembina_id" required><option value="">- pilih guru -</option><?php foreach ($guru as $g): ?><option value="<?= $g['id'] ?>"><?= esc($g['nama']) ?></option><?php endforeach ?></select></div>
  <div class="row"><div class="field"><label for="fHari">Hari</label><select id="fHari" name="hari"><?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h): ?><option><?= $h ?></option><?php endforeach ?></select></div>
    <div class="field"><label for="fMulai">Mulai</label><input type="time" id="fMulai" name="jam_mulai" required></div><div class="field"><label for="fSelesai">Selesai</label><input type="time" id="fSelesai" name="jam_selesai" required></div></div>
  <div class="field mt"><label for="fTempat">Tempat</label><input id="fTempat" name="tempat" maxlength="80" placeholder="Lapangan / aula / lab"></div>
  <label class="cek"><input type="checkbox" name="aktif" id="fAktif" checked> Aktif</label>
  <button class="btn blok mt">Simpan</button>
</form></dialog>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>function isi(e) { fId.value = e.id || ''; fNama.value = e.nama || ''; fDesk.value = e.deskripsi || ''; fPemb.value = e.pembina_id || ''; fHari.value = e.hari || 'Jumat'; fMulai.value = (e.jam_mulai || '14:30').slice(0, 5); fSelesai.value = (e.jam_selesai || '16:00').slice(0, 5); fTempat.value = e.tempat || ''; fWarna.value = e.warna || '#1849a9'; fAktif.checked = e.id ? e.aktif == 1 : true; }</script>
<?= $this->endSection() ?>
