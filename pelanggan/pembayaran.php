<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
$o = pel_order((int)($_GET['id'] ?? 0));
if ($o['metode_pembayaran'] !== 'QRIS' || $o['status_pembayaran'] === 'berhasil') redirect('pelanggan/konfirmasi.php?id=' . $o['id_transaksi']);
$batas = strtotime($o['waktu_pesanan']) + 15 * 60;
$payload = 'WARKOP612|TRX' . $o['id_transaksi'] . '|' . (int)$o['total_harga'];
pel_header('Pembayaran QRIS', $meja); ?>
<div class="p-4 space-y-4">
  <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-center justify-between"><span class="text-xs font-bold text-amber-800">Selesaikan dalam</span><b id="cd" class="text-lg text-amber-900">15:00</b></div>
  <div class="card p-5 text-center">
    <p class="lbl">Total Tagihan • Meja <?= (int)$meja['nomor_meja'] ?></p><p class="text-3xl font-black text-pine"><?= e(rupiah($o['total_harga'])) ?></p><p class="text-[11px] text-slate-400">ID: WKP-612-<?= str_pad($o['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></p>
    <div class="mt-4 inline-block bg-white p-3 rounded-2xl border border-line"><div id="qr"></div></div>
    <p class="text-xs font-bold mt-3">WARKOP 612 OFFICIAL – KEDAI KOPI</p><p class="text-sm text-slate-500 mt-1" id="msg">Menunggu pembayaran Anda...</p>
  </div>
  <div class="card p-4 space-y-3"><p class="font-bold text-sm">Cara Pembayaran</p>
    <?php foreach (['Buka aplikasi e-wallet / mobile banking', 'Pilih menu Bayar / Scan QRIS lalu arahkan kamera ke kode di atas', 'Periksa nominal & konfirmasi transaksi'] as $i => $s): ?><div class="flex gap-3 text-sm"><span class="w-6 h-6 shrink-0 rounded-full bg-pine text-white text-xs font-bold flex items-center justify-center"><?= $i + 1 ?></span><?= e($s) ?></div><?php endforeach; ?></div>
  <button id="cek" class="btn btn-primary w-full py-3.5 text-sm">Cek Status Pembayaran</button>
  <p class="text-[11px] text-center text-slate-400">Pembayaran diverifikasi oleh kasir. Anda akan diarahkan otomatis setelah lunas.</p>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qr'), {text: <?= json_encode($payload) ?>, width: 200, height: 200});
const ID = <?= (int)$o['id_transaksi'] ?>, END = <?= $batas ?> * 1000;
async function cek(manual) {
  try {
    const r = await WK.get('/api/pelanggan.php?action=status&id=' + ID);
    if (r.bayar === 'berhasil') location.href = '<?= e(url('pelanggan/konfirmasi.php?id=' . $o['id_transaksi'])) ?>';
    else if (r.bayar === 'gagal') { document.getElementById('msg').textContent = 'Pembayaran ditolak / pesanan dibatalkan.'; }
    else if (manual) WK.toast('Pembayaran belum terverifikasi');
  } catch (e) { if (manual) WK.toast(e.message, true); }
}
document.getElementById('cek').onclick = () => cek(true);
setInterval(cek, 4000);
setInterval(() => { const s = Math.max(0, Math.round((END - Date.now()) / 1000)); document.getElementById('cd').textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); }, 1000);
</script>
<?php pel_footer('cart');
