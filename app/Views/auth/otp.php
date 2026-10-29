<?= $this->extend('layouts/public') ?>

<?php
$isActivation = ($purpose ?? '') === 'activation';
$action = $isActivation ? base_url('aktivasi/otp') : base_url('masuk/otp');
$restart = $isActivation ? base_url('aktivasi') : base_url('masuk');
$heading = $isActivation ? 'Verifikasi aktivasi' : 'Verifikasi masuk';
$description = $isActivation
    ? 'Masukkan kode OTP untuk mengaktifkan akun pemilihmu.'
    : 'Masukkan kode OTP untuk melanjutkan ke akun pemilih.';
?>

<?= $this->section('content') ?>
<section class="shell py-10 sm:py-14">
    <div class="mx-auto max-w-2xl">
        <div class="rounded-5xl border-2 border-ink bg-paper p-6 shadow-hard sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="<?= esc($restart) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Kembali</a>
                <span class="badge-warm">Langkah 2 dari 3</span>
            </div>

            <div class="mt-7 text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl border-2 border-ink bg-sun shadow-hard-sm"><?= icon('whatsapp', 'h-8 w-8') ?></span>
                <p class="mt-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/35"><?= esc($heading) ?></p>
                <h1 class="mt-2 text-3xl font-bold tracking-[-0.03em] sm:text-4xl">Masukkan 6 digit OTP</h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink/55"><?= esc($description) ?> Kode berlaku <span class="font-semibold text-ink">5 menit</span> dan hanya dapat dipakai sekali.</p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full border-2 border-ink/10 bg-cream px-4 py-2 text-sm">
                    <span class="text-ink/40">Dikirim ke</span>
                    <span class="font-display font-semibold"><?= esc($contact ?: 'nomor terdaftar') ?></span>
                </p>
            </div>

            <?php if (! empty($demoOtp)): ?>
                <div class="mt-6 rounded-3xl border-2 border-dashed border-cobalt/40 bg-sky/25 p-4" x-data="copyText()">
                    <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cobalt text-white"><?= icon('key', 'h-5 w-5') ?></span>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-cobalt">Mode demo · kode OTP</p>
                                <p class="font-mono text-2xl font-bold tracking-[0.22em] text-ink"><?= esc($demoOtp) ?></p>
                            </div>
                        </div>
                        <button type="button" @click="copy('<?= esc($demoOtp, 'js') ?>')" class="btn btn-light btn-sm">
                            <span x-show="!copied"><?= icon('copy', 'h-4 w-4') ?> Salin</span>
                            <span x-show="copied" x-cloak><?= icon('check', 'h-4 w-4') ?> Tersalin</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= esc($action) ?>" method="post" class="mt-7" x-data="otpField()" autocomplete="off">
                <?= csrf_field() ?>
                <label for="otp" class="field-label justify-center">Kode OTP</label>
                <input
                    type="text"
                    name="otp"
                    id="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    placeholder="••••••"
                    class="input text-center font-mono text-3xl font-bold tracking-[0.5em] sm:text-4xl sm:tracking-[0.65em]"
                    x-model="value"
                    @input="handleInput($event)"
                    required
                    autofocus
                    aria-describedby="otp-help"
                >
                <p id="otp-help" class="field-hint text-center">Ketik 6 angka tanpa spasi.</p>

                <button type="submit" x-ref="submit" class="btn btn-primary btn-block mt-5">Verifikasi &amp; lanjutkan <?= icon('arrow-right', 'h-4 w-4') ?></button>
            </form>

            <div class="mt-6 flex flex-col items-center gap-2 text-center">
                <p class="text-xs text-ink/45">Tidak menerima kode?</p>
                <a href="<?= esc($restart) ?>" class="link-underline inline-flex items-center gap-1.5 text-sm"><?= icon('refresh', 'h-4 w-4') ?> Minta kode baru</a>
                <p class="mt-1 max-w-sm text-[11px] leading-relaxed text-ink/35">Demi keamanan, permintaan ulang dibatasi. Jika terus gagal, hubungi panitia dengan membawa kartu pelajar.</p>
            </div>
        </div>

        <div class="mt-5 flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-5 py-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-mint/40 text-ink"><?= icon('shield', 'h-5 w-5') ?></span>
            <p class="text-xs leading-relaxed text-ink/55"><span class="font-semibold text-ink">Jangan bagikan kode OTP.</span> Panitia tidak pernah meminta kode ini melalui chat, telepon, atau formulir apa pun.</p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>