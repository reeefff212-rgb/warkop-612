<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$meja = qall("SELECT m.*,
    (SELECT t.nama_pemesan FROM transaksi t WHERE t.id_meja = m.id_meja AND t.status_pesanan IN ('menunggu','diproses') ORDER BY t.waktu_pesanan DESC LIMIT 1) AS pemesan,
    (SELECT t.id_transaksi FROM transaksi t WHERE t.id_meja = m.id_meja AND t.status_pesanan IN ('menunggu','diproses') ORDER BY t.waktu_pesanan DESC LIMIT 1) AS id_trx
    FROM meja m ORDER BY m.nomor_meja");
$terisi = count(array_filter($meja, fn($m) => $m['status_meja'] === 'terisi'));
$total = count($meja);
$js = array_map(fn($m) => ['id' => (int)$m['id_meja'], 'nomor' => (int)$m['nomor_meja'], 'url' => full_url('pelanggan/index.php?meja=' . $m['id_meja'])], $meja);

$GLOBALS['page_actions'] = '<button onclick="WKMeja.printAll()" class="btn btn-ghost">' . icon('print') . ' Cetak Semua QR</button><a href="' . e(url('kasir/meja_form.php')) . '" class="btn btn-primary">' . icon('plus') . ' Tambah Meja Baru</a>';
admin_header('Manajemen Meja & QR Code', 'meja', 'Pengaturan warkop', 'Kelola nomor meja, pantau status keterisian, dan cetak stiker QR pemesanan mandiri.'); ?>
<section class="grid grid-cols-3 gap-3.5">
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Meja Terdaftar</p><p class="text-3xl font-extrabold mt-2"><?= $total ?></p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Meja Terisi</p><p class="text-3xl font-extrabold mt-2"><?= $terisi ?></p><p class="text-[11px] text-slate-500 mt-1"><?= $total ? round($terisi / $total * 100, 1) : 0 ?>% okupansi</p></div>
  <div class="card p-4"><p class="text-[11px] font-bold tracking-wider text-slate-500 uppercase">Meja Kosong</p><p class="text-3xl font-extrabold mt-2"><?= $total - $terisi ?></p></div>
</section>

<div class="flex gap-2 no-print" id="filter-meja">
  <button class="chip on" data-f="all">Semua (<?= $total ?>)</button><button class="chip" data-f="tersedia">Kosong (<?= $total - $terisi ?>)</button><button class="chip" data-f="terisi">Terisi (<?= $terisi ?>)</button>
</div>

<section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4" id="grid-meja">
<?php foreach ($meja as $m): ?>
  <article class="card p-4" data-status="<?= e($m['status_meja']) ?>">
    <div class="flex items-start justify-between"><div><p class="text-3xl font-black text-pine"><?= str_pad($m['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></p><p class="text-xs font-bold text-slate-500">Meja <?= (int)$m['nomor_meja'] ?></p></div><?= status_badge($m['status_meja']) ?></div>
    <div class="mt-3 text-xs text-slate-600 min-h-[2.5rem]">
      <?php if ($m['pemesan']): ?>Pelanggan aktif: <b><?= e($m['pemesan']) ?></b><br><span class="text-slate-400">Pesanan #<?= (int)$m['id_trx'] ?></span><?php else: ?><span class="text-slate-400">Siap ditempati</span><?php endif; ?>
    </div>
    <div class="flex gap-2 mt-3">
      <button class="btn btn-ghost btn-sm flex-1" onclick="WKMeja.show(<?= (int)$m['id_meja'] ?>)"><?= icon('qr','w-3.5 h-3.5') ?> QR</button>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('kasir/meja_form.php?id=' . $m['id_meja'])) ?>" title="Edit"><?= icon('edit','w-3.5 h-3.5') ?></a>
      <button class="btn btn-danger btn-sm" data-confirm="Hapus Meja <?= (int)$m['nomor_meja'] ?>?" onclick="WKMeja.del(<?= (int)$m['id_meja'] ?>)" title="Hapus"><?= icon('trash','w-3.5 h-3.5') ?></button>
    </div>
  </article>
<?php endforeach; if (!$meja): ?><p class="col-span-full text-center text-slate-400 py-12">Belum ada meja. Klik "Tambah Meja Baru".</p><?php endif; ?>
</section>

<div class="modal-bg" id="qr-modal"><div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center">
  <h3 class="font-bold mb-4">Pratinjau Stiker Meja</h3>
  <div id="qr-sticker" class="mx-auto"></div>
  <p id="qr-link" class="text-[11px] text-slate-400 break-all mt-3"></p>
  <div class="flex gap-2 mt-4"><button class="btn btn-ghost flex-1" onclick="document.getElementById('qr-modal').classList.remove('open')">Tutup</button><button class="btn btn-primary flex-1" onclick="WKMeja.printOne()"><?= icon('print') ?> Cetak</button></div>
</div></div>
<script>window.MEJA = <?= json_encode($js, JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php admin_footer(['meja.js']);
