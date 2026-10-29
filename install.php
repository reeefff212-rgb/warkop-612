<?php
/**
 * Jalankan SEKALI setelah database.sql diimpor: membuat akun awal.
 * Setelah berhasil, HAPUS file ini dari server.
 */
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=utf-8');
if (qval('SELECT COUNT(*) FROM pengguna') > 0) { echo "Akun sudah ada. Hapus file install.php.\n"; exit; }
$akun = [
    ['Owner Warkop',  'owner@warkop612.id', 'owner123', 'admin'],
    ['Budi Santoso',  'kasir@warkop612.id', 'kasir123', 'kasir'],
];
$st = db()->prepare('INSERT INTO pengguna (nama_pengguna,email,password,role) VALUES (?,?,?,?)');
foreach ($akun as [$n, $em, $pw, $r]) { $st->execute([$n, $em, password_hash($pw, PASSWORD_DEFAULT), $r]); echo "Dibuat: $em / $pw ($r)\n"; }
echo "\nSelesai. Segera ganti kata sandi & HAPUS install.php.\n";
