<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$firstName = explode(' ', $student['full_name'] ?? 'Pemilih')[0];
?>

<?= $this->section('content') ?>
<section class="border-b-2 border-ink/10 bg-ink text-cream">
    <div class="shell py-8 sm:py-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cream/20 px-3 py-1 font-display text-[10px] font-semibold uppercase tracking-[0.18em] text-cream/60"><?= icon('ballot', 'h-3.5 w-3.5 text-sun') ?> Bilik suara</span>
                    <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                </div>
                <h1 class="mt-4 text-3xl font-bold leading-[0.98] tracking-[-0.035em] sm:text-5xl">Halo, <?= esc($firstName) ?>.<br><span class="text-sun">Pilih dengan sadar.</span></h1>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-cream/60">Baca visi, misi, dan program setiap kandidat. Pilihan hanya bisa dikirim satu kali dan tidak dapat diubah.</p>
            </div>
            <div class="flex items-center gap-3">
                <?php foreach ([['1', 'Pilih', true], ['2', 'Konfirmasi', false], ['3', 'Selesai', false]] as $step): ?>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full border font-display text-xs font-bold <?= $step[2] ? 'border-sun bg-sun text-ink' : 'border-cream/20 text-cream/40' ?>"><?= esc($step[0]) ?></span>
                        <span class="hidden text-[11px] font-semibold uppercase tracking-wide sm:block <?= $step[2] ? 'text-cream' : 'text-cream/35' ?>"><?= esc($step[1]) ?></span>
                    </div>
                    <?php if ($step[0] !== '3'): ?><span class="h-px w-5 bg-cream/20"></span><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="shell py-8 sm:py-10" x-data="{ selected: null }">
    <div class="mb-6">
        <?= $this->include('partials/election_tabs') ?>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm text-ink/50">
            <?= icon('info', 'h-4 w-4 text-cobalt') ?>
            <p>Pilih <span class="font-semibold text-ink">satu</span> kandidat, lalu lanjutkan ke konfirmasi.</p>
        </div>
        <span class="chip"><?= icon('users', 'h-3.5 w-3.5') ?> <?= count($candidates) ?> kandidat</span>
    </div>

    <?php if ($candidates === []): ?>
        <div class="mt-6 rounded-4xl border-2 border-ink/10 bg-paper p-10 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sun/40"><?= icon('ballot', 'h-7 w-7') ?></span>
            <h2 class="mt-5 text-2xl font-bold">Belum ada kandidat aktif.</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-ink/50">Panitia belum menambahkan kandidat untuk pemilihan ini. Silakan kembali lagi nanti.</p>
        </div>
    <?php else: ?>
        <form action="<?= base_url('voting/konfirmasi') ?>" method="post" class="mt-6">
            <?= csrf_field() ?>
            <input type="hidden" name="election" value="<?= esc($election['slug'] ?? '') ?>">

            <div class="grid gap-4 md:grid-cols-2">
                <?php foreach ($candidates as $index => $candidate): ?>
                    <?php $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT); ?>
                    <label
                        class="group relative block cursor-pointer overflow-hidden rounded-4xl border-2 bg-paper p-3 transition duration-200"
                        :class="selected == '<?= (int) $candidate['id'] ?>' ? 'border-ink shadow-hard' : 'border-ink/10 hover:border-ink/30 hover:-translate-y-0.5'"
                    >
                        <input type="radio" name="candidate_id" value="<?= (int) $candidate['id'] ?>" class="sr-only" x-model="selected" required>

                        <div class="flex gap-4">
                            <div class="relative h-32 w-24 shrink-0 overflow-hidden rounded-3xl border-2 border-ink/10 bg-cream sm:h-40 sm:w-32">
                                <?php if (! empty($candidate['photo_path'])): ?>
                                    <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?> font-display text-3xl font-bold"><?= esc(initials($candidate['full_name'])) ?></div>
                                <?php endif; ?>
                                <span class="absolute left-2 top-2 flex h-9 w-9 items-center justify-center rounded-full border-2 border-ink bg-sun font-display text-xs font-bold"><?= esc($number) ?></span>
                            </div>

                            <div class="min-w-0 flex-1 py-1 pr-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Kandidat <?= esc($number) ?></p>
                                        <p class="mt-1 truncate font-display text-lg font-bold leading-tight"><?= esc($candidate['full_name']) ?></p>
                                        <p class="text-xs text-ink/45">Kelas <?= esc($candidate['class_name']) ?></p>
                                    </div>
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 transition" :class="selected == '<?= (int) $candidate['id'] ?>' ? 'border-ink bg-ink text-sun' : 'border-ink/15 bg-white text-transparent'">
                                        <?= icon('check', 'h-4 w-4') ?>
                                    </span>
                                </div>
                                <p class="mt-2.5 text-xs italic leading-relaxed text-coral">“<?= esc(excerpt($candidate['tagline'] ?: $candidate['vision'], 70)) ?>”</p>
                                <p class="mt-2 text-xs leading-relaxed text-ink/50"><?= esc(excerpt($candidate['vision'], 100)) ?></p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 rounded-2xl bg-cream/80 px-4 py-2.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-ink/50"><?= icon('eye', 'h-3.5 w-3.5') ?> Lihat profil lengkap</span>
                            <a href="<?= base_url('kandidat/' . $candidate['id']) ?>" target="_blank" rel="noopener" class="text-[11px] font-semibold underline decoration-coral decoration-2 underline-offset-2">Buka</a>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="sticky bottom-4 z-30 mt-6 rounded-4xl border-2 border-ink bg-paper p-4 shadow-hard">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sun/50 text-ink"><?= icon('ballot', 'h-5 w-5') ?></span>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Pilihanmu</p>
                            <p class="text-sm font-medium" x-show="!selected" x-cloak>Belum ada kandidat dipilih</p>
                            <p class="text-sm font-semibold" x-show="selected" x-cloak>Kandidat dipilih. Lanjutkan untuk meninjau.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <span class="text-[11px] leading-relaxed text-ink/40">Pilihan belum dikirim.</span>
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="!selected" :class="!selected ? 'opacity-50' : ''">
                            Lanjut konfirmasi <?= icon('arrow-right', 'h-4 w-4') ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <div class="mt-6 flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-5 py-4">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cobalt/10 text-cobalt"><?= icon('lock', 'h-5 w-5') ?></span>
        <p class="text-xs leading-relaxed text-ink/55"><span class="font-semibold text-ink">Suara rahasia.</span> Sistem menyimpan pilihan secara anonim dan tidak menampilkannya kembali di akunmu. Setelah dikirim, suara tidak dapat diubah.</p>
    </div>

    <div class="mt-4 text-center">
        <a href="<?= base_url('akun') ?>" class="text-xs font-medium text-ink/40 underline decoration-ink/20 underline-offset-4 transition hover:text-ink">Kembali ke akun saya</a>
    </div>
</section>
<?= $this->endSection() ?>