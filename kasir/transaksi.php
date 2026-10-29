<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area(AREA);

$ok = fn($d, $def) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d ?? '') ? $d : $def;
$dari = $ok($_GET['dari'] ?? '', date('Y-m-d')); $sampai = $ok($_GET['sampai'] ?? '', date('Y-m-d'));
if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
$fm = $_GET['metode'] ?? ''; $fj = (int)($_GET['meja'] ?? 0); $q = trim($_GET['q'] ?? '');
$w = ['t.waktu_pesanan BETWEEN ? AND ?']; $p = ["$dari 00:00:00", "$sampai 23:59:59"];
if (in_array($fm, ['QRIS', 'cash'], true)) { $w[] = 'p.metode_pembayaran = ?'; $p[] = $fm; }
if ($fj) { $w[] = 't.id_meja = ?'; $p[] = $fj; }
if ($q !== '') { $w[] = '(t.nama_pemesan LIKE ? OR t.id_transaksi = ?)'; $p[] = "%$q%"; $p[] = (int)$q; }
$rows = qall("SELECT t.*, m.nomor_meja, p.metode_pembayaran, p.status_pembayaran,
              (SELECT GROUP_CONCAT(CONCAT(d.jumlah,'x ',mn.nama_menu) SEPARATOR ', ') FROM detail_transaksi d JOIN menu mn ON mn.id_menu = d.id_menu WHERE d.id_transaksi = t.id_transaksi) item,
              (SELECT SUM(d.jumlah) FROM detail_transaksi d WHERE d.id_transaksi = t.id_transaksi) qty
              FROM transaksi t LEFT JOIN meja m ON m.id_meja = t.id_meja LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
              WHERE " . implode(' AND ', $w) . ' ORDER BY t.waktu_pesanan DESC LIMIT 1000', $p);

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=transaksi_{$dari}_{$sampai}.csv");
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, ['No', 'Waktu', 'Pemesan', 'Meja', 'Item', 'Metode', 'Status Bayar', 'Status Pesanan', 'Total']);
    foreach ($rows as $r) fputcsv($o, [$r['id_transaksi'], $r['waktu_pesanan'], $r['nama_pemesan'], $r['nomor_meja'] ?: 'Bungkus', $r['item'], $r['metode_pembayaran'], $r['status_pembayaran'], $r['status_pesanan'], (int)$r['total_harga']]);
    exit;
}

$paid = array_filter($rows, fn($r) => $r['status_pembayaran'] === 'berhasil');
$sum = fn($arr) => array_sum(array_column($arr, 'total_harga'));
$qr = array_filter($paid, fn($r) => $r['metode_pembayaran'] === 'QRIS'); $cs = array_filter($paid, fn($r) => $r['metode_pembayaran'] === 'cash');
$tot = $sum($paid); $avg = count($paid) ? $tot / count($paid) : 0;
$meja = qall('SELECT * FROM meja ORDER BY nomor_meja');
$pre = fn($a, $b) => '?' . http_build_query(['dari' => $a, 'sampai' => $b]);

$GLOBALS['page_actions'] = '<a class="btn btn-ghost" href="?' . e(http_build_query(array_merge($_GET, ['export' => 'csv']))) . '">' . icon('download') . ' Export CSV / Excel</a>';
admin_header('Riwayat Transaksi', 'transaksi', AREA === 'owner' ? 'Owner › Pembukuan' : 'Kasir & pembukuan', 'Histori seluruh transaksi, detail pesanan, dan metode pembayaran.'); ?>
<section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
  <div class="rounded-2xl p-4 bg-pine text-white"><p class="text-[11px] font-bold tracking-wider text-emerald-200 uppercase">Total Nilai (Lunas)</p><p class="text-2xl font-black mt-2"><?= e(rupiah($tot)) ?></p><p class="text-[11px] text-emerald-200/80 mt-1"><?= count($paid) ?> transaksi</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Volume QRIS</p><p class="text-xl font-black mt-2"><?= e(rupiah($sum($qr))) ?></p><p class="text-[11px] text-slate-500"><?= count($qr) ?> transaksi</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Volume Tunai</p><p class="text-xl font-black mt-2"><?= e(rupiah($sum($cs))) ?></p><p class="text-[11px] text-slate-500"><?= count($cs) ?> transaksi</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Rata-rata Keranjang</p><p class="text-xl font-black mt-2"><?= e(rupiah($avg)) ?></p><p class="text-[11px] text-slate-500">per tiket</p></div>
