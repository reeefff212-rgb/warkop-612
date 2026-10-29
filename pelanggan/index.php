<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$err = null;
if (isset($_GET['meja'])) {
    $m = qrow('SELECT * FROM meja WHERE id_meja = ?', [(int)$_GET['meja']]);
    if ($m) { $_SESSION['meja'] = (int)$m['id_meja']; }
    else $err = 'QR meja tidak dikenali. Silakan hubungi kasir.';
}
$meja = !empty($_SESSION['meja']) ? qrow('SELECT * FROM meja WHERE id_meja = ?', [$_SESSION['meja']]) : null;
$rek = qrow("SELECT * FROM menu WHERE status_menu = 'tersedia' AND stok > 0 ORDER BY RAND() LIMIT 1");
$flash = get_flash();
html_head('Pemesanan Meja', 'bg-[#F3F0E7]'); ?>
<div class="max-w-md mx-auto min-h-screen bg-cream shadow-xl px-6 py-10 flex flex-col">
  <div class="text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-pine text-amber-100 flex items-center justify-center mb-3"><?= icon('cup','w-8 h-8') ?></div>
    <p class="text-[11px] font-bold tracking-[.2em] text-tanText">WARUNG KOPI OTENTIK</p>
    <h1 class="text-3xl font-extrabold mt-1">WARKOP 612</h1>
    <p class="text-sm text-slate-500 mt-3">Pesan menu favorit langsung dari meja, tanpa antre di kasir.</p>
  </div>
  <?php if ($err || $flash): ?><div class="mt-5 rounded-xl px-4 py-3 text-sm font-semibold bg-red-50 text-red-700 border border-red-200"><?= e($err ?: $flash[0]) ?></div><?php endif; ?>
  <?php if ($meja): ?>
    <div class="mt-6 card p-5 flex items-center justify-between"><div><p class="text-[10px] font-bold tracking-wider text-emerald-700 uppercase">● Deteksi QR aktif</p><p class="text-2xl font-black mt-1">Meja <?= str_pad($meja['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></p></div><div class="text-pine"><?= icon('qr','w-9 h-9') ?></div></div>
    <div class="mt-6"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase mb-3">Alur pemesanan cepat</p>
      <?php foreach (['Pilih menu & jumlah', 'Tulis catatan & nama', 'Pilih bayar Cash / QRIS', 'Duduk tenang, pesanan diantar'] as $i => $s): ?>
        <div class="flex items-center gap-3 py-2"><span class="w-7 h-7 rounded-full bg-pine text-white text-xs font-bold flex items-center justify-center"><?= $i + 1 ?></span><span class="text-sm font-semibold"><?= e($s) ?></span></div>
      <?php endforeach; ?></div>
    <?php if ($rek): ?><div class="mt-4 rounded-2xl bg-tan/60 border border-[#DCCB9F] px-4 py-3 text-sm"><span class="text-[10px] font-bold tracking-wider text-tanText uppercase">Rekomendasi hari ini</span><div class="font-bold mt-0.5"><?= e($rek['nama_menu']) ?> — <?= e(rupiah($rek['harga'])) ?></div></div><?php endif; ?>
    <a href="<?= e(url('pelanggan/katalog.php')) ?>" class="btn btn-primary w-full py-4 text-sm mt-auto mt-8">Lihat Katalog Menu Meja <?= str_pad($meja['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></a>
    <p class="text-center text-[11px] text-slate-400 mt-3">Pemesanan instan tanpa perlu registrasi akun</p>
    <script src="<?= e(url('assets/js/app.js')) ?>"></script><script src="<?= e(url('assets/js/pelanggan.js')) ?>"></script>
    <script>Cart.useMeja(<?= (int)$meja['id_meja'] ?>);</script>
  <?php else: ?>
    <div class="mt-8 card p-6 text-center"><div class="text-pine mx-auto w-fit"><?= icon('qr','w-12 h-12') ?></div><p class="font-bold mt-3">Scan QR di meja Anda</p><p class="text-sm text-slate-500 mt-1">Arahkan kamera ponsel ke stiker QR di meja untuk mulai memesan.</p></div>
    <p class="text-center text-xs text-slate-400 mt-6">Demo: <a class="underline font-bold" href="?meja=1">Buka Meja 1</a></p>
  <?php endif; ?>
</div></body></html>
