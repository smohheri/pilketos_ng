<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$winner = $visible && $report !== null && ! empty($report['candidates']) ? $report['candidates'][0] : null;
$colors = ['bg-coral', 'bg-sun', 'bg-cobalt', 'bg-mint', 'bg-lilac', 'bg-sky'];
$combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
$voteWeight = (float) ($report['vote_weight'] ?? 50);
$assessmentWeight = (float) ($report['assessment_weight'] ?? 50);
$scoreNumber = static fn ($value, int $decimals = 2): string => $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
?>

<?= $this->section('head') ?>
<title><?= esc('Hasil Pemilihan — ' . $appName) ?></title>
<meta name="description" content="Hasil agregat <?= esc($election['title'] ?? 'pemilihan') ?>. Tidak menampilkan identitas pemilih.">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="relative overflow-hidden border-b-2 border-ink/10">
    <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full border-[44px] border-sun/50" aria-hidden="true"></div>
    <div class="shell relative py-12 sm:py-16">
        <div class="grid items-end gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="eyebrow-solid"><?= icon('chart', 'h-3.5 w-3.5') ?> Hasil &amp; laporan</span>
                    <span class="<?= esc(election_type_badge($election['type'] ?? null)) ?>"><?= esc(election_type_label($election['type'] ?? null)) ?></span>
                </div>
                <h1 class="mt-5 text-4xl font-bold leading-[0.95] tracking-[-0.04em] sm:text-6xl lg:text-7xl"><?= $combined ? 'Nilai akhir' : 'Suara sudah' ?><br><span class="text-cobalt"><?= $combined ? 'sudah dihitung.' : 'dihitung.' ?></span></h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-ink/60"><?= esc($election['title'] ?? 'Pemilihan Ketua OSIS') ?>. Laporan publik hanya menampilkan data agregat tanpa identitas pemilih.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <span class="<?= esc($meta['class']) ?>"><?= esc($meta['label']) ?></span>
                <?php if ($visible && $report !== null): ?>
                    <span class="<?= $combined ? 'badge-cool' : 'badge-neutral' ?>"><?= icon('star', 'h-3.5 w-3.5') ?> <?= esc($report['mode_label'] ?? 'Suara voting saja') ?></span>
                <?php endif; ?>
                <a href="<?= base_url('bukti') ?>" class="btn btn-light btn-sm"><?= icon('fingerprint', 'h-4 w-4') ?> Cek bukti</a>
            </div>
        </div>
    </div>
</section>

<div class="shell pt-6">
    <?= $this->include('partials/election_tabs') ?>
</div>

