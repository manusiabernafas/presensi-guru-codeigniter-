<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<?php $sudahMasuk = $hariIni && $hariIni['jam_masuk']; $sudahPulang = $hariIni && $hariIni['jam_pulang']; $izin = $hariIni && in_array($hariIni['status'], ['izin','sakit','dinas'], true); $aksi = $sudahMasuk ? 'pulang' : 'masuk'; ?>
<div class="grid g-side">
  <div>
    <div class="absen-card">
      <div class="garis" aria-hidden="true"></div>
      <div style="position:relative">
        <div class="small" style="color:#c9d5ef;font-weight:600"><?= tgl_indo(date('Y-m-d'), true) ?></div>
        <div class="jam-besar" id="jamNow"><?= date('H:i') ?></div>
        <div class="info">
          <span>Jam masuk <?= esc($set['jam_masuk']) ?></span><span>Toleransi <?= esc($set['toleransi_menit']) ?> menit</span><span>Pulang <?= esc($set['jam_pulang']) ?></span>
        </div>
      </div>
    </div>

    <div class="card mt">
      <?php if ($libur): ?>
        <h2><?= esc($libur) ?></h2><p class="muted">Tidak perlu absen hari ini. Selamat beristirahat.</p>
      <?php elseif ($izin): ?>
        <?php [$lb] = status_label($hariIni['status']); ?>
        <h2>Hari ini Anda tercatat <?= strtolower($lb) ?></h2><p class="muted"><?= esc($hariIni['keterangan']) ?></p>
      <?php elseif ($sudahPulang): ?>
        <h2>Presensi hari ini lengkap</h2>
        <p class="muted">Masuk <?= jam($hariIni['jam_masuk']) ?> · pulang <?= jam($hariIni['jam_pulang']) ?>. Terima kasih!</p>
      <?php else: ?>
        <div class="card-h"><h2><?= $sudahMasuk ? 'Absen pulang' : 'Absen masuk' ?></h2>
          <?php if ($sudahMasuk): ?><span class="badge <?= status_label($hariIni['status'])[1] ?>">Masuk <?= jam($hariIni['jam_masuk']) ?></span><?php endif ?></div>
        <?php if ($set['metode_qr'] === '1'): ?>
          <p class="muted" style="margin-top:0">Scan QR di layar kiosk dengan kamera HP, atau ketik kode 6 angka yang tampil di bawah QR.</p>
          <form method="post" action="<?= site_url('guru/absen') ?>" class="row">
            <?= csrf_field() ?><input type="hidden" name="metode" value="kode"><input type="hidden" name="aksi" value="<?= $aksi ?>">
            <div class="field" style="flex:1 1 200px"><label for="kode">Kode kiosk</label><input id="kode" name="kode" class="kode-input" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}" placeholder="000000" required></div>
            <button class="btn lg"><?= ikon($sudahMasuk ? 'keluar' : 'masuk') ?>Absen <?= $aksi ?></button>
          </form>
        <?php endif ?>
        <?php if ($set['metode_lokasi'] === '1'): ?>
          <form method="post" action="<?= site_url('guru/absen') ?>" id="formLokasi" class="mt">
            <?= csrf_field() ?><input type="hidden" name="metode" value="lokasi"><input type="hidden" name="aksi" value="<?= $aksi ?>"><input type="hidden" name="lat"><input type="hidden" name="lng">
            <button type="button" class="btn kuning blok lg" id="btnLokasi"><?= ikon('pin') ?>Absen <?= $aksi ?> dengan lokasi</button>
            <p class="muted small" id="lokasiInfo" style="margin:8px 0 0">Hanya bisa di dalam radius <?= esc($set['radius_m']) ?> m dari sekolah.</p>
          </form>
        <?php endif ?>
      <?php endif ?>
    </div>
  </div>

  <div>
    <div class="card">
      <h2>Bulan ini</h2>
      <div style="display:flex;align-items:baseline;gap:8px;margin-top:6px"><b style="font-family:Montserrat;font-size:44px"><?= $ringkas['persen'] ?>%</b><span class="muted">kehadiran dari <?= $ringkas['kerja'] ?> hari kerja</span></div>
      <div class="grid g3 tetap mt" style="gap:10px">
        <div class="stat"><div><b><?= (int) $ringkas['hadir'] ?></b><span>Tepat waktu</span></div></div>
        <div class="stat"><div><b><?= (int) $ringkas['terlambat'] ?></b><span>Terlambat</span></div></div>
        <div class="stat"><div><b><?= (int) $ringkas['izin'] + (int) $ringkas['sakit'] + (int) $ringkas['dinas'] ?></b><span>Izin/sakit/dinas</span></div></div>
      </div>
      <a class="btn ghost blok mt" href="<?= site_url('guru/riwayat') ?>"><?= ikon('kalender') ?>Lihat riwayat</a>
    </div>
    <?php if ($izinMenunggu): ?><div class="alert info mt"><?= $izinMenunggu ?> pengajuan izin masih menunggu persetujuan.</div><?php endif ?>
    <?php if ($ekskulHariIni): ?>
      <div class="card mt"><h2>Ekskul binaan hari ini</h2>
        <?php foreach ($ekskulHariIni as $e): ?>
          <div class="card-h mt"><div><b><?= esc($e['nama']) ?></b><div class="muted small"><?= jam($e['jam_mulai']) ?>–<?= jam($e['jam_selesai']) ?> · <?= esc($e['tempat']) ?></div></div><a class="btn sm" href="<?= site_url('ekskul/' . $e['id']) ?>">Isi presensi</a></div>
        <?php endforeach ?>
      </div>
    <?php endif ?>
    <div class="card mt"><h2>7 hari terakhir</h2>
      <?php if (! $terbaru): ?><p class="muted">Belum ada riwayat.</p><?php endif ?>
      <?php foreach ($terbaru as $r): [$lb, $cls] = status_label($r['status']); ?>
        <div class="card-h" style="padding:8px 0;border-bottom:1px solid var(--line)"><span><?= tgl_indo($r['tanggal'], true) ?></span><span class="nowrap"><span class="muted small"><?= jam($r['jam_masuk']) ?></span> <span class="badge <?= $cls ?>"><?= $lb ?></span></span></div>
      <?php endforeach ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>
setInterval(() => { const n = new Date(); document.getElementById('jamNow').textContent = String(n.getHours()).padStart(2,'0') + ':' + String(n.getMinutes()).padStart(2,'0'); }, 5000);
const b = document.getElementById('btnLokasi');
if (b) b.addEventListener('click', () => {
  const info = document.getElementById('lokasiInfo'), f = document.getElementById('formLokasi');
  if (!navigator.geolocation) { info.textContent = 'Browser ini tidak mendukung lokasi.'; return; }
  if (!window.isSecureContext) { info.textContent = 'Absen lokasi butuh alamat HTTPS. Gunakan kode kiosk, atau minta admin memasang HTTPS.'; return; }
  b.disabled = true; info.textContent = 'Membaca lokasi...';
  navigator.geolocation.getCurrentPosition(p => {
    f.lat.value = p.coords.latitude; f.lng.value = p.coords.longitude; f.submit();
  }, err => { b.disabled = false; info.textContent = err.code === 1 ? 'Akses lokasi ditolak. Izinkan lokasi di pengaturan browser lalu coba lagi.' : 'Lokasi belum terbaca, coba lagi di tempat terbuka.'; }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
});
</script>
<?= $this->endSection() ?>
