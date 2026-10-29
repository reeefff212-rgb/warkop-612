<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area(AREA);
$readonly = AREA === 'owner';

$a = date('Y-m-d 00:00:00'); $b = date('Y-m-d 23:59:59');
$sum = fn($m) => qrow("SELECT COUNT(*) c, COALESCE(SUM(p.jumlah_bayar),0) s FROM pembayaran p JOIN transaksi t ON t.id_transaksi = p.id_transaksi
                       WHERE p.status_pembayaran = 'berhasil' AND t.waktu_pesanan BETWEEN ? AND ?" . ($m ? " AND p.metode_pembayaran = '$m'" : ''), [$a, $b]);
$all = $sum(''); $qr = $sum('QRIS'); $cs = $sum('cash');
$wait = qrow("SELECT COUNT(*) c, COALESCE(SUM(jumlah_bayar),0) s FROM pembayaran WHERE status_pembayaran = 'menunggu'");

$fs = $_GET['status'] ?? ''; $fm = $_GET['metode'] ?? ''; $q = trim($_GET['q'] ?? '');
$w = ["t.waktu_pesanan >= ?"]; $p = [date('Y-m-d 00:00:00', strtotime('-6 days'))];
if (in_array($fs, ['menunggu', 'berhasil', 'gagal'], true)) { $w[] = 'p.status_pembayaran = ?'; $p[] = $fs; }
if (in_array($fm, ['QRIS', 'cash'], true)) { $w[] = 'p.metode_pembayaran = ?'; $p[] = $fm; }
if ($q !== '') { $w[] = '(t.nama_pemesan LIKE ? OR t.id_transaksi = ? OR m.nomor_meja = ?)'; $p[] = "%$q%"; $p[] = (int)$q; $p[] = (int)$q; }
$rows = qall("SELECT p.*, t.nama_pemesan, t.waktu_pesanan, t.status_pesanan, m.nomor_meja,
              (SELECT GROUP_CONCAT(CONCAT(d.jumlah,'x ',mn.nama_menu) SEPARATOR ', ') FROM detail_transaksi d JOIN menu mn ON mn.id_menu = d.id_menu WHERE d.id_transaksi = t.id_transaksi) item
              FROM pembayaran p JOIN transaksi t ON t.id_transaksi = p.id_transaksi LEFT JOIN meja m ON m.id_meja = t.id_meja
              WHERE " . implode(' AND ', $w) . " ORDER BY (p.status_pembayaran = 'menunggu') DESC, t.waktu_pesanan DESC LIMIT 200", $p);

admin_header('Manajemen Pembayaran', 'pembayaran', $readonly ? 'Owner › Keuangan' : 'Kasir & keuangan', 'Verifikasi pembayaran QRIS dan rekonsiliasi penerimaan tunai. Data 7 hari terakhir.'); ?>
<section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
  <div class="rounded-2xl p-4 bg-pine text-white"><p class="text-[11px] font-bold tracking-wider text-emerald-200 uppercase">Total Penerimaan Hari Ini</p><p class="text-2xl font-black mt-2"><?= e(rupiah($all['s'])) ?></p><p class="text-[11px] text-emerald-200/80 mt-1"><?= (int)$all['c'] ?> transaksi lunas</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Pembayaran QRIS</p><p class="text-2xl font-black mt-2"><?= e(rupiah($qr['s'])) ?></p><p class="text-[11px] text-slate-500 mt-1"><?= (int)$qr['c'] ?> transaksi</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Pembayaran Tunai</p><p class="text-2xl font-black mt-2"><?= e(rupiah($cs['s'])) ?></p><p class="text-[11px] text-slate-500 mt-1"><?= (int)$cs['c'] ?> transaksi</p></div>
  <div class="card p-4 border-l-4 !border-l-amber-500"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menunggu Konfirmasi</p><p class="text-2xl font-black mt-2"><?= e(rupiah($wait['s'])) ?></p><p class="text-[11px] text-amber-700 font-semibold mt-1"><?= (int)$wait['c'] ?> butuh aksi kasir</p></div>
</section>

<form class="card p-4 flex flex-wrap gap-3 items-end no-print" method="get">
  <div class="flex-1 min-w-[200px]"><label class="lbl">Cari</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="ID transaksi, nama, no. meja"></div>
  <div><label class="lbl">Metode</label><select class="input" name="metode"><option value="">Semua</option><option value="QRIS" <?= $fm === 'QRIS' ? 'selected' : '' ?>>QRIS</option><option value="cash" <?= $fm === 'cash' ? 'selected' : '' ?>>Tunai</option></select></div>
  <div><label class="lbl">Status</label><select class="input" name="status"><option value="">Semua</option><option value="menunggu" <?= $fs === 'menunggu' ? 'selected' : '' ?>>Menunggu</option><option value="berhasil" <?= $fs === 'berhasil' ? 'selected' : '' ?>>Lunas</option><option value="gagal" <?= $fs === 'gagal' ? 'selected' : '' ?>>Gagal</option></select></div>
  <button class="btn btn-primary">Terapkan</button><a href="?" class="btn btn-ghost">Reset</a>
</form>

<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl">
  <thead><tr><th>Waktu &amp; ID</th><th>Pemesan &amp; Meja</th><th>Rincian Menu</th><th>Metode</th><th>Total</th><th>Status</th><?php if (!$readonly): ?><th class="no-print">Aksi</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td class="whitespace-nowrap"><b><?= e(date('H:i', strtotime($r['waktu_pesanan']))) ?> WIB</b><div class="text-[11px] text-slate-400">#PAY-<?= str_pad($r['id_pembayaran'], 4, '0', STR_PAD_LEFT) ?> • <?= e(date('d/m', strtotime($r['waktu_pesanan']))) ?></div></td>
      <td><?= e($r['nama_pemesan']) ?><div class="text-[11px] text-slate-400"><?= $r['nomor_meja'] ? 'Meja ' . (int)$r['nomor_meja'] : 'Bungkus' ?></div></td>
      <td class="max-w-[260px] text-slate-600"><?= e($r['item']) ?></td>
      <td><?= $r['metode_pembayaran'] === 'QRIS' ? badge('QRIS', 'blue') : badge('Tunai', 'gray') ?></td>
      <td class="font-bold whitespace-nowrap"><?= e(rupiah($r['jumlah_bayar'])) ?></td>
      <td><?= status_badge($r['status_pembayaran']) ?></td>
      <?php if (!$readonly): ?><td class="no-print whitespace-nowrap"><?php if ($r['status_pembayaran'] === 'menunggu'): ?>
        <button class="btn btn-primary btn-sm" onclick="bayar(<?= (int)$r['id_transaksi'] ?>,true)"><?= icon('check','w-3.5 h-3.5') ?> Konfirmasi</button>
        <button class="btn btn-danger btn-sm" data-confirm="Tolak pembayaran dan batalkan pesanan #<?= (int)$r['id_transaksi'] ?>?" onclick="bayar(<?= (int)$r['id_transaksi'] ?>,false)">Tolak</button><?php endif; ?></td><?php endif; ?></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-slate-400 py-10">Tidak ada data.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<script>
async function bayar(id, ok) {
  try { const r = await WK.post('/api/kasir.php?action=konfirmasi_bayar', {id, ok}); WK.toast(r.msg); setTimeout(() => location.reload(), 600); }
  catch (e) { WK.toast(e.message, true); }
}
<?php if (!$readonly): ?>setInterval(() => { if (!document.hidden) location.reload(); }, 30000);<?php endif; ?>
</script>
<?php admin_footer();