<section class="shell py-10 sm:py-14">
    <?php if (! $visible): ?>
        <div class="rounded-5xl border-2 border-ink bg-paper p-8 text-center shadow-hard sm:p-14">
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-ink bg-sun/50"><?= icon('lock', 'h-9 w-9') ?></span>
            <h2 class="mt-6 text-3xl font-bold tracking-[-0.03em] sm:text-4xl">Hasil belum dipublikasikan.</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-ink/55">Panitia akan mengumumkan hasil setelah proses rekapitulasi selesai. Pantau jadwal resmi di halaman utama.</p>
            <?php if ($election !== null && ! empty($election['result_publish_at'])): ?>
                <div class="mx-auto mt-6 inline-flex items-center gap-3 rounded-2xl border-2 border-ink/10 bg-cream px-5 py-4">
                    <?= icon('calendar', 'h-5 w-5 text-coral') ?>
                    <div class="text-left">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/40">Dijadwalkan</p>
                        <p class="font-display font-semibold"><?= esc(format_date_id($election['result_publish_at'], true)) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="<?= base_url('/') ?>" class="btn btn-light">Kembali ke beranda</a>
                <a href="<?= base_url('#jadwal') ?>" class="btn btn-sun">Lihat jadwal</a>
            </div>
        </div>
    <?php else: ?>
        <?php if ($winner !== null): ?>
            <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-ink p-7 text-cream shadow-hard sm:p-10">
                <div class="pointer-events-none absolute -right-10 -top-16 h-64 w-64 rounded-full border-[40px] border-sun/30" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-10 left-1/3 h-40 w-40 rounded-full bg-coral/30 blur-2xl" aria-hidden="true"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-cream/20 px-3 py-1 font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-cream/60"><?= icon('star', 'h-3.5 w-3.5 text-sun') ?> <?= $combined ? 'Nilai akhir tertinggi' : 'Perolehan suara tertinggi' ?></span>
                        <p class="mt-5 font-display text-[11px] font-semibold uppercase tracking-[0.2em] text-cream/40">Kandidat nomor <?= str_pad((string) $winner['candidate_number'], 2, '0', STR_PAD_LEFT) ?></p>
                        <h2 class="mt-2 text-4xl font-bold leading-none tracking-[-0.04em] sm:text-6xl"><?= esc($winner['full_name']) ?></h2>
                        <p class="mt-3 inline-flex items-center gap-2 rounded-full border border-cream/15 bg-cream/5 px-3 py-1.5 text-sm text-cream/65"><?= icon('school', 'h-4 w-4 text-sun') ?> Kelas <?= esc($winner['class_name']) ?></p>
                    </div>
                    <div class="flex items-end gap-6 lg:justify-end">
                        <div>
                            <?php if ($combined): ?>
                                <p class="font-display text-6xl font-bold leading-none text-sun sm:text-7xl"><?= esc($scoreNumber($winner['final_score'], 1)) ?></p>
                                <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-cream/40">Nilai akhir</p>
                                <p class="mt-1 text-[11px] text-cream/45"><?= number_format($winner['votes'], 0, ',', '.') ?> suara &middot; <?= percent($winner['share'], 1) ?></p>
                            <?php else: ?>
                                <p class="font-display text-6xl font-bold leading-none text-sun sm:text-7xl"><?= number_format($winner['votes'], 0, ',', '.') ?></p>
                                <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-cream/40">Suara &middot; <?= percent($winner['share'], 1) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (! empty($winner['photo_path'])): ?>
                            <img src="<?= esc(media_url($winner['photo_path'])) ?>" alt="" class="hidden h-36 w-28 rounded-3xl border-2 border-cream/20 object-cover sm:block">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <?php
            $cards = [
                ['Pemilih terdaftar', $report['eligible'], 'bg-paper', 'users'],
                ['Sudah memilih', $report['voted'], 'bg-mint/30', 'check-circle'],
                ['Belum memilih', $report['not_voted'], 'bg-sun/30', 'clock'],
                ['Partisipasi', percent($report['participation'], 1), 'bg-cobalt text-white', 'chart'],
            ];
            foreach ($cards as $card): ?>
                <div class="rounded-3xl border-2 border-ink/10 p-5 <?= $card[2] ?>">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-current/10 bg-white/20"><?= icon($card[3], 'h-4 w-4') ?></span>
                    <p class="mt-4 font-display text-3xl font-bold tracking-tight"><?= is_int($card[1]) ? number_format($card[1], 0, ',', '.') : esc($card[1]) ?></p>
                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] <?= $card[2] === 'bg-cobalt text-white' ? 'text-white/60' : 'text-ink/45' ?>"><?= esc($card[0]) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6 sm:p-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="eyebrow"><?= icon('chart', 'h-3.5 w-3.5') ?> Rekapitulasi</span>
                        <h2 class="mt-3 text-2xl font-bold tracking-[-0.03em]"><?= $combined ? 'Nilai akhir kandidat' : 'Perolehan suara kandidat' ?></h2>
                    </div>
                    <p class="hidden text-xs text-ink/40 sm:block">Total <?= number_format($report['total_votes'], 0, ',', '.') ?> suara sah</p>
                </div>

                <div class="mt-7 space-y-5">
                    <?php foreach ($report['candidates'] as $index => $candidate): ?>
                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-ink/10 bg-cream font-display text-xs font-bold"><?= str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT) ?></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-display font-semibold"><?= esc($candidate['full_name']) ?></p>
                                        <p class="text-xs text-ink/40"><?= esc($candidate['class_name']) ?> &middot; Peringkat <?= (int) $candidate['rank'] ?></p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <?php if ($combined): ?>
                                        <p class="font-display font-bold"><?= esc($scoreNumber($candidate['final_score'])) ?></p>
                                        <p class="text-[10px] text-ink/40"><?= number_format($candidate['votes'], 0, ',', '.') ?> suara &middot; <?= percent($candidate['share'], 1) ?></p>
                                    <?php else: ?>
                                        <p class="font-display font-bold"><?= number_format($candidate['votes'], 0, ',', '.') ?></p>
                                        <p class="text-[10px] text-ink/40"><?= percent($candidate['share'], 1) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-2.5 progress">
                                <div class="<?= $colors[$index % count($colors)] ?> h-full rounded-full" style="width: <?= max(2, $combined ? (float) $candidate['final_score'] : (float) $candidate['share']) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($combined): ?>
                    <p class="mt-5 rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-[11px] leading-relaxed text-ink/50">
                        Nilai akhir = <?= esc($scoreNumber($voteWeight)) ?>% suara + <?= esc($scoreNumber($assessmentWeight)) ?>% penilaian.
                        Nilai penilaian dihitung dari rata-rata berbobot variabel penilaian yang ditetapkan panitia.
                    </p>
                <?php endif; ?>
            </div>

            <div class="space-y-4">
                <div class="rounded-4xl border-2 border-ink bg-sun p-6 shadow-hard-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-ink/10 bg-white/40"><?= icon('shield', 'h-5 w-5') ?></span>
                    <p class="mt-4 font-display text-lg font-bold">Privasi terjaga.</p>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">Laporan ini tidak memuat NIS, tanggal lahir, nomor WhatsApp, atau identitas pemilih. Data suara disimpan terpisah dari data pemilih.</p>
                </div>
                <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Waktu pengumuman</p>
                    <p class="mt-2 font-display font-semibold"><?= esc(format_date_id($election['result_publish_at'] ?? null, true)) ?></p>
                    <p class="mt-1 text-xs text-ink/45"><?= esc($election['timezone'] ?? 'Asia/Makassar') ?></p>
                    <div class="my-4 divider-dots"></div>
                    <a href="<?= base_url('bukti') ?>" class="link-underline inline-flex items-center gap-1.5 text-sm">Verifikasi bukti suara <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>