<?php
defined('AREA') or define('AREA', 'kasir');
require_once __DIR__ . '/../includes/layout.php';
require_area('kasir');

$id = (int)($_GET['id'] ?? 0);
$m = $id ? qrow('SELECT * FROM menu WHERE id_menu = ?', [$id]) : null;
if ($id && !$m) { flash('Menu tidak ditemukan.', 'err'); redirect('kasir/menu.php'); }
$kat = qall('SELECT * FROM kategori_menu ORDER BY id_kategori');
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_menu'] ?? ''); $kid = (int)($_POST['id_kategori'] ?? 0);
    $desk = trim($_POST['deskripsi'] ?? ''); $harga = (float)str_replace(['.', ','], ['', '.'], $_POST['harga'] ?? '0');
    $stok = max(0, (int)($_POST['stok'] ?? 0)); $status = ($_POST['status_menu'] ?? '') === 'habis' ? 'habis' : 'tersedia';
    if ($stok <= 0) $status = 'habis';
    $foto = $m['foto_menu'] ?? null;

    if (!csrf_ok($_POST['csrf'] ?? '')) $err = 'Token keamanan tidak valid.';
    elseif ($nama === '' || mb_strlen($nama) > 60) $err = 'Nama menu wajib diisi (maks. 60 karakter).';
    elseif (!qrow('SELECT 1 FROM kategori_menu WHERE id_kategori = ?', [$kid])) $err = 'Pilih kategori.';
    elseif ($harga <= 0) $err = 'Harga harus lebih dari 0.';
    if (!$err && !empty($_FILES['foto']['name'])) {
        $f = $_FILES['foto'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $info = $f['error'] === UPLOAD_ERR_OK ? @getimagesize($f['tmp_name']) : false;
        if ($f['error'] !== UPLOAD_ERR_OK) $err = 'Upload foto gagal.';
        elseif (!$info || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $err = 'Foto harus berformat JPG, PNG, atau WebP.';
        elseif ($f['size'] > 5 * 1024 * 1024) $err = 'Ukuran foto maksimal 5 MB.';
        else {
            $dir = __DIR__ . '/../uploads/menu'; if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fn = 'menu_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], "$dir/$fn")) {
                if ($foto && is_file(__DIR__ . '/../' . $foto)) @unlink(__DIR__ . '/../' . $foto);
                $foto = "uploads/menu/$fn";
            } else $err = 'Tidak dapat menyimpan foto (cek izin folder uploads).';
        }
    }
    if (!$err) {
        if ($m) db()->prepare('UPDATE menu SET id_kategori=?,nama_menu=?,deskripsi=?,harga=?,stok=?,foto_menu=?,status_menu=? WHERE id_menu=?')->execute([$kid, $nama, $desk ?: null, $harga, $stok, $foto, $status, $id]);
        else db()->prepare('INSERT INTO menu (id_kategori,nama_menu,deskripsi,harga,stok,foto_menu,status_menu) VALUES (?,?,?,?,?,?,?)')->execute([$kid, $nama, $desk ?: null, $harga, $stok, $foto, $status]);
        flash($m ? 'Menu diperbarui.' : 'Menu baru ditayangkan.');
        redirect('kasir/menu.php');
    }
}
$v = fn($k, $d = '') => $_POST[$k] ?? ($m[$k] ?? $d);
admin_header($m ? 'Edit Menu' : 'Tambah Menu Baru', 'menu', 'Katalog produk & dapur › Manajemen menu', 'Perubahan langsung tampil di katalog pelanggan dan terminal kasir.'); ?>
<form method="post" enctype="multipart/form-data" class="card p-6 max-w-3xl space-y-5">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <?php if ($err): ?><div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3"><?= e($err) ?></div><?php endif; ?>
  <div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2"><label class="lbl">Nama Menu * <span class="normal-case font-normal">(maks. 60 karakter)</span></label><input class="input" name="nama_menu" maxlength="60" required value="<?= e($v('nama_menu')) ?>"></div>
    <div><label class="lbl">Kategori *</label><select class="input" name="id_kategori" required><option value="">Pilih...</option><?php foreach ($kat as $k): ?><option value="<?= (int)$k['id_kategori'] ?>" <?= (int)$v('id_kategori') === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option><?php endforeach; ?></select></div>
    <div><label class="lbl">Harga Jual (Rp) *</label><input class="input" name="harga" inputmode="numeric" required value="<?= e($m && !$_POST ? (int)$m['harga'] : ($_POST['harga'] ?? '')) ?>" placeholder="10000"></div>
    <div><label class="lbl">Stok</label><input class="input" type="number" min="0" name="stok" value="<?= e($v('stok', 50)) ?>"></div>
    <div><label class="lbl">Status</label><select class="input" name="status_menu"><option value="tersedia" <?= $v('status_menu', 'tersedia') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option><option value="habis" <?= $v('status_menu') === 'habis' ? 'selected' : '' ?>>Habis</option></select></div>
    <div class="sm:col-span-2"><label class="lbl">Deskripsi</label><textarea class="input" name="deskripsi" rows="3" placeholder="Tampil di katalog pelanggan"><?= e($v('deskripsi')) ?></textarea></div>
    <div class="sm:col-span-2"><label class="lbl">Foto Menu <span class="normal-case font-normal">(JPG/PNG/WebP, maks. 5 MB)</span></label>
      <?php if ($m && $m['foto_menu']): ?><img src="<?= e(url($m['foto_menu'])) ?>" class="w-24 h-24 rounded-xl object-cover mb-2" alt=""><?php endif; ?>
      <input class="input" type="file" name="foto" accept="image/jpeg,image/png,image/webp"></div>
  </div>
  <div class="flex gap-2 justify-end"><a href="<?= e(url('kasir/menu.php')) ?>" class="btn btn-ghost">Batal / Kembali</a><button class="btn btn-primary"><?= $m ? 'Simpan Perubahan' : 'Simpan & Tayangkan Menu' ?></button></div>
</form>
<?php admin_footer();
