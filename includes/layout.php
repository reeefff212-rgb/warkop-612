<?php
require_once __DIR__ . '/auth.php';

function icon(string $n, string $cls = 'w-4 h-4'): string {
    static $p = [
        'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        'qr' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4z',
        'plus' => 'M12 4v16m8-8H4',
        'clip' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'cup' => 'M3 8h14v6a5 5 0 01-5 5H8a5 5 0 01-5-5V8zm14 1h2a2 2 0 010 4h-2M6 3v2m4-2v2m4-2v2',
        'receipt' => 'M9 12h6m-6 4h6M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'trash' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'edit' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'check' => 'M5 13l4 4L19 7',
        'x' => 'M6 18L18 6M6 6l12 12',
        'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'print' => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
        'download' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'cart' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    ];
    return '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="' . ($p[$n] ?? '') . '"/></svg>';
}

/** <head> + konfigurasi Tailwind (mengikuti palet desain). */
function html_head(string $title, string $bodyClass = ''): void { ?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf" content="<?= e(csrf_token()) ?>"><meta name="base" content="<?= e(BASE_URL) ?>">
<title><?= e($title) ?> - WARKOP 612</title>
<link href="https://fonts.googleapis.com" rel="preconnect"><link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{pine:'#1E3A2F',pineDark:'#163026',cream:'#F7F5EE',creamField:'#EFECE3',tan:'#EBDDBB',tanText:'#6D5D3B',line:'#EFECE4'}}}}</script>
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="min-h-screen font-sans antialiased text-slate-800 bg-cream <?= e($bodyClass) ?>">
<?php }

