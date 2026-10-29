<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/order.php';

/** Pastikan pelanggan sudah "scan" meja; kembalikan data meja. */
function pel_meja(): array {
    $id = (int)($_SESSION['meja'] ?? 0);
    $m = $id ? qrow('SELECT * FROM meja WHERE id_meja = ?', [$id]) : null;
    if (!$m) { flash('Silakan scan QR di meja Anda terlebih dahulu.', 'err'); redirect('pelanggan/index.php'); }
    return $m;
}
/** Ambil pesanan milik sesi ini. */
function pel_order(?int $id = null): array {
    $id = $id ?: (int)($_SESSION['last_order'] ?? 0);
    if (!$id || !in_array($id, $_SESSION['orders'] ?? [], true)) { redirect('pelanggan/katalog.php'); }
    $o = qrow('SELECT t.*, m.nomor_meja, p.metode_pembayaran, p.status_pembayaran, p.waktu_pembayaran
               FROM transaksi t LEFT JOIN meja m ON m.id_meja = t.id_meja LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi WHERE t.id_transaksi = ?', [$id]);
    if (!$o) redirect('pelanggan/katalog.php');
    return $o;
}

function pel_header(string $title, ?array $meja = null): void {
    html_head($title, 'bg-[#F3F0E7]'); ?>
<div class="max-w-md mx-auto min-h-screen bg-cream shadow-xl relative pb-24">
  <header class="sticky top-0 z-30 bg-pine text-white px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2.5"><div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center"><?= icon('cup','w-4 h-4') ?></div>
      <div><p class="text-sm font-extrabold leading-none">WARKOP 612</p><p class="text-[10px] text-emerald-200 mt-0.5"><?= e($title) ?></p></div></div>
    <?php if ($meja): ?><span class="text-xs font-extrabold px-3 py-1 rounded-full bg-tan text-[#4F4225]">MEJA <?= str_pad($meja['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span><?php endif; ?>
  </header>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<script src="<?= e(url('assets/js/pelanggan.js')) ?>"></script>
<?php }

function pel_footer(string $active = '', array $scripts = []): void {
    $tabs = [['menu', 'Menu', 'pelanggan/katalog.php', 'cup'], ['cart', 'Keranjang', 'pelanggan/keranjang.php', 'cart'], ['status', 'Pesanan', 'pelanggan/status.php', 'clip'], ['meja', 'Meja', 'pelanggan/index.php', 'qr']]; ?>
  <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white border-t border-line grid grid-cols-4 z-40">
    <?php foreach ($tabs as [$k, $l, $href, $ic]): ?>
      <a href="<?= e(url($href)) ?>" class="relative flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-bold <?= $k === $active ? 'text-pine' : 'text-slate-400' ?>"><?= icon($ic, 'w-5 h-5') ?><?= $l ?>
      <?php if ($k === 'cart'): ?><span id="nav-badge" class="hidden absolute top-1 right-[28%] min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[9px] items-center justify-center">0</span><?php endif; ?></a>
    <?php endforeach; ?>
  </nav>
</div>
<div id="toast-root"></div>
<?php foreach ($scripts as $s): ?><script src="<?= e($s) ?>"></script><?php endforeach; ?>
</body></html>
<?php }
