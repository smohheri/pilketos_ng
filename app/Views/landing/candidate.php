<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$student = current_student();
$number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT);
$missionLines = lines($candidate['mission']);
$primaryHref = $student !== null ? election_voting_url($election) : base_url('aktivasi');
$primaryLabel = $student !== null ? 'Masuk Bilik Suara' : 'Aktivasi untuk Memilih';
if ($phase === 'scheduled') {
    $primaryHref = base_url('#jadwal');
    $primaryLabel = 'Lihat Jadwal Pemilihan';
}
if ($phase === 'results') {
    $primaryHref = election_results_url($election);
    $primaryLabel = 'Lihat Hasil Pemilihan';
}
$candidateAnchor = ($election['type'] ?? '') === 'osis' ? '#kandidat' : '#kandidat-' . ($election['slug'] ?? $election['id']);
$description = excerpt($candidate['vision'] ?? $candidate['tagline'] ?? '', 160);
?>

<?= $this->section('head') ?>
<meta property="og:title" content="<?= esc($candidate['full_name']) ?> — <?= esc($appName) ?>">
<meta property="og:description" content="<?= esc($description) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="shell pt-8 sm:pt-10">
    <nav class="flex items-center gap-2 text-xs font-medium text-ink/45" aria-label="Breadcrumb">
        <a href="<?= base_url('/') ?>" class="transition hover:text-ink">Beranda</a>
        <?= icon('chevron-right', 'h-3.5 w-3.5') ?>
        <a href="<?= base_url($candidateAnchor) ?>" class="transition hover:text-ink">Kandidat</a>
        <?= icon('chevron-right', 'h-3.5 w-3.5') ?>
        <span class="text-ink"><?= esc($candidate['full_name']) ?></span>
    </nav>

    <div class="mt-6 grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
        <div>
            <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-cream shadow-hard">
                <?php if (! empty($candidate['photo_path'])): ?>
                    <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="Foto <?= esc($candidate['full_name']) ?>" class="aspect-[4/5] w-full object-cover">
                <?php else: ?>
                    <div class="flex aspect-[4/5] w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?>">
                        <span class="font-display text-8xl font-bold"><?= esc(initials($candidate['full_name'])) ?></span>
                    </div>
                <?php endif; ?>
                <span class="absolute left-5 top-5 flex h-16 w-16 items-center justify-center rounded-full border-2 border-ink bg-sun font-display text-xl font-bold shadow-hard-sm"><?= esc($number) ?></span>
                <span class="absolute bottom-5 left-5 right-5 rounded-2xl border-2 border-ink bg-paper/95 px-4 py-3 backdrop-blur">
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/40">Kandidat nomor urut</span>
                    <span class="mt-0.5 block font-display text-2xl font-bold"><?= esc($number) ?> · <?= esc($candidate['class_name']) ?></span>
                </span>
            </div>
        </div>

        <div class="lg:pt-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="eyebrow"><?= icon('user', 'h-3.5 w-3.5') ?> Profil kandidat</span>
                <span class="<?= esc(election_type_badge($election['type'] ?? null)) ?>"><?= esc(election_type_label($election['type'] ?? null)) ?></span>
                <span class="<?= esc($meta['class']) ?>"><?= esc($meta['label']) ?></span>
                <?php if ((int) $candidate['is_active'] !== 1): ?>
                    <span class="badge-neutral">Tidak aktif</span>
                <?php endif; ?>
            </div>

            <h1 class="mt-5 text-4xl font-bold leading-[0.98] tracking-[-0.04em] sm:text-6xl"><?= esc($candidate['full_name']) ?></h1>
            <p class="mt-3 font-display text-lg font-medium text-coral">“<?= esc($candidate['tagline'] ?: 'Siap membawa perubahan untuk sekolah.') ?>”</p>
            <p class="mt-4 inline-flex items-center gap-2 rounded-full border-2 border-ink/10 bg-paper px-3 py-1.5 text-xs font-medium text-ink/60">
                <?= icon('school', 'h-3.5 w-3.5') ?> Kelas <?= esc($candidate['class_name']) ?>
            </p>

            <div class="mt-8 space-y-5">
                <div class="rounded-3xl border-2 border-ink/10 bg-paper p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-coral">Visi</p>
                    <p class="mt-2 text-lg leading-relaxed text-ink/80"><?= esc($candidate['vision'] ?: 'Visi belum diisi.') ?></p>
                </div>

                <div class="rounded-3xl border-2 border-ink bg-ink p-6 text-cream">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-sun">Misi</p>
                    <?php if ($missionLines !== []): ?>
                        <ul class="mt-3 space-y-3">
                            <?php foreach ($missionLines as $index => $mission): ?>
                                <li class="flex gap-3 text-sm leading-relaxed text-cream/80">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sun font-display text-[10px] font-bold text-ink"><?= $index + 1 ?></span>
                                    <span><?= esc($mission) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mt-2 text-sm text-cream/60">Misi belum diisi.</p>
                    <?php endif; ?>
                </div>

                <div class="rounded-3xl border-2 border-ink/10 bg-mint/20 p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/50">Program kerja</p>
                    <p class="mt-2 text-sm leading-relaxed text-ink/70"><?= esc($candidate['program_description'] ?: 'Program kerja belum diisi.') ?></p>
                </div>
            </div>

            <div class="mt-8 rounded-3xl border-2 border-ink bg-sun p-5 shadow-hard-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-display font-bold">Sudah menemukan pilihanmu?</p>
                        <p class="mt-1 text-sm text-ink/60">Pastikan akunmu aktif sebelum masuk ke bilik suara.</p>
                    </div>
                    <a href="<?= esc($primaryHref) ?>" class="btn btn-dark btn-sm shrink-0"><?= esc($primaryLabel) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-10 flex items-center gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-5 py-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cobalt/10 text-cobalt"><?= icon('lock', 'h-5 w-5') ?></span>
        <p class="text-sm text-ink/60">Sistem tidak menampilkan atau mencatat kandidat yang kamu pilih pada halaman akun. Pilihanmu tetap rahasia.</p>
    </div>
