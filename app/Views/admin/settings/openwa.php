<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'WhatsApp Gateway';
$pageSubtitle = 'Hubungkan CAKRA ke server OpenWA untuk pengiriman kode OTP.';
$wa = $whatsapp ?? [];
$baseUrl = (string) ($wa['base_url'] ?? '');
$sessionId = (string) ($wa['session_id'] ?? '');
$hasApiKey = ($wa['api_key'] ?? '') !== '';
$hasUser = ($wa['user'] ?? '') !== '';
$hasPass = ($wa['pass_auth'] ?? '') !== '';
$sessionPresent = $sessionId !== '';
$sessionUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $sessionId) === 1;
$sessionMode = $sessionUuid;
$enabled = old('enabled') !== null ? old('enabled') === '1' : ! empty($wa['enabled']);
$verifySsl = old('verify_ssl') !== null ? old('verify_ssl') === '1' : (bool) ($wa['verify_ssl'] ?? true);
$payloadStyle = (string) (old('payload_style') ?? ($wa['payload_style'] ?? 'args'));
$value = static fn (string $key, string $default = ''): string => (string) (old($key) ?? ($wa[$key] ?? $default));
$active = ! empty($wa['enabled']) && $baseUrl !== '';
$ready = $enabled && $baseUrl !== '' && $sessionMode && $hasApiKey;
?>

<?= $this->section('content') ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <a href="<?= base_url('admin/pengaturan') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-ink/45 transition hover:text-ink">
        <?= icon('chevron-left', 'h-4 w-4') ?> Kembali ke pengaturan
    </a>
    <div class="flex flex-wrap items-center gap-2">
        <span class="<?= $active ? 'badge-live' : 'badge-neutral' ?>"><?= icon('whatsapp', 'h-3.5 w-3.5') ?> <?= $active ? 'Gateway aktif' : 'Gateway nonaktif' ?></span>
        <span class="<?= $ready ? 'badge-cool' : 'badge-warm' ?>"><?= $ready ? 'Siap mengirim' : 'Perlu dilengkapi' ?></span>
    </div>
</div>

