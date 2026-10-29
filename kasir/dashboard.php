<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$a = date('Y-m-d 00:00:00'); $b = date('Y-m-d 23:59:59');
$ya = date('Y-m-d 00:00:00', strtotime('-1 day')); $yb = date('Y-m-d 23:59:59', strtotime('-1 day'));
$cnt = fn($s, $x, $y) => (int)qval("SELECT COUNT(*) FROM transaksi WHERE waktu_pesanan BETWEEN ? AND ? AND $s", [$x, $y]);
$hari = $cnt("status_pesanan <> 'dibatalkan'", $a, $b);
$kemarin = $cnt("status_pesanan <> 'dibatalkan'", $ya, $yb);
$proses = (int)qval("SELECT COUNT(*) FROM transaksi WHERE status_pesanan IN ('menunggu','diproses')");
$selesai = $cnt("status_pesanan = 'selesai'", $a, $b);
$bayar = fn($m) => qrow("SELECT COUNT(*) c, COALESCE(SUM(p.jumlah_bayar),0) s FROM pembayaran p JOIN transaksi t ON t.id_transaksi = p.id_transaksi
                         WHERE p.status_pembayaran = 'berhasil' AND t.waktu_pesanan BETWEEN ? AND ?" . ($m ? " AND p.metode_pembayaran = '$m'" : ''), [$a, $b]);
$all = $bayar(''); $qris = $bayar('QRIS'); $cash = $bayar('cash');
$menunggu = qrow("SELECT COUNT(*) c, COALESCE(SUM(jumlah_bayar),0) s FROM pembayaran WHERE status_pembayaran = 'menunggu'");
$selisih = $kemarin > 0 ? round(($hari - $kemarin) / $kemarin * 100) : null;

$terbaru = qall("SELECT t.*, m.nomor_meja, p.metode_pembayaran, p.status_pembayaran,
                        (SELECT GROUP_CONCAT(CONCAT(d.jumlah,'x ',mn.nama_menu) SEPARATOR ', ') FROM detail_transaksi d JOIN menu mn ON mn.id_menu = d.id_menu WHERE d.id_transaksi = t.id_transaksi) AS item
                 FROM transaksi t LEFT JOIN meja m ON m.id_meja = t.id_meja LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
                 ORDER BY t.waktu_pesanan DESC LIMIT 8");
$laris = qall("SELECT mn.nama_menu, SUM(d.jumlah) qty FROM detail_transaksi d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN menu mn ON mn.id_menu = d.id_menu
               WHERE t.waktu_pesanan BETWEEN ? AND ? AND t.status_pesanan <> 'dibatalkan' GROUP BY mn.id_menu ORDER BY qty DESC LIMIT 5", [$a, $b]);
$stokTipis = qall("SELECT nama_menu, stok, status_menu FROM menu WHERE stok <= 10 ORDER BY stok ASC LIMIT 5");

$GLOBALS['page_actions'] = '<a href="' . e(url('kasir/pesanan_manual.php')) . '" class="btn btn-ghost">' . icon('plus') . ' Pesanan Baru</a><button onclick="location.reload()" class="btn btn-primary">' . icon('refresh') . ' Segarkan Data</button>';
admin_header('Ikhtisar Kasir & Dapur', 'dashboard', 'Ringkasan operasional harian'); ?>
<section class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
  <article class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Pesanan Hari Ini</p><p class="text-3xl font-extrabold mt-3"><?= $hari ?></p>
    <p class="text-[11px] font-semibold mt-2 <?= ($selisih ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-600' ?>"><?= $selisih === null ? 'Belum ada data kemarin' : ($selisih >= 0 ? '+' : '') . $selisih . '% vs kemarin' ?></p></article>
  <article class="card p-4 border-l-4 !border-l-amber-600"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Antrean Aktif</p><p class="text-3xl font-extrabold mt-3"><?= $proses ?></p><p class="text-[11px] text-slate-600 mt-2">Menunggu &amp; diproses dapur</p></article>
  <article class="card p-4 border-l-4 !border-l-teal-600"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Pesanan Selesai</p><p class="text-3xl font-extrabold mt-3"><?= $selesai ?></p><p class="text-[11px] text-slate-600 mt-2">Telah tersaji</p></article>
  <article class="rounded-2xl p-4 bg-pine text-white shadow-sm"><p class="text-[11px] font-bold tracking-wider text-emerald-200 uppercase">Pendapatan Hari Ini</p><p class="text-2xl font-black mt-3"><?= e(rupiah($all['s'])) ?></p><p class="text-[11px] text-emerald-200/80 mt-2">Pembayaran terverifikasi</p></article>
  <article class="card p-4 border-l-4 !border-l-pine"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Transaksi</p><p class="text-3xl font-extrabold mt-3"><?= (int)$all['c'] ?></p>
    <p class="text-[11px] text-slate-600 mt-2">Tunai: <b><?= (int)$cash['c'] ?></b> | QRIS: <b><?= (int)$qris['c'] ?></b></p></article>
</section>

<?php if ($menunggu['c'] > 0): ?>
<a href="<?= e(url('kasir/pembayaran.php?status=menunggu')) ?>" class="block rounded-2xl bg-amber-50 border border-amber-200 px-5 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100"><?= (int)$menunggu['c'] ?> pembayaran menunggu konfirmasi (<?= e(rupiah($menunggu['s'])) ?>) - klik untuk memproses &rarr;</a>
<?php endif; ?>

<section class="grid lg:grid-cols-3 gap-5">
  <div class="card lg:col-span-2 overflow-hidden">
    <div class="px-5 py-4 flex items-center justify-between border-b border-line"><h3 class="font-bold">Pesanan Terbaru</h3><a href="<?= e(url('kasir/pesanan.php')) ?>" class="text-xs font-bold text-pine">Kelola antrean &rarr;</a></div>
    <div class="overflow-x-auto"><table class="tbl">
      <thead><tr><th>ID</th><th>Pemesan</th><th>Lokasi</th><th>Item</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($terbaru as $r): ?>
        <tr><td class="font-bold">#<?= (int)$r['id_transaksi'] ?><div class="text-[11px] font-medium text-slate-400"><?= e(date('H:i', strtotime($r['waktu_pesanan']))) ?></div></td>
          <td><?= e($r['nama_pemesan']) ?><div class="text-[11px] text-slate-400"><?= $r['tipe_pesanan'] === 'QR' ? 'Via QR' : 'Manual' ?></div></td>
          <td><?= $r['nomor_meja'] ? 'Meja ' . str_pad($r['nomor_meja'], 2, '0', STR_PAD_LEFT) : 'Bungkus' ?></td>
          <td class="max-w-[220px] text-slate-600"><?= e($r['item']) ?></td>
          <td class="font-bold whitespace-nowrap"><?= e(rupiah($r['total_harga'])) ?></td>
          <td><?= status_badge($r['status_pesanan']) ?></td></tr>
      <?php endforeach; if (!$terbaru): ?><tr><td colspan="6" class="text-center text-slate-400 py-10">Belum ada pesanan.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
  <div class="space-y-5">
    <div class="card p-5"><h3 class="font-bold mb-3">Menu Terlaris Hari Ini</h3>
      <?php foreach ($laris as $i => $m): ?><div class="flex items-center justify-between py-2 border-b border-line last:border-0 text-sm"><span><b class="text-slate-400 mr-2"><?= $i + 1 ?></b><?= e($m['nama_menu']) ?></span><b><?= (int)$m['qty'] ?>x</b></div>
      <?php endforeach; if (!$laris) echo '<p class="text-sm text-slate-400">Belum ada penjualan.</p>'; ?></div>
    <div class="card p-5"><h3 class="font-bold mb-3">Stok Menipis</h3>
      <?php foreach ($stokTipis as $m): ?><div class="flex items-center justify-between py-2 border-b border-line last:border-0 text-sm"><span><?= e($m['nama_menu']) ?></span><span class="font-bold <?= $m['stok'] <= 0 ? 'text-red-600' : 'text-amber-700' ?>"><?= (int)$m['stok'] ?> sisa</span></div>
      <?php endforeach; if (!$stokTipis) echo '<p class="text-sm text-slate-400">Semua stok aman.</p>'; ?></div>
  </div>
</section>
<script>setTimeout(()=>location.reload(),60000)</script>
<?php admin_footer();
