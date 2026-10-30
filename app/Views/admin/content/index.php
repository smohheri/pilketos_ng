<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('admin/konten') ?>" method="post">
    <?= csrf_field() ?>

    <div class="grid gap-5 xl:grid-cols-2">
        <?php foreach ($groups as $groupKey => $group): ?>
            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('edit', 'h-5 w-5') ?></span>
                    <h2 class="text-lg font-bold tracking-[-0.02em]"><?= esc($group['title']) ?></h2>
                </div>

                <div class="mt-5 space-y-4">
                    <?php foreach ($group['fields'] as $field): ?>
                        <?php $value = old($field['key']) ?? landing_text($field['key'], $field['default']); ?>
                        <div class="field mb-0">
                            <label for="<?= esc($field['key']) ?>" class="field-label"><?= esc($field['label']) ?></label>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea name="<?= esc($field['key']) ?>" id="<?= esc($field['key']) ?>" class="textarea" rows="2"><?= esc($value) ?></textarea>
                            <?php else: ?>
                                <input type="text" name="<?= esc($field['key']) ?>" id="<?= esc($field['key']) ?>" value="<?= esc($value) ?>" class="input" maxlength="<?= (int) ($field['maxlength'] ?? 160) ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
        <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan konten landing</button>
        <a href="<?= base_url('/') ?>" target="_blank" rel="noopener" class="btn btn-light btn-sm">Lihat halaman landing <?= icon('external', 'h-3.5 w-3.5') ?></a>
    </div>
</form>
<?= $this->endSection() ?>