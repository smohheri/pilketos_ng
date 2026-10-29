<?php
$appName = $appName ?? app_setting('app_name', 'CAKRA 2026');
$profile = $profile ?? null;
$schoolName = $profile['school_name'] ?? 'SMA Negeri 1 Nusantara';
$schoolShort = $profile['short_name'] ?? $schoolName;
$logoPath = media_url(! empty($profile['logo_path']) ? $profile['logo_path'] : school_logo_path());
$student = current_student();
$title = $title ?? ($appName . ' — ' . ($profile['footer_text'] ?? 'Pilih dengan Hati, Pimpin dengan Aksi.'));
$description = $description ?? meta_description($profile['description'] ?? 'Aplikasi pemilihan Ketua OSIS CAKRA 2026. Paperless, aman, dan transparan.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F6F0E4">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($description) ?>">
    <link rel="icon" href="<?= esc($logoPath) ?>"<?= school_logo_mime() !== '' ? ' type="' . esc(school_logo_mime()) . '"' : '' ?>>
    <link rel="preload" href="<?= base_url('assets/fonts/outfit-400.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url('assets/fonts/space-grotesk-600.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script defer src="<?= base_url('assets/js/app.js') ?>"></script>
    <script defer src="<?= base_url('assets/js/alpine.min.js') ?>"></script>
    <?= $this->renderSection('head') ?>
</head>
<body>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-full focus:bg-ink focus:px-5 focus:py-3 focus:text-cream">Lewati ke konten</a>
<?= $this->include('partials/flash') ?>

<header class="sticky top-0 z-50 border-b-2 border-ink/10 bg-cream/85 backdrop-blur-md" x-data="{ open: false }">
    <div class="shell">
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="<?= base_url('/') ?>" class="group flex items-center gap-3">
                <span class="relative">
                    <img src="<?= esc($logoPath) ?>" alt="Logo <?= esc($schoolShort) ?>" class="h-11 w-11 rounded-2xl border-2 border-ink bg-paper object-contain p-0.5 transition group-hover:rotate-6">
                </span>
                <span class="leading-none">
                    <span class="block font-display text-lg font-bold tracking-tight text-ink"><?= esc($appName) ?></span>
                    <span class="mt-1 block max-w-[14rem] truncate text-[11px] font-medium uppercase tracking-[0.14em] text-ink/45"><?= esc($schoolShort) ?></span>
                </span>
            </a>

            <nav class="hidden items-center gap-0.5 xl:flex" aria-label="Navigasi utama">
                <a href="<?= base_url('#beranda') ?>" class="nav-link">Beranda</a>
                <a href="<?= base_url('#tentang') ?>" class="nav-link">Tentang</a>
                <a href="<?= base_url('#visi-misi') ?>" class="nav-link">Visi-Misi</a>
                <a href="<?= base_url('#pemilihan') ?>" class="nav-link">Pemilihan</a>
                <a href="<?= base_url('#kandidat') ?>" class="nav-link">Kandidat</a>
                <a href="<?= base_url('#jadwal') ?>" class="nav-link">Jadwal</a>
                <a href="<?= base_url('#panduan') ?>" class="nav-link">Panduan</a>
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                <?php if ($student !== null): ?>
                    <a href="<?= base_url('akun') ?>" class="btn btn-light btn-sm">
                        <?= icon('user', 'h-4 w-4') ?> <?= esc(explode(' ', $student['name'])[0]) ?>
                    </a>
                    <a href="<?= base_url('voting') ?>" class="btn btn-primary btn-sm">Bilik Suara <?= icon('arrow-right', 'h-4 w-4') ?></a>
                <?php else: ?>
                    <a href="<?= base_url('masuk') ?>" class="btn btn-light btn-sm">Masuk</a>
                    <a href="<?= base_url('aktivasi') ?>" class="btn btn-primary btn-sm">Aktivasi Akun</a>
                <?php endif; ?>
            </div>

            <button type="button" class="icon-btn lg:hidden" @click="open = !open" :aria-expanded="open.toString()" aria-label="Buka menu">
                <span x-show="!open"><?= icon('menu') ?></span>
                <span x-show="open" x-cloak><?= icon('x') ?></span>
            </button>
        </div>
    </div>

    <div class="border-t-2 border-ink/10 bg-paper lg:hidden" x-show="open" x-cloak x-transition.opacity>
        <div class="shell grid gap-1 py-4">
            <a href="<?= base_url('#tentang') ?>" class="nav-link text-left">Tentang</a>
            <a href="<?= base_url('#pemilihan') ?>" class="nav-link text-left">Dua Pemilihan</a>
            <a href="<?= base_url('#visi-misi') ?>" class="nav-link text-left">Visi-Misi Sekolah</a>
            <a href="<?= base_url('#kandidat') ?>" class="nav-link text-left">Kandidat</a>
            <a href="<?= base_url('#jadwal') ?>" class="nav-link text-left">Jadwal</a>
            <a href="<?= base_url('#panduan') ?>" class="nav-link text-left">Panduan</a>
            <a href="<?= base_url('hasil') ?>" class="nav-link text-left">Hasil Pemilihan</a>
            <div class="mt-3 grid gap-2 border-t-2 border-ink/10 pt-3">
                <?php if ($student !== null): ?>
                    <a href="<?= base_url('akun') ?>" class="btn btn-light btn-block">Akun Saya</a>
                    <a href="<?= base_url('voting') ?>" class="btn btn-primary btn-block">Masuk Bilik Suara</a>
                <?php else: ?>
                    <a href="<?= base_url('masuk') ?>" class="btn btn-light btn-block">Masuk</a>
                    <a href="<?= base_url('aktivasi') ?>" class="btn btn-primary btn-block">Aktivasi Akun</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main id="main">
    <?= $this->renderSection('content') ?>
</main>

<footer class="mt-10 bg-ink text-cream">
    <div class="shell py-14">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <div class="flex items-center gap-3">
                    <img src="<?= esc($logoPath) ?>" alt="" class="h-14 w-14 rounded-2xl border-2 border-cream/20 bg-cream object-contain p-1">
                    <div>
                        <p class="font-display text-2xl font-bold leading-none"><?= esc($appName) ?></p>
                        <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-cream/45"><?= esc($schoolShort) ?></p>
                    </div>
                </div>
                <p class="mt-5 max-w-md text-sm leading-relaxed text-cream/65"><?= esc($profile['description'] ?? 'Pemilihan Ketua OSIS yang paperless, mudah diakses, aman, dan transparan.') ?></p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="chip border-cream/15 bg-cream/5 text-cream"><?= icon('shield', 'h-3.5 w-3.5 text-mint') ?> Privasi terjaga</span>
                    <span class="chip border-cream/15 bg-cream/5 text-cream"><?= icon('https', 'h-3.5 w-3.5 text-sun') ?> Paperless</span>
                </div>
            </div>

            <div>
                <p class="font-display text-xs font-semibold uppercase tracking-[0.2em] text-cream/35">Jelajahi</p>
                <ul class="mt-4 space-y-2.5 text-sm text-cream/70">
                    <li><a href="<?= base_url('#kandidat') ?>" class="transition hover:text-sun">Kandidat</a></li>
                    <li><a href="<?= base_url('#jadwal') ?>" class="transition hover:text-sun">Jadwal tahapan</a></li>
                    <li><a href="<?= base_url('#panduan') ?>" class="transition hover:text-sun">Panduan memilih</a></li>
                    <li><a href="<?= base_url('hasil') ?>" class="transition hover:text-sun">Hasil pemilihan</a></li>
                    <li><a href="<?= base_url('bukti') ?>" class="transition hover:text-sun">Cek bukti suara</a></li>
                </ul>
            </div>

            <div>
                <p class="font-display text-xs font-semibold uppercase tracking-[0.2em] text-cream/35">Bantuan</p>
                <ul class="mt-4 space-y-3 text-sm text-cream/70">
                    <?php if (! empty($profile['official_contact'])): ?>
                        <li class="flex items-start gap-2.5"><?= icon('whatsapp', 'mt-0.5 h-4 w-4 shrink-0 text-mint') ?><span><?= esc($profile['official_contact']) ?></span></li>
                    <?php endif; ?>
                    <?php if (! empty($profile['official_website'])): ?>
                        <li class="flex items-start gap-2.5"><?= icon('link', 'mt-0.5 h-4 w-4 shrink-0 text-sun') ?><a href="<?= esc(safe_url($profile['official_website'])) ?>" target="_blank" rel="noopener" class="transition hover:text-sun">Situs resmi sekolah</a></li>
                    <?php endif; ?>
                    <li class="flex items-start gap-2.5"><?= icon('school', 'mt-0.5 h-4 w-4 shrink-0 text-lilac') ?><span><?= esc($profile['address'] ?? 'Kota Nusantara') ?></span></li>
                    <li class="pt-1"><a href="<?= base_url('admin/login') ?>" class="inline-flex items-center gap-1.5 text-xs text-cream/40 transition hover:text-cream"><?= icon('lock', 'h-3.5 w-3.5') ?> Panel Panitia</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-cream/10 pt-6 text-xs text-cream/40 sm:flex-row sm:items-center sm:justify-between">
            <p><?= esc($profile['footer_text'] ?? ('© ' . date('Y') . ' ' . $appName . '. Seluruh hak cipta dilindungi.')) ?></p>
            <p>Dibuat untuk pemilihan yang jujur, rahasia, dan bermakna.</p>
        </div>
    </div>
</footer>

<?= $this->include('partials/confirm_modal') ?>
<?= $this->renderSection('scripts') ?>
</body>
</html>