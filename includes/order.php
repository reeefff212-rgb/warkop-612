<?php
require_once __DIR__ . '/../config.php';

/**
 * Membuat pesanan lengkap (transaksi + detail + pembayaran) dalam satu transaksi database.
 * $o['items']: [['id'=>menuId,'qty'=>n,'catatan'=>'...'], ...]
 * Harga SELALU diambil dari database (bukan dari klien).
 * @throws RuntimeException bila stok/menu tidak valid
 */
function create_order(array $o): int {
    $pdo = db();
    $items = $o['items'] ?? [];
    if (!$items) throw new RuntimeException('Keranjang kosong.');
    $nama = trim($o['nama'] ?? '');
    if ($nama === '') throw new RuntimeException('Nama pemesan wajib diisi.');
    $metode = ($o['metode'] ?? '') === 'QRIS' ? 'QRIS' : 'cash';
    $jenis  = ($o['jenis'] ?? 'dine_in') === 'takeaway' ? 'takeaway' : 'dine_in';
    $meja   = $jenis === 'dine_in' ? (int)($o['meja'] ?? 0) : 0;
    if ($jenis === 'dine_in' && !$meja) throw new RuntimeException('Pilih nomor meja atau opsi bungkus.');

    $pdo->beginTransaction();
    try {
        if ($meja && !qrow('SELECT id_meja FROM meja WHERE id_meja = ?', [$meja])) throw new RuntimeException('Meja tidak ditemukan.');
        $now = date('Y-m-d H:i:s');
        $st = $pdo->prepare('INSERT INTO transaksi (id_meja,id_pengguna,nama_pemesan,tipe_pesanan,jenis_pesanan,waktu_pesanan,status_pesanan,total_harga) VALUES (?,?,?,?,?,?,?,0)');
        $st->execute([$meja ?: null, $o['pengguna'] ?? null, mb_substr($nama, 0, 100), $o['tipe'] ?? 'QR', $jenis, $now, 'menunggu']);
        $idTrx = (int)$pdo->lastInsertId();

        $total = 0;
        $sel = $pdo->prepare('SELECT * FROM menu WHERE id_menu = ? FOR UPDATE');
        $ins = $pdo->prepare('INSERT INTO detail_transaksi (id_transaksi,id_menu,jumlah,harga_satuan,subtotal,catatan) VALUES (?,?,?,?,?,?)');
        $upd = $pdo->prepare('UPDATE menu SET status_menu = IF(stok - ? <= 0, "habis", status_menu), stok = stok - ? WHERE id_menu = ?');
        $merged = [];
        foreach ($items as $it) {           // gabungkan baris dengan menu & catatan sama
            $k = (int)$it['id'] . '|' . trim($it['catatan'] ?? '');
            if (!isset($merged[$k])) $merged[$k] = ['id' => (int)$it['id'], 'qty' => 0, 'catatan' => trim($it['catatan'] ?? '')];
            $merged[$k]['qty'] += max(0, (int)$it['qty']);
        }
        foreach ($merged as $it) {
            if ($it['qty'] < 1) continue;
            $sel->execute([$it['id']]);
            $m = $sel->fetch();
            if (!$m) throw new RuntimeException('Menu tidak ditemukan.');
            if ($m['status_menu'] !== 'tersedia' || $m['stok'] < $it['qty'])
                throw new RuntimeException('Stok "' . $m['nama_menu'] . '" tidak mencukupi (sisa ' . max(0, $m['stok']) . ').');
            $sub = $m['harga'] * $it['qty'];
            $total += $sub;
            $ins->execute([$idTrx, $m['id_menu'], $it['qty'], $m['harga'], $sub, mb_substr($it['catatan'], 0, 255) ?: null]);
            $upd->execute([$it['qty'], $it['qty'], $m['id_menu']]);
        }
        if ($total <= 0) throw new RuntimeException('Keranjang kosong.');
        $pdo->prepare('UPDATE transaksi SET total_harga = ? WHERE id_transaksi = ?')->execute([$total, $idTrx]);

        $bayarLangsung = !empty($o['lunas']);
        $pdo->prepare('INSERT INTO pembayaran (id_transaksi,metode_pembayaran,jumlah_bayar,status_pembayaran,waktu_pembayaran) VALUES (?,?,?,?,?)')
            ->execute([$idTrx, $metode, $total, $bayarLangsung ? 'berhasil' : 'menunggu', $now]);
        if ($meja) $pdo->prepare('UPDATE meja SET status_meja = "terisi" WHERE id_meja = ?')->execute([$meja]);
        $pdo->commit();
        return $idTrx;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex instanceof RuntimeException ? $ex : new RuntimeException('Gagal menyimpan pesanan.');
    }
}

/** Ubah status pesanan; batal => stok dikembalikan & pembayaran menunggu jadi gagal. */
function set_order_status(int $id, string $status): void {
    if (!in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true)) throw new RuntimeException('Status tidak valid.');
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $t = qrow('SELECT * FROM transaksi WHERE id_transaksi = ? FOR UPDATE', [$id]);
        if (!$t) throw new RuntimeException('Pesanan tidak ditemukan.');
        if ($t['status_pesanan'] === 'dibatalkan' || $t['status_pesanan'] === $status) { $pdo->commit(); return; }
        if ($status === 'dibatalkan') {
            foreach (qall('SELECT id_menu, jumlah FROM detail_transaksi WHERE id_transaksi = ?', [$id]) as $d)
                $pdo->prepare('UPDATE menu SET stok = stok + ?, status_menu = "tersedia" WHERE id_menu = ?')->execute([$d['jumlah'], $d['id_menu']]);
            $pdo->prepare('UPDATE pembayaran SET status_pembayaran = "gagal" WHERE id_transaksi = ? AND status_pembayaran = "menunggu"')->execute([$id]);
        }
        $pdo->prepare('UPDATE transaksi SET status_pesanan = ? WHERE id_transaksi = ?')->execute([$status, $id]);
        if ($t['id_meja'] && in_array($status, ['selesai', 'dibatalkan'], true)) {
            $aktif = qval('SELECT COUNT(*) FROM transaksi WHERE id_meja = ? AND status_pesanan IN ("menunggu","diproses")', [$t['id_meja']]);
            if ($aktif == 0) $pdo->prepare('UPDATE meja SET status_meja = "tersedia" WHERE id_meja = ?')->execute([$t['id_meja']]);
        }
        $pdo->commit();
    } catch (Throwable $ex) { $pdo->rollBack(); throw $ex; }
}

/** Konfirmasi / tolak pembayaran yang masih menunggu. */
function set_payment(int $idTrx, bool $ok): void {
    db()->prepare('UPDATE pembayaran SET status_pembayaran = ?, waktu_pembayaran = ? WHERE id_transaksi = ? AND status_pembayaran = "menunggu"')
        ->execute([$ok ? 'berhasil' : 'gagal', date('Y-m-d H:i:s'), $idTrx]);
    if (!$ok) set_order_status($idTrx, 'dibatalkan');
}

function order_items(int $idTrx): array {
    return qall('SELECT d.*, m.nama_menu FROM detail_transaksi d JOIN menu m ON m.id_menu = d.id_menu WHERE d.id_transaksi = ? ORDER BY d.id_detail', [$idTrx]);
}
