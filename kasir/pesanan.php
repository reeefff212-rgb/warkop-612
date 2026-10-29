<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');
$GLOBALS['page_actions'] = '<span class="text-xs font-semibold text-slate-500" id="total-antre"></span><a href="' . e(url('kasir/pesanan_manual.php')) . '" class="btn btn-primary">' . icon('plus') . ' Pesanan Baru</a>';
admin_header('Manajemen Antrean Pesanan', 'pesanan', 'KDS & live floor monitor', 'Pantau tiket aktif, proses pesanan dapur, dan perbarui status saji. Diperbarui otomatis tiap 10 detik.'); ?>
<section class="grid md:grid-cols-3 gap-4 items-start">
  <div><h3 class="text-xs font-extrabold tracking-wider uppercase text-amber-700 mb-2">Menunggu <span id="c-menunggu" class="ml-1 px-2 py-0.5 rounded-full bg-amber-100">0</span></h3><div id="col-menunggu" class="space-y-3"></div></div>
  <div><h3 class="text-xs font-extrabold tracking-wider uppercase text-sky-700 mb-2">Sedang Diproses <span id="c-diproses" class="ml-1 px-2 py-0.5 rounded-full bg-sky-100">0</span></h3><div id="col-diproses" class="space-y-3"></div></div>
  <div><h3 class="text-xs font-extrabold tracking-wider uppercase text-emerald-700 mb-2">Selesai Hari Ini <span id="c-selesai" class="ml-1 px-2 py-0.5 rounded-full bg-emerald-100">0</span></h3><div id="col-selesai" class="space-y-3"></div></div>
</section>
<details class="card p-4 no-print"><summary class="text-sm font-bold cursor-pointer">Pesanan dibatalkan hari ini (<span id="c-dibatalkan">0</span>)</summary><div id="col-dibatalkan" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-3"></div></details>
<?php admin_footer(['pesanan.js']);
