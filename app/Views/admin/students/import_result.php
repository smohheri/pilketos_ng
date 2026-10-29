<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Hasil Impor';
$pageSubtitle = 'Ringkasan data siswa yang berhasil diproses.';
?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-2xl">
    <div class="rounded-5xl border-2 border-ink bg-paper p-6 text-center shadow-hard sm:p-10">
        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-ink bg-mint">
            <?= icon('check', 'h-9 w-9') ?>
        </span>
        <h2 class="mt-6 text-3xl font-bold tracking-[-0.03em]">Impor selesai.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink/55">Data siswa telah diproses. Akun pemilih dan daftar pemilih dibuat otomatis untuk siswa baru.</p>

        <div class="mx-auto mt-6 grid max-w-sm grid-cols-2 gap-3">
            <div class="rounded-2xl border-2 border-mint/40 bg-mint/15 px-4 py-4">
                <p class="font-display text-3xl font-bold"><?= number_format((int) $result['inserted'], 0, ',', '.') ?></p>
                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Ditambahkan</p>
            </div>
            <div class="rounded-2xl border-2 border-ink/10 bg-cream px-4 py-4">
                <p class="font-display text-3xl font-bold"><?= number_format((int) $result['skipped'], 0, ',', '.') ?></p>
                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink/40">Dilewati</p>
            </div>
        </div>

        <div class="mt-7 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="<?= base_url('admin/siswa') ?>" class="btn btn-primary">Lihat data siswa <?= icon('arrow-right', 'h-4 w-4') ?></a>
            <a href="<?= base_url('admin/siswa/import') ?>" class="btn btn-light">Impor file lain</a>
        </div>
    </div>

    <p class="mt-5 text-center text-[11px] leading-relaxed text-ink/40">Baris duplikat atau tidak valid tidak disimpan. Periksa daftar siswa untuk memastikan data sudah benar.</p>
</div>
<?= $this->endSection() ?>