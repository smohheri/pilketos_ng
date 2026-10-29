<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Profil Sekolah';
$pageSubtitle = 'Identitas sekolah yang tampil di halaman publik dan materi pemilihan.';
$profile = $profile ?? [];
$value = static fn (string $key): string => (string) (old($key) ?? ($profile[$key] ?? ''));
$logoPath = school_logo_url();
$heroPath = ! empty($profile['hero_image_path']) ? media_url($profile['hero_image_path']) : null;
?>

<?= $this->section('content') ?>
<form action="<?= base_url('admin/profil') ?>" method="post" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
    <?= csrf_field() ?>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('school', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Identitas sekolah</h2>
                    <p class="text-xs text-ink/45">Nama dan deskripsi yang dilihat publik.</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="field mb-0 sm:col-span-2">
                    <label for="school_name" class="field-label">Nama sekolah <span class="text-coral">*</span></label>
                    <input type="text" name="school_name" id="school_name" value="<?= esc($value('school_name')) ?>" class="input" required>
                </div>
                <div class="field mb-0">
                    <label for="short_name" class="field-label">Nama singkat</label>
                    <input type="text" name="short_name" id="short_name" value="<?= esc($value('short_name')) ?>" class="input" placeholder="SMAN 1 Nusantara">
                </div>
                <div class="field mb-0">
                    <label for="footer_text" class="field-label">Teks footer</label>
                    <input type="text" name="footer_text" id="footer_text" value="<?= esc($value('footer_text')) ?>" class="input" placeholder="© 2026 CAKRA 2026">
                </div>
                <div class="field mb-0 sm:col-span-2">
                    <label for="description" class="field-label">Deskripsi sekolah</label>
                    <textarea name="description" id="description" class="textarea" rows="4" placeholder="Ceritakan singkat tentang sekolah..."><?= esc($value('description')) ?></textarea>
                </div>
                <div class="field mb-0">
                    <label for="address" class="field-label">Alamat</label>
                    <textarea name="address" id="address" class="textarea" rows="3"><?= esc($value('address')) ?></textarea>
                </div>
                <div class="space-y-3">
                    <div class="field mb-0">
                        <label for="official_contact" class="field-label">Kontak resmi</label>
                        <input type="text" name="official_contact" id="official_contact" value="<?= esc($value('official_contact')) ?>" class="input" placeholder="0812-3456-7890">
                    </div>
                    <div class="field mb-0">
                        <label for="official_website" class="field-label">Situs resmi</label>
                        <input type="text" name="official_website" id="official_website" value="<?= esc($value('official_website')) ?>" class="input" placeholder="https://sekolah.sch.id">
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-mint/40"><?= icon('sparkles', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Visi &amp; misi sekolah</h2>
                    <p class="text-xs text-ink/45">Menampilkan nilai dan arah sekolah di beranda.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="field mb-0">
                    <label for="vision" class="field-label">Visi</label>
                    <textarea name="vision" id="vision" class="textarea" rows="3"><?= esc($value('vision')) ?></textarea>
                </div>
                <div class="field mb-0">
                    <label for="mission" class="field-label">Misi</label>
                    <textarea name="mission" id="mission" class="textarea" rows="6" placeholder="Satu misi per baris..."><?= esc($value('mission')) ?></textarea>
                    <p class="field-hint">Tulis satu poin misi per baris agar tampil sebagai daftar.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Logo sekolah</h2>
            <p class="mt-1 text-xs text-ink/45">PNG, JPG, WEBP, atau SVG. Maksimal 2 MB.</p>

            <div class="mt-4 flex items-center gap-4 rounded-3xl border-2 border-ink/10 bg-cream p-4">
                <img src="<?= esc($logoPath) ?>" alt="Logo saat ini" class="h-20 w-20 rounded-2xl border-2 border-ink/10 bg-white object-contain p-1">
                <div class="min-w-0 text-xs leading-relaxed text-ink/50">
                    <p class="font-display text-sm font-semibold text-ink">Logo aktif</p>
                    <p class="mt-1">Gunakan gambar persegi agar tampil rapi di navigasi dan footer.</p>
                </div>
            </div>

            <label for="logo" class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-ink/20 bg-cream/60 px-4 py-6 text-center transition hover:border-cobalt/40 hover:bg-sky/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-cobalt"><?= icon('upload', 'h-5 w-5') ?></span>
                <span class="mt-2 font-display text-sm font-semibold">Pilih logo baru</span>
                <span class="mt-0.5 text-[11px] text-ink/40">Kosongkan jika tidak ingin mengubah</span>
                <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only">
            </label>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Gambar hero</h2>
            <p class="mt-1 text-xs text-ink/45">Opsional. Tampil sebagai latar visual di beranda.</p>

            <?php if ($heroPath !== null): ?>
                <img src="<?= esc($heroPath) ?>" alt="Gambar hero saat ini" class="mt-4 aspect-video w-full rounded-2xl border-2 border-ink/10 object-cover">
            <?php else: ?>
                <div class="mt-4 flex aspect-video w-full items-center justify-center rounded-2xl border-2 border-dashed border-ink/15 bg-cream text-center">
                    <div>
                        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-ink/35"><?= icon('eye', 'h-5 w-5') ?></span>
                        <p class="mt-2 text-xs text-ink/40">Belum ada gambar hero.</p>
                    </div>
                </div>
            <?php endif; ?>

            <label for="hero" class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-ink/20 bg-cream/60 px-4 py-6 text-center transition hover:border-cobalt/40 hover:bg-sky/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-cobalt"><?= icon('upload', 'h-5 w-5') ?></span>
                <span class="mt-2 font-display text-sm font-semibold">Pilih gambar hero</span>
                <span class="mt-0.5 text-[11px] text-ink/40">Rasio 16:9 direkomendasikan</span>
                <input type="file" name="hero" id="hero" accept="image/png,image/jpeg,image/webp" class="sr-only">
            </label>
        </div>

        <div class="rounded-4xl border-2 border-ink bg-sun p-5 shadow-hard-sm sm:p-6">
            <p class="font-display font-bold">Simpan perubahan?</p>
            <p class="mt-1 text-xs leading-relaxed text-ink/60">Perubahan akan langsung tampil di halaman publik setelah disimpan.</p>
            <button type="submit" class="btn btn-dark btn-block mt-4"><?= icon('check', 'h-4 w-4') ?> Simpan profil</button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>