<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
if (empty($_SESSION['orders'])) { pel_header('Status Pesanan', $meja); ?>
  <div class="p-8 text-center"><p class="font-bold">Belum ada pesanan</p><p class="text-sm text-slate-500 mt-1">Pesanan Anda akan muncul di sini.</p><a class="btn btn-primary mt-4" href="<?= e(url('pelanggan/katalog.php')) ?>">Lihat Menu</a></div>
<?php pel_footer('status'); exit; }
$o = pel_order(isset($_GET['id']) ? (int)$_GET['id'] : null);
$items = order_items($o['id_transaksi']);
$st = $o['status_pesanan'];
$steps = [['menunggu', 'Menunggu', 'Pesanan masuk ke sistem kasir.'], ['diproses', 'Diproses', 'Barista sedang meracik & memasak pesanan Anda.'], ['selesai', 'Selesai', 'Pesanan telah diantar ke meja Anda. Selamat menikmati!']];
$idx = array_search($st, array_column($steps, 0), true);
pel_header('Status Pesanan', $meja); ?>
<div class="p-4 space-y-4">
  <div class="card p-4"><div class="flex justify-between"><div><p class="text-[11px] font-bold text-slate-400">#ORD-<?= str_pad($o['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></p><p class="font-extrabold text-lg"><?= e($o['nama_pemesan']) ?></p><p class="text-xs text-slate-500">Dipesan <?= e(date('H:i', strtotime($o['waktu_pesanan']))) ?> WIB • <?= count($items) ?> menu</p></div><?= $o['status_pembayaran'] === 'berhasil' ? badge('Lunas', 'green') : badge('Belum bayar', 'amber') ?></div></div>
  <div class="card p-5" id="timeline">
    <?php if ($st === 'dibatalkan'): ?><p class="font-bold text-red-600">Pesanan dibatalkan.</p><p class="text-sm text-slate-500 mt-1">Hubungi kasir bila ini tidak sesuai.</p>
    <?php else: foreach ($steps as $i => [$k, $l, $d]): $done = $idx !== false && $i < $idx; $cur = $i === $idx; ?>
      <div class="flex gap-3 <?= $i < 2 ? 'pb-5' : '' ?>"><div class="flex flex-col items-center"><span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold <?= $done || ($cur && $k === 'selesai') ? 'bg-emerald-600 text-white' : ($cur ? 'bg-pine text-white ring-4 ring-emerald-100' : 'bg-creamField text-slate-400') ?>"><?= $done || ($cur && $k === 'selesai') ? '✓' : $i + 1 ?></span><?php if ($i < 2): ?><span class="w-0.5 flex-1 <?= $done ? 'bg-emerald-600' : 'bg-creamField' ?>"></span><?php endif; ?></div>
        <div><p class="font-bold text-sm <?= $cur ? 'text-pine' : ($done ? '' : 'text-slate-400') ?>"><?= $l ?><?= $cur && $k !== 'selesai' ? ' <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 ml-1">Sedang berlangsung</span>' : '' ?></p><p class="text-xs text-slate-500"><?= $d ?></p></div></div>
    <?php endforeach; endif; ?></div>
  <div class="rounded-2xl bg-tan/50 border border-[#DCCB9F] p-4 text-xs text-[#4F4225]"><b>Panduan pengambilan:</b> saat pesanan siap, kasir/barista akan memanggil nama Anda (“<?= e($o['nama_pemesan']) ?> – Meja <?= (int)$o['nomor_meja'] ?>”). Tidak ada notifikasi otomatis.</div>
  <div class="card p-4"><p class="lbl">Rincian Pesanan</p>
    <?php foreach ($items as $i): ?><div class="flex justify-between gap-3 py-2 border-b border-line last:border-0 text-sm"><div><b><?= (int)$i['jumlah'] ?>×</b> <?= e($i['nama_menu']) ?><?php if ($i['catatan']): ?><div class="text-[11px] text-slate-400">Catatan: <?= e($i['catatan']) ?></div><?php endif; ?></div><span class="font-semibold"><?= e(rupiah($i['subtotal'])) ?></span></div><?php endforeach; ?>
    <div class="flex justify-between pt-3 font-extrabold"><span>Total Pembayaran</span><span class="text-pine"><?= e(rupiah($o['total_harga'])) ?></span></div></div>
  <?php if ($o['status_pembayaran'] === 'menunggu' && $o['metode_pembayaran'] === 'QRIS' && $st !== 'dibatalkan'): ?><a class="btn btn-primary w-full py-3" href="<?= e(url('pelanggan/pembayaran.php?id=' . $o['id_transaksi'])) ?>">Bayar dengan QRIS</a><?php endif; ?>
  <a class="btn btn-ghost w-full py-3.5 text-sm" href="<?= e(url('pelanggan/katalog.php')) ?>">Pesan Tambahan Menu Lain</a>
</div>
<script>
const CUR = <?= json_encode($st) ?>, PAY = <?= json_encode($o['status_pembayaran']) ?>;
if (CUR !== 'selesai' && CUR !== 'dibatalkan') setInterval(async () => {
  try { const r = await WK.get('/api/pelanggan.php?action=status&id=<?= (int)$o['id_transaksi'] ?>'); if (r.pesanan !== CUR || r.bayar !== PAY) location.reload(); } catch (e) {}
}, 8000);
</script>
<?php pel_footer('status');
