/* Input Pesanan Manual (POS) */
(() => {
  const cart = new Map();           // id -> {id,nama,harga,qty,stok,catatan}
  let meja = null, metode = 'cash';
  const $ = s => document.querySelector(s);
  const ticket = $('#ticket');

  function render() {
    let total = 0, html = '';
    cart.forEach(it => {
      total += it.harga * it.qty;
      html += `<div class="rounded-xl border border-line p-2.5" data-id="${it.id}">
        <div class="flex items-center justify-between gap-2"><span class="text-sm font-semibold">${WK.esc(it.nama)}</span><span class="text-sm font-bold">${WK.rupiah(it.harga * it.qty)}</span></div>
        <div class="flex items-center gap-2 mt-1.5">
          <button class="btn btn-ghost btn-sm" data-act="dec">−</button><b class="w-6 text-center text-sm">${it.qty}</b><button class="btn btn-ghost btn-sm" data-act="inc">+</button>
          <input class="input !py-1 !text-xs flex-1" data-act="note" placeholder="Catatan (opsional)" maxlength="255" value="${WK.esc(it.catatan)}">
        </div></div>`;
    });
    ticket.innerHTML = html || '<p class="text-xs text-slate-400 py-4 text-center">Belum ada item. Klik menu di sebelah kiri.</p>';
    $('#total').textContent = WK.rupiah(total);
  }

  $('#menu-grid').addEventListener('click', e => {
    const b = e.target.closest('button[data-id]'); if (!b || b.disabled) return;
    const id = +b.dataset.id, it = cart.get(id);
    if (it) { if (it.qty >= it.stok) return WK.toast('Stok tidak cukup', true); it.qty++; }
    else cart.set(id, {id, nama: b.dataset.nama, harga: +b.dataset.harga, qty: 1, stok: +b.dataset.stok, catatan: ''});
    render();
  });
  ticket.addEventListener('click', e => {
    const act = e.target.dataset.act, id = +e.target.closest('[data-id]')?.dataset.id; const it = cart.get(id);
    if (!it) return;
    if (act === 'inc') { if (it.qty >= it.stok) return WK.toast('Stok tidak cukup', true); it.qty++; }
    if (act === 'dec') { it.qty--; if (it.qty <= 0) cart.delete(id); }
    if (act === 'inc' || act === 'dec') render();
  });
  ticket.addEventListener('input', e => {
    if (e.target.dataset.act !== 'note') return;
    const it = cart.get(+e.target.closest('[data-id]').dataset.id); if (it) it.catatan = e.target.value;
  });

  // Filter menu: kategori + pencarian
  let kat = 0;
  const filter = () => {
    const q = $('#q').value.trim().toLowerCase();
    document.querySelectorAll('#menu-grid button').forEach(b => {
      b.style.display = ((!kat || +b.dataset.kat === kat) && b.dataset.nama.toLowerCase().includes(q)) ? '' : 'none';
    });
  };
  $('#q').addEventListener('input', filter);
  $('#kat').addEventListener('click', e => {
    const c = e.target.closest('.chip'); if (!c) return;
    kat = +c.dataset.k; document.querySelectorAll('#kat .chip').forEach(x => x.classList.toggle('on', x === c)); filter();
  });
  $('#meja-pick').addEventListener('click', e => {
    const c = e.target.closest('.chip'); if (!c) return;
    meja = +c.dataset.m; document.querySelectorAll('#meja-pick .chip').forEach(x => x.classList.toggle('on', x === c));
  });
  $('#metode').addEventListener('click', e => {
    const c = e.target.closest('.chip'); if (!c) return;
    metode = c.dataset.p; document.querySelectorAll('#metode .chip').forEach(x => x.classList.toggle('on', x === c));
    $('#metode-info').textContent = metode === 'cash' ? 'Tunai: dianggap langsung lunas saat disimpan.' : 'QRIS: status "menunggu" sampai dikonfirmasi di Manajemen Pembayaran.';
  });
  $('#reset').addEventListener('click', () => { cart.clear(); render(); });

  $('#kirim').addEventListener('click', async () => {
    const nama = $('#nama').value.trim();
    if (!nama) return WK.toast('Nama pemesan wajib diisi', true);
    if (meja === null) return WK.toast('Pilih nomor meja atau Bungkus', true);
    if (!cart.size) return WK.toast('Pilih minimal satu menu', true);
    const btn = $('#kirim'); btn.disabled = true;
    try {
      const r = await WK.post('/api/kasir.php?action=buat_pesanan', {
        nama, metode, meja, jenis: meja === 0 ? 'takeaway' : 'dine_in',
        items: [...cart.values()].map(i => ({id: i.id, qty: i.qty, catatan: i.catatan})),
      });
      WK.toast(r.msg);
      setTimeout(() => location.href = WK.base + '/kasir/pesanan.php', 700);
    } catch (err) { WK.toast(err.message, true); btn.disabled = false; }
  });
  render();
})();
