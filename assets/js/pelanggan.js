/* Keranjang pelanggan (disimpan di localStorage, dikunci per meja) */
const Cart = (() => {
  const KEY = 'wk612_cart';
  const load = () => { try { return JSON.parse(localStorage.getItem(KEY)) || {meja: null, items: {}}; } catch (e) { return {meja: null, items: {}}; } };
  const save = c => { localStorage.setItem(KEY, JSON.stringify(c)); badge(); };
  function badge() {
    const n = count(), b = document.getElementById('nav-badge');
    if (b) { b.textContent = n; b.classList.toggle('hidden', n === 0); b.classList.toggle('flex', n > 0); }
  }
  const count = () => Object.values(load().items).reduce((a, i) => a + i.qty, 0);
  const total = () => Object.values(load().items).reduce((a, i) => a + i.qty * i.harga, 0);
  return {
    load, save, count, total, badge,
    useMeja(id) { const c = load(); if (c.meja !== id) save({meja: id, items: {}}); },
    qty(id) { return load().items[id]?.qty || 0; },
    set(menu, qty, catatan) {
      const c = load();
      if (qty <= 0) delete c.items[menu.id];
      else c.items[menu.id] = {id: menu.id, nama: menu.nama, harga: menu.harga, stok: menu.stok, qty: Math.min(qty, menu.stok || 99),
                               catatan: catatan ?? c.items[menu.id]?.catatan ?? ''};
      save(c);
    },
    clear() { const c = load(); c.items = {}; save(c); },
  };
})();
document.addEventListener('DOMContentLoaded', Cart.badge);