<div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
    <form action="<?= base_url('admin/pengaturan/openwa') ?>" method="post" class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <?= csrf_field() ?>

        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-mint/40"><?= icon('whatsapp', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Koneksi OpenWA</h2>
                <p class="text-xs text-ink/45">Kredensial disimpan di database dan menimpa nilai .env.</p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border-2 border-ink/10 bg-cream/60 p-4">
            <input type="hidden" name="enabled" value="0">
            <label class="flex cursor-pointer items-start gap-3">
                <input type="checkbox" name="enabled" value="1" class="mt-0.5 h-4 w-4 rounded border-ink/20 text-cobalt focus:ring-cobalt" <?= $enabled ? 'checked' : '' ?>>
                <span>
                    <span class="block font-display text-sm font-semibold">Aktifkan pengiriman WhatsApp</span>
                    <span class="mt-0.5 block text-xs leading-relaxed text-ink/45">Jika nonaktif, kode OTP tidak dikirim melalui gateway.</span>
                </span>
            </label>
        </div>

        <div class="mt-5 space-y-4">
            <div class="field mb-0">
                <label for="base_url" class="field-label">URL server OpenWA</label>
                <input type="url" name="base_url" id="base_url" value="<?= esc($value('base_url')) ?>" class="input" placeholder="http://localhost:2785" autocomplete="off">
                <p class="field-hint">Base URL server, tanpa <code>/api</code>. Contoh: <code>http://localhost:2785</code>.</p>
            </div>

            <div class="field mb-0">
                <label for="session_id" class="field-label">Session ID</label>
                <input type="text" name="session_id" id="session_id" value="<?= esc($value('session_id')) ?>" class="input font-mono text-xs" placeholder="8f3c2b1a-9d4e-4c7a-8b2f-1e6d5a4c3b2a" autocomplete="off" spellcheck="false">
                <p class="field-hint">UUID sesi OpenWA, bukan nama sesi. Kosongkan untuk memakai mode legacy <code>/sendText</code>.</p>
            </div>

            <?php if ($sessionPresent && ! $sessionUuid): ?>
                <div class="rounded-2xl border-2 border-sun/40 bg-sun/10 px-4 py-3 text-xs leading-relaxed text-ink/70">
                    <strong>Session ID belum berformat UUID.</strong> Ambil nilai <code>id</code> dari <code>GET /api/sessions</code>, atau kosongkan kolom ini untuk mode legacy.
                </div>
            <?php endif; ?>

            <div class="my-2 divider-dots"></div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="field mb-0 sm:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <label for="api_key" class="field-label">API Key</label>
                        <?php if ($hasApiKey): ?>
                            <span class="badge-live !px-2 !py-0.5 !text-[9px]">Tersimpan</span>
                        <?php endif; ?>
                    </div>
                    <input type="password" name="api_key" id="api_key" value="" class="input font-mono text-xs" placeholder="<?= $hasApiKey ? 'Kosongkan jika tidak diubah' : 'API key OpenWA' ?>" autocomplete="new-password">
                    <p class="field-hint">Diambil dari <code>data/.api-key</code> pada server OpenWA. Wajib untuk mode session.</p>
                </div>

                <div class="field mb-0">
                    <div class="flex items-center justify-between gap-3">
                        <label for="user" class="field-label">User auth</label>
                        <?php if ($hasUser): ?>
                            <span class="badge-neutral !px-2 !py-0.5 !text-[9px]">Tersimpan</span>
                        <?php endif; ?>
                    </div>
                    <input type="text" name="user" id="user" value="" class="input" placeholder="<?= $hasUser ? 'Kosongkan jika tidak diubah' : 'Opsional' ?>" autocomplete="off">
                </div>

                <div class="field mb-0">
                    <div class="flex items-center justify-between gap-3">
                        <label for="pass_auth" class="field-label">Pass auth</label>
                        <?php if ($hasPass): ?>
                            <span class="badge-neutral !px-2 !py-0.5 !text-[9px]">Tersimpan</span>
                        <?php endif; ?>
                    </div>
                    <input type="password" name="pass_auth" id="pass_auth" value="" class="input" placeholder="<?= $hasPass ? 'Kosongkan jika tidak diubah' : 'Opsional' ?>" autocomplete="new-password">
                </div>
            </div>
            <p class="field-hint -mt-2">User dan pass auth hanya diperlukan jika server OpenWA dilindungi Basic Auth (misalnya Nginx).</p>

            <details class="rounded-2xl border-2 border-ink/10 bg-cream/50 px-4 py-3">
                <summary class="cursor-pointer font-display text-xs font-semibold">Opsi lanjutan</summary>
                <div class="mt-4 space-y-4">
                    <div class="field mb-0">
                        <label for="endpoint" class="field-label">Endpoint legacy</label>
                        <input type="text" name="endpoint" id="endpoint" value="<?= esc($value('endpoint', '/sendText')) ?>" class="input font-mono text-xs" placeholder="/sendText">
                        <p class="field-hint">Dipakai hanya jika Session ID dikosongkan.</p>
                    </div>

                    <div class="field mb-0">
                        <label for="payload_style" class="field-label">Format payload legacy</label>
                        <select name="payload_style" id="payload_style" class="select">
                            <option value="args" <?= $payloadStyle === 'args' ? 'selected' : '' ?>>args — { args: { to, content } }</option>
                            <option value="flat" <?= $payloadStyle === 'flat' ? 'selected' : '' ?>>flat — { to, message }</option>
                        </select>
                    </div>

                    <input type="hidden" name="verify_ssl" value="0">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="verify_ssl" value="1" class="mt-0.5 h-4 w-4 rounded border-ink/20 text-cobalt focus:ring-cobalt" <?= $verifySsl ? 'checked' : '' ?>>
                        <span>
                            <span class="block text-xs font-semibold">Verifikasi SSL</span>
                            <span class="mt-0.5 block text-[11px] leading-relaxed text-ink/45">Nonaktifkan hanya untuk server OpenWA dengan sertifikat self-signed.</span>
                        </span>
                    </label>
                </div>
            </details>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan pengaturan</button>
            <span class="text-[10px] text-ink/35">Perubahan berlaku untuk OTP berikutnya.</span>
        </div>
    </form>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Status konfigurasi</p>
            <ul class="mt-4 space-y-3 text-xs">
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 <?= $active ? 'text-mint' : 'text-ink/25' ?>"><?= icon($active ? 'check-circle' : 'alert', 'h-4 w-4') ?></span>
                    <span><strong>Gateway</strong><span class="block text-ink/45"><?= $active ? 'Aktif dan akan dipakai untuk OTP.' : 'Nonaktif atau URL server belum diisi.' ?></span></span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 <?= $baseUrl !== '' ? 'text-mint' : 'text-ink/25' ?>"><?= icon($baseUrl !== '' ? 'check-circle' : 'alert', 'h-4 w-4') ?></span>
                    <span><strong>URL server</strong><span class="block break-all text-ink/45"><?= $baseUrl !== '' ? esc($baseUrl) : 'Belum diisi.' ?></span></span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 <?= $sessionUuid ? 'text-mint' : 'text-ink/25' ?>"><?= icon($sessionUuid ? 'check-circle' : 'alert', 'h-4 w-4') ?></span>
                    <span><strong>Session ID</strong><span class="block break-all text-ink/45"><?= $sessionUuid ? esc($sessionId) : ($sessionPresent ? 'Terisi, tetapi bukan UUID — memakai endpoint legacy.' : 'Kosong — memakai endpoint legacy.') ?></span></span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 <?= $hasApiKey ? 'text-mint' : 'text-ink/25' ?>"><?= icon($hasApiKey ? 'check-circle' : 'alert', 'h-4 w-4') ?></span>
                    <span><strong>API key</strong><span class="block text-ink/45"><?= $hasApiKey ? 'Tersimpan.' : 'Belum diisi.' ?></span></span>
                </li>
            </ul>

            <?php if ($baseUrl !== '' && $sessionMode): ?>
                <form action="<?= base_url('admin/pengaturan/openwa/test') ?>" method="post" class="mt-5">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-light btn-sm w-full justify-center"><?= icon('link', 'h-4 w-4') ?> Tes koneksi tersimpan</button>
                </form>
            <?php else: ?>
                <p class="mt-5 rounded-xl bg-cream px-3 py-2 text-[10px] leading-relaxed text-ink/40">Tes koneksi tersedia setelah URL server dan Session ID diisi lalu disimpan.</p>
            <?php endif; ?>
        </div>

        <div class="rounded-4xl border-2 border-ink bg-ink p-5 text-cream shadow-hard-sm sm:p-6">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl border border-cream/15 bg-cream/5 text-sun"><?= icon('whatsapp', 'h-5 w-5') ?></span>
            <h2 class="mt-4 text-lg font-bold">Langkah cepat</h2>
            <ol class="mt-3 space-y-2.5 text-xs leading-relaxed text-cream/60">
                <li><span class="font-semibold text-cream">1.</span> Jalankan server OpenWA dan pastikan endpoint <code>/api</code> aktif.</li>
                <li><span class="font-semibold text-cream">2.</span> Salin API key dari <code>data/.api-key</code>.</li>
                <li><span class="font-semibold text-cream">3.</span> Buat sesi, lalu salin <strong>UUID</strong> sesi — bukan namanya.</li>
                <li><span class="font-semibold text-cream">4.</span> Simpan konfigurasi, aktifkan gateway, lalu tes koneksi.</li>
            </ol>
        </div>

        <?php if (! empty($demoMode)): ?>
            <div class="rounded-4xl border-2 border-sun/50 bg-sun/15 p-5 text-xs leading-relaxed text-ink/70 sm:p-6">
                <p class="font-display font-semibold">Mode demo aktif</p>
                <p class="mt-1 text-ink/50">Selama gateway tidak terhubung, kode OTP ditampilkan di layar. Nonaktifkan <code>cakra.demoMode</code> di produksi.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
