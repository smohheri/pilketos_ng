<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Ubah Data Siswa';
$pageSubtitle = 'Perbarui identitas siswa. Perubahan nomor WhatsApp memengaruhi login OTP.';
$value = static fn (string $key): string => (string) (old($key) ?? ($student[$key] ?? ''));
?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-3xl">
    <a href="<?= base_url('admin/siswa') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Kembali ke data siswa</a>

    <form action="<?= base_url('admin/siswa/' . $student['id']) ?>" method="post" class="mt-5 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <?= csrf_field() ?>

        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= avatar_tone($student['full_name']) ?> font-display text-sm font-bold"><?= esc(initials($student['full_name'])) ?></span>
            <div class="min-w-0">
                <h2 class="truncate text-lg font-bold tracking-[-0.02em]"><?= esc($student['full_name']) ?></h2>
                <p class="text-xs text-ink/45">NIS <?= esc($student['nis']) ?></p>
            </div>
            <span class="<?= (int) $student['is_active'] === 1 ? 'badge-live' : 'badge-neutral' ?> ml-auto"><?= (int) $student['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?></span>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="field mb-0">
                <label for="nis" class="field-label">NIS <span class="text-coral">*</span></label>
                <input type="text" name="nis" id="nis" value="<?= esc($value('nis')) ?>" class="input font-mono" required>
            </div>
            <div class="field mb-0">
                <label for="class_name" class="field-label">Kelas <span class="text-coral">*</span></label>
                <input type="text" name="class_name" id="class_name" value="<?= esc($value('class_name')) ?>" class="input" required>
            </div>
            <div class="field mb-0 sm:col-span-2">
                <label for="full_name" class="field-label">Nama lengkap <span class="text-coral">*</span></label>
                <input type="text" name="full_name" id="full_name" value="<?= esc($value('full_name')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="date_of_birth" class="field-label">Tanggal lahir <span class="text-coral">*</span></label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="<?= esc($value('date_of_birth')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="whatsapp_number" class="field-label">Nomor WhatsApp <span class="text-coral">*</span></label>
                <input type="tel" name="whatsapp_number" id="whatsapp_number" value="<?= esc($value('whatsapp_number')) ?>" class="input" required>
                <p class="field-hint">Format 08xx atau 62xx. Nomor ini dipakai untuk OTP.</p>
            </div>
        </div>

        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border-2 border-coral/20 bg-coral/5 px-4 py-3 text-[11px] leading-relaxed text-ink/55">
            <?= icon('alert', 'h-4 w-4 mt-0.5 shrink-0 text-coral') ?>
            <p>Jika siswa sudah memiliki akun aktif, mengubah nomor WhatsApp akan mengalihkan tujuan OTP ke nomor baru. Pastikan nomor sudah benar.</p>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan perubahan</button>
            <a href="<?= base_url('admin/siswa') ?>" class="btn btn-light btn-sm">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>