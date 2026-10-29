<?= $this->extend('layouts/public') ?>

<?php $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT); ?>

<?= $this->section('content') ?>
<section class="shell py-8 sm:py-12">
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <?= $this->include('partials/election_tabs') ?>
        </div>
        <div class="flex items-center justify-between gap-3">
            <a href="<?= esc(election_voting_url($election)) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Ubah pilihan</a>
            <span class="badge-warm">Langkah 2 dari 3</span>
        </div>

        <div class="mt-5 overflow-hidden rounded-5xl border-2 border-ink bg-paper shadow-hard">
            <div class="border-b-2 border-ink bg-ink px-6 py-6 text-cream sm:px-8">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-cream/40">Konfirmasi pilihan</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight tracking-[-0.03em] sm:text-4xl">Pastikan kandidatnya benar.</h1>
                <p class="mt-2 text-sm leading-relaxed text-cream/55">Setelah suara dikirim, pilihan tidak dapat diubah atau dibatalkan.</p>
            </div>

            <div class="p-6 sm:p-8">
                <div class="flex flex-col gap-5 rounded-4xl border-2 border-ink/10 bg-cream p-4 sm:flex-row sm:items-center">
                    <div class="relative h-44 w-36 shrink-0 overflow-hidden rounded-3xl border-2 border-ink bg-paper">
                        <?php if (! empty($candidate['photo_path'])): ?>
                            <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="Foto <?= esc($candidate['full_name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?> font-display text-4xl font-bold"><?= esc(initials($candidate['full_name'])) ?></div>
                        <?php endif; ?>
                        <span class="absolute left-2.5 top-2.5 flex h-10 w-10 items-center justify-center rounded-full border-2 border-ink bg-sun font-display text-sm font-bold"><?= esc($number) ?></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Kandidat nomor <?= esc($number) ?></p>
                        <h2 class="mt-1 text-2xl font-bold tracking-[-0.02em]"><?= esc($candidate['full_name']) ?></h2>
                        <p class="mt-1 inline-flex items-center gap-1.5 text-xs text-ink/50"><?= icon('school', 'h-3.5 w-3.5') ?> Kelas <?= esc($candidate['class_name']) ?></p>
                        <p class="mt-3 text-sm italic leading-relaxed text-coral">“<?= esc($candidate['tagline'] ?: excerpt($candidate['vision'], 80)) ?>”</p>
                    </div>

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center self-start rounded-full bg-mint text-ink sm:self-center"><?= icon('check', 'h-6 w-6') ?></span>
                </div>

                <div class="mt-6 rounded-3xl border-2 border-coral/30 bg-coral/10 p-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-coral text-white"><?= icon('alert', 'h-5 w-5') ?></span>
                        <div>
                            <p class="font-display font-bold">Pilihan ini bersifat final.</p>
                            <p class="mt-1 text-sm leading-relaxed text-ink/60">Sistem mencatat satu suara per pemilih. Kamu tidak dapat memilih ulang setelah menekan tombol kirim.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-2.5 rounded-2xl border border-ink/10 bg-white px-4 py-3 text-xs text-ink/55">
                        <?= icon('user', 'h-4 w-4 shrink-0 text-cobalt') ?>
                        <p>Pemilih: <span class="font-semibold text-ink"><?= esc($student['full_name'] ?? 'Siswa') ?></span></p>
                    </div>
                    <div class="flex items-center gap-2.5 rounded-2xl border border-ink/10 bg-white px-4 py-3 text-xs text-ink/55">
                        <?= icon('lock', 'h-4 w-4 shrink-0 text-mint') ?>
                        <p>Pilihan disimpan anonim.</p>
                    </div>
                </div>

                <form
                    action="<?= base_url('voting/kirim') ?>"
                    method="post"
                    class="mt-7"
                    data-confirm="Suara yang sudah dikirim tidak dapat diubah atau dibatalkan. Pastikan pilihanmu sudah benar."
                    data-confirm-title="Kirim suara sekarang?"
                    data-confirm-label="Ya, kirim suara"
                >
                    <?= csrf_field() ?>
                    <input type="hidden" name="election" value="<?= esc($election['slug'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?= icon('ballot', 'h-4 w-4') ?> Kirim suara sekarang
                    </button>
                </form>

                <a href="<?= esc(election_voting_url($election)) ?>" class="btn btn-light btn-block mt-3">Kembali pilih kandidat</a>

                <p class="mt-5 text-center text-[11px] leading-relaxed text-ink/40">Dengan mengirim, kamu menyatakan pilihan ini dibuat tanpa paksaan dan sesuai kehendak sendiri.</p>
            </div>
        </div>

        <div class="mt-5 flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-5 py-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sun/40"><?= icon('clock', 'h-5 w-5') ?></span>
            <div>
                <p class="text-xs font-semibold">Pemungutan suara berakhir</p>
                <p class="mt-0.5 text-xs text-ink/50"><?= esc(format_date_id($election['end_at'] ?? null, true)) ?> · <?= esc($election['timezone'] ?? 'Asia/Makassar') ?></p>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>