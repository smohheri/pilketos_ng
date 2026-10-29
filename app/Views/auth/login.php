<?= $this->extend('layouts/public') ?>

<?php
$demoMode = filter_var(env('cakra.demoMode'), FILTER_VALIDATE_BOOLEAN);
?>

<?= $this->section('content') ?>
<section class="shell py-10 sm:py-14">
    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch lg:gap-8">
        <div class="rounded-5xl border-2 border-ink bg-paper p-6 shadow-hard sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/35">Akun pemilih</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-[-0.03em] sm:text-4xl">Selamat datang kembali.</h1>
                </div>
                <span class="badge-live">Masuk aman</span>
            </div>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink/55">Masukkan nomor WhatsApp yang terdaftar. Kami akan mengirim kode OTP untuk memastikan akun ini benar milikmu.</p>

            <?php if ($demoMode): ?>
                <div class="mt-5 flex items-start gap-2.5 rounded-2xl border-2 border-dashed border-cobalt/30 bg-sky/20 px-4 py-3 text-xs leading-relaxed text-ink/65">
                    <?= icon('info', 'h-4 w-4 mt-0.5 shrink-0 text-cobalt') ?>
                    <p><span class="font-semibold text-ink">Mode demo aktif.</span> Kode OTP ditampilkan di halaman berikutnya.</p>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('masuk') ?>" method="post" class="mt-7" autocomplete="off">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="whatsapp_number" class="field-label">Nomor WhatsApp <span class="text-coral">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-mint"><?= icon('whatsapp', 'h-5 w-5') ?></span>
                        <input type="tel" name="whatsapp_number" id="whatsapp_number" value="<?= esc(old('whatsapp_number')) ?>" inputmode="tel" placeholder="08xx xxxx xxxx" class="input pl-12" required autofocus>
                    </div>
                    <p class="field-hint">Boleh diawali 08, 62, atau +62.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><?= icon('whatsapp', 'h-4 w-4') ?> Kirim kode OTP</button>
            </form>

            <div class="my-7 divider-dots"></div>

            <div class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p class="text-ink/50">Belum pernah aktivasi?</p>
                <a href="<?= base_url('aktivasi') ?>" class="link-underline inline-flex items-center gap-1.5">Aktivasi akun baru <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>
        </div>

        <aside class="relative overflow-hidden rounded-5xl border-2 border-ink bg-coral p-7 text-white sm:p-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full border-[44px] border-white/20" aria-hidden="true"></div>
            <div class="relative flex h-full flex-col">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10"><?= icon('ballot', 'h-6 w-6') ?></span>
                <h2 class="mt-7 max-w-sm text-3xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-4xl">Satu suara, satu langkah untuk sekolah.</h2>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/75">Masuk hanya saat kamu siap memilih. Jangan bagikan kode OTP kepada siapa pun, termasuk yang mengaku panitia.</p>

                <div class="mt-auto grid gap-3 pt-10">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                        <p class="font-display text-sm font-semibold">Pilihanmu rahasia</p>
                        <p class="mt-1 text-xs leading-relaxed text-white/65">Sistem tidak menampilkan kandidat yang kamu pilih di halaman akun.</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4">
                        <p class="font-display text-sm font-semibold">Sekali kirim, selesai</p>
                        <p class="mt-1 text-xs leading-relaxed text-white/65">Suara tidak dapat diubah setelah dikonfirmasi. Baca visi-misi sebelum memilih.</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>