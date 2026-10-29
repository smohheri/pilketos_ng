<?= $this->extend('layouts/public') ?>

<?php $meta = phase_meta($phase ?? 'draft'); ?>

<?= $this->section('head') ?>
<title><?= esc('Cek Bukti Suara — ' . $appName) ?></title>
<meta name="description" content="Verifikasi keaslian kode bukti suara CAKRA 2026 tanpa menampilkan identitas atau pilihan pemilih.">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="shell py-10 sm:py-16">
    <div class="grid gap-8 lg:grid-cols-[1fr_1fr] lg:items-start lg:gap-14">
        <div>
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Kembali ke beranda</a>
            <span class="eyebrow mt-6"><?= icon('fingerprint', 'h-3.5 w-3.5') ?> Verifikasi bukti</span>
            <h1 class="mt-5 text-4xl font-bold leading-[0.96] tracking-[-0.04em] sm:text-6xl">Pastikan suaramu<br><span class="text-coral">benar-benar tercatat.</span></h1>
            <p class="mt-4 max-w-lg text-base leading-relaxed text-ink/60">Masukkan kode bukti yang kamu terima setelah memilih. Sistem hanya memverifikasi keaslian kode — bukan identitas, bukan pilihan kandidat.</p>

            <div class="mt-8 space-y-3">
                <?php foreach ([
                    ['Kode unik per pemilih', 'Setiap kode dibuat acak dan tidak dapat ditebak.'],
                    ['Tanpa data pribadi', 'Kode tidak memuat NIS, tanggal lahir, atau nomor WhatsApp.'],
                    ['Tidak menunjukkan pilihan', 'Verifikasi tidak mengungkap kandidat yang kamu pilih.'],
                ] as $item): ?>
                    <div class="flex items-start gap-3 rounded-2xl border-2 border-ink/10 bg-paper px-4 py-3.5">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-mint/40 text-ink"><?= icon('check', 'h-4 w-4') ?></span>
                        <div>
                            <p class="font-display text-sm font-semibold"><?= esc($item[0]) ?></p>
                            <p class="mt-0.5 text-xs leading-relaxed text-ink/50"><?= esc($item[1]) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="rounded-5xl border-2 border-ink bg-paper p-6 shadow-hard sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink/35">Cek kode bukti</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-[-0.03em]">Verifikasi sekarang</h2>
                </div>
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
            </div>

            <form method="post" action="<?= base_url('bukti') ?>" class="mt-6" autocomplete="off">
                <?= csrf_field() ?>
                <label for="receipt_code" class="field-label">Kode bukti suara</label>
                <input
                    type="text"
                    name="receipt_code"
                    id="receipt_code"
                    value="<?= esc($receipt ?? old('receipt_code')) ?>"
                    placeholder="CK26-ABCD-2345"
                    class="input font-mono text-base uppercase tracking-[0.12em]"
                    maxlength="16"
                    required
                    autofocus
                    aria-describedby="receipt-help"
                    oninput="this.value = this.value.toUpperCase()"
                >
                <p id="receipt-help" class="field-hint">Format: <span class="font-mono">CK26-XXXX-XXXX</span>. Huruf dan angka tanpa spasi.</p>

                <button type="submit" class="btn btn-primary btn-block mt-5"><?= icon('fingerprint', 'h-4 w-4') ?> Verifikasi kode</button>
            </form>

            <?php if (is_array($result)): ?>
                <?php if (! empty($result['valid'])): ?>
                    <div class="mt-6 rounded-3xl border-2 border-mint bg-mint/20 p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-mint text-ink"><?= icon('check', 'h-5 w-5') ?></span>
                            <div>
                                <p class="font-display font-bold">Kode bukti valid.</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink/60">Suara dengan kode ini tercatat pada sistem CAKRA 2026.</p>
                                <?php if (! empty($result['cast_at'])): ?>
                                    <p class="mt-3 inline-flex items-center gap-2 rounded-full border border-ink/10 bg-white/60 px-3 py-1.5 text-xs text-ink/60"><?= icon('clock', 'h-3.5 w-3.5') ?> Dicatat <?= esc(format_date_id($result['cast_at'], true)) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mt-6 rounded-3xl border-2 border-coral/40 bg-coral/10 p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-coral text-white"><?= icon('alert', 'h-5 w-5') ?></span>
                            <div>
                                <p class="font-display font-bold">Kode tidak ditemukan.</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink/60"><?= esc($result['message'] ?? 'Periksa kembali kode bukti yang kamu masukkan.') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mt-6 flex items-center gap-2.5 rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-xs text-ink/50">
                <?= icon('info', 'h-4 w-4 shrink-0 text-cobalt') ?>
                <p>Kehilangan kode? Hubungi panitia dengan membawa kartu pelajar. Kode tidak dapat dicari berdasarkan nama.</p>
            </div>
        </div>
    </div>

    <div class="mt-10 rounded-3xl border-2 border-ink/10 bg-ink p-6 text-cream sm:p-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-xl">
                <p class="font-display text-lg font-bold">Belum menyalin kode bukti?</p>
                <p class="mt-1 text-sm leading-relaxed text-cream/60">Kode ditampilkan sekali setelah kamu mengirim suara. Simpan tangkapan layar atau salin kodenya sebelum meninggalkan bilik suara.</p>
            </div>
            <a href="<?= base_url('masuk') ?>" class="btn btn-sun btn-sm shrink-0">Masuk ke akun <?= icon('arrow-right', 'h-4 w-4') ?></a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>