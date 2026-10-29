<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
$kat = qall('SELECT * FROM kategori_menu ORDER BY id_kategori');
$menu = qall('SELECT * FROM menu ORDER BY (status_menu = "tersedia" AND stok > 0) DESC, nama_menu');
$antre = (int)qval("SELECT COUNT(*) FROM transaksi WHERE status_pesanan IN ('menunggu','diproses')");
pel_header('Menu Katalog', $meja); ?>
<div class="px-4 pt-4 space-y-3">
  <div class="flex items-center justify-between text-xs"><span class="text-slate-500">Pesan langsung dari mejamu, diantar hangat ke meja.</span>
    <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 font-bold whitespace-nowrap">Antrean: <?= $antre ?></span></div>
  <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= icon('search') ?></span><input id="q" class="input pl-10 !rounded-full" placeholder="Cari kopi, mie, atau camilan..."></div>
  <div class="flex gap-2 overflow-x-auto pb-1" id="kat"><button class="chip on" data-k="0">Semua</button><?php foreach ($kat as $k): ?><button class="chip" data-k="<?= (int)$k['id_kategori'] ?>"><?= e($k['nama_kategori']) ?></button><?php endforeach; ?></div>
  <div class="space-y-3" id="list">
  <?php foreach ($menu as $m): $ok = $m['status_menu'] === 'tersedia' && $m['stok'] > 0; ?>
    <article class="card p-3 flex gap-3 <?= $ok ? '' : 'opacity-60' ?>" data-kat="<?= (int)$m['id_kategori'] ?>" data-nama="<?= e(mb_strtolower($m['nama_menu'])) ?>">
      <a href="<?= e(url('pelanggan/detail.php?id=' . $m['id_menu'])) ?>" class="shrink-0"><?php if ($m['foto_menu']): ?><img class="w-20 h-20 rounded-xl object-cover" src="<?= e(url($m['foto_menu'])) ?>" alt=""><?php else: ?><div class="w-20 h-20 rounded-xl bg-creamField text-slate-400 flex items-center justify-center"><?= icon('cup','w-7 h-7') ?></div><?php endif; ?></a>
      <div class="flex-1 min-w-0 flex flex-col">
        <a href="<?= e(url('pelanggan/detail.php?id=' . $m['id_menu'])) ?>" class="font-bold text-sm leading-tight"><?= e($m['nama_menu']) ?></a>
        <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5"><?= e($m['deskripsi']) ?></p>
        <div class="flex items-center justify-between mt-auto pt-2"><span class="font-extrabold text-pine text-sm"><?= e(rupiah($m['harga'])) ?></span>
          <?php if ($ok): ?><div class="ctl" data-id="<?= (int)$m['id_menu'] ?>" data-nama="<?= e($m['nama_menu']) ?>" data-harga="<?= (float)$m['harga'] ?>" data-stok="<?= (int)$m['stok'] ?>"></div>
          <?php else: ?><span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-600">HABIS</span><?php endif; ?></div>
      </div>
    </article>
  <?php endforeach; ?></div>
  <p id="kosong" class="hidden text-center text-sm text-slate-400 py-10">Menu tidak ditemukan.</p>
</div>
<a id="bar" href="<?= e(url('pelanggan/keranjang.php')) ?>" class="hidden fixed bottom-16 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-[26rem] bg-pine text-white rounded-2xl px-4 py-3 items-center justify-between shadow-xl z-30">
  <span class="text-sm font-bold">Buka Keranjang (<span id="bar-n">0</span>)</span><span class="text-sm font-extrabold" id="bar-t"></span></a>
<script>
const MEJA_ID = <?= (int)$meja['id_meja'] ?>;
Cart.useMeja(MEJA_ID);
function paint() {
  document.querySelectorAll('.ctl').forEach(c => {
    const q = Cart.qty(+c.dataset.id);
    c.innerHTML = q ? `<div class="flex items-center gap-2"><button class="btn btn-ghost btn-sm" data-d="-1">−</button><b class="text-sm w-5 text-center">${q}</b><button class="btn btn-primary btn-sm" data-d="1">+</button></div>`
                    : `<button class="btn btn-primary btn-sm" data-d="1">+ Tambah</button>`;
  });
  const n = Cart.count(), bar = document.getElementById('bar');
  bar.classList.toggle('hidden', !n); bar.classList.toggle('flex', !!n);
  document.getElementById('bar-n').textContent = n; document.getElementById('bar-t').textContent = WK.rupiah(Cart.total());
  Cart.badge();
}
document.getElementById('list').addEventListener('click', e => {
  const b = e.target.closest('button[data-d]'); if (!b) return;
  const c = b.closest('.ctl'), menu = {id: +c.dataset.id, nama: c.dataset.nama, harga: +c.dataset.harga, stok: +c.dataset.stok};
  const next = Cart.qty(menu.id) + +b.dataset.d;
  if (next > menu.stok) return WK.toast('Stok tidak cukup', true);
  Cart.set(menu, next); paint();
});
let kat = 0;
function filter() {
  const q = document.getElementById('q').value.trim().toLowerCase(); let n = 0;
  document.querySelectorAll('#list article').forEach(a => { const ok = (!kat || +a.dataset.kat === kat) && a.dataset.nama.includes(q); a.style.display = ok ? '' : 'none'; if (ok) n++; });
  document.getElementById('kosong').classList.toggle('hidden', n > 0);
}
document.getElementById('q').addEventListener('input', filter);
document.getElementById('kat').addEventListener('click', e => { const c = e.target.closest('.chip'); if (!c) return; kat = +c.dataset.k; document.querySelectorAll('#kat .chip').forEach(x => x.classList.toggle('on', x === c)); filter(); });
paint();
</script>
<?php pel_footer('menu');
