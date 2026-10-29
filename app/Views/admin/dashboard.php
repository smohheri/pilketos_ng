<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Ringkasan operasional pemilihan dan aktivitas terbaru.';
$meta = phase_meta($phase);
$colors = ['bg-coral', 'bg-sun', 'bg-cobalt', 'bg-mint', 'bg-lilac', 'bg-sky'];
$admin = current_admin();
$combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
$scoreNumber = static fn ($value, int $decimals = 2): string => $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
?>

<?= $this->section('content') ?>
<?php if ($election === null): ?>
    <div class="rounded-4xl border-2 border-ink bg-paper p-8 text-center shadow-hard">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sun/40"><?= icon('ballot', 'h-7 w-7') ?></span>
        <h2 class="mt-5 text-2xl font-bold">Belum ada konfigurasi pemilihan.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink/50">Buat konfigurasi pemilihan, tambahkan kandidat, lalu impor data siswa untuk memulai.</p>
        <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-primary mt-6">Buat pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-4 rounded-4xl border-2 border-ink bg-ink p-5 text-cream shadow-hard-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                <span class="<?= $combined ? 'badge-cool' : 'badge-neutral' ?>"><?= icon('star', 'h-3.5 w-3.5') ?> <?= esc($report['mode_label'] ?? 'Suara voting saja') ?></span>
                <span class="text-[10px] uppercase tracking-[0.16em] text-cream/35"><?= esc($election['timezone']) ?></span>
            </div>
            <h2 class="mt-3 truncate text-xl font-bold tracking-[-0.02em] sm:text-2xl"><?= esc($election['title']) ?></h2>
            <p class="mt-1 text-xs text-cream/45"><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?></p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-sun btn-sm">Atur pemilihan</a>
            <a href="<?= base_url('admin/laporan') ?>" class="inline-flex items-center gap-2 rounded-full border border-cream/20 bg-cream/5 px-4 py-2.5 text-xs font-semibold text-cream/70 transition hover:bg-cream/10">Laporan <?= icon('arrow-right', 'h-4 w-4') ?></a>
        </div>
    </div>

    <?php if ($combined && empty($report['assessment_complete'])): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
            <p class="text-xs leading-relaxed text-ink/60">Penilaian belum lengkap. Nilai kosong dihitung 0 pada nilai akhir. <a href="<?= base_url('admin/penilaian') ?>" class="font-semibold text-coral underline decoration-coral/30 underline-offset-2">Lengkapi penilaian</a>.</p>
        </div>
    <?php endif; ?>

    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <?php
        $stats = [
            ['Pemilih terdaftar', number_format($report['eligible'], 0, ',', '.'), 'users', 'bg-paper', 'text-cobalt'],
            ['Sudah memilih', number_format($report['voted'], 0, ',', '.'), 'check-circle', 'bg-mint/25', 'text-ink'],
            ['Belum memilih', number_format($report['not_voted'], 0, ',', '.'), 'clock', 'bg-sun/25', 'text-ink'],
            ['Partisipasi', percent($report['participation'], 1), 'chart', 'bg-ink text-cream', 'text-sun'],
        ];
        foreach ($stats as $stat): ?>
            <div class="rounded-3xl border-2 border-ink/10 p-4 sm:p-5 <?= $stat[3] ?>">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-current/10 bg-white/20 <?= $stat[4] ?>"><?= icon($stat[2], 'h-4 w-4') ?></span>
                <p class="mt-4 font-display text-2xl font-bold tracking-tight sm:text-3xl"><?= esc($stat[1]) ?></p>
                <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.14em] <?= $stat[3] === 'bg-ink text-cream' ? 'text-cream/45' : 'text-ink/40' ?>"><?= esc($stat[0]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Hasil kandidat</p>
                    <h3 class="mt-1 text-xl font-bold tracking-[-0.02em]"><?= $combined ? 'Nilai akhir & peringkat' : 'Distribusi suara' ?></h3>
                </div>
                <span class="chip"><?= icon('ballot', 'h-3.5 w-3.5') ?> <?= number_format($report['total_votes'], 0, ',', '.') ?> suara</span>
            </div>

            <?php if ($report['candidates'] === []): ?>
                <p class="mt-6 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada kandidat. <a href="<?= base_url('admin/kandidat/baru') ?>" class="font-semibold underline decoration-coral underline-offset-2">Tambah kandidat</a>.</p>
            <?php else: ?>
                <div class="mt-5 space-y-4">
                    <?php foreach ($report['candidates'] as $index => $candidate): ?>
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-cream font-display text-[10px] font-bold"><?= str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT) ?></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-display text-sm font-semibold"><?= esc($candidate['full_name']) ?></p>
                                        <p class="text-[9px] text-ink/35">Peringkat <?= (int) $candidate['rank'] ?></p>
                                    </div>
                                </div>
                                <?php if ($combined): ?>
                                    <p class="shrink-0 text-right text-xs font-semibold"><?= esc($scoreNumber($candidate['final_score'])) ?> <span class="block font-normal text-ink/35"><?= number_format($candidate['votes'], 0, ',', '.') ?> suara</span></p>
                                <?php else: ?>
                                    <p class="shrink-0 text-xs font-semibold"><?= number_format($candidate['votes'], 0, ',', '.') ?> <span class="font-normal text-ink/35">(<?= percent($candidate['share'], 1) ?>)</span></p>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 progress"><div class="<?= $colors[$index % count($colors)] ?> h-full rounded-full" style="width: <?= max(2, $combined ? (float) $candidate['final_score'] : (float) $candidate['share']) ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-5">
            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Tahapan berikutnya</p>
                        <h3 class="mt-1 text-lg font-bold tracking-[-0.02em]">Jadwal pemilihan</h3>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cobalt/10 text-cobalt"><?= icon('calendar', 'h-4 w-4') ?></span>
                </div>
                <?php if ($nextStage !== null): ?>
                    <div class="mt-4 rounded-2xl border border-ink/10 bg-cream px-4 py-3">
                        <p class="font-display text-sm font-semibold"><?= esc($nextStage['title']) ?></p>
                        <p class="mt-0.5 text-xs text-ink/45"><?= esc(format_date_range($nextStage['start_at'], $nextStage['end_at'])) ?></p>
                    </div>
                <?php else: ?>
                    <p class="mt-4 rounded-2xl bg-cream px-4 py-5 text-center text-xs text-ink/45">Tidak ada tahapan mendatang.</p>
                <?php endif; ?>
                <a href="<?= base_url('admin/pemilihan') ?>" class="link-underline mt-4 inline-flex items-center gap-1.5 text-xs">Kelola jadwal <?= icon('arrow-right', 'h-3.5 w-3.5') ?></a>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Akses cepat</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <?php foreach ([
                        ['Tambah kandidat', 'admin/kandidat/baru', 'plus', 'bg-sun/40'],
                        ['Impor siswa', 'admin/siswa/import', 'upload', 'bg-mint/30'],
                        ['Laporan', 'admin/laporan', 'chart', 'bg-cobalt/10'],
                        ['Lihat situs', '/', 'external', 'bg-lilac/40'],
                    ] as $action): ?>
                        <a href="<?= base_url($action[1]) ?>" <?= $action[1] === '/' ? 'target="_blank" rel="noopener"' : '' ?> class="flex items-center gap-2 rounded-2xl border border-ink/10 px-3 py-3 text-xs font-semibold transition hover:border-ink/25 hover:-translate-y-0.5 <?= $action[3] ?>">
                            <?= icon($action[2], 'h-4 w-4 shrink-0') ?>
                            <span class="truncate"><?= esc($action[0]) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (is_admin()): ?>
        <div class="mt-5 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Jejak aktivitas</p>
                    <h3 class="mt-1 text-xl font-bold tracking-[-0.02em]">Audit log terbaru</h3>
                </div>
                <a href="<?= base_url('admin/audit') ?>" class="text-xs font-semibold underline decoration-coral decoration-2 underline-offset-4">Lihat semua</a>
            </div>
            <?php if ($recentLogs === []): ?>
                <p class="mt-4 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada aktivitas tercatat.</p>
            <?php else: ?>
                <div class="mt-4 divide-y divide-ink/10">
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-cream text-ink/50"><?= icon('shield', 'h-4 w-4') ?></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-display text-xs font-semibold"><span class="font-mono text-[11px]"><?= esc($log['action']) ?></span> · <?= esc($log['entity_type'] ?? '-') ?><?= ! empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : '' ?></p>
                                <p class="text-[10px] text-ink/40"><?= esc($log['actor_name'] ?? 'Sistem') ?> · <?= esc(format_date_id($log['created_at'] ?? null, true)) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>