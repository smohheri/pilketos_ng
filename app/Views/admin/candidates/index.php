<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Kandidat';
$pageSubtitle = 'Kelola profil, nomor urut, dan status kandidat.';
$meta = phase_meta($phase);
$activeCount = count(array_filter($candidates, static fn (array $c): bool => (int) $c['is_active'] === 1));
$locked = in_array($phase, ['ongoing', 'closed', 'results'], true);
?>

<?= $this->section('content') ?>
<?php if ($election === null): ?>
    <div class="rounded-4xl border-2 border-ink bg-paper p-8 text-center shadow-hard">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sun/40"><?= icon('ballot', 'h-7 w-7') ?></span>
        <h2 class="mt-5 text-2xl font-bold">Konfigurasi pemilihan belum ada.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink/50">Buat pemilihan terlebih dahulu sebelum menambahkan kandidat.</p>
        <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-primary mt-6">Buat pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-4 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                <?php if ($locked): ?>
                    <span class="badge-warm"><?= icon('lock', 'h-3.5 w-3.5') ?> <?= is_admin() ? 'Edit tercatat di audit' : 'Terkunci untuk panitia' ?></span>
                <?php endif; ?>
            </div>
            <h2 class="mt-2 truncate text-lg font-bold"><?= esc($election['title']) ?></h2>
            <p class="text-xs text-ink/45"><?= $activeCount ?> kandidat aktif dari <?= count($candidates) ?> kandidat terdaftar.</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="<?= base_url('admin/kandidat/baru') ?>" class="btn btn-primary btn-sm"><?= icon('plus', 'h-4 w-4') ?> Tambah kandidat</a>
            <a href="<?= base_url('/') . '#kandidat' ?>" target="_blank" rel="noopener" class="btn btn-light btn-sm">Lihat publik <?= icon('external', 'h-3.5 w-3.5') ?></a>
        </div>
    </div>

    <?php if ($locked): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
            <p class="text-xs leading-relaxed text-ink/60">Pemilihan sudah dimulai. <?= is_admin() ? 'Sebagai admin, kamu masih dapat mengubah data kandidat — setiap perubahan dicatat pada audit log.' : 'Panitia tidak dapat mengubah kandidat setelah pemilihan dimulai. Hubungi admin jika ada koreksi.' ?></p>
        </div>
    <?php endif; ?>

    <?php if ($candidates === []): ?>
        <div class="mt-5 rounded-4xl border-2 border-dashed border-ink/20 bg-paper p-10 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-cream"><?= icon('users', 'h-7 w-7 text-ink/40') ?></span>
            <h2 class="mt-5 text-xl font-bold">Belum ada kandidat.</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-ink/50">Tambahkan kandidat pertama beserta visi, misi, dan program kerjanya.</p>
            <a href="<?= base_url('admin/kandidat/baru') ?>" class="btn btn-primary mt-5">Tambah kandidat <?= icon('arrow-right', 'h-4 w-4') ?></a>
        </div>
    <?php else: ?>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($candidates as $candidate): ?>
                <?php
                $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT);
                $votes = $voteCounts[(int) $candidate['id']] ?? 0;
                ?>
                <div class="flex flex-col overflow-hidden rounded-4xl border-2 <?= (int) $candidate['is_active'] === 1 ? 'border-ink/10' : 'border-dashed border-ink/20' ?> bg-paper">
                    <div class="flex gap-4 p-4">
                        <div class="relative h-28 w-[88px] shrink-0 overflow-hidden rounded-2xl border-2 border-ink/10 bg-cream">
                            <?php if (! empty($candidate['photo_path'])): ?>
                                <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="" class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?> font-display text-2xl font-bold"><?= esc(initials($candidate['full_name'])) ?></div>
                            <?php endif; ?>
                            <span class="absolute left-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full border border-ink bg-sun font-display text-[10px] font-bold"><?= esc($number) ?></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[9px] font-semibold uppercase tracking-[0.14em] text-ink/35">Kandidat <?= esc($number) ?></p>
                                    <h3 class="mt-0.5 truncate font-display text-base font-bold"><?= esc($candidate['full_name']) ?></h3>
                                    <p class="text-[11px] text-ink/45">Kelas <?= esc($candidate['class_name']) ?></p>
                                </div>
                                <?php if ((int) $candidate['is_active'] === 1): ?>
                                    <span class="badge-live shrink-0 px-2 py-0.5 text-[9px]">Aktif</span>
                                <?php else: ?>
                                    <span class="badge-neutral shrink-0 px-2 py-0.5 text-[9px]">Nonaktif</span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2 line-clamp-2 text-[11px] leading-relaxed text-ink/50"><?= esc(excerpt($candidate['tagline'] ?: $candidate['vision'], 75)) ?></p>
                            <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-cream px-2.5 py-1 text-[10px] font-semibold text-ink/55"><?= icon('chart', 'h-3 w-3') ?> <?= number_format($votes, 0, ',', '.') ?> suara</div>
                        </div>
                    </div>

                    <div class="mt-auto grid grid-cols-3 gap-px border-t-2 border-ink/10 bg-ink/10">
                        <a href="<?= base_url('admin/kandidat/' . $candidate['id'] . '/ubah') ?>" class="flex items-center justify-center gap-1.5 bg-paper py-2.5 text-[11px] font-semibold transition hover:bg-sun/30"><?= icon('edit', 'h-3.5 w-3.5') ?> Ubah</a>
                        <form action="<?= base_url('admin/kandidat/' . $candidate['id'] . '/status') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="flex w-full items-center justify-center gap-1.5 bg-paper py-2.5 text-[11px] font-semibold transition hover:bg-mint/30"><?= icon('refresh', 'h-3.5 w-3.5') ?> <?= (int) $candidate['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                        </form>
                        <form
                            action="<?= base_url('admin/kandidat/' . $candidate['id'] . '/hapus') ?>"
                            method="post"
                            data-confirm="Kandidat beserta data terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan."
                            data-confirm-title="Hapus kandidat?"
                            data-confirm-label="Hapus kandidat"
                        >
                            <?= csrf_field() ?>
                            <button type="submit" class="flex w-full items-center justify-center gap-1.5 bg-paper py-2.5 text-[11px] font-semibold text-coral transition hover:bg-coral/10"><?= icon('trash', 'h-3.5 w-3.5') ?> Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>