</section>

<?php if ($others !== []): ?>
<section class="shell section-pad pb-16">
    <div class="flex items-end justify-between gap-4">
        <div>
            <span class="eyebrow"><?= icon('users', 'h-3.5 w-3.5') ?> Kandidat lain</span>
            <h2 class="mt-3 text-3xl font-bold tracking-[-0.03em]">Bandingkan visi &amp; misi.</h2>
        </div>
        <a href="<?= base_url($candidateAnchor) ?>" class="hidden text-sm font-semibold underline decoration-coral decoration-2 underline-offset-4 sm:inline-flex">Semua kandidat</a>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($others as $item): ?>
            <a href="<?= base_url('kandidat/' . $item['id']) ?>" class="group flex items-center gap-4 rounded-3xl border-2 border-ink/10 bg-paper p-3 pr-5 transition hover:-translate-y-1 hover:border-ink/25">
                <div class="h-20 w-16 shrink-0 overflow-hidden rounded-2xl border-2 border-ink/10 bg-cream">
                    <?php if (! empty($item['photo_path'])): ?>
                        <img src="<?= esc(media_url($item['photo_path'])) ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-full w-full items-center justify-center <?= avatar_tone($item['full_name']) ?> font-display text-lg font-bold"><?= esc(initials($item['full_name'])) ?></div>
                    <?php endif; ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/35">Kandidat <?= str_pad((string) $item['candidate_number'], 2, '0', STR_PAD_LEFT) ?></p>
                    <p class="truncate font-display font-bold"><?= esc($item['full_name']) ?></p>
                    <p class="truncate text-xs text-ink/45"><?= esc($item['class_name']) ?></p>
                </div>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-ink/15 bg-white transition group-hover:bg-sun"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?= $this->endSection() ?>