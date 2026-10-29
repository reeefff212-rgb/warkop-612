<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area(AREA);

$per = in_array($_GET['periode'] ?? '', ['harian', 'mingguan', 'bulanan'], true) ? $_GET['periode'] : 'harian';
$tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl'] ?? '') ? $_GET['tgl'] : date('Y-m-d');
$ts = strtotime($tgl);
if ($per === 'harian') { $d1 = $d2 = $tgl; $p1 = $p2 = date('Y-m-d', strtotime('-1 day', $ts)); $label = tgl_id($tgl); }
elseif ($per === 'mingguan') {
    $d1 = date('Y-m-d', strtotime('monday this week', $ts)); $d2 = date('Y-m-d', strtotime('sunday this week', $ts));
    $p1 = date('Y-m-d', strtotime('-7 days', strtotime($d1))); $p2 = date('Y-m-d', strtotime('-7 days', strtotime($d2)));
    $label = date('j M', strtotime($d1)) . ' – ' . date('j M Y', strtotime($d2));
} else {
    $d1 = date('Y-m-01', $ts); $d2 = date('Y-m-t', $ts);
    $p1 = date('Y-m-01', strtotime('-1 month', strtotime($d1))); $p2 = date('Y-m-t', strtotime($p1));
    $label = bulan_id((int)date('n', $ts)) . ' ' . date('Y', $ts);
}
$R = fn($a, $b) => ["$a 00:00:00", "$b 23:59:59"];
$stat = function ($a, $b, $m = '') {
    [$x, $y] = [$a . ' 00:00:00', $b . ' 23:59:59'];
    return qrow("SELECT COUNT(*) trx, COALESCE(SUM(p.jumlah_bayar),0) omzet FROM pembayaran p JOIN transaksi t ON t.id_transaksi = p.id_transaksi
                 WHERE p.status_pembayaran = 'berhasil' AND t.status_pesanan <> 'dibatalkan' AND t.waktu_pesanan BETWEEN ? AND ?" . ($m ? " AND p.metode_pembayaran = '$m'" : ''), [$x, $y]);
};
$now = $stat($d1, $d2); $prev = $stat($p1, $p2); $qr = $stat($d1, $d2, 'QRIS'); $cs = $stat($d1, $d2, 'cash');
$delta = $prev['omzet'] > 0 ? round(($now['omzet'] - $prev['omzet']) / $prev['omzet'] * 100) : null;
[$x, $y] = $R($d1, $d2);
$base = "FROM detail_transaksi d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
         JOIN menu m ON m.id_menu = d.id_menu JOIN kategori_menu k ON k.id_kategori = m.id_kategori
         WHERE p.status_pembayaran = 'berhasil' AND t.status_pesanan <> 'dibatalkan' AND t.waktu_pesanan BETWEEN ? AND ?";
$terjual = (int)qval("SELECT COALESCE(SUM(d.jumlah),0) $base", [$x, $y]);
$top = qall("SELECT m.nama_menu, k.nama_kategori, SUM(d.jumlah) qty, SUM(d.subtotal) omzet $base GROUP BY m.id_menu ORDER BY qty DESC LIMIT 10", [$x, $y]);
$perKat = qall("SELECT k.nama_kategori, SUM(d.jumlah) qty $base GROUP BY k.id_kategori ORDER BY qty DESC", [$x, $y]);
$grp = $per === 'harian' ? "HOUR(t.waktu_pesanan)" : "DATE(t.waktu_pesanan)";
$tren = qall("SELECT $grp k, SUM(p.jumlah_bayar) omzet, COUNT(*) trx FROM pembayaran p JOIN transaksi t ON t.id_transaksi = p.id_transaksi
              WHERE p.status_pembayaran = 'berhasil' AND t.status_pesanan <> 'dibatalkan' AND t.waktu_pesanan BETWEEN ? AND ? GROUP BY k ORDER BY k", [$x, $y]);
$maks = max(array_merge([1], array_map(fn($r) => (float)$r['omzet'], $tren)));
$maksMenu = max(array_merge([1], array_map(fn($r) => (int)$r['qty'], $top)));

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8'); header("Content-Disposition: attachment; filename=laporan_{$per}_{$d1}.csv");
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, ["Laporan Penjualan WARKOP 612", $label]); fputcsv($o, ['Pendapatan', (int)$now['omzet']]); fputcsv($o, ['Transaksi', (int)$now['trx']]);
    fputcsv($o, ['QRIS', (int)$qr['omzet']]); fputcsv($o, ['Tunai', (int)$cs['omzet']]); fputcsv($o, ['Menu Terjual', $terjual]); fputcsv($o, []);
    fputcsv($o, ['Menu', 'Kategori', 'Qty', 'Omzet']); foreach ($top as $r) fputcsv($o, [$r['nama_menu'], $r['nama_kategori'], $r['qty'], (int)$r['omzet']]);
    fputcsv($o, []); fputcsv($o, [$per === 'harian' ? 'Jam' : 'Tanggal', 'Transaksi', 'Omzet']); foreach ($tren as $r) fputcsv($o, [$r['k'], $r['trx'], (int)$r['omzet']]);
    exit;
}
$pct = fn($a, $b) => $b > 0 ? round($a / $b * 100) : 0;
$GLOBALS['page_actions'] = '<button onclick="window.print()" class="btn btn-ghost">' . icon('print') . ' Unduh PDF</button><a class="btn btn-primary" href="?' . e(http_build_query(array_merge($_GET, ['export' => 'csv']))) . '">' . icon('download') . ' Ekspor Excel</a>';
admin_header('Laporan Penjualan', 'laporan', 'Laporan & analitik › ' . ucfirst($per), 'Pendapatan, volume transaksi, dan performa menu terlaris.'); ?>
<form method="get" class="card p-4 flex flex-wrap gap-3 items-end no-print">
  <div class="flex gap-1 p-1 bg-creamField rounded-xl text-xs font-bold">
    <?php foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $l): ?><button name="periode" value="<?= $k ?>" class="px-4 py-2 rounded-lg <?= $per === $k ? 'bg-pine text-white' : '' ?>"><?= $l ?></button><?php endforeach; ?></div>
  <div><label class="lbl">Tanggal acuan</label><input class="input" type="date" name="tgl" value="<?= e($tgl) ?>" onchange="this.form.submit()"></div>
  <p class="ml-auto text-sm font-bold text-pine"><?= e($label) ?></p>
