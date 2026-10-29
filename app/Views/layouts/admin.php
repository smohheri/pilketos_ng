<?php
$appName = $appName ?? app_setting('app_name', 'CAKRA 2026');
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? 'Panel panitia pemilihan';
$path = trim(service('request')->getUri()->getPath(), '/');
$is = static function (string $needle) use ($path): bool {
    if ($needle === '') {
        return $path === 'admin';
    }

    return $path === 'admin/' . $needle || str_starts_with($path, 'admin/' . $needle . '/');
};
$logo = school_logo_url();
$profile = $profile ?? null;
$schoolShort = $profile['short_name'] ?? app_setting('app_name', 'CAKRA 2026');
$adminElection = admin_current_election();
$adminElections = admin_elections();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F6F0E4">
    <title><?= esc($pageTitle . ' · ' . $appName) ?></title>
    <link rel="icon" href="<?= esc($logo) ?>"<?= school_logo_mime() !== '' ? ' type="' . esc(school_logo_mime()) . '"' : '' ?>>
    <link rel="preload" href="<?= base_url('assets/fonts/outfit-400.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url('assets/fonts/space-grotesk-600.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script defer src="<?= base_url('assets/js/app.js') ?>"></script>
    <script defer src="<?= base_url('assets/js/alpine.min.js') ?>"></script>
    <?= $this->renderSection('head') ?>
</head>
<body class="bg-cream" x-data="{ sidebar: false, userMenu: false }">
<?= $this->include('partials/flash') ?>

