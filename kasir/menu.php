<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$kat = qall('SELECT * FROM kategori_menu ORDER BY id_kategori');
$fk = (int)($_GET['kat'] ?? 0); $fs = $_GET['status'] ?? ''; $q = trim($_GET['q'] ?? '');
$w = ['1=1']; $p = [];
if ($fk) { $w[] = 'm.id_kategori = ?'; $p[] = $fk; }
if (in_array($fs, ['tersedia', 'habis'], true)) { $w[] = 'm.status_menu = ?'; $p[] = $fs; }
if ($q !== '') { $w[] = 'm.nama_menu LIKE ?'; $p[] = "%$q%"; }
$where = implode(' AND ', $w);
$per = 10; $total = (int)qval("SELECT COUNT(*) FROM menu m WHERE $where", $p);
$pages = max(1, (int)ceil($total / $per)); $pg = min($pages, max(1, (int)($_GET['p'] ?? 1)));
$rows = qall("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori_menu k ON k.id_kategori = m.id_kategori WHERE $where ORDER BY m.id_menu DESC LIMIT $per OFFSET " . (($pg - 1) * $per), $p);

$all = (int)qval('SELECT COUNT(*) FROM menu'); $ready = (int)qval("SELECT COUNT(*) FROM menu WHERE status_menu = 'tersedia'");
$laris = qrow("SELECT mn.nama_menu, SUM(d.jumlah) qty FROM detail_transaksi d JOIN transaksi t ON t.id_transaksi = d.id_transaksi JOIN menu mn ON mn.id_menu = d.id_menu
               WHERE t.waktu_pesanan >= ? AND t.status_pesanan <> 'dibatalkan' GROUP BY mn.id_menu ORDER BY qty DESC LIMIT 1", [date('Y-m-d 00:00:00')]);
$qs = fn($o = []) => '?' . http_build_query(array_merge(['kat' => $fk ?: '', 'status' => $fs, 'q' => $q], $o));

$GLOBALS['page_actions'] = '<a href="' . e(url('kasir/menu_form.php')) . '" class="btn btn-primary">' . icon('plus') . ' Tambah Menu Baru</a>';
admin_header('Manajemen Menu & Stok', 'menu', 'Katalog produk & dapur', 'Kelola racikan, harga, stok, dan status ketersediaan menu.'); ?>
<section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Menu</p><p class="text-3xl font-extrabold mt-2"><?= $all ?></p><p class="text-[11px] text-slate-500"><?= count($kat) ?> kategori</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Siap Disajikan</p><p class="text-3xl font-extrabold mt-2"><?= $ready ?></p><p class="text-[11px] text-slate-500"><?= $all ? round($ready / $all * 100) : 0 ?>% siap</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Menu Habis</p><p class="text-3xl font-extrabold mt-2 text-red-600"><?= $all - $ready ?></p><p class="text-[11px] text-slate-500">Perlu restock</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Terlaris Hari Ini</p><p class="text-base font-extrabold mt-2 leading-tight"><?= e($laris['nama_menu'] ?? '-') ?></p><p class="text-[11px] text-slate-500"><?= (int)($laris['qty'] ?? 0) ?> porsi terjual</p></div>
</section>
<form method="get" class="card p-4 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[180px]"><label class="lbl">Cari Menu</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Nama menu..."></div>
  <div><label class="lbl">Kategori</label><select class="input" name="kat"><option value="0">Semua</option><?php foreach ($kat as $k): ?><option value="<?= (int)$k['id_kategori'] ?>" <?= $fk === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option><?php endforeach; ?></select></div>
  <div><label class="lbl">Status</label><select class="input" name="status"><option value="">Semua</option><option value="tersedia" <?= $fs === 'tersedia' ? 'selected' : '' ?>>Tersedia</option><option value="habis" <?= $fs === 'habis' ? 'selected' : '' ?>>Habis</option></select></div>
  <button class="btn btn-primary">Filter</button><a class="btn btn-ghost" href="?">Reset</a>
</form>
<div class="card overflow-hidden"><div class="overflow-x-auto"><table class="tbl">
  <thead><tr><th>Foto</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $m): ?>
    <tr><td><?php if ($m['foto_menu']): ?><img class="w-12 h-12 rounded-xl object-cover" src="<?= e(url($m['foto_menu'])) ?>" alt=""><?php else: ?><div class="w-12 h-12 rounded-xl bg-creamField text-slate-400 flex items-center justify-center"><?= icon('cup','w-5 h-5') ?></div><?php endif; ?></td>
      <td class="font-bold"><?= e($m['nama_menu']) ?><div class="text-[11px] font-normal text-slate-400 max-w-xs truncate"><?= e($m['deskripsi']) ?></div></td>
      <td><?= e($m['nama_kategori']) ?></td><td class="font-bold whitespace-nowrap"><?= e(rupiah($m['harga'])) ?></td>
      <td class="<?= $m['stok'] <= 10 ? 'text-red-600 font-bold' : '' ?>"><?= (int)$m['stok'] ?></td>
      <td><button onclick="toggleMenu(<?= (int)$m['id_menu'] ?>)" title="Klik untuk ubah status"><?= status_badge($m['status_menu']) ?></button></td>
      <td class="whitespace-nowrap"><a class="btn btn-ghost btn-sm" href="<?= e(url('kasir/menu_form.php?id=' . $m['id_menu'])) ?>"><?= icon('edit','w-3.5 h-3.5') ?> Edit</a>
        <button class="btn btn-danger btn-sm" data-confirm="Hapus menu '<?= e($m['nama_menu']) ?>'?" onclick="hapusMenu(<?= (int)$m['id_menu'] ?>)"><?= icon('trash','w-3.5 h-3.5') ?></button></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-slate-400 py-10">Menu tidak ditemukan.</td></tr><?php endif; ?>
  </tbody></table></div>
  <div class="px-4 py-3 flex items-center justify-between text-xs text-slate-500 border-t border-line"><span>Menampilkan <?= $total ? ($pg - 1) * $per + 1 : 0 ?> – <?= min($total, $pg * $per) ?> dari <?= $total ?> menu</span>
    <div class="flex gap-1"><?php for ($i = 1; $i <= $pages; $i++): ?><a class="px-3 py-1 rounded-lg font-bold <?= $i === $pg ? 'bg-pine text-white' : 'bg-creamField' ?>" href="<?= e($qs(['p' => $i])) ?>"><?= $i ?></a><?php endfor; ?></div></div>
</div>
<script>
async function toggleMenu(id){ try{ await WK.post('/api/kasir.php?action=toggle_menu',{id}); location.reload(); }catch(e){ WK.toast(e.message,true);} }
async function hapusMenu(id){ try{ const r=await WK.post('/api/kasir.php?action=hapus_menu',{id}); WK.toast(r.msg); setTimeout(()=>location.reload(),900);}catch(e){ WK.toast(e.message,true);} }
</script>
<?php admin_footer();
