/* Manajemen Meja: filter, pratinjau & cetak stiker QR */
const WKMeja = (() => {
  let current = null;
  const qrDataUrl = text => new Promise(res => {
    const box = document.createElement('div');
    new QRCode(box, {text, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M});
    setTimeout(() => res(box.querySelector('canvas')?.toDataURL() || box.querySelector('img')?.src || ''), 60);
  });
  const sticker = (nomor, img) => `
    <div class="sticker" style="width:260px;border:2px solid #1E3A2F;border-radius:20px;padding:18px;text-align:center;background:#FAF7F0;font-family:sans-serif;display:inline-block;margin:8px">
      <div style="font-size:11px;font-weight:800;letter-spacing:.15em;color:#6D5D3B">WARKOP 612 &bull; EST. 2024</div>
      <div style="font-size:28px;font-weight:900;color:#1E3A2F;margin:6px 0">MEJA ${String(nomor).padStart(2,'0')}</div>
      <img src="${img}" width="200" height="200" style="background:#fff;padding:8px;border-radius:12px">
      <div style="font-weight:800;margin-top:8px;color:#1E3A2F">SCAN UNTUK PESAN</div>
      <div style="font-size:11px;color:#64748b">Langsung dari meja Anda &bull; Tanpa antre di kasir</div>
    </div>`;
  const printWin = html => {
    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) return WK.toast('Izinkan pop-up untuk mencetak', true);
    w.document.write('<html><head><title>Cetak QR Meja</title></head><body style="text-align:center">' + html + '</body></html>');
    w.document.close(); w.focus(); setTimeout(() => w.print(), 400);
  };
  return {
    async show(id) {
      current = MEJA.find(m => m.id === id);
      const img = await qrDataUrl(current.url);
      document.getElementById('qr-sticker').innerHTML = sticker(current.nomor, img);
      document.getElementById('qr-link').textContent = current.url;
      document.getElementById('qr-modal').classList.add('open');
    },
    async printOne() { printWin(sticker(current.nomor, await qrDataUrl(current.url))); },
    async printAll() {
      if (!MEJA.length) return WK.toast('Belum ada meja', true);
      let html = ''; for (const m of MEJA) html += sticker(m.nomor, await qrDataUrl(m.url));
      printWin(html);
    },
    async del(id) {
      try { const r = await WK.post('/api/kasir.php?action=hapus_meja', {id}); WK.toast(r.msg); setTimeout(() => location.reload(), 600); }
      catch (e) { WK.toast(e.message, true); }
    },
  };
})();
document.getElementById('filter-meja')?.addEventListener('click', e => {
  const b = e.target.closest('.chip'); if (!b) return;
  document.querySelectorAll('#filter-meja .chip').forEach(c => c.classList.toggle('on', c === b));
  document.querySelectorAll('#grid-meja article').forEach(a => a.style.display = (b.dataset.f === 'all' || a.dataset.status === b.dataset.f) ? '' : 'none');
});
document.getElementById('qr-modal')?.addEventListener('click', e => { if (e.target.id === 'qr-modal') e.target.classList.remove('open'); });
