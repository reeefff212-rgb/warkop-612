<?php
require_once __DIR__ . '/includes/layout.php';
html_head('Beranda', 'flex items-center justify-center p-6'); ?>
<div class="w-full max-w-3xl text-center">
  <div class="w-16 h-16 mx-auto rounded-2xl bg-pine text-amber-100 flex items-center justify-center mb-5"><?= icon('cup','w-8 h-8') ?></div>
  <h1 class="text-3xl font-extrabold">WARKOP 612</h1>
  <p class="text-slate-500 mt-2 mb-8">Pilih portal yang ingin Anda buka</p>
  <div class="grid sm:grid-cols-3 gap-4 text-left">
    <a href="<?= e(url('kasir/login.php')) ?>" class="card p-6 hover:shadow-lg transition"><div class="text-pine mb-3"><?= icon('card','w-7 h-7') ?></div><h2 class="font-bold">Portal Kasir</h2><p class="text-xs text-slate-500 mt-1">Pesanan, pembayaran, menu, meja &amp; laporan.</p></a>
    <a href="<?= e(url('owner/login.php')) ?>" class="card p-6 hover:shadow-lg transition"><div class="text-pine mb-3"><?= icon('chart','w-7 h-7') ?></div><h2 class="font-bold">Portal Owner</h2><p class="text-xs text-slate-500 mt-1">Pantau laporan, transaksi &amp; pembayaran.</p></a>
    <a href="<?= e(url('pelanggan/index.php?meja=1')) ?>" class="card p-6 hover:shadow-lg transition"><div class="text-pine mb-3"><?= icon('qr','w-7 h-7') ?></div><h2 class="font-bold">Pelanggan (Demo)</h2><p class="text-xs text-slate-500 mt-1">Simulasi scan QR Meja 1.</p></a>
  </div>
</div>
</body></html>
