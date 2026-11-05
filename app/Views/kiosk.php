<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Kiosk Presensi · <?= esc($set['nama_sekolah']) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<script src="<?= base_url('assets/js/qrcode.js') ?>"></script>
</head>
<body style="background:var(--navy)">
<div class="kiosk">
  <div>
    <div class="brand" style="padding:0"><div class="logo"><?= $set['logo'] ? '<img src="' . base_url('uploads/' . esc($set['logo'])) . '" alt="">' : 'PG' ?></div><div><b style="font-size:20px"><?= esc($set['nama_sekolah']) ?></b><small>Presensi guru</small></div></div>
    <div class="jam mt2" id="jam">--:--</div>
    <div id="tanggal" style="font-size:22px;font-weight:600;color:#c9d5ef"></div>
    <p style="font-size:20px;margin-top:26px;max-width:520px;color:#fff">Arahkan kamera HP ke kode QR, lalu tekan <b style="color:var(--kuning)">Absen</b>. Atau ketik kode 6 angka di aplikasi.</p>
    <div style="display:flex;gap:12px;margin-top:10px"><span class="badge" style="background:var(--kuning);color:var(--navy);font-size:15px;padding:8px 14px">Hadir hari ini: <span id="hadir">0</span> / <span id="total">0</span></span></div>
    <ul id="terakhir"></ul>
  </div>
  <div class="qr">
    <div id="libur" style="display:none;max-width:380px;padding:30px;font-family:Montserrat;font-weight:800;font-size:26px"></div>
    <div id="qrwrap"><div id="qr"></div>
      <div class="kode" id="kode">------</div>
      <div class="sisa"><i id="bar" style="width:100%"></i></div>
      <p class="muted small" style="margin:8px 0 0">Kode berganti otomatis tiap 30 detik</p></div>
  </div>
</div>
<script>
const URL_STATUS = <?= json_encode(site_url('kiosk/' . $kunci . '/status')) ?>;
const HARI = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'], BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
let kodeKini = '', sisa = 30, jendela = 30;
function gambarQR(teks) {
  const qr = qrcode(0, 'M'); qr.addData(teks); qr.make();
  document.getElementById('qr').innerHTML = qr.createImgTag(10, 0, 'QR presensi');
}
function el(t, c) { const e = document.createElement(t); if (c) e.textContent = c; return e; }
async function muat() {
  try {
    const r = await fetch(URL_STATUS, { cache: 'no-store' }); const d = await r.json();
    document.getElementById('hadir').textContent = d.hadir; document.getElementById('total').textContent = d.total;
    const ul = document.getElementById('terakhir'); ul.replaceChildren(...d.terakhir.map(x => { const li = el('li'); li.append(el('span', x.nama), el('span', x.teks)); return li; }));
    if (d.libur) { document.getElementById('libur').style.display = 'block'; document.getElementById('libur').textContent = d.libur; document.getElementById('qrwrap').style.display = 'none'; return; }
    document.getElementById('libur').style.display = 'none'; document.getElementById('qrwrap').style.display = 'block';
    if (d.kode !== kodeKini) { kodeKini = d.kode; gambarQR(d.url); document.getElementById('kode').textContent = d.kode; }
    sisa = d.sisa; jendela = d.jendela;
  } catch (e) { /* jaringan putus sebentar: coba lagi di putaran berikutnya */ }
}
setInterval(() => {
  const n = new Date();
  document.getElementById('jam').textContent = n.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
  document.getElementById('tanggal').textContent = `${HARI[n.getDay()]}, ${n.getDate()} ${BULAN[n.getMonth()]} ${n.getFullYear()}`;
  sisa--; document.getElementById('bar').style.width = Math.max(0, sisa / jendela * 100) + '%';
  if (sisa <= 0) muat();
}, 1000);
setInterval(muat, 10000);
muat();
</script>
</body>
</html>