<div class="admin-main">
    <div class="fixed inset-0 z-30 bg-ink/40 backdrop-blur-sm lg:hidden" x-show="sidebar" x-cloak @click="sidebar = false" x-transition.opacity></div>

    <aside class="admin-sidebar" :class="sidebar ? 'is-open' : ''">
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between gap-3 px-1">
                <a href="<?= base_url('admin') ?>" class="flex items-center gap-3">
                    <img src="<?= esc($logo) ?>" alt="" class="h-11 w-11 rounded-2xl border-2 border-ink bg-cream object-contain p-0.5">
                    <span>
                        <span class="block font-display text-base font-bold leading-none tracking-tight"><?= esc($appName) ?></span>
                        <span class="mt-1 block text-[9px] font-semibold uppercase tracking-[0.16em] text-ink/40">Panel Panitia</span>
                    </span>
                </a>
                <button type="button" class="icon-btn lg:hidden" @click="sidebar = false" aria-label="Tutup menu"><?= icon('x', 'h-4 w-4') ?></button>
            </div>

            <div class="mt-5 rounded-2xl border-2 border-ink/10 bg-cream px-4 py-3">
                <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-ink/35">Sekolah</p>
                <p class="mt-1 truncate font-display text-sm font-semibold"><?= esc($schoolShort) ?></p>
            </div>

            <?php if ($adminElection !== null): ?>
                <div class="mt-3 rounded-2xl border-2 border-ink/10 bg-white px-4 py-3">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-ink/35">Pemilihan aktif</p>
                    <?php if (is_admin() && count($adminElections) > 1): ?>
                        <div class="mt-2 flex gap-1.5">
                            <?php foreach ($adminElections as $option): ?>
                                <?php $active = (int) $option['id'] === (int) $adminElection['id']; ?>
                                <form action="<?= base_url('admin/pemilihan/pilih/' . $option['id']) ?>" method="post" class="flex-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="w-full rounded-xl border-2 px-2 py-1.5 font-display text-[10px] font-bold transition <?= $active ? 'border-ink bg-ink text-sun' : 'border-ink/10 bg-cream text-ink/55 hover:border-ink/30' ?>">
                                        <?= esc(election_type_label($option['type'] ?? null)) ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-1 font-display text-sm font-semibold"><?= esc(election_type_label($adminElection['type'] ?? null)) ?></p>
                    <?php endif; ?>
                    <p class="mt-2 truncate text-[10px] text-ink/40"><?= esc($adminElection['title']) ?></p>
                </div>
            <?php endif; ?>

            <nav class="mt-5 flex-1 overflow-y-auto pb-4 no-scrollbar" aria-label="Navigasi panel">
                <p class="admin-nav-section">Utama</p>
                <a href="<?= base_url('admin') ?>" class="admin-link <?= $is('') ? 'is-active' : '' ?>"><?= icon('home', 'h-[18px] w-[18px]') ?> Dashboard</a>

                <p class="admin-nav-section">Konten</p>
                <a href="<?= base_url('admin/profil') ?>" class="admin-link <?= $is('profil') ? 'is-active' : '' ?>"><?= icon('school', 'h-[18px] w-[18px]') ?> Profil Sekolah</a>
                <a href="<?= base_url('admin/kandidat') ?>" class="admin-link <?= $is('kandidat') ? 'is-active' : '' ?>"><?= icon('users', 'h-[18px] w-[18px]') ?> Kandidat</a>
                <a href="<?= base_url('admin/pemilihan') ?>" class="admin-link <?= $is('pemilihan') ? 'is-active' : '' ?>"><?= icon('ballot', 'h-[18px] w-[18px]') ?> Pemilihan &amp; Jadwal</a>
                <a href="<?= base_url('admin/penilaian') ?>" class="admin-link <?= $is('penilaian') ? 'is-active' : '' ?>"><?= icon('star', 'h-[18px] w-[18px]') ?> Penilaian</a>
                <a href="<?= base_url('admin/faq') ?>" class="admin-link <?= $is('faq') ? 'is-active' : '' ?>"><?= icon('info', 'h-[18px] w-[18px]') ?> FAQ</a>

                <p class="admin-nav-section">Data</p>
                <a href="<?= base_url('admin/siswa') ?>" class="admin-link <?= $is('siswa') ? 'is-active' : '' ?>"><?= icon('list', 'h-[18px] w-[18px]') ?> Data Siswa</a>
                <a href="<?= base_url('admin/laporan') ?>" class="admin-link <?= $is('laporan') ? 'is-active' : '' ?>"><?= icon('chart', 'h-[18px] w-[18px]') ?> Laporan</a>

                <?php if (is_admin()): ?>
                    <p class="admin-nav-section">Khusus Admin</p>
                    <a href="<?= base_url('admin/pengguna') ?>" class="admin-link <?= $is('pengguna') ? 'is-active' : '' ?>"><?= icon('key', 'h-[18px] w-[18px]') ?> Pengguna</a>
                    <a href="<?= base_url('admin/audit') ?>" class="admin-link <?= $is('audit') ? 'is-active' : '' ?>"><?= icon('shield', 'h-[18px] w-[18px]') ?> Audit Log</a>
                    <a href="<?= base_url('admin/pengaturan') ?>" class="admin-link <?= $path === 'admin/pengaturan' ? 'is-active' : '' ?>"><?= icon('settings', 'h-[18px] w-[18px]') ?> Pengaturan</a>
                    <a href="<?= base_url('admin/pengaturan/openwa') ?>" class="admin-link <?= $is('pengaturan/openwa') ? 'is-active' : '' ?>"><?= icon('whatsapp', 'h-[18px] w-[18px]') ?> WhatsApp</a>
                <?php endif; ?>
            </nav>

            <div class="border-t-2 border-ink/10 pt-4">
                <a href="<?= base_url('/') ?>" target="_blank" rel="noopener" class="admin-link"><?= icon('external', 'h-[18px] w-[18px]') ?> Lihat situs publik</a>
                <form action="<?= base_url('admin/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="admin-link w-full text-coral hover:bg-coral/10 hover:text-coral"><?= icon('logout', 'h-[18px] w-[18px]') ?> Keluar</button>
                </form>

                <div class="mt-3 flex items-center gap-3 rounded-2xl border-2 border-ink/10 bg-white px-3 py-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full <?= avatar_tone($admin['name'] ?? 'Panitia') ?> font-display text-xs font-bold"><?= esc(initials($admin['name'] ?? 'Panitia')) ?></span>
                    <div class="min-w-0">
                        <p class="truncate font-display text-xs font-semibold"><?= esc($admin['name'] ?? 'Panitia') ?></p>
                        <p class="text-[10px] uppercase tracking-wide text-ink/40"><?= esc(role_label($admin['role'] ?? null)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="min-h-screen">
        <header class="admin-topbar">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="icon-btn lg:hidden" @click="sidebar = true" aria-label="Buka menu"><?= icon('menu', 'h-4 w-4') ?></button>
                    <div class="min-w-0">
                        <p class="text-[9px] font-semibold uppercase tracking-[0.18em] text-ink/35">Panel Panitia</p>
                        <h1 class="truncate font-display text-lg font-bold leading-tight tracking-tight"><?= esc($pageTitle) ?></h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden items-center gap-1.5 rounded-full border border-ink/10 bg-paper px-3 py-1.5 text-[10px] font-semibold text-ink/50 sm:inline-flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-mint"></span> Sistem aktif
                    </span>
                    <a href="<?= base_url('/') ?>" target="_blank" rel="noopener" class="icon-btn" aria-label="Lihat situs publik"><?= icon('external', 'h-4 w-4') ?></a>
                    <div class="relative" @click.outside="userMenu = false">
                        <button type="button" class="flex items-center gap-2 rounded-full border-2 border-ink/10 bg-paper py-1 pl-1 pr-3 transition hover:border-ink/25" @click="userMenu = !userMenu">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full <?= avatar_tone($admin['name'] ?? 'Panitia') ?> font-display text-[10px] font-bold"><?= esc(initials($admin['name'] ?? 'Panitia')) ?></span>
                            <span class="hidden text-xs font-semibold sm:block"><?= esc($admin['name'] ?? 'Panitia') ?></span>
                            <?= icon('chevron-down', 'h-3.5 w-3.5 text-ink/40') ?>
                        </button>
                        <div class="absolute right-0 top-12 w-56 rounded-2xl border-2 border-ink/10 bg-paper p-2 shadow-soft" x-show="userMenu" x-cloak x-transition.opacity>
                            <div class="px-3 py-2">
                                <p class="truncate font-display text-sm font-semibold"><?= esc($admin['name'] ?? 'Panitia') ?></p>
                                <p class="truncate text-[11px] text-ink/40"><?= esc($admin['email'] ?? '') ?></p>
                            </div>
                            <div class="my-1 divider-dots"></div>
                            <a href="<?= base_url('admin/profil') ?>" class="admin-link py-2 text-xs"><?= icon('school', 'h-4 w-4') ?> Profil Sekolah</a>
                            <form action="<?= base_url('admin/logout') ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="admin-link w-full py-2 text-xs text-coral hover:bg-coral/10 hover:text-coral"><?= icon('logout', 'h-4 w-4') ?> Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">
                <?php if (! empty($pageSubtitle)): ?>
                    <div class="mb-6 flex flex-col gap-1">
                        <h2 class="font-display text-2xl font-bold tracking-[-0.03em] sm:text-3xl"><?= esc($pageTitle) ?></h2>
                        <p class="text-sm text-ink/50"><?= esc($pageSubtitle) ?></p>
                    </div>
                <?php endif; ?>
                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <footer class="px-4 pb-6 text-center text-[10px] text-ink/35 sm:px-6 lg:px-8">
            <?= esc($appName) ?> <?= esc(app_version(true)) ?> · Panel internal panitia. Jangan bagikan akses kepada pihak yang tidak berwenang.
        </footer>
    </div>
</div>

<?= $this->include('partials/confirm_modal') ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>