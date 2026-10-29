<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
$m = qrow('SELECT m.*, k.nama_kategori FROM menu m JOIN kategori_menu k ON k.id_kategori = m.id_kategori WHERE m.id_menu = ?', [(int)($_GET['id'] ?? 0)]);
if (!$m) redirect('pelanggan/katalog.php');
$ok = $m['status_menu'] === 'tersedia' && $m['stok'] > 0;
pel_header('Detail Menu', $meja); ?>
<div class="p-4 space-y-4">
  <a href="<?= e(url('pelanggan/katalog.php')) ?>" class="text-xs font-bold text-pine">&larr; Kembali ke menu</a>
  <?php if ($m['foto_menu']): ?><img src="<?= e(url($m['foto_menu'])) ?>" class="w-full h-52 object-cover rounded-2xl" alt=""><?php else: ?><div class="w-full h-40 rounded-2xl bg-creamField text-slate-300 flex items-center justify-center"><?= icon('cup','w-16 h-16') ?></div><?php endif; ?>
  <div><div class="flex items-center gap-2 text-[11px] font-bold"><span class="text-tanText uppercase tracking-wider"><?= e($m['nama_kategori']) ?></span><?= status_badge($ok ? 'tersedia' : 'habis') ?></div>
    <h1 class="text-2xl font-extrabold mt-1"><?= e($m['nama_menu']) ?></h1>
    <p class="text-xl font-black text-pine mt-1"><?= e(rupiah($m['harga'])) ?> <span class="text-xs font-semibold text-slate-400">/ porsi</span></p>
    <p class="text-sm text-slate-600 mt-2"><?= e($m['deskripsi']) ?></p></div>
  <?php if ($ok): ?>
  <div class="card p-4 flex items-center justify-between"><div><p class="text-sm font-bold">Jumlah Porsi</p><p class="text-[11px] text-slate-500">Porsi untuk meja kamu</p></div>
    <div class="flex items-center gap-3"><button class="btn btn-ghost" id="dec">−</button><b id="qty" class="w-6 text-center">1</b><button class="btn btn-primary" id="inc">+</button></div></div>
  <div><label class="lbl" for="cat">Catatan Pesanan <span class="normal-case font-normal">(opsional)</span></label><textarea class="input" id="cat" rows="3" maxlength="255" placeholder="Contoh: Gula sedikit saja, pedas level 2"></textarea></div>
  <button id="add" class="btn btn-primary w-full py-4 text-sm justify-between"><span>Masukkan ke Keranjang</span><span id="sub"></span></button>
  <?php endif; ?>
</div>
<script>
const M = {id: <?= (int)$m['id_menu'] ?>, nama: <?= json_encode($m['nama_menu']) ?>, harga: <?= (float)$m['harga'] ?>, stok: <?= (int)$m['stok'] ?>};
Cart.useMeja(<?= (int)$meja['id_meja'] ?>);
const q = document.getElementById('qty'); if (q) {
  const ex = Cart.load().items[M.id]; let n = ex ? ex.qty : 1; if (ex) document.getElementById('cat').value = ex.catatan || '';
  const up = () => { q.textContent = n; document.getElementById('sub').textContent = WK.rupiah(n * M.harga); }; up();
  document.getElementById('inc').onclick = () => { if (n >= M.stok) return WK.toast('Stok tidak cukup', true); n++; up(); };
  document.getElementById('dec').onclick = () => { if (n > 1) n--; up(); };
  document.getElementById('add').onclick = () => { Cart.set(M, n, document.getElementById('cat').value.trim()); location.href = '<?= e(url('pelanggan/katalog.php')) ?>'; };
}
</script>
<?php pel_footer('menu');
