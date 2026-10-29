<?php
require_once __DIR__ . '/../config.php';

function me(): ?array { return $_SESSION['user'] ?? null; }

/** Area 'kasir' => admin & kasir. Area 'owner' => hanya admin (owner). */
function require_area(string $area, bool $json = false): array {
    $u = me();
    $allowed = $area === 'owner' ? ['admin'] : ['admin', 'kasir'];
    if (!$u || !in_array($u['role'], $allowed, true)) {
        if ($json) json_out(['ok' => false, 'msg' => 'Sesi berakhir, silakan login ulang.'], 401);
        redirect($area === 'owner' ? 'owner/login.php' : 'kasir/login.php');
    }
    return $u;
}

function do_login(string $email, string $pass, array $roles): ?string {
    $u = qrow('SELECT * FROM pengguna WHERE email = ?', [trim($email)]);
    if (!$u || !password_verify($pass, $u['password'])) return 'Email atau kata sandi salah.';
    if (!in_array($u['role'], $roles, true)) return 'Akun ini tidak memiliki akses ke portal tersebut.';
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$u['id_pengguna'], 'nama' => $u['nama_pengguna'], 'role' => $u['role']];
    return null;
}
