<?php
require_once __DIR__ . '/../includes/pelanggan.php';
$meja = pel_meja();
pel_header('Keranjang Meja', $meja); ?>
<div class="p-4 space-y-4">
  <div class="card p-4 flex items-center justify-between"><div><p class="text-[10px] font-bold tracking-wider text-emerald-700 uppercase">● Terkoneksi meja</p><p class="font-bold">Pesanan untuk Meja <?= str_pad($meja['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></p></div><span class="text-pine"><?= icon('cart','w-6 h-6') ?></span></div>
  <div id="items" class="space-y-3"></div>
  <div id="form" class="space-y-4 hidden">
    <div class="card p-4"><label class="lbl" for="nama">Nama Pemesan *</label><input class="input" id="nama" maxlength="100" placeholder="Ketik nama Anda"><p class="text-[11px] text-slate-500 mt-1">Dipakai saat barista memanggil pesanan Anda.</p></div>
    <div class="card p-4 space-y-2"><p class="lbl">Metode Pembayaran</p>
      <label class="flex gap-3 items-start p-3 rounded-xl border border-line cursor-pointer has-[:checked]:border-pine has-[:checked]:bg-emerald-50/50"><input type="radio" name="metode" value="QRIS" checked class="mt-1 text-pine"><div><p class="font-bold text-sm">QRIS Instant <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">REKOMENDASI</span></p><p class="text-xs text-slate-500">Scan lewat GoPay, OVO, Dana, ShopeePay &amp; m-banking.</p></div></label>
      <label class="flex gap-3 items-start p-3 rounded-xl border border-line cursor-pointer has-[:checked]:border-pine has-[:checked]:bg-emerald-50/50"><input type="radio" name="metode" value="cash" class="mt-1 text-pine"><div><p class="font-bold text-sm">Cash / Tunai di Kasir</p><p class="text-xs text-slate-500">Bayar langsung ke kasir sebelum atau sesudah pesanan diantar.</p></div></label></div>
    <div class="card p-4"><div class="flex justify-between text-sm"><span class="text-slate-500">Subtotal (<span id="n">0</span> item)</span><span id="sub"></span></div>
      <div class="flex justify-between mt-2 pt-2 border-t border-line font-extrabold"><span>Total Bayar</span><span id="tot" class="text-pine"></span></div></div>
    <button id="go" class="btn btn-primary w-full py-4 text-sm justify-between"><span>Checkout Pesanan</span><span id="go-t"></span></button>
    <p class="text-[11px] text-center text-slate-400">Pesanan langsung masuk antrean barista. Duduk santai, kami antarkan ke Meja <?= (int)$meja['nomor_meja'] ?>.</p>
  </div>
</div>
<script>
Cart.useMeja(<?= (int)$meja['id_meja'] ?>);
const $ = s => document.querySelector(s);
function paint() {
  const items = Object.values(Cart.load().items), box = $('#items');
  if (!items.length) { box.innerHTML = `<div class="card p-8 text-center"><p class="font-bold">Keranjang masih kosong</p><a class="btn btn-primary mt-4" href="<?= e(url('pelanggan/katalog.php')) ?>">Lihat Menu</a></div>`; $('#form').classList.add('hidden'); Cart.badge(); return; }
  $('#form').classList.remove('hidden');
  box.innerHTML = '<p class="lbl">Item dipesan (' + items.length + ')</p>' + items.map(i => `<div class="card p-3" data-id="${i.id}">
    <div class="flex justify-between gap-2"><div><p class="font-bold text-sm">${WK.esc(i.nama)}</p><p class="text-[11px] text-slate-500">${WK.rupiah(i.harga)}${i.catatan ? ' • “' + WK.esc(i.catatan) + '”' : ''}</p></div><b class="text-sm">${WK.rupiah(i.harga * i.qty)}</b></div>
    <div class="flex items-center justify-between mt-2"><div class="flex items-center gap-2"><button class="btn btn-ghost btn-sm" data-d="-1">−</button><b class="w-5 text-center text-sm">${i.qty}</b><button class="btn btn-primary btn-sm" data-d="1">+</button></div>
    <button class="text-xs font-bold text-red-600" data-d="-999">Hapus</button></div></div>`).join('');
  $('#n').textContent = Cart.count(); $('#sub').textContent = $('#tot').textContent = $('#go-t').textContent = WK.rupiah(Cart.total());
  Cart.badge();
}
$('#items').addEventListener('click', e => {
  const b = e.target.closest('button[data-d]'); if (!b) return;
  const it = Cart.load().items[+b.closest('[data-id]').dataset.id];
  const n = it.qty + +b.dataset.d;
  if (n > it.stok) return WK.toast('Stok tidak cukup', true);
  Cart.set(it, n); paint();
});
$('#nama').value = localStorage.getItem('wk612_nama') || '';
$('#go').addEventListener('click', async () => {
  const nama = $('#nama').value.trim(); if (!nama) { $('#nama').focus(); return WK.toast('Nama pemesan wajib diisi', true); }
  localStorage.setItem('wk612_nama', nama);
  const btn = $('#go'); btn.disabled = true;
  try {
    const r = await WK.post('/api/pelanggan.php?action=checkout', {nama, metode: document.querySelector('input[name=metode]:checked').value,
      items: Object.values(Cart.load().items).map(i => ({id: i.id, qty: i.qty, catatan: i.catatan}))});
    Cart.clear(); location.href = r.next;
  } catch (e) { WK.toast(e.message, true); btn.disabled = false; }
});
paint();
</script>
<?php pel_footer('cart');