/** Pembuka halaman admin (sidebar + top bar). AREA = 'kasir' | 'owner'. */
function admin_header(string $title, string $active, string $eyebrow = '', string $sub = ''): void {
    $area = AREA; $u = me();
    $nav = $area === 'owner'
        ? [['Laporan' ,[['laporan','Laporan Penjualan','chart'],['transaksi','Riwayat Transaksi','receipt'],['pembayaran','Manajemen Pembayaran','card']]]]
        : [['Operasional',[['dashboard','Dashboard','grid'],['meja','Manajemen Meja','qr'],['pesanan_manual','Input Pesanan Manual','plus'],['pesanan','Manajemen Pesanan','clip'],['pembayaran','Manajemen Pembayaran','card']]],
           ['Katalog & Data',[['menu','Manajemen Menu','cup'],['transaksi','Transaksi','receipt'],['laporan','Laporan Penjualan','chart']]]];
    html_head($title);
    $flash = get_flash(); ?>
<div class="flex min-h-screen">
<aside class="no-print w-64 shrink-0 min-h-screen bg-cream border-r border-[#E8E4DA] p-4 hidden lg:flex flex-col justify-between sticky top-0 h-screen overflow-y-auto">
  <div class="space-y-5">
    <div class="flex items-center gap-3 px-2 pt-1">
      <div class="w-10 h-10 rounded-xl bg-pine text-amber-100 flex items-center justify-center shadow-sm"><?= icon('cup','w-5 h-5') ?></div>
      <div><h1 class="text-base font-bold tracking-tight leading-none">WARKOP 612</h1>
      <p class="text-[10px] tracking-wider uppercase font-semibold text-slate-500 mt-1"><?= $area === 'owner' ? 'Portal Owner' : 'Sistem Informasi Kasir' ?></p></div>
    </div>
    <div class="mx-1 px-3 py-2 bg-creamField border border-[#E4DEC9] rounded-xl flex items-center justify-between">
      <span class="text-[11px] font-bold text-slate-600 tracking-wide uppercase">Role: <?= $u['role'] === 'admin' ? 'Admin / Owner' : 'Kasir' ?></span>
      <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 ring-2 ring-emerald-200"></span>
    </div>
    <?php if ($area === 'kasir'): ?>
    <a href="<?= e(url('pelanggan/index.php?meja=1')) ?>" target="_blank" class="mx-1 bg-tan hover:bg-[#e4d4b0] text-[#4F4225] font-semibold text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 border border-[#DCCB9F]"><?= icon('eye') ?> Pratinjau Pelanggan</a>
    <?php endif; ?>
    <?php foreach ($nav as [$grp, $items]): ?>
    <div class="space-y-1"><p class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase"><?= e($grp) ?></p>
      <?php foreach ($items as [$key, $label, $ic]): $on = $key === $active; ?>
      <a href="<?= e(url("$area/$key.php")) ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition <?= $on ? 'bg-pine text-white shadow-sm' : 'text-slate-700 hover:bg-creamField' ?>"><?= icon($ic, 'w-4 h-4 ' . ($on ? 'text-emerald-300' : 'text-slate-500')) ?><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="pt-4 border-t border-[#E8E4DA]">
    <div class="flex items-center justify-between px-2 py-2 rounded-xl bg-white border border-[#EBE7DF] shadow-sm">
      <div class="flex items-center gap-2.5"><div class="w-8 h-8 rounded-full bg-pineDark text-amber-200 flex items-center justify-center font-bold text-xs"><?= e(strtoupper(mb_substr($u['nama'],0,1))) ?></div>
      <span class="text-xs font-bold"><?= e($u['nama']) ?></span></div>
      <a href="<?= e(url('logout.php?to=' . $area)) ?>" title="Keluar" class="text-slate-400 hover:text-red-600 p-1 rounded-lg hover:bg-slate-100"><?= icon('logout') ?></a>
    </div>
  </div>
</aside>
<main class="flex-1 min-w-0 flex flex-col">
  <header class="no-print px-4 lg:px-8 py-3 flex items-center justify-between gap-4 border-b border-[#EBE7DF]">
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-creamField border border-[#E3DECE]"><span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span><span class="text-xs font-semibold">Sistem Online: Warkop 612 Pusat</span></div>
    <div class="flex items-center gap-2 text-xs font-semibold">
      <span class="hidden sm:inline text-slate-500"><?= e(tgl_id(date('Y-m-d'))) ?></span>
      <a href="<?= e(url("logout.php?to=$area")) ?>" class="lg:hidden px-3 py-1.5 rounded-lg bg-pine text-white">Keluar</a>
    </div>
  </header>
  <nav class="no-print lg:hidden flex gap-2 overflow-x-auto px-4 py-2 border-b border-[#EBE7DF] bg-white text-xs font-semibold">
    <?php foreach ($nav as [$grp, $items]) foreach ($items as [$key, $label]): ?>
      <a href="<?= e(url("$area/$key.php")) ?>" class="whitespace-nowrap px-3 py-1.5 rounded-full <?= $key === $active ? 'bg-pine text-white' : 'bg-creamField' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 lg:p-8 space-y-6">
    <?php if ($flash): ?><div class="no-print rounded-xl px-4 py-3 text-sm font-semibold <?= $flash[1] === 'err' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?>"><?= e($flash[0]) ?></div><?php endif; ?>
    <section class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <?php if ($eyebrow): ?><p class="text-xs font-semibold text-slate-500 uppercase tracking-wider"><?= e($eyebrow) ?></p><?php endif; ?>
        <h2 class="text-2xl font-bold text-slate-900 mt-1"><?= e($title) ?></h2>
        <?php if ($sub): ?><p class="text-sm text-slate-500 mt-1 max-w-2xl"><?= e($sub) ?></p><?php endif; ?>
      </div>
      <div class="flex flex-wrap items-center gap-2 no-print"><?= $GLOBALS['page_actions'] ?? '' ?></div>
    </section>
<?php }

function admin_footer(array $scripts = []): void { ?>
  </div>
</main>
</div>
<div id="toast-root"></div>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<?php foreach ($scripts as $s): ?><script src="<?= e(url("assets/js/$s")) ?>"></script><?php endforeach; ?>
</body></html>
<?php }

function badge(string $text, string $color): string {
    $c = ['green' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
          'red' => 'bg-red-50 text-red-700 border-red-200', 'gray' => 'bg-slate-100 text-slate-600 border-slate-200', 'blue' => 'bg-sky-50 text-sky-700 border-sky-200'][$color] ?? '';
    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-bold uppercase tracking-wide ' . $c . '">' . e($text) . '</span>';
}
function status_badge(string $s): string {
    return ['menunggu' => badge('Menunggu', 'amber'), 'diproses' => badge('Diproses', 'blue'), 'selesai' => badge('Selesai', 'green'), 'dibatalkan' => badge('Dibatalkan', 'red'),
            'berhasil' => badge('Lunas', 'green'), 'gagal' => badge('Gagal', 'red'), 'tersedia' => badge('Tersedia', 'green'), 'habis' => badge('Habis', 'red'), 'terisi' => badge('Terisi', 'amber')][$s] ?? badge($s, 'gray');
}
