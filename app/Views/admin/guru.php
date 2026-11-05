<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Guru & staf</h1><p><?= count($daftar) ?> akun</p></div>
  <div class="row"><form class="row" method="get"><input type="search" name="q" value="<?= esc($q) ?>" placeholder="Cari nama / NIP / username" aria-label="Cari"><button class="btn ghost">Cari</button></form>
    <button type="button" class="btn" data-buka="dlg" onclick="isi({})"><?= ikon('plus') ?>Tambah akun</button></div></div>
<div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Nama</th><th>Username</th><th>Jabatan / mapel</th><th>Peran</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (! $daftar): ?><tr><td colspan="6" class="kosong">Tidak ada data.</td></tr><?php endif ?>
  <?php foreach ($daftar as $g): ?>
    <tr><td><div class="orang"><div class="ava"><?= esc(inisial($g['nama'])) ?></div><div><b><?= esc($g['nama']) ?></b><div class="muted small">NIP <?= esc($g['nip'] ?: '-') ?></div></div></div></td>
      <td><?= esc($g['username']) ?></td><td class="small"><?= esc($g['jabatan'] ?: '-') ?><div class="muted"><?= esc($g['mapel'] ?? '') ?></div></td>
      <td><span class="badge <?= $g['role'] === 'admin' ? 'warn' : 'info' ?>"><?= $g['role'] === 'admin' ? 'Admin' : 'Guru' ?></span></td>
      <td><?= $g['aktif'] ? '<span class="badge ok">Aktif</span>' : '<span class="badge muted">Nonaktif</span>' ?></td>
      <td><div class="aksi">
        <button type="button" class="btn sm ghost" data-buka="dlg" onclick='isi(<?= json_encode(array_intersect_key($g, array_flip(['id', 'nama', 'username', 'nip', 'jabatan', 'mapel', 'no_hp', 'role', 'aktif'])), JSON_HEX_APOS | JSON_HEX_TAG) ?>)'><?= ikon('edit') ?>Ubah</button>
        <form method="post" action="<?= site_url('admin/guru/' . $g['id'] . '/reset') ?>" data-konfirmasi="Buat kata sandi baru untuk <?= esc($g['nama'], 'attr') ?>?"><?= csrf_field() ?><button class="btn sm ghost">Reset sandi</button></form>
        <?php if ($g['id'] != $user['id']): ?><form method="post" action="<?= site_url('admin/guru/' . $g['id'] . '/hapus') ?>" data-konfirmasi="Hapus akun <?= esc($g['nama'], 'attr') ?> beserta riwayat presensinya? Sebaiknya nonaktifkan saja bila pindah tugas."><?= csrf_field() ?><button class="btn sm danger" aria-label="Hapus"><?= ikon('hapus') ?></button></form><?php endif ?>
      </div></td></tr>
  <?php endforeach ?>
  </tbody></table></div>
<dialog id="dlg"><form class="dlg" method="post" action="<?= site_url('admin/guru') ?>" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" id="fId">
  <div class="dlg-h"><h2 id="fJudul">Tambah akun</h2><button type="button" class="x" data-tutup aria-label="Tutup">&times;</button></div>
  <div class="field"><label for="fNama">Nama lengkap</label><input id="fNama" name="nama" required></div>
  <div class="row"><div class="field"><label for="fUser">Username</label><input id="fUser" name="username" required></div><div class="field"><label for="fNip">NIP</label><input id="fNip" name="nip" inputmode="numeric"></div></div>
  <div class="row mt"><div class="field"><label for="fJab">Jabatan</label><input id="fJab" name="jabatan" placeholder="Guru Mapel"></div><div class="field"><label for="fMapel">Mata pelajaran</label><input id="fMapel" name="mapel"></div></div>
  <div class="row mt"><div class="field"><label for="fHp">No. HP</label><input id="fHp" name="no_hp" inputmode="tel"></div><div class="field"><label for="fRole">Peran</label><select id="fRole" name="role"><option value="guru">Guru</option><option value="admin">Admin / TU</option></select></div></div>
  <div class="field mt"><label for="fPass">Kata sandi</label><input type="password" id="fPass" name="password" autocomplete="new-password"><span class="hint" id="fPassHint">Minimal 8 karakter.</span></div>
  <label class="cek"><input type="checkbox" name="aktif" id="fAktif" checked> Akun aktif</label>
  <button class="btn blok mt">Simpan</button>
</form></dialog>
<?= $this->endSection() ?>
<?= $this->section('skrip') ?>
<script>
function isi(g) { fId.value = g.id || ''; fJudul.textContent = g.id ? 'Ubah akun' : 'Tambah akun'; fNama.value = g.nama || ''; fUser.value = g.username || ''; fNip.value = g.nip || ''; fJab.value = g.jabatan || ''; fMapel.value = g.mapel || ''; fHp.value = g.no_hp || ''; fRole.value = g.role || 'guru'; fAktif.checked = g.id ? g.aktif == 1 : true; fPass.value = ''; fPassHint.textContent = g.id ? 'Kosongkan bila tidak diganti.' : 'Minimal 8 karakter.'; }
</script>
<?= $this->endSection() ?>
