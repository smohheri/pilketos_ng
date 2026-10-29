<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Laporan';
$pageSubtitle = 'Rekapitulasi suara, penilaian, dan partisipasi pemilih.';
$meta = phase_meta($phase);
$colors = ['bg-coral', 'bg-sun', 'bg-cobalt', 'bg-mint', 'bg-lilac', 'bg-sky'];
$combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
$voteWeight = (float) ($report['vote_weight'] ?? 50);
$assessmentWeight = (float) ($report['assessment_weight'] ?? 50);
$scoreNumber = static fn ($value, int $decimals = 2): string => $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
?>

<?= $this->section('content') ?>
<?php if ($election === null): ?>
    <div class="rounded-4xl border-2 border-ink bg-paper p-8 text-center shadow-hard">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sun/40"><?= icon('chart', 'h-7 w-7') ?></span>
        <h2 class="mt-5 text-2xl font-bold">Belum ada data untuk dilaporkan.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink/50">Konfigurasi pemilihan dan tambahkan data siswa terlebih dahulu.</p>
        <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-primary mt-6">Atur pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-4 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                <span class="<?= $combined ? 'badge-cool' : 'badge-neutral' ?>"><?= icon('star', 'h-3.5 w-3.5') ?> <?= esc($report['mode_label'] ?? 'Suara voting saja') ?></span>
                <span class="text-[10px] uppercase tracking-[0.14em] text-ink/35"><?= esc($election['timezone']) ?></span>
            </div>
            <h2 class="mt-2 truncate text-lg font-bold"><?= esc($election['title']) ?></h2>
            <p class="text-xs text-ink/45"><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?> &middot; <?= $combined ? 'Suara ' . esc($scoreNumber($voteWeight)) . '% + penilaian ' . esc($scoreNumber($assessmentWeight)) . '%' : 'Peringkat berdasarkan suara sah' ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= base_url('admin/penilaian') ?>" class="btn btn-light btn-sm"><?= icon('star', 'h-4 w-4') ?> Penilaian</a>
            <a href="<?= base_url('admin/laporan/export') ?>" class="btn btn-light btn-sm"><?= icon('download', 'h-4 w-4') ?> Excel</a>
            <a href="<?= base_url('admin/laporan/pdf') ?>" class="btn btn-dark btn-sm"><?= icon('file-text', 'h-4 w-4') ?> Export PDF</a>
        </div>
    </div>

    <?php if (! empty($report['mode_fallback'])): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-sun/40 bg-sun/15 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-ink') ?>
            <p class="text-xs leading-relaxed text-ink/60">Mode suara + penilaian dipilih, tetapi belum ada variabel aktif. Hasil sementara memakai suara voting saja.</p>
        </div>
    <?php elseif ($combined && empty($report['assessment_complete'])): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
            <p class="text-xs leading-relaxed text-ink/60">Penilaian belum lengkap. Nilai kosong dihitung 0 pada nilai akhir. <a href="<?= base_url('admin/penilaian') ?>" class="font-semibold text-coral underline decoration-coral/30 underline-offset-2">Lengkapi penilaian</a> sebelum mengumumkan hasil.</p>
        </div>
    <?php endif; ?>

    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <?php foreach ([
            ['Pemilih terdaftar', $report['eligible'], 'users'],
            ['Sudah memilih', $report['voted'], 'check-circle'],
            ['Belum memilih', $report['not_voted'], 'clock'],
            ['Partisipasi', percent($report['participation'], 1), 'chart'],
        ] as $index => $card): ?>
            <div class="rounded-3xl border-2 border-ink/10 p-4 sm:p-5 <?= $index === 3 ? 'bg-ink text-cream' : 'bg-paper' ?>">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-current/10 bg-white/20 <?= $index === 3 ? 'text-sun' : 'text-ink/50' ?>"><?= icon($card[2], 'h-4 w-4') ?></span>
                <p class="mt-4 font-display text-2xl font-bold tracking-tight sm:text-3xl"><?= is_int($card[1]) ? number_format($card[1], 0, ',', '.') : esc($card[1]) ?></p>
                <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.14em] <?= $index === 3 ? 'text-cream/45' : 'text-ink/40' ?>"><?= esc($card[0]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Hasil kandidat</p>
                    <h2 class="mt-1 text-xl font-bold tracking-[-0.02em]"><?= $combined ? 'Nilai akhir & peringkat' : 'Perolehan suara' ?></h2>
                </div>
                <span class="chip"><?= icon('ballot', 'h-3.5 w-3.5') ?> <?= number_format($report['total_votes'], 0, ',', '.') ?> suara sah</span>
            </div>

            <?php if ($report['candidates'] === []): ?>
                <p class="mt-5 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada kandidat.</p>
            <?php else: ?>
                <div class="mt-5 space-y-5">
                    <?php foreach ($report['candidates'] as $index => $candidate): ?>
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-cream font-display text-[10px] font-bold"><?= str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT) ?></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-display text-sm font-semibold"><?= esc($candidate['full_name']) ?></p>
                                        <p class="text-[10px] text-ink/40"><?= esc($candidate['class_name']) ?> &middot; Peringkat <?= (int) $candidate['rank'] ?></p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <?php if ($combined): ?>
                                        <p class="font-display text-sm font-bold"><?= esc($scoreNumber($candidate['final_score'])) ?></p>
                                        <p class="text-[9px] text-ink/40">Nilai akhir &middot; <?= number_format($candidate['votes'], 0, ',', '.') ?> suara</p>
                                        <p class="text-[9px] text-ink/40">Penilaian <?= esc($scoreNumber($candidate['assessment_score'])) ?></p>
                                    <?php else: ?>
                                        <p class="font-display text-sm font-bold"><?= number_format($candidate['votes'], 0, ',', '.') ?></p>
                                        <p class="text-[10px] text-ink/40"><?= percent($candidate['share'], 1) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-2 progress"><div class="<?= $colors[$index % count($colors)] ?> h-full rounded-full" style="width: <?= max(2, $combined ? (float) $candidate['final_score'] : (float) $candidate['share']) ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Partisipasi kelas</p>
            <h2 class="mt-1 text-xl font-bold tracking-[-0.02em]">Per kelas</h2>

            <?php if ($byClass === []): ?>
                <p class="mt-5 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada data kelas.</p>
            <?php else: ?>
                <div class="mt-4 space-y-3">
                    <?php foreach ($byClass as $item): ?>
                        <?php
                        $eligible = (int) $item['eligible'];
                        $voted = (int) $item['voted'];
                        $rate = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0;
                        ?>
                        <div class="rounded-2xl border border-ink/10 bg-cream/70 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-display text-sm font-semibold"><?= esc($item['class_name']) ?></p>
                                <p class="text-[11px] text-ink/45"><?= $voted ?>/<?= $eligible ?> &middot; <?= percent($rate, 1) ?></p>
                            </div>
                            <div class="mt-2 progress h-2"><div class="progress-bar" style="width: <?= max(2, $rate) ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-5 flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-4 py-3">
        <?= icon('shield', 'h-4 w-4 mt-0.5 shrink-0 text-mint') ?>
        <div class="space-y-1">
            <p class="text-[11px] leading-relaxed text-ink/50">Laporan ini hanya memuat data agregat. Sistem tidak menyimpan hubungan antara identitas pemilih dan kandidat yang dipilih.</p>
            <?php if ($combined): ?>
                <p class="text-[11px] leading-relaxed text-ink/50">Nilai akhir = (persentase suara &middot; <?= esc($scoreNumber($voteWeight)) ?>%) + (nilai penilaian &middot; <?= esc($scoreNumber($assessmentWeight)) ?>%). Nilai penilaian dihitung dari rata-rata berbobot variabel aktif.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
