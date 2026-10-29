<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
$o = pel_order((int)($_GET['id'] ?? 0));
$items = order_items($o['id_transaksi']);
$lunas = $o['status_pembayaran'] === 'berhasil';
pel_header('Konfirmasi Pesanan', $meja); ?>
<div class="p-4 space-y-4">
  <div class="text-center py-4"><div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><?= icon('check','w-8 h-8') ?></div>
    <h1 class="text-2xl font-extrabold mt-3">Pesanan Berhasil Diterima!</h1><p class="text-sm text-slate-500 mt-1">Dapur WARKOP 612 sedang meracik hidangan untuk Anda.</p></div>
  <div class="card p-4 space-y-2 text-sm">
    <div class="flex justify-between"><span class="text-slate-500">No. Pesanan</span><b>#WKP-<?= str_pad($o['id_transaksi'], 3, '0', STR_PAD_LEFT) ?></b></div>
    <div class="flex justify-between"><span class="text-slate-500">Waktu Order</span><b><?= e(date('H:i', strtotime($o['waktu_pesanan']))) ?> WIB</b></div>
    <div class="flex justify-between"><span class="text-slate-500">Lokasi</span><b>Meja <?= (int)$o['nomor_meja'] ?></b></div>
    <div class="flex justify-between"><span class="text-slate-500">Pembayaran</span><span><?= e($o['metode_pembayaran'] === 'QRIS' ? 'QRIS' : 'Tunai') ?> • <?= $lunas ? badge('Lunas', 'green') : badge('Belum dibayar', 'amber') ?></span></div></div>
  <?php if (!$lunas && $o['metode_pembayaran'] === 'cash'): ?><div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 font-semibold">Silakan bayar tunai <?= e(rupiah($o['total_harga'])) ?> ke kasir.</div><?php endif; ?>
  <div class="card p-4"><p class="lbl">Rincian Menu (<?= count($items) ?>)</p>
    <?php foreach ($items as $i): ?><div class="flex justify-between gap-3 py-2 border-b border-line last:border-0 text-sm"><div><b><?= (int)$i['jumlah'] ?>x</b> <?= e($i['nama_menu']) ?><?php if ($i['catatan']): ?><div class="text-[11px] text-slate-400">“<?= e($i['catatan']) ?>”</div><?php endif; ?></div><span class="font-semibold whitespace-nowrap"><?= e(rupiah($i['subtotal'])) ?></span></div><?php endforeach; ?>
    <div class="flex justify-between pt-3 font-extrabold"><span>Total Pembayaran</span><span class="text-pine"><?= e(rupiah($o['total_harga'])) ?></span></div></div>
  <a class="btn btn-primary w-full py-3.5 text-sm" href="<?= e(url('pelanggan/status.php?id=' . $o['id_transaksi'])) ?>">Lihat Progres Pesanan Meja <?= (int)$o['nomor_meja'] ?></a>
  <a class="btn btn-ghost w-full py-3.5 text-sm" href="<?= e(url('pelanggan/katalog.php')) ?>">Kembali ke Menu Utama</a>
</div>
<script>Cart.clear();</script>
<?php pel_footer('status');
