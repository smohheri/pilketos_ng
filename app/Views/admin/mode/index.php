<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
    <form
        action="<?= base_url('admin/mode-aplikasi') ?>"
        method="post"
        class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6"
        data-confirm="Pemilihan yang dinonaktifkan akan disembunyikan dari publik, login, dan bilik suara. Data dan hasilnya tetap tersimpan."
        data-confirm-title="Terapkan mode aplikasi?"
        data-confirm-label="Terapkan"
        data-confirm-tone="primary"
    >
        <?= csrf_field() ?>
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('ballot', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Pemilihan yang digunakan</h2>
                <p class="text-xs text-ink/45">Aktifkan satu atau keduanya.</p>
            </div>
        </div>

        <div class="mt-5 space-y-3">
            <?php foreach ($items as $item): ?>
                <?php
                $election = $item['election'];
                $meta = phase_meta($item['phase']);
                $enabled = (int) ($election['is_enabled'] ?? 1) === 1;
                $type = (string) ($election['type'] ?? '');
                ?>
                <div class="rounded-3xl border-2 p-4 transition <?= $enabled ? 'border-ink bg-cream/60' : 'border-ink/10 bg-paper' ?>">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl <?= esc(election_type_badge($type)) ?> text-ink"><?= icon($type === 'poltar' ? 'shield' : 'star', 'h-5 w-5') ?></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-display text-sm font-bold"><?= esc(election_type_label($type)) ?></span>
                                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                            </span>
                            <span class="mt-1 block truncate text-xs text-ink/50"><?= esc($election['title'] ?? '') ?></span>
                            <span class="mt-1 block text-[11px] text-ink/40"><?= (int) $item['candidateCount'] ?> kandidat</span>
                        </span>
                        <label class="relative shrink-0 cursor-pointer">
                            <input type="checkbox" name="enabled[]" value="<?= (int) $election['id'] ?>" class="sr-only peer" <?= $enabled ? 'checked' : '' ?>>
                            <span class="flex h-7 w-12 rounded-full transition peer-checked:bg-cobalt <?= $enabled ? 'bg-cobalt' : 'bg-ink/15' ?>">
                                <span class="h-5 w-5 translate-x-1 rounded-full bg-white shadow-sm transition-all peer-checked:translate-x-6"></span>
                            </span>
                        </label>
                    </div>
                    <div class="field mt-4 mb-0">
                        <label for="label_<?= esc($type) ?>" class="field-label">Nama pemilihan</label>
                        <input
                            type="text"
                            id="label_<?= esc($type) ?>"
                            name="labels[<?= esc($type) ?>]"
                            value="<?= esc(election_type_label($type)) ?>"
                            placeholder="<?= esc(election_type_options()[$type] ?? 'Pemilihan') ?>"
                            class="input py-2.5 text-sm"
                            maxlength="60"
                        >
                        <p class="field-hint">Kosongkan untuk kembali ke nama bawaan.</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan mode aplikasi</button>
            <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-light btn-sm">Atur jadwal &amp; status per pemilihan</a>
        </div>
    </form>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Cara kerjanya</h2>
            <ul class="mt-4 space-y-3 text-xs text-ink/55">
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-mint/40 font-display text-[10px] font-bold">1</span>
                    <span class="pt-1">Centang pemilihan yang ingin dipakai, lalu simpan. Minimal satu pemilihan harus aktif.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-mint/40 font-display text-[10px] font-bold">2</span>
                    <span class="pt-1">Pemilihan nonaktif disembunyikan dari publik, login, dan bilik suara, tetapi datanya tetap tersimpan.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-mint/40 font-display text-[10px] font-bold">3</span>
                    <span class="pt-1">Jadwal dan status publikasi tetap diatur di halaman Pemilihan.</span>
                </li>
            </ul>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-cream/60 p-5 text-xs leading-relaxed text-ink/55 sm:p-6">
            <p class="font-display font-semibold">Hasil akhir mode</p>
            <ul class="mt-3 space-y-1.5">
                <?php
                $enabledTypes = array_map(
                    static fn (array $item): string => (string) ($item['election']['type'] ?? ''),
                    array_filter($items, static fn (array $item): bool => (int) ($item['election']['is_enabled'] ?? 1) === 1)
                );
                ?>
                <?php if (count($enabledTypes) === 1): ?>
                    <li>Hanya <span class="font-semibold text-ink"><?= esc(election_type_label($enabledTypes[0])) ?></span> yang tampil di aplikasi.</li>
                <?php elseif (count($enabledTypes) > 1): ?>
                    <li>Kedua pemilihan tampil, dengan alur voting OSIS lalu Poltar.</li>
                <?php else: ?>
                    <li>Saat ini belum ada pemilihan aktif.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection() ?>