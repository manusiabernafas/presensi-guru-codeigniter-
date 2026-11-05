<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><a class="small" href="<?= site_url('ekskul/' . $e['id']) ?>">&larr; <?= esc($e['nama']) ?></a><h1 style="margin-top:6px">Presensi <?= tgl_indo($p['tanggal'], true) ?></h1><p>H = hadir, I = izin, S = sakit, A = alpa</p></div></div>
<form method="post" action="<?= site_url('ekskul/pertemuan/' . $p['id']) ?>">
  <?= csrf_field() ?>
  <div class="card mb"><div class="row"><div class="field"><label for="materi">Materi / kegiatan</label><input id="materi" name="materi" maxlength="200" value="<?= esc(old('materi', $p['materi'])) ?>" required></div>
    <div class="field"><label for="catatan">Catatan <span class="muted small">(opsional)</span></label><input id="catatan" name="catatan" maxlength="300" value="<?= esc(old('catatan', $p['catatan'] ?? '')) ?>"></div></div></div>
  <div class="card-h mb"><span class="muted"><?= count($anggota) ?> anggota</span><button type="button" class="btn ghost sm" onclick="document.querySelectorAll('input[value=H]').forEach(r => r.checked = true); hitung()">Tandai semua hadir</button></div>
  <div class="tbl-wrap"><table class="tbl">
    <thead><tr><th class="hp-sembunyi">#</th><th>Nama</th><th class="hp-sembunyi">Kelas</th><th>Kehadiran</th></tr></thead>
    <tbody>
    <?php foreach ($anggota as $i => $a): $st = $status[$a['id']] ?? 'H'; ?>
      <tr><td class="muted hp-sembunyi"><?= $i + 1 ?></td><td><?= esc($a['nama']) ?><div class="muted small"><?= esc($a['kelas'] ?? '') ?></div></td><td class="hp-sembunyi"><?= esc($a['kelas'] ?? '-') ?></td>
        <td><div class="hisa" role="radiogroup" aria-label="Kehadiran <?= esc($a['nama'], 'attr') ?>">
          <?php foreach (['H', 'I', 'S', 'A'] as $v): $idr = "s{$a['id']}{$v}"; ?><input type="radio" id="<?= $idr ?>" name="status[<?= $a['id'] ?>]" value="<?= $v ?>" <?= $st === $v ? 'checked' : '' ?> onchange="hitung()"><label class="<?= $v ?>" for="<?= $idr ?>"><?= $v ?></label><?php endforeach ?>
        </div></td></tr>
    <?php endforeach ?>
    </tbody></table></div>
  <div class="card-h mt"><b id="ringkas"></b><button class="btn lg">Simpan presensi</button></div>
</form>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>
function hitung() { const c = { H: 0, I: 0, S: 0, A: 0 }; document.querySelectorAll('.hisa input:checked').forEach(r => c[r.value]++); document.getElementById('ringkas').textContent = `Hadir ${c.H} · Izin ${c.I} · Sakit ${c.S} · Alpa ${c.A}`; }
hitung();
</script>
<?= $this->endSection() ?>