</section>
<form method="get" class="card p-4 space-y-3 no-print">
  <div class="flex flex-wrap gap-2 text-xs font-bold">
    <a class="chip" href="<?= e($pre(date('Y-m-d'), date('Y-m-d'))) ?>">Hari Ini</a><a class="chip" href="<?= e($pre(date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')))) ?>">Kemarin</a>
    <a class="chip" href="<?= e($pre(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'))) ?>">7 Hari</a><a class="chip" href="<?= e($pre(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'))) ?>">30 Hari</a>
  </div>
  <div class="flex flex-wrap gap-3 items-end">
    <div><label class="lbl">Dari</label><input class="input" type="date" name="dari" value="<?= e($dari) ?>"></div>
    <div><label class="lbl">Sampai</label><input class="input" type="date" name="sampai" value="<?= e($sampai) ?>"></div>
    <div><label class="lbl">Metode</label><select class="input" name="metode"><option value="">Semua</option><option value="QRIS" <?= $fm === 'QRIS' ? 'selected' : '' ?>>QRIS</option><option value="cash" <?= $fm === 'cash' ? 'selected' : '' ?>>Tunai</option></select></div>
    <div><label class="lbl">Meja</label><select class="input" name="meja"><option value="0">Semua</option><?php foreach ($meja as $m): ?><option value="<?= (int)$m['id_meja'] ?>" <?= $fj === (int)$m['id_meja'] ? 'selected' : '' ?>>Meja <?= (int)$m['nomor_meja'] ?></option><?php endforeach; ?></select></div>
    <div class="flex-1 min-w-[160px]"><label class="lbl">Cari</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="No. transaksi / nama"></div>
    <button class="btn btn-primary">Terapkan</button>
  </div>
</form>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl">
  <thead><tr><th>No &amp; Waktu</th><th>Pelanggan</th><th>Meja</th><th>Item</th><th>Metode</th><th>Total</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td class="whitespace-nowrap"><b>#TRX-<?= str_pad($r['id_transaksi'], 3, '0', STR_PAD_LEFT) ?></b><div class="text-[11px] text-slate-400"><?= e(date('d M Y H:i', strtotime($r['waktu_pesanan']))) ?></div></td>
      <td><?= e($r['nama_pemesan']) ?><div class="text-[11px] text-slate-400"><?= $r['tipe_pesanan'] === 'QR' ? 'Via QR' : 'Manual' ?></div></td>
      <td><?= $r['nomor_meja'] ? 'Meja ' . str_pad($r['nomor_meja'], 2, '0', STR_PAD_LEFT) : 'Bungkus' ?></td>
      <td class="max-w-[260px]"><b><?= (int)$r['qty'] ?> item:</b> <span class="text-slate-600"><?= e($r['item']) ?></span></td>
      <td><?= $r['metode_pembayaran'] === 'QRIS' ? badge('QRIS', 'blue') : badge('Tunai', 'gray') ?></td>
      <td class="font-bold whitespace-nowrap"><?= e(rupiah($r['total_harga'])) ?></td>
      <td><?= status_badge($r['status_pesanan']) ?> <div class="mt-1"><?= status_badge($r['status_pembayaran'] ?? 'menunggu') ?></div></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-slate-400 py-10">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?>
  </tbody></table></div>
  <div class="px-4 py-3 text-xs text-slate-500 border-t border-line">Menampilkan <?= count($rows) ?> transaksi (maks. 1000) • Total nilai lunas: <b><?= e(rupiah($tot)) ?></b></div></div>
<?php admin_footer();
