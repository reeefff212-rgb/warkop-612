<?php
require_once __DIR__ . '/layout.php';
/** Tampilan login bersama untuk portal kasir & owner. */
function login_page(string $area): void {
    $roles = $area === 'owner' ? ['admin'] : ['admin', 'kasir'];
    if (me() && in_array(me()['role'], $roles, true)) redirect($area === 'owner' ? 'owner/laporan.php' : 'kasir/dashboard.php');
    $err = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_ok($_POST['csrf'] ?? '')) $err = 'Sesi tidak valid, muat ulang halaman.';
        else {
            $err = do_login($_POST['email'] ?? '', $_POST['password'] ?? '', $roles);
            if (!$err) redirect($area === 'owner' ? 'owner/laporan.php' : 'kasir/dashboard.php');
        }
    }
    $judul = $area === 'owner' ? 'Portal Owner' : 'Portal Kasir';
    html_head($judul, 'flex flex-col'); ?>
<header class="px-6 lg:px-12 py-3.5 flex items-center justify-between border-b border-[#ECE7DC] bg-[#FAF8F4]">
  <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-pine text-emerald-100 flex items-center justify-center"><?= icon('cup','w-5 h-5') ?></div>
  <div><div class="text-base font-bold leading-tight">WARKOP 612</div><div class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Sistem Informasi Kasir</div></div></div>
  <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EAE6DD]/70 border border-[#DDD6C8] text-xs font-medium"><span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>Sistem Online: Warkop 612 Pusat</div>
</header>
<main class="flex-grow flex items-center justify-center px-4 py-10">
  <div class="w-full max-w-[460px] bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-[#EDE9DF]">
    <div class="text-center mb-6">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-pine text-amber-100 flex items-center justify-center mb-4"><?= icon('cup','w-7 h-7') ?></div>
      <h1 class="text-2xl font-extrabold"><?= e($judul) ?></h1>
      <p class="text-sm text-slate-500 mt-1">Masuk dengan akun <?= $area === 'owner' ? 'pemilik / administrator' : 'kasir atau administrator' ?> WARKOP 612</p>
    </div>
    <div class="grid grid-cols-2 gap-1 p-1 bg-creamField rounded-xl mb-5 text-xs font-bold">
      <a href="<?= e(url('kasir/login.php')) ?>" class="text-center py-2 rounded-lg <?= $area === 'kasir' ? 'bg-pine text-white' : 'text-slate-600' ?>">Kasir Operasional</a>
      <a href="<?= e(url('owner/login.php')) ?>" class="text-center py-2 rounded-lg <?= $area === 'owner' ? 'bg-pine text-white' : 'text-slate-600' ?>">Owner Warkop</a>
    </div>
    <?php if ($err): ?><div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold px-4 py-3"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="space-y-4" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div><label class="lbl" for="email">Email</label><input class="input" id="email" type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@warkop612.id"></div>
      <div><label class="lbl" for="pw">Kata Sandi</label>
        <div class="relative"><input class="input pr-16" id="pw" type="password" name="password" required placeholder="••••••••">
        <button type="button" onclick="const i=document.getElementById('pw');i.type=i.type==='password'?'text':'password'" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Lihat</button></div></div>
      <button class="btn btn-primary w-full py-3 text-sm" type="submit">Masuk ke <?= e($judul) ?></button>
    </form>
  </div>
</main>
</body></html>
<?php }
