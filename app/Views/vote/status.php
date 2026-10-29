<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$student = current_student();
$countdownTarget = null;
$countdownLabel = '';
$title = 'Bilik suara belum tersedia';
$message = 'Saat ini kamu belum dapat mengirim suara. Periksa status pemilihan dan kelayakan akunmu di bawah.';
$icon = 'clock';
$tone = 'bg-sun/40';

if ($election === null) {
    $title = 'Belum ada pemilihan aktif.';
    $message = 'Panitia belum membuka pemilihan. Pantau informasi resmi dari sekolah.';
    $icon = 'info';
    $tone = 'bg-sky/50';
} elseif ($phase === 'scheduled') {
    $countdownTarget = strtotime((string) $election['start_at']);
    $countdownLabel = 'Bilik suara dibuka dalam';
    $title = 'Bilik suara belum dibuka.';
    $message = 'Pemungutan suara belum dimulai. Kamu bisa membaca profil kandidat terlebih dahulu.';
    $icon = 'clock';
    $tone = 'bg-sun/40';
} elseif ($phase === 'ongoing' && ($voter === null || $voter['eligibility_status'] !== 'eligible')) {
    $title = 'Akun belum terdaftar sebagai pemilih.';
    $message = 'Datamu belum masuk daftar pemilih untuk pemilihan ini. Hubungi panitia untuk verifikasi.';
    $icon = 'alert';
    $tone = 'bg-coral/20';
} elseif ($phase === 'ongoing' && $voter !== null && (int) $voter['has_voted'] === 1) {
    $title = 'Suaramu sudah tercatat.';
    $message = 'Satu pemilih hanya dapat mengirim satu suara. Kamu tidak perlu memilih lagi.';
    $icon = 'check-circle';
    $tone = 'bg-mint/50';
} elseif (in_array($phase, ['closed', 'results'], true)) {
    $title = $phase === 'results' ? 'Pemilihan telah selesai.' : 'Pemungutan suara telah ditutup.';
    $message = $phase === 'results' ? 'Hasil pemilihan sudah dapat dilihat di halaman hasil resmi.' : 'Hasil akan diumumkan sesuai jadwal rekapitulasi panitia.';
    $icon = 'lock';
    $tone = 'bg-lilac/50';
}
?>

<?= $this->section('content') ?>
<section class="shell py-10 sm:py-16">
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <?= $this->include('partials/election_tabs') ?>
        </div>
        <div class="rounded-5xl border-2 border-ink bg-paper p-6 shadow-hard sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="<?= base_url('akun') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Akun saya</a>
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
            </div>

            <div class="mt-8 text-center">
                <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-ink <?= esc($tone) ?>"><?= icon($icon, 'h-9 w-9') ?></span>
                <h1 class="mt-6 text-3xl font-bold leading-[1.02] tracking-[-0.035em] sm:text-5xl"><?= esc($title) ?></h1>
                <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-ink/55"><?= esc($message) ?></p>
            </div>

            <?php if ($countdownTarget !== null): ?>
                <div class="mx-auto mt-8 max-w-md" x-data="countdown('<?= esc(date('c', $countdownTarget), 'js') ?>')">
                    <p class="text-center text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/40"><?= esc($countdownLabel) ?></p>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        <?php foreach ([['days', 'Hari'], ['hours', 'Jam'], ['minutes', 'Menit'], ['seconds', 'Detik']] as $unit): ?>
                            <div class="rounded-2xl border-2 border-ink/10 bg-cream py-3 text-center">
                                <p class="font-display text-2xl font-bold tabular-nums" x-text="<?= esc($unit[0]) ?>">00</p>
                                <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-ink/35"><?= esc($unit[1]) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($election !== null): ?>
                <div class="mt-8 rounded-3xl border-2 border-ink/10 bg-cream p-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-ink text-sun"><?= icon('ballot', 'h-5 w-5') ?></span>
                        <div>
                            <p class="font-display font-semibold"><?= esc($election['title']) ?></p>
                            <p class="text-xs text-ink/45"><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <?php if ($phase === 'results'): ?>
                    <a href="<?= esc(election_results_url($election)) ?>" class="btn btn-primary">Lihat hasil pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
                <?php elseif ($phase === 'scheduled'): ?>
                    <a href="<?= base_url('#kandidat') ?>" class="btn btn-primary">Lihat profil kandidat <?= icon('arrow-right', 'h-4 w-4') ?></a>
                <?php else: ?>
                    <a href="<?= base_url('/') ?>" class="btn btn-primary">Kembali ke beranda <?= icon('home', 'h-4 w-4') ?></a>
                <?php endif; ?>
                <a href="<?= base_url('akun') ?>" class="btn btn-light">Kembali ke akun</a>
            </div>

            <?php if ($phase === 'ongoing' && ($voter === null || $voter['eligibility_status'] !== 'eligible')): ?>
                <div class="mt-6 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3">
                    <?= icon('whatsapp', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
                    <p class="text-xs leading-relaxed text-ink/60">Hubungi panitia di <span class="font-semibold text-ink"><?= esc($profile['official_contact'] ?? 'ruang OSIS') ?></span> dan bawa kartu pelajar. Jangan membuat akun ganda.</p>
                </div>
            <?php endif; ?>
        </div>

        <p class="mt-5 text-center text-xs text-ink/35">Butuh bantuan? <a href="<?= base_url('#panduan') ?>" class="font-semibold underline decoration-coral underline-offset-2">Baca panduan pemilih</a></p>
    </div>
</section>
<?= $this->endSection() ?>