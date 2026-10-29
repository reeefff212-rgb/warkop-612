/* WARKOP 612 - helper bersama */
const WK = {
  csrf: document.querySelector('meta[name=csrf]')?.content || '',
  base: document.querySelector('meta[name=base]')?.content || '',
};
WK.rupiah = n => 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
WK.esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
WK.toast = (msg, err = false) => {
  const el = document.createElement('div');
  el.className = 'toast' + (err ? ' err' : ''); el.textContent = msg;
  document.getElementById('toast-root')?.appendChild(el);
  setTimeout(() => el.remove(), 3200);
};
/** POST JSON ke endpoint API. Mengembalikan objek JSON; melempar Error bila ok=false. */
WK.post = async (path, data = {}) => {
  const r = await fetch(WK.base + path, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': WK.csrf},
    body: JSON.stringify(data),
  });
  let j; try { j = await r.json(); } catch (e) { throw new Error('Respons server tidak valid'); }
  if (!j.ok) throw new Error(j.msg || 'Terjadi kesalahan');
  return j;
};
WK.get = async path => {
  const r = await fetch(WK.base + path, {headers: {'Accept': 'application/json'}});
  const j = await r.json();
  if (!j.ok) throw new Error(j.msg || 'Terjadi kesalahan');
  return j;
};
/** Tombol dengan data-confirm akan meminta konfirmasi. */
document.addEventListener('click', e => {
  const b = e.target.closest('[data-confirm]');
  if (b && !confirm(b.dataset.confirm)) { e.preventDefault(); e.stopImmediatePropagation(); }
}, true);
