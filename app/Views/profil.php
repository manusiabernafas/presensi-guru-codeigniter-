<?= $this->extend('layout') ?>
<?= $this->section('konten') ?>
<div class="head"><div><h1>Profil & kata sandi</h1><p><?= esc($u['username']) ?> · <?= $u['role'] === 'admin' ? 'Admin' : 'Guru' ?></p></div></div>
<div class="grid g2">
  <form class="card" method="post" action="<?= site_url('profil') ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?><h2>Data diri</h2>
    <div class="orang mt"><div class="ava" style="width:64px;height:64px;font-size:22px"><?= $u['foto'] ? '<img src="' . base_url('uploads/' . esc($u['foto'])) . '" alt="">' : esc(inisial($u['nama'])) ?></div><div class="muted small">NIP <?= esc($u['nip'] ?: '-') ?><br><?= esc($u['jabatan'] ?: '') ?></div></div>
    <div class="field mt"><label for="nama">Nama lengkap</label><input id="nama" name="nama" value="<?= esc(old('nama', $u['nama'])) ?>" required></div>
    <div class="field"><label for="no_hp">No. HP</label><input id="no_hp" name="no_hp" inputmode="tel" value="<?= esc(old('no_hp', $u['no_hp'])) ?>"></div>
    <div class="field"><label for="foto">Foto profil</label><input type="file" id="foto" name="foto" accept="image/*"><span class="hint">JPG/PNG/WebP maks 1 MB.</span></div>
    <button class="btn">Simpan profil</button>
  </form>
  <form class="card" method="post" action="<?= site_url('profil/password') ?>" novalidate>
    <?= csrf_field() ?><h2>Ganti kata sandi</h2>
    <div class="field mt"><label for="lama">Kata sandi lama</label><input type="password" id="lama" name="lama" autocomplete="current-password" required></div>
    <div class="field"><label for="baru">Kata sandi baru</label><input type="password" id="baru" name="baru" autocomplete="new-password" minlength="8" required><span class="hint">Minimal 8 karakter.</span></div>
    <div class="field"><label for="ulang">Ulangi kata sandi baru</label><input type="password" id="ulang" name="ulang" autocomplete="new-password" required></div>
    <button class="btn"><?= ikon('kunci') ?>Ganti kata sandi</button>
  </form>
</div>
<?= $this->endSection() ?>
