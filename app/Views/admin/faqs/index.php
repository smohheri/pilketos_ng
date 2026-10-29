<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'FAQ';
$pageSubtitle = 'Kelola pertanyaan yang sering diajukan pemilih.';
?>

<?= $this->section('content') ?>
<div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Konten bantuan</p>
                <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]">Daftar FAQ</h2>
            </div>
            <span class="chip"><?= icon('info', 'h-3.5 w-3.5') ?> <?= count($faqs) ?> pertanyaan</span>
        </div>

        <?php if ($faqs === []): ?>
            <p class="mt-5 rounded-2xl bg-cream px-4 py-8 text-center text-sm text-ink/45">Belum ada FAQ. Tambahkan pertanyaan pertama di samping.</p>
        <?php else: ?>
            <div class="mt-5 space-y-3">
                <?php foreach ($faqs as $faq): ?>
                    <div class="rounded-2xl border-2 <?= (int) $faq['is_active'] === 1 ? 'border-ink/10' : 'border-dashed border-ink/20' ?> bg-cream/50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-display text-sm font-semibold leading-snug"><?= esc($faq['question']) ?></p>
                                <p class="mt-1 text-xs leading-relaxed text-ink/50"><?= esc(excerpt($faq['answer'], 150)) ?></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                <span class="<?= (int) $faq['is_active'] === 1 ? 'badge-live' : 'badge-neutral' ?>"><?= (int) $faq['is_active'] === 1 ? 'Aktif' : 'Draft' ?></span>
                                <span class="text-[10px] text-ink/35">Urutan <?= (int) $faq['sort_order'] ?></span>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <details class="flex-1">
                                <summary class="cursor-pointer text-[11px] font-semibold text-cobalt">Ubah FAQ</summary>
                                <form action="<?= base_url('admin/faq/' . $faq['id']) ?>" method="post" class="mt-3 space-y-2">
                                    <?= csrf_field() ?>
                                    <input type="text" name="question" value="<?= esc($faq['question']) ?>" class="input py-2 text-xs" required>
                                    <textarea name="answer" class="textarea min-h-[80px] py-2 text-xs" rows="3" required><?= esc($faq['answer']) ?></textarea>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="sort_order" value="<?= (int) $faq['sort_order'] ?>" class="input w-20 py-2 text-xs" min="0">
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="flex items-center gap-1.5 text-[11px]"><input type="checkbox" name="is_active" value="1" class="h-3.5 w-3.5 rounded border-ink/20 text-cobalt" <?= (int) $faq['is_active'] === 1 ? 'checked' : '' ?>> Aktif</label>
                                        <button type="submit" class="btn btn-dark btn-sm ml-auto">Simpan</button>
                                    </div>
                                </form>
                            </details>
                            <form
                                action="<?= base_url('admin/faq/' . $faq['id'] . '/hapus') ?>"
                                method="post"
                                data-confirm="FAQ ini akan dihapus permanen."
                                data-confirm-title="Hapus FAQ?"
                                data-confirm-label="Hapus FAQ"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="rounded-full p-2 text-coral transition hover:bg-coral/10" aria-label="Hapus FAQ"><?= icon('trash', 'h-4 w-4') ?></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('plus', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Tambah FAQ</h2>
                <p class="text-xs text-ink/45">Jawaban singkat dan jelas lebih membantu.</p>
            </div>
        </div>

        <form action="<?= base_url('admin/faq') ?>" method="post" class="mt-5 space-y-3">
            <?= csrf_field() ?>
            <div class="field mb-0">
                <label for="question" class="field-label">Pertanyaan <span class="text-coral">*</span></label>
                <input type="text" name="question" id="question" value="<?= esc(old('question')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="answer" class="field-label">Jawaban <span class="text-coral">*</span></label>
                <textarea name="answer" id="answer" class="textarea" rows="5" required><?= esc(old('answer')) ?></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="field mb-0">
                    <label for="sort_order" class="field-label">Urutan</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?= esc(old('sort_order', (string) (count($faqs) + 1))) ?>" class="input" min="0">
                </div>
                <div class="field mb-0 flex items-end">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex w-full items-center gap-2 rounded-2xl border-2 border-ink/10 bg-cream px-3 py-3 text-xs font-semibold">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-ink/20 text-cobalt" checked> Tampilkan
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm btn-block">Tambah FAQ</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>