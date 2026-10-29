<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$id = (int)($_GET['id'] ?? 0);
$m = $id ? qrow('SELECT * FROM meja WHERE id_meja = ?', [$id]) : null;
if ($id && !$m) { flash('Meja tidak ditemukan.', 'err'); redirect('kasir/meja.php'); }
$saran = (int)qval('SELECT COALESCE(MAX(nomor_meja),0)+1 FROM meja');
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no = (int)($_POST['nomor_meja'] ?? 0);
    if (!csrf_ok($_POST['csrf'] ?? '')) $err = 'Token keamanan tidak valid.';
    elseif ($no < 1 || $no > 999) $err = 'Nomor meja harus 1 - 999.';
    elseif (qval('SELECT COUNT(*) FROM meja WHERE nomor_meja = ? AND id_meja <> ?', [$no, $id]) > 0) $err = "Meja $no sudah terdaftar.";
    else {
        if ($m) {
            db()->prepare('UPDATE meja SET nomor_meja = ? WHERE id_meja = ?')->execute([$no, $id]);
        } else {
            db()->prepare('INSERT INTO meja (nomor_meja, status_meja) VALUES (?, "tersedia")')->execute([$no]);
            $nid = (int)db()->lastInsertId();
            db()->prepare('UPDATE meja SET qr_code = ? WHERE id_meja = ?')->execute(["pelanggan/index.php?meja=$nid", $nid]);
        }
        flash($m ? 'Meja diperbarui.' : "Meja $no ditambahkan. Cetak stikernya dari kartu meja.");
        redirect('kasir/meja.php');
    }
}
$val = $_POST['nomor_meja'] ?? ($m['nomor_meja'] ?? $saran);
admin_header($m ? 'Edit Meja' : 'Tambah Meja Baru', 'meja', 'Pengaturan warkop › Tata letak & meja', 'QR code pemesanan dibuat otomatis setelah meja disimpan.'); ?>
<div class="grid lg:grid-cols-3 gap-5 max-w-4xl">
  <form method="post" class="card p-6 lg:col-span-2 space-y-5">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php if ($err): ?><div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3"><?= e($err) ?></div><?php endif; ?>
    <div><label class="lbl" for="nm">Nomor Meja *</label><input class="input" id="nm" type="number" name="nomor_meja" min="1" max="999" required value="<?= e($val) ?>" oninput="document.getElementById('prev').textContent=String(this.value||0).padStart(2,'0')">
      <?php if (!$m): ?><p class="text-xs text-slate-500 mt-2">Saran berikutnya: <button type="button" class="font-bold text-pine underline" onclick="document.getElementById('nm').value=<?= $saran ?>;document.getElementById('nm').dispatchEvent(new Event('input'))">Meja <?= $saran ?></button></p><?php endif; ?></div>
    <div class="flex gap-2 justify-end"><a href="<?= e(url('kasir/meja.php')) ?>" class="btn btn-ghost">Batal</a><button class="btn btn-primary" type="submit"><?= $m ? 'Simpan Perubahan' : 'Simpan Meja' ?></button></div>
  </form>
  <div class="card p-6 text-center"><p class="lbl">Pratinjau Stiker</p><div class="sticker mx-auto"><p class="text-[11px] font-extrabold tracking-widest text-tanText">WARKOP 612</p><p class="text-4xl font-black text-pine my-2" id="prev"><?= str_pad((int)$val, 2, '0', STR_PAD_LEFT) ?></p><p class="text-xs font-bold">SCAN UNTUK PESAN</p></div></div>
</div>
<?php admin_footer();
