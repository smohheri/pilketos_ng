<?= $this->extend('layouts/admin') ?>

<?php
$isEdit = $candidate !== null;
$pageTitle = $isEdit ? 'Ubah Kandidat' : 'Tambah Kandidat';
$pageSubtitle = $isEdit ? 'Perbarui profil kandidat. Perubahan setelah pemilihan dimulai akan dicatat.' : 'Lengkapi data kandidat sebelum pemilihan dibuka.';
$value = static fn (string $key, string $default = ''): string => (string) (old($key) ?? ($candidate[$key] ?? $default));
$active = old('is_active') !== null ? old('is_active') === '1' : (! $isEdit || (int) $candidate['is_active'] === 1);
$action = $isEdit ? base_url('admin/kandidat/' . $candidate['id']) : base_url('admin/kandidat');
$photo = ! empty($candidate['photo_path']) ? media_url($candidate['photo_path']) : null;
?>

<?= $this->section('content') ?>
<form action="<?= esc($action) ?>" method="post" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
    <?= csrf_field() ?>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('user', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Data utama</h2>
                    <p class="text-xs text-ink/45">Identitas kandidat yang tampil di surat suara.</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-[100px_1fr]">
                <div class="field mb-0">
                    <label for="candidate_number" class="field-label">No. urut <span class="text-coral">*</span></label>
                    <input type="number" name="candidate_number" id="candidate_number" value="<?= esc($value('candidate_number', (string) $nextNumber)) ?>" min="1" max="999" class="input text-center font-display text-xl font-bold" required>
                </div>
                <div class="space-y-4">
                    <div class="field mb-0">
                        <label for="full_name" class="field-label">Nama lengkap <span class="text-coral">*</span></label>
                        <input type="text" name="full_name" id="full_name" value="<?= esc($value('full_name')) ?>" class="input" required>
                    </div>
                    <div class="field mb-0">
                        <label for="class_name" class="field-label">Kelas <span class="text-coral">*</span></label>
                        <input type="text" name="class_name" id="class_name" value="<?= esc($value('class_name')) ?>" class="input" placeholder="XI-1" required>
                    </div>
                </div>
            </div>

            <div class="field mt-4 mb-0">
                <label for="tagline" class="field-label">Tagline</label>
                <input type="text" name="tagline" id="tagline" value="<?= esc($value('tagline')) ?>" class="input" maxlength="180" placeholder="Satu kalimat yang mewakili kandidat">
                <p class="field-hint">Maksimal 180 karakter. Muncul di kartu kandidat.</p>
            </div>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-mint/40"><?= icon('megaphone', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Visi, misi &amp; program</h2>
                    <p class="text-xs text-ink/45">Bahan pertimbangan pemilih.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="field mb-0">
                    <label for="vision" class="field-label">Visi</label>
                    <textarea name="vision" id="vision" class="textarea" rows="3" placeholder="Visi kandidat..."><?= esc($value('vision')) ?></textarea>
                </div>
                <div class="field mb-0">
                    <label for="mission" class="field-label">Misi</label>
                    <textarea name="mission" id="mission" class="textarea" rows="5" placeholder="Satu misi per baris..."><?= esc($value('mission')) ?></textarea>
                    <p class="field-hint">Tulis satu misi per baris agar tampil sebagai daftar bernomor.</p>
                </div>
                <div class="field mb-0">
                    <label for="program_description" class="field-label">Program kerja</label>
                    <textarea name="program_description" id="program_description" class="textarea" rows="5" placeholder="Program unggulan dan rencana kerja..."><?= esc($value('program_description')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Foto kandidat</h2>
            <p class="mt-1 text-xs text-ink/45">Rasio potret 4:5. Maksimal 2 MB.</p>

            <div class="mt-4 overflow-hidden rounded-3xl border-2 border-ink/10 bg-cream">
                <?php if ($photo !== null): ?>
                    <img src="<?= esc($photo) ?>" alt="Foto saat ini" class="aspect-[4/5] w-full object-cover">
                <?php else: ?>
                    <div class="flex aspect-[4/5] w-full flex-col items-center justify-center text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-ink/30"><?= icon('user', 'h-6 w-6') ?></span>
                        <p class="mt-2 text-xs text-ink/35">Belum ada foto</p>
                    </div>
                <?php endif; ?>
            </div>

            <label for="photo" class="mt-4 flex cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink/20 bg-cream/60 px-4 py-4 text-sm font-semibold transition hover:border-cobalt/40 hover:bg-sky/10">
                <?= icon('upload', 'h-4 w-4 text-cobalt') ?> <?= $isEdit ? 'Ganti foto' : 'Pilih foto' ?>
                <input type="file" name="photo" id="photo" accept="image/png,image/jpeg,image/webp" class="sr-only">
            </label>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Status</h2>
            <input type="hidden" name="is_active" value="0">
            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-cream px-4 py-3">
                <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 rounded border-ink/20 text-cobalt focus:ring-cobalt" <?= $active ? 'checked' : '' ?>>
                <span>
                    <span class="block font-display text-sm font-semibold">Tampilkan sebagai kandidat aktif</span>
                    <span class="mt-0.5 block text-[11px] leading-relaxed text-ink/45">Kandidat nonaktif tidak muncul di bilik suara dan halaman publik.</span>
                </span>
            </label>
        </div>

        <div class="rounded-4xl border-2 border-ink bg-sun p-5 shadow-hard-sm sm:p-6">
            <p class="font-display font-bold"><?= $isEdit ? 'Simpan perubahan?' : 'Tambahkan kandidat?' ?></p>
            <p class="mt-1 text-xs leading-relaxed text-ink/60">Pastikan nomor urut dan nama sudah benar. Nomor urut tidak boleh sama dengan kandidat lain.</p>
            <button type="submit" class="btn btn-dark btn-block mt-4"><?= icon('check', 'h-4 w-4') ?> <?= $isEdit ? 'Simpan perubahan' : 'Tambah kandidat' ?></button>
            <a href="<?= base_url('admin/kandidat') ?>" class="btn btn-light btn-block mt-2">Batal</a>
        </div>
    </div>
</form>
<?= $this->endSection() ?>