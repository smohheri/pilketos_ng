<?= $this->extend('layouts/admin_auth') ?>

<?php
$pageTitle = 'Masuk Panel';
?>

<?= $this->section('content') ?>
<div class="w-full max-w-md">
    <div class="rounded-5xl border-2 border-cream/15 bg-paper p-6 text-ink shadow-hard sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <span class="badge-warm"><?= icon('lock', 'h-3.5 w-3.5') ?> Akses panitia</span>
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-ink/40 transition hover:text-ink">Beranda <?= icon('arrow-right', 'h-3 w-3') ?></a>
        </div>

        <h1 class="mt-6 text-3xl font-bold leading-tight tracking-[-0.035em]">Masuk ke panel.</h1>
        <p class="mt-2 text-sm leading-relaxed text-ink/50">Gunakan akun panitia yang diberikan admin. Jangan bagikan kredensial kepada siapa pun.</p>

        <form action="<?= base_url('admin/login') ?>" method="post" class="mt-6" x-data="{ show: false }" autocomplete="off">
            <?= csrf_field() ?>

            <div class="field">
                <label for="email" class="field-label">Email</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink/30"><?= icon('user', 'h-5 w-5') ?></span>
                    <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" class="input pl-12" placeholder="nama@sekolah.sch.id" required autofocus>
                </div>
            </div>

            <div class="field">
                <label for="password" class="field-label">Kata sandi</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink/30"><?= icon('key', 'h-5 w-5') ?></span>
                    <input :type="show ? 'text' : 'password'" type="password" name="password" id="password" class="input pl-12 pr-12" placeholder="Masukkan kata sandi" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-ink/35 transition hover:bg-ink/5 hover:text-ink" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <?= icon('eye', 'h-4 w-4') ?>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Masuk panel <?= icon('arrow-right', 'h-4 w-4') ?></button>
        </form>

        <p class="mt-5 text-center text-[11px] leading-relaxed text-ink/40">Dengan masuk, kamu menyetujui bahwa seluruh aktivitas administratif dicatat untuk audit.</p>
    </div>

    <p class="mt-5 text-center text-[11px] text-cream/35">Bukan panitia? <a href="<?= base_url('masuk') ?>" class="font-semibold text-cream/60 underline underline-offset-2 hover:text-cream">Masuk sebagai pemilih</a></p>
</div>
<?= $this->endSection() ?>