<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Presensi harian</h1><p><?= tgl_indo($tgl, true) ?><?= $libur ? ' · ' . esc($libur) : '' ?></p></div>
  <form class="row" method="get"><div class="field"><label for="tanggal">Tanggal</label><input type="date" id="tanggal" name="tanggal" value="<?= esc($tgl) ?>" max="<?= date('Y-m-d') ?>"></div><button class="btn ghost">Tampilkan</button></form></div>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Guru</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>Cara</th><th>Keterangan</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($guru as $g): $r = $rows[$g['id']] ?? null; ?>
    <tr><td><div class="orang"><div class="ava"><?= esc(inisial($g['nama'])) ?></div><div><b><?= esc($g['nama']) ?></b><div class="muted small"><?= esc($g['nip'] ?: '-') ?></div></div></div></td>
    <?php if ($r): [$lb, $cls] = status_label($r['status']); ?>
      <td><?= jam($r['jam_masuk']) ?></td><td><?= jam($r['jam_pulang']) ?></td><td><span class="badge <?= $cls ?>"><?= $lb ?><?= $r['menit_terlambat'] ? ' ' . $r['menit_terlambat'] . ' mnt' : '' ?></span></td>
      <td class="muted small"><?= esc(['qr' => 'QR', 'kode' => 'Kode', 'lokasi' => 'Lokasi' . ($r['jarak_m'] !== null ? ' (' . $r['jarak_m'] . ' m)' : ''), 'manual' => 'Admin', 'izin' => 'Pengajuan'][$r['metode']] ?? '') ?></td><td class="muted small"><?= esc($r['keterangan'] ?? '') ?></td>
    <?php else: ?>
      <td>-</td><td>-</td><td><?= $libur ? '<span class="badge muted">Libur</span>' : ($tgl < date('Y-m-d') ? '<span class="badge bad">Alpa</span>' : '<span class="badge muted">Belum absen</span>') ?></td><td></td><td></td>
    <?php endif ?>
    <td><button type="button" class="btn sm ghost" data-buka="dlgKoreksi" onclick='isiKoreksi(<?= json_encode(['id' => $g['id'], 'nama' => $g['nama'], 'status' => $r['status'] ?? 'hadir', 'masuk' => $r ? substr((string) $r['jam_masuk'], 0, 5) : '', 'pulang' => $r ? substr((string) $r['jam_pulang'], 0, 5) : '', 'ket' => $r['keterangan'] ?? ''], JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><?= ikon('edit') ?>Koreksi</button></td></tr>
  <?php endforeach ?>
  </tbody></table></div>

<dialog id="dlgKoreksi"><form class="dlg" method="post" action="<?= site_url('admin/presensi') ?>">
  <?= csrf_field() ?><input type="hidden" name="user_id" id="kUser"><input type="hidden" name="tanggal" value="<?= esc($tgl) ?>">
  <div class="dlg-h"><h2>Koreksi presensi</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <p class="muted" style="margin-top:0"><b id="kNama"></b> · <?= tgl_indo($tgl) ?></p>
  <div class="field"><label for="kStatus">Status</label><select name="status" id="kStatus"><option value="hadir">Hadir</option><option value="terlambat">Terlambat</option><option value="izin">Izin</option><option value="sakit">Sakit</option><option value="dinas">Dinas luar</option><option value="alpa">Alpa</option><option value="hapus">Hapus data (kosongkan)</option></select></div>
  <div class="row"><div class="field"><label for="kMasuk">Jam masuk</label><input type="time" name="jam_masuk" id="kMasuk"></div><div class="field"><label for="kPulang">Jam pulang</label><input type="time" name="jam_pulang" id="kPulang"></div></div>
  <div class="field mt"><label for="kKet">Keterangan</label><input name="keterangan" id="kKet" maxlength="200" placeholder="Contoh: HP rusak, absen dicatat manual"></div>
  <button class="btn blok">Simpan koreksi</button>
</form></dialog>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>
function isiKoreksi(d) { kUser.value = d.id; kNama.textContent = d.nama; kStatus.value = d.status; kMasuk.value = d.masuk || '<?= esc($set['jam_masuk']) ?>'; kPulang.value = d.pulang; kKet.value = d.ket; }
</script>
<?= $this->endSection() ?>