</form>
<h3 class="print-only text-lg font-bold">Laporan Penjualan WARKOP 612 – <?= e($label) ?></h3>

<section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
  <div class="rounded-2xl p-4 bg-pine text-white"><p class="text-[11px] font-bold tracking-wider text-emerald-200 uppercase">Pendapatan</p><p class="text-2xl font-black mt-2"><?= e(rupiah($now['omzet'])) ?></p>
    <p class="text-[11px] text-emerald-200/90 mt-1"><?= $delta === null ? 'Belum ada pembanding' : ($delta >= 0 ? '▲ +' : '▼ ') . $delta . '% vs periode lalu' ?></p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Jumlah Transaksi</p><p class="text-3xl font-extrabold mt-2"><?= (int)$now['trx'] ?></p><p class="text-[11px] text-slate-500">order lunas</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menu Terjual</p><p class="text-3xl font-extrabold mt-2"><?= $terjual ?></p><p class="text-[11px] text-slate-500">porsi / cangkir</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Rata-rata / Transaksi</p><p class="text-xl font-extrabold mt-2"><?= e(rupiah($now['trx'] ? $now['omzet'] / $now['trx'] : 0)) ?></p></div>
</section>

<section class="grid lg:grid-cols-3 gap-5">
  <div class="card p-5 lg:col-span-2"><h3 class="font-bold mb-1">Grafik <?= $per === 'harian' ? 'Jam Operasional' : 'Harian' ?></h3><p class="text-xs text-slate-500 mb-4">Pendapatan terverifikasi per <?= $per === 'harian' ? 'jam' : 'hari' ?></p>
    <?php if ($tren): ?><div class="flex items-end gap-1.5 h-48 overflow-x-auto">
      <?php foreach ($tren as $r): $h = max(4, round($r['omzet'] / $maks * 100)); ?>
        <div class="flex flex-col items-center justify-end h-full min-w-[28px] flex-1 group" title="<?= e(rupiah($r['omzet'])) ?> • <?= (int)$r['trx'] ?> trx">
          <span class="text-[9px] text-slate-400 opacity-0 group-hover:opacity-100"><?= e(number_format($r['omzet'] / 1000, 0)) ?>k</span>
          <div class="w-full rounded-t-md bg-pine/80 group-hover:bg-pine" style="height:<?= $h ?>%"></div>
          <span class="text-[10px] mt-1 text-slate-500"><?= $per === 'harian' ? str_pad($r['k'], 2, '0', STR_PAD_LEFT) : date('j', strtotime($r['k'])) ?></span></div>
      <?php endforeach; ?></div><?php else: ?><p class="text-sm text-slate-400 py-12 text-center">Belum ada data penjualan pada periode ini.</p><?php endif; ?></div>
  <div class="card p-5"><h3 class="font-bold mb-4">Cash vs QRIS</h3>
    <?php foreach ([['QRIS', $qr, 'bg-sky-600'], ['Tunai', $cs, 'bg-amber-600']] as [$n, $d, $c]): ?>
      <div class="mb-4"><div class="flex justify-between text-sm"><b><?= $n ?></b><span><?= e(rupiah($d['omzet'])) ?> • <?= (int)$d['trx'] ?> order</span></div>
        <div class="h-2.5 rounded-full bg-creamField mt-1.5"><div class="h-full rounded-full <?= $c ?>" style="width:<?= $pct($d['omzet'], $now['omzet']) ?>%"></div></div><p class="text-[11px] text-slate-500 mt-1"><?= $pct($d['omzet'], $now['omzet']) ?>%</p></div>
    <?php endforeach; ?>
    <h4 class="font-bold text-sm mt-5 mb-2">Per Kategori</h4>
    <?php foreach ($perKat as $k): ?><div class="flex justify-between text-sm py-1"><span><?= e($k['nama_kategori']) ?></span><b><?= (int)$k['qty'] ?></b></div><?php endforeach; if (!$perKat) echo '<p class="text-xs text-slate-400">–</p>'; ?>
  </div>
</section>

<div class="card overflow-hidden"><div class="px-5 py-4 border-b border-line"><h3 class="font-bold">Menu Terlaris</h3></div>
  <table class="tbl"><thead><tr><th>#</th><th>Menu</th><th>Kategori</th><th>Terjual</th><th>Omzet</th></tr></thead><tbody>
  <?php foreach ($top as $i => $r): ?><tr><td class="font-bold text-slate-400"><?= $i + 1 ?></td><td class="font-bold"><?= e($r['nama_menu']) ?>
    <div class="h-1.5 rounded-full bg-creamField mt-1.5 w-40"><div class="h-full rounded-full bg-pine" style="width:<?= round($r['qty'] / $maksMenu * 100) ?>%"></div></div></td>
    <td><?= e($r['nama_kategori']) ?></td><td class="font-bold"><?= (int)$r['qty'] ?></td><td><?= e(rupiah($r['omzet'])) ?></td></tr>
  <?php endforeach; if (!$top) echo '<tr><td colspan="5" class="text-center text-slate-400 py-8">Belum ada data.</td></tr>'; ?>
  </tbody></table></div>
<?php admin_footer();
