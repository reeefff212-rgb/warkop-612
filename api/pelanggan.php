<?php
/** API publik untuk pelanggan (tanpa login; akses pesanan dibatasi lewat session). */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/order.php';
$action = $_GET['action'] ?? '';

function punya_pesanan(int $id): bool { return in_array($id, $_SESSION['orders'] ?? [], true); }

try {
    if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $meja = (int)($_SESSION['meja'] ?? 0);
        if (!$meja) throw new RuntimeException('Silakan scan ulang QR di meja Anda.');
        if (count($in['items'] ?? []) > 50) throw new RuntimeException('Terlalu banyak item.');
        $metode = ($in['metode'] ?? '') === 'QRIS' ? 'QRIS' : 'cash';
        $id = create_order(['nama' => $in['nama'] ?? '', 'meja' => $meja, 'jenis' => 'dine_in', 'metode' => $metode,
                            'items' => $in['items'] ?? [], 'tipe' => 'QR', 'lunas' => false]);
        $_SESSION['orders'][] = $id;
        $_SESSION['last_order'] = $id;
        json_out(['ok' => true, 'id' => $id, 'next' => url($metode === 'QRIS' ? "pelanggan/pembayaran.php?id=$id" : "pelanggan/konfirmasi.php?id=$id")]);
    }
    if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = (int)($_GET['id'] ?? 0);
        if (!punya_pesanan($id)) json_out(['ok' => false, 'msg' => 'Pesanan tidak ditemukan.'], 404);
        $t = qrow('SELECT t.status_pesanan, p.status_pembayaran FROM transaksi t LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi WHERE t.id_transaksi = ?', [$id]);
        json_out(['ok' => true, 'pesanan' => $t['status_pesanan'], 'bayar' => $t['status_pembayaran']]);
    }
    json_out(['ok' => false, 'msg' => 'Aksi tidak dikenal.'], 404);
} catch (RuntimeException $ex) {
    json_out(['ok' => false, 'msg' => $ex->getMessage()], 400);
} catch (Throwable $ex) {
    json_out(['ok' => false, 'msg' => 'Kesalahan server.'], 500);
}
