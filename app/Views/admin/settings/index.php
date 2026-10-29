<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Pengaturan';
$pageSubtitle = 'Konfigurasi identitas aplikasi, kontak bantuan, dan gateway WhatsApp.';
$value = static fn (string $key, string $default = ''): string => (string) (old($key) ?? ($settings[$key] ?? $default));
$wa = $whatsapp ?? [];
$waActive = ! empty($wa['enabled']) && ($wa['base_url'] ?? '') !== '';
$waMode = ! empty($wa['session_mode']) ? 'OpenWA session' : 'Legacy /sendText';
?>

<?= $this->section('content') ?>
<div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
    <form action="<?= base_url('admin/pengaturan') ?>" method="post" class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <?= csrf_field() ?>

        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('settings', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Identitas aplikasi</h2>
                <p class="text-xs text-ink/45">Tampil di navigasi, judul halaman, dan footer.</p>
            </div>
        </div>

        <div class="mt-5 space-y-4">
            <div class="field mb-0">
                <label for="app_name" class="field-label">Nama aplikasi <span class="text-coral">*</span></label>
                <input type="text" name="app_name" id="app_name" value="<?= esc($value('app_name', 'CAKRA 2026')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="app_tagline" class="field-label">Tagline</label>
                <input type="text" name="app_tagline" id="app_tagline" value="<?= esc($value('app_tagline')) ?>" class="input" placeholder="Pilih dengan Hati, Pimpin dengan Aksi.">
            </div>
            <div class="field mb-0">
                <label for="school_year" class="field-label">Tahun ajaran</label>
                <input type="text" name="school_year" id="school_year" value="<?= esc($value('school_year')) ?>" class="input" placeholder="2025/2026">
            </div>
            <div class="field mb-0">
                <label for="announcement" class="field-label">Pengumuman singkat</label>
                <input type="text" name="announcement" id="announcement" value="<?= esc($value('announcement')) ?>" class="input" maxlength="255" placeholder="Informasi penting untuk pemilih...">
                <p class="field-hint">Maksimal 255 karakter. Gunakan untuk info mendesak.</p>
            </div>

            <div class="my-2 divider-dots"></div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="field mb-0">
                    <label for="support_email" class="field-label">Email bantuan</label>
                    <input type="email" name="support_email" id="support_email" value="<?= esc($value('support_email')) ?>" class="input" placeholder="panitia@sekolah.sch.id">
                </div>
                <div class="field mb-0">
                    <label for="support_whatsapp" class="field-label">WhatsApp bantuan</label>
                    <input type="tel" name="support_whatsapp" id="support_whatsapp" value="<?= esc($value('support_whatsapp')) ?>" class="input" placeholder="0812-3456-7890">
                    <p class="field-hint">Nomor akan dinormalkan ke format 62.</p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan pengaturan</button>
        </div>
    </form>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-mint/40 text-ink"><?= icon('whatsapp', 'h-5 w-5') ?></span>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Notifikasi OTP</p>
                    <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]">Gateway WhatsApp</h2>
                    <p class="mt-1 text-xs leading-relaxed text-ink/45">Hubungkan CAKRA ke server OpenWA untuk mengirim kode OTP.</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="<?= $waActive ? 'badge-live' : 'badge-neutral' ?>"><?= $waActive ? 'Aktif' : 'Nonaktif' ?></span>
                <span class="badge-neutral"><?= esc($waMode) ?></span>
            </div>
            <a href="<?= base_url('admin/pengaturan/openwa') ?>" class="btn btn-light btn-sm mt-4 w-full justify-center">Kelola OpenWA <?= icon('arrow-right', 'h-4 w-4') ?></a>
        </div>

        <div class="rounded-4xl border-2 border-ink bg-ink p-5 text-cream shadow-hard-sm sm:p-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl border border-cream/15 bg-cream/5 text-sun"><?= icon('shield', 'h-5 w-5') ?></span>
            <h2 class="mt-4 text-lg font-bold">Praktik aman</h2>
            <ul class="mt-3 space-y-2.5 text-xs leading-relaxed text-cream/60">
                <?php foreach ([
                    'Gunakan kata sandi unik untuk setiap akun panitia.',
                    'Jangan membagikan kode OTP atau tautan panel.',
                    'Ekspor data siswa hanya untuk keperluan resmi.',
                    'Tinjau audit log secara berkala.',
                ] as $tip): ?>
                    <li class="flex gap-2.5"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-mint"></span><?= esc($tip) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Informasi sistem</p>
            <dl class="mt-4 space-y-3 text-xs">
                <div class="flex items-center justify-between gap-3"><dt class="text-ink/45">Zona waktu</dt><dd class="font-semibold"><?= esc(config('App')->appTimezone) ?></dd></div>
                <div class="flex items-center justify-between gap-3"><dt class="text-ink/45">Lingkungan</dt><dd class="font-semibold"><?= esc(ENVIRONMENT) ?></dd></div>
                <div class="flex items-center justify-between gap-3"><dt class="text-ink/45">Versi aplikasi</dt><dd class="text-right font-semibold" title="Rilis <?= esc(config('Cakra')->releaseDate) ?>"><?= esc($appName ?? 'CAKRA 2026') ?> <span class="font-mono text-[11px] font-normal text-ink/50"><?= esc(app_version(true)) ?></span></dd></div>
            </dl>
            <p class="mt-4 rounded-xl bg-cream px-3 py-2 text-[10px] leading-relaxed text-ink/40">Kredensial database dan kunci enkripsi tetap dikelola melalui file .env. Kredensial gateway WhatsApp dapat diatur dari halaman <a href="<?= base_url('admin/pengaturan/openwa') ?>" class="font-semibold underline decoration-coral underline-offset-2">OpenWA</a>.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>