<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$firstName = explode(' ', $student['full_name'] ?? 'Pemilih')[0];
$hasVoted = $voter !== null && (int) $voter['has_voted'] === 1;
$eligible = $voter !== null && $voter['eligibility_status'] === 'eligible';
$canVote = $phase === 'ongoing' && $eligible && ! $hasVoted;
?>

<?= $this->section('content') ?>
<section class="border-b-2 border-ink/10 bg-ink text-cream">
    <div class="shell py-8 sm:py-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl border-2 border-cream/20 <?= avatar_tone($student['full_name'] ?? 'Siswa') ?> font-display text-xl font-bold"><?= esc(initials($student['full_name'] ?? 'Siswa')) ?></span>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-cream/40">Akun pemilih</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-[-0.02em] sm:text-3xl"><?= esc($student['full_name'] ?? 'Siswa') ?></h1>
                    <p class="mt-1 inline-flex items-center gap-1.5 text-xs text-cream/50"><?= icon('school', 'h-3.5 w-3.5') ?> Kelas <?= esc($student['class_name'] ?? '-') ?> · NIS <?= esc($student['nis'] ?? '-') ?></p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                <form action="<?= base_url('keluar') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-cream/20 bg-cream/5 px-4 py-2 text-xs font-semibold text-cream/70 transition hover:bg-cream/10 hover:text-cream"><?= icon('logout', 'h-3.5 w-3.5') ?> Keluar</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="shell pt-7">
    <div class="mb-3 flex items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Pemilihan</p>
            <h2 class="mt-1 text-xl font-bold tracking-[-0.02em]">Status keikutsertaan</h2>
        </div>
        <span class="chip"><?= count($elections) ?> pemilihan</span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <?php foreach ($elections as $group): ?>
            <?php
            $item = $group['election'];
            $active = (int) $item['id'] === (int) ($election['id'] ?? 0);
            $statusLabel = $group['has_voted']
                ? 'Sudah memilih'
                : ($group['can_vote'] ? 'Siap memilih' : $group['meta']['label']);
            $statusClass = $group['has_voted']
                ? 'badge-live'
                : ($group['can_vote'] ? 'badge-warm' : 'badge-neutral');
            ?>
            <a href="<?= esc(base_url('akun?election=' . rawurlencode((string) ($item['slug'] ?? '')))) ?>" class="rounded-3xl border-2 p-4 transition <?= $active ? 'border-ink bg-paper shadow-hard-sm' : 'border-ink/10 bg-paper hover:border-ink/30' ?>">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="<?= esc(election_type_badge($item['type'] ?? null)) ?>"><?= esc(election_type_label($item['type'] ?? null)) ?></span>
                        <p class="mt-2 font-display text-sm font-bold"><?= esc($item['title']) ?></p>
                        <p class="mt-1 text-[11px] text-ink/45"><?= esc(format_date_range($item['start_at'], $item['end_at'])) ?></p>
                    </div>
                    <span class="<?= esc($statusClass) ?> shrink-0"><?= esc($statusLabel) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="shell py-8 sm:py-10">
    <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-5">
            <div class="relative overflow-hidden rounded-4xl border-2 border-ink bg-paper p-6 shadow-hard-sm sm:p-7">
                <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full border-[28px] border-sun/40" aria-hidden="true"></div>
                <div class="relative">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Status memilih</p>
                            <h2 class="mt-1 text-2xl font-bold tracking-[-0.03em]">
                                <?php if ($hasVoted): ?>Suara sudah tercatat.
                                <?php elseif ($canVote): ?>Kamu siap memilih.
                                <?php elseif (! $eligible): ?>Belum terdaftar sebagai pemilih.
                                <?php elseif ($phase === 'scheduled'): ?>Menunggu bilik suara dibuka.
                                <?php elseif (in_array($phase, ['closed', 'results'], true)): ?>Pemungutan suara telah selesai.
                                <?php else: ?>Belum ada pemilihan aktif.
                                <?php endif; ?>
                            </h2>
                        </div>
                        <?php if ($hasVoted): ?>
                            <span class="badge-live"><?= icon('check', 'h-3.5 w-3.5') ?> Sudah memilih</span>
                        <?php elseif ($canVote): ?>
                            <span class="badge-warm"><?= icon('clock', 'h-3.5 w-3.5') ?> Belum memilih</span>
                        <?php else: ?>
                            <span class="badge-neutral">Tidak dapat memilih</span>
                        <?php endif; ?>
                    </div>

                    <p class="mt-3 max-w-lg text-sm leading-relaxed text-ink/55">
                        <?php if ($hasVoted): ?>
                            Terima kasih sudah berpartisipasi. Sistem tidak menampilkan kandidat pilihanmu demi menjaga kerahasiaan suara.
                        <?php elseif ($canVote): ?>
                            Pemungutan suara sedang berlangsung. Luangkan waktu untuk membaca visi-misi sebelum mengirim suara.
                        <?php elseif (! $eligible): ?>
                            Datamu belum masuk daftar pemilih. Hubungi panitia dengan membawa kartu pelajar untuk verifikasi.
                        <?php elseif ($phase === 'scheduled'): ?>
                            Pantau jadwal pembukaan. Kamu tetap dapat membaca profil kandidat sekarang.
                        <?php else: ?>
                            Lihat hasil resmi atau hubungi panitia jika ada pertanyaan tentang akunmu.
                        <?php endif; ?>
                    </p>

                    <?php if ($election !== null): ?>
                        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs text-ink/45">
                            <span class="<?= esc(election_type_badge($election['type'] ?? null)) ?>"><?= esc(election_type_label($election['type'] ?? null)) ?></span>
                            <span class="chip"><?= icon('ballot', 'h-3.5 w-3.5') ?> <?= esc($election['title']) ?></span>
                            <span class="chip"><?= icon('calendar', 'h-3.5 w-3.5') ?> <?= esc(format_date_range($election['start_at'], $election['end_at'])) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                        <?php if ($canVote): ?>
                            <a href="<?= esc(election_voting_url($election)) ?>" class="btn btn-primary btn-sm">Masuk bilik suara <?= icon('arrow-right', 'h-4 w-4') ?></a>
                        <?php elseif ($hasVoted): ?>
                            <a href="<?= esc(election_done_url($election)) ?>" class="btn btn-light btn-sm">Lihat status suara</a>
                        <?php elseif ($phase === 'results'): ?>
                            <a href="<?= esc(election_results_url($election)) ?>" class="btn btn-primary btn-sm">Lihat hasil pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
                        <?php else: ?>
                            <a href="<?= base_url('#kandidat') ?>" class="btn btn-light btn-sm">Lihat kandidat</a>
                        <?php endif; ?>
                        <a href="<?= base_url('bukti') ?>" class="btn btn-light btn-sm"><?= icon('fingerprint', 'h-4 w-4') ?> Cek bukti suara</a>
                    </div>
                </div>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6 sm:p-7">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Data akun</p>
                        <h2 class="mt-1 text-xl font-bold tracking-[-0.02em]">Informasi pemilih</h2>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cobalt/10 text-cobalt"><?= icon('user', 'h-5 w-5') ?></span>
                </div>

                <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-ink/10 bg-cream/70 px-4 py-3">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/35">Nama lengkap</dt>
                        <dd class="mt-1 text-sm font-semibold"><?= esc($student['full_name'] ?? '-') ?></dd>
                    </div>
                    <div class="rounded-2xl border border-ink/10 bg-cream/70 px-4 py-3">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/35">NIS</dt>
                        <dd class="mt-1 font-mono text-sm font-semibold"><?= esc($student['nis'] ?? '-') ?></dd>
                    </div>
                    <div class="rounded-2xl border border-ink/10 bg-cream/70 px-4 py-3">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/35">Kelas</dt>
                        <dd class="mt-1 text-sm font-semibold"><?= esc($student['class_name'] ?? '-') ?></dd>
                    </div>
                    <div class="rounded-2xl border border-ink/10 bg-cream/70 px-4 py-3">
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/35">WhatsApp</dt>
                        <dd class="mt-1 text-sm font-semibold"><?= esc(mask_phone($student['whatsapp_number'] ?? '')) ?></dd>
                    </div>
                </dl>

                <p class="mt-4 flex items-start gap-2 text-xs leading-relaxed text-ink/45">
                    <?= icon('info', 'mt-0.5 h-4 w-4 shrink-0 text-cobalt') ?>
                    Data di atas tidak dapat diubah sendiri. Jika ada kesalahan, hubungi panitia.
                </p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-4xl border-2 border-ink bg-sun p-6 shadow-hard-sm">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-ink/10 bg-white/40"><?= icon('shield', 'h-5 w-5') ?></span>
                <p class="mt-4 font-display text-lg font-bold">Privasi suara</p>
                <p class="mt-2 text-sm leading-relaxed text-ink/60">Pilihanmu tidak tersimpan di halaman akun dan tidak dapat dilihat oleh panitia. Yang tercatat hanya status sudah memilih.</p>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Aktivitas akun</p>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-ink/50">Status akun</span>
                        <span class="font-semibold"><?= ($account['account_status'] ?? '') === 'active' ? 'Aktif' : 'Belum aktif' ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-ink/50">Terakhir masuk</span>
                        <span class="font-semibold"><?= esc(format_date_id($account['last_login_at'] ?? null, true)) ?></span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-ink/50">Kelayakan</span>
                        <span class="font-semibold"><?= $eligible ? 'Terdaftar' : 'Belum terdaftar' ?></span>
                    </div>
                </div>
                <div class="my-4 divider-dots"></div>
                <a href="<?= base_url('#panduan') ?>" class="link-underline inline-flex items-center gap-1.5 text-sm">Panduan memilih <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6">
                <p class="flex items-center gap-2 text-xs font-semibold text-ink/60"><?= icon('whatsapp', 'h-4 w-4 text-mint') ?> Butuh bantuan?</p>
                <p class="mt-2 text-sm font-display font-semibold"><?= esc($profile['official_contact'] ?? 'Hubungi panitia sekolah') ?></p>
                <p class="mt-1 text-xs leading-relaxed text-ink/45">Bawa kartu pelajar saat menghubungi panitia. Jangan bagikan kode OTP kepada siapa pun.</p>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>