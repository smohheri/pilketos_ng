<?= $this->extend('layouts/public') ?>

<?php $nextElection = $nextElection ?? null; ?>

<?= $this->section('content') ?>
<section class="shell py-8 sm:py-12">
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <?= $this->include('partials/election_tabs') ?>
        </div>
        <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-paper shadow-hard">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border-[38px] border-mint/50" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -left-10 bottom-24 h-32 w-32 rotate-12 rounded-3xl bg-sun/40" aria-hidden="true"></div>

            <div class="relative p-6 text-center sm:p-10">
                <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-ink bg-mint shadow-hard-sm">
                    <?= icon('check', 'h-10 w-10 text-ink') ?>
                </span>
                <p class="mt-6 text-[10px] font-semibold uppercase tracking-[0.2em] text-ink/35">Suara tercatat · <?= esc(election_type_label($election['type'] ?? null)) ?></p>
                <h1 class="mt-2 text-4xl font-bold leading-[0.98] tracking-[-0.04em] sm:text-5xl"><?= $nextElection !== null ? 'Satu suara sudah tercatat.' : 'Suaramu sudah tercatat.' ?></h1>
                <p class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-ink/55">Terima kasih sudah berpartisipasi, <?= esc(explode(' ', $student['full_name'] ?? 'Pemilih')[0]) ?>. Suara disimpan anonim dan tidak dapat diubah.</p>

                <?php if ($nextElection !== null): ?>
                    <div class="mx-auto mt-7 max-w-md rounded-4xl border-2 border-ink bg-sun/30 p-5 text-left shadow-hard-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="<?= esc(election_type_badge($nextElection['type'] ?? null)) ?>"><?= esc(election_type_label($nextElection['type'] ?? null)) ?></span>
                            <span class="badge-live">Belum memilih</span>
                        </div>
                        <p class="mt-3 font-display text-lg font-bold leading-tight">Satu langkah lagi: pilih kandidat <?= esc(election_type_label($nextElection['type'] ?? null)) ?>.</p>
                        <p class="mt-1 text-xs leading-relaxed text-ink/55">Selesaikan pemilihan kedua agar suaramu lengkap untuk OSIS dan Poltar.</p>
                        <a href="<?= esc(election_voting_url($nextElection)) ?>" class="btn btn-primary btn-block mt-4">Pilih kandidat <?= esc(election_type_label($nextElection['type'] ?? null)) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a>
                    </div>
                <?php endif; ?>

                <?php if ($receipt !== ''): ?>
                    <div class="mx-auto mt-7 max-w-md rounded-4xl border-2 border-ink bg-cream p-5 text-left shadow-hard-sm" x-data="copyText()">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/40">Kode bukti suara</p>
                                <p class="mt-1 font-mono text-2xl font-bold tracking-[0.12em] text-ink sm:text-3xl"><?= esc($receipt) ?></p>
                            </div>
                            <button type="button" @click="copy('<?= esc($receipt, 'js') ?>')" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border-2 border-ink bg-sun transition hover:-translate-y-0.5" aria-label="Salin kode bukti">
                                <span x-show="!copied"><?= icon('copy', 'h-5 w-5') ?></span>
                                <span x-show="copied" x-cloak><?= icon('check', 'h-5 w-5') ?></span>
                            </button>
                        </div>
                        <p class="mt-3 flex items-start gap-2 rounded-xl bg-white/70 px-3 py-2 text-[11px] leading-relaxed text-ink/55">
                            <?= icon('info', 'mt-0.5 h-3.5 w-3.5 shrink-0 text-cobalt') ?>
                            <span>Simpan kode ini. Kode hanya ditampilkan sekali dan tidak bisa dicari berdasarkan nama.</span>
                        </p>
                        <p class="mt-2 text-right text-[10px] font-semibold text-mint" x-show="copied" x-cloak>Kode disalin.</p>
                    </div>
                <?php else: ?>
                    <div class="mx-auto mt-7 max-w-md rounded-3xl border-2 border-dashed border-ink/20 bg-cream p-5">
                        <p class="font-display font-bold">Kode bukti tidak ditampilkan lagi.</p>
                        <p class="mt-1 text-xs leading-relaxed text-ink/50">Kode hanya tampil sekali setelah suara dikirim. Jika kamu kehilangan kode, hubungi panitia dengan membawa kartu pelajar.</p>
                    </div>
                <?php endif; ?>

                <div class="mx-auto mt-7 grid max-w-md gap-3">
                    <?php if ($receipt !== ''): ?>
                        <a href="<?= base_url('bukti') ?>" class="btn btn-primary btn-block"><?= icon('fingerprint', 'h-4 w-4') ?> Verifikasi bukti suara</a>
                    <?php endif; ?>
                    <a href="<?= base_url('akun') ?>" class="btn btn-light btn-block">Lihat akun saya</a>
                </div>

                <div class="mx-auto mt-7 max-w-md rounded-2xl border border-ink/10 bg-white/60 px-4 py-3 text-left">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Pemungutan suara · <?= esc(election_type_label($election['type'] ?? null)) ?></p>
                    <p class="mt-1 text-xs text-ink/55"><?= esc($election['title'] ?? 'Pemilihan Ketua OSIS') ?></p>
                    <p class="mt-0.5 text-xs text-ink/40">Ditutup <?= esc(format_date_id($election['end_at'] ?? null, true)) ?></p>
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-5 py-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-lilac/50 text-ink"><?= icon('megaphone', 'h-5 w-5') ?></span>
            <p class="text-xs leading-relaxed text-ink/55"><span class="font-semibold text-ink">Hasil diumumkan setelah rekapitulasi.</span> Pantau halaman hasil resmi. Data publik hanya menampilkan agregat suara, bukan identitas pemilih.</p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>