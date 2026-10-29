<?php
/** API internal kasir/owner (JSON). Semua aksi tulis wajib header X-CSRF-Token. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/order.php';
$user = require_area('kasir', true);
$action = $_GET['action'] ?? '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'pesanan_list') {
            $rows = qall("SELECT t.id_transaksi, t.nama_pemesan, t.jenis_pesanan, t.tipe_pesanan, t.waktu_pesanan, t.status_pesanan, t.total_harga,
                                 m.nomor_meja, p.metode_pembayaran, p.status_pembayaran
                          FROM transaksi t LEFT JOIN meja m ON m.id_meja = t.id_meja LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
                          WHERE t.status_pesanan IN ('menunggu','diproses') OR t.waktu_pesanan >= ?
                          ORDER BY t.waktu_pesanan ASC", [date('Y-m-d 00:00:00')]);
            $ids = array_column($rows, 'id_transaksi');
            $items = [];
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                foreach (qall("SELECT d.id_transaksi, d.jumlah, d.subtotal, d.catatan, m.nama_menu FROM detail_transaksi d JOIN menu m ON m.id_menu = d.id_menu WHERE d.id_transaksi IN ($in) ORDER BY d.id_detail", $ids) as $d)
                    $items[$d['id_transaksi']][] = $d;
            }
            foreach ($rows as &$r) { $r['items'] = $items[$r['id_transaksi']] ?? []; $r['jam'] = date('H:i', strtotime($r['waktu_pesanan'])); }
            json_out(['ok' => true, 'orders' => $rows]);
        }
        json_out(['ok' => false, 'msg' => 'Aksi tidak dikenal.'], 404);
    }

    if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) json_out(['ok' => false, 'msg' => 'Token keamanan tidak valid, muat ulang halaman.'], 403);
    $in = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($action) {
        case 'buat_pesanan':
            $metode = ($in['metode'] ?? 'cash') === 'QRIS' ? 'QRIS' : 'cash';
            $id = create_order([
                'nama' => $in['nama'] ?? '', 'meja' => $in['meja'] ?? 0, 'jenis' => $in['jenis'] ?? 'dine_in',
                'metode' => $metode, 'items' => $in['items'] ?? [], 'tipe' => 'manual',
                'pengguna' => $user['id'], 'lunas' => $metode === 'cash',   // tunai diterima langsung oleh kasir
            ]);
            json_out(['ok' => true, 'id' => $id, 'msg' => 'Pesanan #' . $id . ' dikirim ke antrean dapur.']);

        case 'ubah_status':
            set_order_status((int)($in['id'] ?? 0), (string)($in['status'] ?? ''));
            json_out(['ok' => true]);

        case 'konfirmasi_bayar':
            set_payment((int)($in['id'] ?? 0), !empty($in['ok']));
            json_out(['ok' => true, 'msg' => !empty($in['ok']) ? 'Pembayaran dikonfirmasi.' : 'Pembayaran ditolak & pesanan dibatalkan.']);

        case 'toggle_menu':
            $m = qrow('SELECT * FROM menu WHERE id_menu = ?', [(int)($in['id'] ?? 0)]);
            if (!$m) throw new RuntimeException('Menu tidak ditemukan.');
            $new = $m['status_menu'] === 'tersedia' ? 'habis' : 'tersedia';
            if ($new === 'tersedia' && $m['stok'] <= 0) throw new RuntimeException('Stok 0. Tambah stok dulu lewat Edit Menu.');
            db()->prepare('UPDATE menu SET status_menu = ? WHERE id_menu = ?')->execute([$new, $m['id_menu']]);
            json_out(['ok' => true, 'status' => $new]);

        case 'hapus_menu':
            $m = qrow('SELECT * FROM menu WHERE id_menu = ?', [(int)($in['id'] ?? 0)]);
            if (!$m) throw new RuntimeException('Menu tidak ditemukan.');
            if (qval('SELECT COUNT(*) FROM detail_transaksi WHERE id_menu = ?', [$m['id_menu']]) > 0) {
                db()->prepare('UPDATE menu SET status_menu = "habis", stok = 0 WHERE id_menu = ?')->execute([$m['id_menu']]);
                json_out(['ok' => true, 'msg' => 'Menu sudah pernah dipesan, jadi tidak dihapus - statusnya diubah menjadi Habis.']);
            }
            db()->prepare('DELETE FROM menu WHERE id_menu = ?')->execute([$m['id_menu']]);
            if ($m['foto_menu'] && is_file(__DIR__ . '/../' . $m['foto_menu'])) @unlink(__DIR__ . '/../' . $m['foto_menu']);
            json_out(['ok' => true, 'msg' => 'Menu dihapus.']);

        case 'hapus_meja':
            $id = (int)($in['id'] ?? 0);
            if (qval('SELECT COUNT(*) FROM transaksi WHERE id_meja = ?', [$id]) > 0) throw new RuntimeException('Meja sudah punya riwayat transaksi, tidak dapat dihapus.');
            db()->prepare('DELETE FROM meja WHERE id_meja = ?')->execute([$id]);
            json_out(['ok' => true, 'msg' => 'Meja dihapus.']);

        case 'set_meja':
            $st = ($in['status'] ?? '') === 'terisi' ? 'terisi' : 'tersedia';
            db()->prepare('UPDATE meja SET status_meja = ? WHERE id_meja = ?')->execute([$st, (int)($in['id'] ?? 0)]);
            json_out(['ok' => true]);
    }
    json_out(['ok' => false, 'msg' => 'Aksi tidak dikenal.'], 404);
} catch (RuntimeException $ex) {
    json_out(['ok' => false, 'msg' => $ex->getMessage()], 400);
} catch (Throwable $ex) {
    json_out(['ok' => false, 'msg' => 'Kesalahan server.'], 500);
}
