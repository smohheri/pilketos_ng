<?php
$success = session('success');
$error = session('error');
$errors = session('errors');
$info = session('info');
?>
<?php if ($success || $error || $info || (is_array($errors) && $errors !== [])): ?>
    <div class="flash-fixed no-print" x-data="{ show: true }" x-show="show" x-transition.opacity>
        <div class="space-y-2">
            <?php if ($success): ?>
                <div class="alert alert-success shadow-soft">
                    <span class="mt-0.5"><?= icon('check-circle', 'h-5 w-5 shrink-0') ?></span>
                    <p class="flex-1"><?= esc($success) ?></p>
                    <button type="button" @click="show = false" class="rounded-full p-1 hover:bg-ink/5" aria-label="Tutup">
                        <?= icon('x', 'h-4 w-4') ?>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error shadow-soft">
                    <span class="mt-0.5"><?= icon('alert', 'h-5 w-5 shrink-0') ?></span>
                    <p class="flex-1"><?= esc($error) ?></p>
                    <button type="button" @click="show = false" class="rounded-full p-1 hover:bg-ink/5" aria-label="Tutup">
                        <?= icon('x', 'h-4 w-4') ?>
                    </button>
                </div>
            <?php endif; ?>
            <?php if (is_array($errors) && $errors !== []): ?>
                <div class="alert alert-error shadow-soft">
                    <span class="mt-0.5"><?= icon('alert', 'h-5 w-5 shrink-0') ?></span>
                    <div class="flex-1">
                        <p class="font-semibold">Periksa kembali data berikut:</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            <?php foreach ($errors as $message): ?>
                                <li><?= esc($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="rounded-full p-1 hover:bg-ink/5" aria-label="Tutup">
                        <?= icon('x', 'h-4 w-4') ?>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($info): ?>
                <div class="alert alert-info shadow-soft">
                    <span class="mt-0.5"><?= icon('info', 'h-5 w-5 shrink-0') ?></span>
                    <p class="flex-1"><?= esc($info) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>