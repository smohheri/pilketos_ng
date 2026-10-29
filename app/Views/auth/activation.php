<?= $this->extend('layouts/public') ?>

<?php
$demoMode = filter_var(env('cakra.demoMode'), FILTER_VALIDATE_BOOLEAN);
?>

<?= $this->section('content') ?>
<section class="shell py-10 sm:py-14">
    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-stretch lg:gap-8">
        <aside class="relative overflow-hidden rounded-5xl border-2 border-ink bg-ink p-7 text-cream sm:p-10">
            <div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full border-[42px] border-coral/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-12 h-56 w-56 rounded-full bg-cobalt/30 blur-3xl" aria-hidden="true"></div>

            <div class="relative flex h-full flex-col">
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-cream/50 transition hover:text-cream"><?= icon('arrow-left', 'h-4 w-4') ?> Beranda</a>

                <div class="mt-10">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cream/20 px-3 py-1.5 font-display text-[10px] font-semibold uppercase tracking-[0.18em] text-cream/60"><?= icon('sparkles', 'h-3.5 w-3.5 text-sun') ?> Langkah pertama</span>
                    <h1 class="mt-5 text-4xl font-bold leading-[0.96] tracking-[-0.04em] sm:text-5xl">Aktifkan akun,<br><span class="text-sun">ambil suaramu.</span></h1>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-cream/60">Satu akun untuk satu siswa. Aktivasi membutuhkan NIS dan tanggal lahir yang terdaftar di sekolah.</p>
                </div>

                <ol class="mt-10 space-y-5">
                    <?php foreach ([
                        ['1', 'Verifikasi data', 'Cocokkan NIS dan tanggal lahirmu.'],
                        ['2', 'Kode OTP', 'Kode dikirim ke WhatsApp terdaftar.'],
                        ['3', 'Siap memilih', 'Masuk ke bilik suara saat pemilihan dibuka.'],
                    ] as $step): ?>
                        <li class="flex gap-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-cream/20 bg-cream/5 font-display text-xs font-bold text-sun"><?= esc($step[0]) ?></span>
                            <div>
                                <p class="font-display font-semibold text-cream"><?= esc($step[1]) ?></p>
                                <p class="mt-0.5 text-xs leading-relaxed text-cream/45"><?= esc($step[2]) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <div class="mt-auto pt-10">
                    <div class="flex items-center gap-2 rounded-2xl border border-cream/10 bg-cream/5 px-4 py-3 text-xs text-cream/50">
                        <?= icon('shield', 'h-4 w-4 shrink-0 text-mint') ?>
                        <p>Data pribadi hanya digunakan untuk verifikasi pemilih dan tidak ditampilkan ke publik.</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="rounded-5xl border-2 border-ink bg-paper p-6 shadow-hard sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/35">Aktivasi akun pemilih</p>
                    <h2 class="mt-1 text-3xl font-bold tracking-[-0.03em]">Masukkan data dirimu</h2>
                </div>
                <span class="badge-warm">Langkah 1 dari 3</span>
            </div>

            <?php if ($demoMode): ?>
                <div class="mt-5 flex items-start gap-2.5 rounded-2xl border-2 border-dashed border-cobalt/30 bg-sky/20 px-4 py-3 text-xs leading-relaxed text-ink/65">
                    <?= icon('info', 'h-4 w-4 mt-0.5 shrink-0 text-cobalt') ?>
                    <p><span class="font-semibold text-ink">Mode demo aktif.</span> Kode OTP akan ditampilkan di layar, bukan dikirim lewat WhatsApp.</p>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('aktivasi') ?>" method="post" class="mt-6" autocomplete="off">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="nis" class="field-label">NIS <span class="text-coral">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink/30"><?= icon('user', 'h-5 w-5') ?></span>
                        <input type="text" name="nis" id="nis" value="<?= esc(old('nis')) ?>" inputmode="numeric" pattern="[0-9]*" placeholder="Contoh: 2026001" class="input pl-12" required autofocus>
                    </div>
                    <p class="field-hint">Gunakan NIS yang tertera di kartu pelajar.</p>
                </div>

                <div class="field">
                    <label for="date_of_birth" class="field-label">Tanggal lahir <span class="text-coral">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="<?= esc(old('date_of_birth')) ?>" class="input" required>
                    <p class="field-hint">Harus sama dengan data yang tercatat di sekolah.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block mt-2">Lanjut verifikasi OTP <?= icon('arrow-right', 'h-4 w-4') ?></button>

                <p class="mt-4 text-center text-xs leading-relaxed text-ink/45">Dengan melanjutkan, kamu menyetujui penggunaan data untuk keperluan pemilihan. <a href="<?= base_url('#panduan') ?>" class="font-semibold underline decoration-coral underline-offset-2">Pelajari alurnya</a>.</p>
            </form>

            <div class="my-7 divider-dots"></div>

            <div class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p class="text-ink/50">Sudah pernah mengaktifkan akun?</p>
                <a href="<?= base_url('masuk') ?>" class="link-underline inline-flex items-center gap-1.5">Masuk dengan WhatsApp <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>

            <div class="mt-6 flex items-start gap-2.5 rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-xs leading-relaxed text-ink/50">
                <?= icon('alert', 'h-4 w-4 mt-0.5 shrink-0 text-coral') ?>
                <p>Data tidak cocok? Jangan mencoba berulang kali. Hubungi panitia di <span class="font-semibold text-ink"><?= esc($profile['official_contact'] ?? 'ruang OSIS') ?></span>.</p>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>