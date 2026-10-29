<?php
$appName = $appName ?? app_setting('app_name', 'CAKRA 2026');
$pageTitle = $pageTitle ?? 'Masuk Panel';
$logo = school_logo_url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101828">
    <title><?= esc($pageTitle . ' · ' . $appName) ?></title>
    <link rel="icon" href="<?= esc($logo) ?>"<?= school_logo_mime() !== '' ? ' type="' . esc(school_logo_mime()) . '"' : '' ?>>
    <link rel="preload" href="<?= base_url('assets/fonts/outfit-400.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <script defer src="<?= base_url('assets/js/alpine.min.js') ?>"></script>
</head>
<body class="flex min-h-screen flex-col bg-ink text-cream">
<?= $this->include('partials/flash') ?>
<div class="pointer-events-none fixed -left-32 top-1/4 h-96 w-96 rounded-full bg-cobalt/20 blur-3xl" aria-hidden="true"></div>
<div class="pointer-events-none fixed -right-24 bottom-0 h-96 w-96 rounded-full border-[54px] border-sun/15" aria-hidden="true"></div>

<header class="relative z-10 px-5 py-6 sm:px-8">
    <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-3">
        <img src="<?= esc($logo) ?>" alt="" class="h-11 w-11 rounded-2xl border-2 border-cream/20 bg-cream object-contain p-0.5">
        <span>
            <span class="block font-display text-lg font-bold leading-none"><?= esc($appName) ?></span>
            <span class="mt-1 block text-[9px] font-semibold uppercase tracking-[0.18em] text-cream/40">Panel Panitia</span>
        </span>
    </a>
</header>

<main class="relative z-10 flex flex-1 items-center justify-center px-5 py-8 sm:px-8">
    <?= $this->renderSection('content') ?>
</main>

<footer class="relative z-10 px-5 pb-6 text-center text-[10px] text-cream/30">
    Akses terbatas untuk panitia pemilihan. Aktivitas masuk dicatat pada audit log.
</footer>
<?= $this->renderSection('scripts') ?>
</body>
</html>