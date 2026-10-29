<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$kat = qall('SELECT k.*, (SELECT COUNT(*) FROM menu m WHERE m.id_kategori = k.id_kategori) jml FROM kategori_menu k ORDER BY id_kategori');
$menu = qall('SELECT m.*, k.nama_kategori FROM menu m JOIN kategori_menu k ON k.id_kategori = m.id_kategori ORDER BY m.nama_menu');
$meja = qall('SELECT * FROM meja ORDER BY nomor_meja');

admin_header('Input Pesanan Manual', 'pesanan_manual', 'Operasional › Walk-in POS', 'Input pesanan langsung untuk pelanggan walk-in. Pesanan otomatis masuk ke antrean dapur.'); ?>
<div class="grid xl:grid-cols-3 gap-5 items-start">
  <section class="xl:col-span-2 space-y-4">
    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= icon('search') ?></span>
      <input id="q" class="input pl-10" placeholder="Cari menu... (cth: Tubruk, Indomie, Pisang)" autocomplete="off"></div>
    <div class="flex gap-2 overflow-x-auto pb-1" id="kat">
      <button class="chip on" data-k="0">Semua (<?= count($menu) ?>)</button>
      <?php foreach ($kat as $k): ?><button class="chip" data-k="<?= (int)$k['id_kategori'] ?>"><?= e($k['nama_kategori']) ?> (<?= (int)$k['jml'] ?>)</button><?php endforeach; ?>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3" id="menu-grid">
      <?php foreach ($menu as $m): $ok = $m['status_menu'] === 'tersedia' && $m['stok'] > 0; ?>
      <button type="button" <?= $ok ? '' : 'disabled' ?> class="card p-4 text-left transition <?= $ok ? 'hover:shadow-md hover:border-pine' : 'opacity-50 cursor-not-allowed' ?>"
        data-id="<?= (int)$m['id_menu'] ?>" data-nama="<?= e($m['nama_menu']) ?>" data-harga="<?= (float)$m['harga'] ?>" data-kat="<?= (int)$m['id_kategori'] ?>" data-stok="<?= (int)$m['stok'] ?>">
        <p class="text-[10px] font-bold tracking-wider text-tanText uppercase"><?= e($m['nama_kategori']) ?></p>
        <p class="font-bold text-sm mt-1"><?= e($m['nama_menu']) ?></p>
        <div class="flex items-center justify-between mt-3"><span class="font-extrabold text-pine"><?= e(rupiah($m['harga'])) ?></span>
        <span class="text-[11px] font-bold <?= $ok ? 'text-emerald-700' : 'text-red-600' ?>"><?= $ok ? '+ Tambah' : 'Habis' ?></span></div>
      </button>
      <?php endforeach; ?>
    </div>
  </section>

  <aside class="card p-5 space-y-4 xl:sticky xl:top-4">
    <div class="flex items-center justify-between"><h3 class="font-bold">Rincian Pesanan</h3><span class="text-[11px] font-bold px-2 py-1 rounded-full bg-creamField">Manual</span></div>
    <div><label class="lbl" for="nama">Nama Pemesan *</label><input class="input" id="nama" maxlength="100" placeholder="Nama pelanggan"></div>
    <div><label class="lbl">Nomor Meja *</label>
      <div class="flex flex-wrap gap-2" id="meja-pick">
        <?php foreach ($meja as $m): ?><button type="button" class="chip" data-m="<?= (int)$m['id_meja'] ?>"><?= str_pad($m['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></button><?php endforeach; ?>
        <button type="button" class="chip" data-m="0">Bungkus / Takeaway</button>
      </div></div>
    <div><label class="lbl">Item Pesanan</label><div id="ticket" class="space-y-2 max-h-72 overflow-y-auto"></div></div>
    <div><label class="lbl">Metode Pembayaran *</label>
      <div class="grid grid-cols-2 gap-2" id="metode">
        <button type="button" class="chip on" data-p="cash">Cash / Tunai</button><button type="button" class="chip" data-p="QRIS">QRIS Instant</button></div>
      <p class="text-[11px] text-slate-500 mt-1.5" id="metode-info">Tunai: dianggap langsung lunas saat disimpan.</p></div>
    <div class="border-t border-line pt-3 flex items-center justify-between"><span class="text-sm font-semibold text-slate-500">Total Tagihan</span><span class="text-xl font-black" id="total">Rp 0</span></div>
    <div class="flex gap-2"><button class="btn btn-ghost" id="reset" type="button">Hapus Semua</button><button class="btn btn-primary flex-1" id="kirim" type="button">Simpan &amp; Kirim ke Antrean</button></div>
  </aside>
</div>
<?php admin_footer(['pos.js']);
