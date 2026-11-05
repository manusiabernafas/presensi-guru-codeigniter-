<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<?php $hk = explode(',', $set['hari_kerja']); $kiosk = site_url('kiosk/' . $set['kiosk_key']); ?>
<div class="head"><div><h1>Pengaturan</h1><p>Identitas sekolah, jam kerja, dan cara absen.</p></div></div>
<div class="card mb" style="border-left:6px solid var(--kuning)">
  <div class="card-h"><div><h2>Layar kiosk QR</h2><p class="muted small" style="margin:4px 0 0">Buka alamat ini di TV/laptop dekat gerbang. Siapa pun yang tahu alamatnya bisa melihat QR, jadi jangan dibagikan.</p></div>
    <div class="row"><a class="btn kuning" href="<?= $kiosk ?>" target="_blank" rel="noopener"><?= ikon('monitor') ?>Buka kiosk</a>
      <form method="post" action="<?= site_url('admin/pengaturan/kiosk') ?>" data-konfirmasi="Ganti alamat kiosk? Alamat lama tidak bisa dipakai lagi."><?= csrf_field() ?><button class="btn ghost">Ganti alamat</button></form></div></div>
  <input class="mt" type="text" readonly value="<?= esc($kiosk) ?>" onclick="this.select()" aria-label="Alamat kiosk">
</div>
<form method="post" action="<?= site_url('admin/pengaturan') ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="grid g2">
    <div class="card"><h2>Identitas sekolah</h2>
      <div class="field mt"><label for="nama_sekolah">Nama sekolah</label><input id="nama_sekolah" name="nama_sekolah" value="<?= esc(old('nama_sekolah', $set['nama_sekolah'])) ?>" required></div>
      <div class="field"><label for="alamat">Alamat</label><input id="alamat" name="alamat" value="<?= esc(old('alamat', $set['alamat'])) ?>"></div>
      <div class="field"><label for="logo">Logo</label><input type="file" id="logo" name="logo" accept="image/*"><span class="hint">PNG/JPG persegi, maks 1 MB.</span></div>
      <div class="row"><div class="field"><label for="tahun_ajaran">Tahun ajaran</label><input id="tahun_ajaran" name="tahun_ajaran" value="<?= esc(old('tahun_ajaran', $set['tahun_ajaran'])) ?>" placeholder="2026/2027" required></div>
        <div class="field"><label for="semester">Semester</label><select id="semester" name="semester"><option value="1" <?= $set['semester'] === '1' ? 'selected' : '' ?>>1 (Ganjil)</option><option value="2" <?= $set['semester'] === '2' ? 'selected' : '' ?>>2 (Genap)</option></select></div></div>
    </div>
    <div class="card"><h2>Jam kerja</h2>
      <div class="row mt"><div class="field"><label for="jam_masuk">Jam masuk</label><input type="time" id="jam_masuk" name="jam_masuk" value="<?= esc(old('jam_masuk', $set['jam_masuk'])) ?>" required></div>
        <div class="field"><label for="toleransi_menit">Toleransi (menit)</label><input type="number" id="toleransi_menit" name="toleransi_menit" min="0" max="120" value="<?= esc(old('toleransi_menit', $set['toleransi_menit'])) ?>" required></div>
        <div class="field"><label for="jam_pulang">Jam pulang</label><input type="time" id="jam_pulang" name="jam_pulang" value="<?= esc(old('jam_pulang', $set['jam_pulang'])) ?>" required></div></div>
      <p class="hint" style="margin:6px 0 0">Absen setelah jam masuk + toleransi dicatat terlambat.</p>
      <div class="field mt"><label>Hari kerja</label><div><?php foreach (HARI_ID as $n => $h): ?><label class="cek"><input type="checkbox" name="hari_kerja[]" value="<?= $n ?>" <?= in_array((string) $n, $hk, true) ? 'checked' : '' ?>><?= $h ?></label><?php endforeach ?></div></div>
    </div>
    <div class="card"><h2>Cara absen</h2>
      <label class="cek mt" style="display:flex"><input type="checkbox" name="metode_qr" <?= $set['metode_qr'] === '1' ? 'checked' : '' ?>> Scan QR / kode di layar kiosk</label>
      <label class="cek" style="display:flex"><input type="checkbox" name="metode_lokasi" <?= $set['metode_lokasi'] === '1' ? 'checked' : '' ?>> Lokasi GPS dalam radius sekolah <span class="muted small">(butuh HTTPS)</span></label>
    </div>
    <div class="card"><h2>Lokasi sekolah</h2>
      <div class="row mt"><div class="field"><label for="lat">Latitude</label><input id="lat" name="lat" inputmode="decimal" value="<?= esc(old('lat', $set['lat'])) ?>" required></div><div class="field"><label for="lng">Longitude</label><input id="lng" name="lng" inputmode="decimal" value="<?= esc(old('lng', $set['lng'])) ?>" required></div>
        <div class="field"><label for="radius_m">Radius (m)</label><input type="number" id="radius_m" name="radius_m" min="20" max="5000" value="<?= esc(old('radius_m', $set['radius_m'])) ?>" required></div></div>
      <button type="button" class="btn sm ghost mt" id="ambilLokasi"><?= ikon('pin') ?>Pakai lokasi saya sekarang</button>
      <p class="hint" style="margin:8px 0 0">Tip: buka Google Maps, klik kanan titik sekolah, salin angka koordinatnya.</p>
    </div>
  </div>
  <button class="btn lg mt">Simpan pengaturan</button>
</form>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>
document.getElementById('ambilLokasi').addEventListener('click', e => {
  if (!navigator.geolocation || !window.isSecureContext) { alert('Lokasi browser butuh HTTPS. Isi koordinat secara manual.'); return; }
  navigator.geolocation.getCurrentPosition(p => { lat.value = p.coords.latitude.toFixed(6); lng.value = p.coords.longitude.toFixed(6); }, () => alert('Lokasi tidak bisa dibaca.'));
});
</script>
<?= $this->endSection() ?>
