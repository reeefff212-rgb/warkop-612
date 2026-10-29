/* Manajemen Antrean Pesanan (KDS) - render kartu dari API & polling */
(() => {
  const NEXT = {menunggu: ['diproses', 'Proses Pesanan'], diproses: ['selesai', 'Tandai Selesai']};
  let busy = false;

  const card = o => {
    const lunas = o.status_pembayaran === 'berhasil';
    const lokasi = o.nomor_meja ? 'MEJA ' + String(o.nomor_meja).padStart(2, '0') : 'BUNGKUS';
    const items = o.items.map(i => `<div class="flex justify-between gap-2 text-xs"><span>${i.jumlah}x ${WK.esc(i.nama_menu)}${i.catatan ? `<br><i class="text-slate-400">"${WK.esc(i.catatan)}"</i>` : ''}</span><span class="font-semibold whitespace-nowrap">${WK.rupiah(i.subtotal)}</span></div>`).join('');
    const nxt = NEXT[o.status_pesanan];
    const act = nxt ? `<div class="flex gap-2 mt-3">
        <button class="btn btn-primary btn-sm flex-1" data-id="${o.id_transaksi}" data-st="${nxt[0]}">${nxt[1]}</button>
        <button class="btn btn-danger btn-sm" data-id="${o.id_transaksi}" data-st="dibatalkan" data-confirm="Batalkan pesanan #${o.id_transaksi}? Stok akan dikembalikan.">Batal</button></div>` : '';
    return `<article class="card p-4">
      <div class="flex items-start justify-between"><div><span class="text-sm font-black text-pine">${lokasi}</span> <span class="text-[11px] text-slate-400">#${o.id_transaksi} • ${o.jam} WIB</span></div>
        <span class="text-[10px] font-bold px-2 py-1 rounded-full ${lunas ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}">${lunas ? 'LUNAS' : 'BELUM BAYAR'} (${o.metode_pembayaran === 'cash' ? 'TUNAI' : 'QRIS'})</span></div>
      <p class="text-sm font-bold mt-1">${WK.esc(o.nama_pemesan)}</p>
      <div class="mt-2 space-y-1.5 border-t border-line pt-2">${items}</div>
      <div class="flex justify-between mt-2 pt-2 border-t border-line text-sm"><span class="text-slate-500">Total</span><b>${WK.rupiah(o.total_harga)}</b></div>${act}</article>`;
  };

  async function load() {
    if (busy) return;
    try {
      const {orders} = await WK.get('/api/kasir.php?action=pesanan_list');
      const g = {menunggu: [], diproses: [], selesai: [], dibatalkan: []};
      orders.forEach(o => g[o.status_pesanan]?.push(o));
      for (const k in g) {
        const el = document.getElementById('col-' + k);
        el.innerHTML = g[k].map(card).join('') || '<p class="text-xs text-slate-400 py-4 text-center border border-dashed border-slate-300 rounded-xl">Kosong</p>';
        document.getElementById('c-' + k).textContent = g[k].length;
      }
      document.getElementById('total-antre').textContent = 'Total antre: ' + (g.menunggu.length + g.diproses.length) + ' tiket';
    } catch (e) { WK.toast(e.message, true); }
  }

  document.addEventListener('click', async e => {
    const b = e.target.closest('button[data-st]'); if (!b) return;
    busy = true; b.disabled = true;
    try { await WK.post('/api/kasir.php?action=ubah_status', {id: +b.dataset.id, status: b.dataset.st}); }
    catch (err) { WK.toast(err.message, true); }
    busy = false; load();
  });
  load(); setInterval(load, 10000);
})();
