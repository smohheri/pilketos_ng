<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Audit Log';
$pageSubtitle = 'Jejak aktivitas administratif yang tidak dapat diubah.';
$totalPages = max(1, (int) ceil($total / $perPage));
$query = $q !== '' ? ['q' => $q] : [];
?>

<?= $this->section('content') ?>
<div class="rounded-4xl border-2 border-ink/10 bg-paper">
    <div class="flex flex-col gap-3 border-b-2 border-ink/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <form action="<?= base_url('admin/audit') ?>" method="get" class="flex w-full max-w-md items-center gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/30"><?= icon('search', 'h-4 w-4') ?></span>
                <input type="search" name="q" value="<?= esc($q) ?>" class="input py-2.5 pl-10 text-sm" placeholder="Cari aksi, entitas, atau nama...">
            </div>
            <button type="submit" class="btn btn-dark btn-sm">Cari</button>
            <?php if ($q !== ''): ?>
                <a href="<?= base_url('admin/audit') ?>" class="rounded-full p-2 text-ink/40 transition hover:bg-ink/5 hover:text-ink" aria-label="Hapus pencarian"><?= icon('x', 'h-4 w-4') ?></a>
            <?php endif; ?>
        </form>
        <span class="chip shrink-0"><?= icon('shield', 'h-3.5 w-3.5') ?> <?= number_format($total, 0, ',', '.') ?> catatan</span>
    </div>

    <div class="table-wrap rounded-none border-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Aksi</th>
                    <th>Entitas</th>
                    <th>Pelaku</th>
                    <th>Metadata</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs === []): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream"><?= icon('shield', 'h-5 w-5 text-ink/30') ?></span>
                            <p class="mt-3 font-display font-semibold">Belum ada catatan audit.</p>
                            <p class="mt-1 text-xs text-ink/40">Aktivitas administratif akan muncul di sini.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-ink px-2.5 py-1 font-mono text-[10px] text-cream"><?= esc($log['action']) ?></span>
                            </td>
                            <td class="text-xs">
                                <p class="font-medium"><?= esc($log['entity_type'] ?? '-') ?></p>
                                <?php if (! empty($log['entity_id'])): ?><p class="font-mono text-[10px] text-ink/35">#<?= (int) $log['entity_id'] ?></p><?php endif; ?>
                            </td>
                            <td>
                                <p class="font-display text-xs font-semibold"><?= esc($log['actor_name'] ?? 'Sistem') ?></p>
                                <p class="font-mono text-[10px] text-ink/35"><?= esc($log['ip_address'] ?? '-') ?></p>
                            </td>
                            <td class="max-w-[260px]">
                                <?php if ($log['meta_array'] !== []): ?>
                                    <details>
                                        <summary class="cursor-pointer text-[11px] font-semibold text-cobalt">Lihat data</summary>
                                        <pre class="mt-1 max-h-32 overflow-auto rounded-lg bg-cream p-2 text-[9px] leading-relaxed text-ink/60"><?= esc(json_encode($log['meta_array'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '') ?></pre>
                                    </details>
                                <?php else: ?>
                                    <span class="text-[10px] text-ink/30">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap text-[11px] text-ink/50"><?= esc(format_date_id($log['created_at'] ?? null, true)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t-2 border-ink/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <p class="text-[11px] text-ink/40">Menampilkan <?= count($logs) ?> dari <?= number_format($total, 0, ',', '.') ?> catatan.</p>
        <?php if ($totalPages > 1): ?>
            <div class="flex items-center gap-1">
                <a href="<?= base_url('admin/audit?' . http_build_query(array_merge($query, ['page' => max(1, $page - 1)]))) ?>" class="flex h-8 w-8 items-center justify-center rounded-full border border-ink/10 bg-paper text-ink/60 transition hover:bg-sun <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>" aria-label="Halaman sebelumnya"><?= icon('chevron-left', 'h-4 w-4') ?></a>
                <span class="px-2 text-[11px] font-semibold"><?= $page ?> / <?= $totalPages ?></span>
                <a href="<?= base_url('admin/audit?' . http_build_query(array_merge($query, ['page' => min($totalPages, $page + 1)]))) ?>" class="flex h-8 w-8 items-center justify-center rounded-full border border-ink/10 bg-paper text-ink/60 transition hover:bg-sun <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>" aria-label="Halaman berikutnya"><?= icon('chevron-right', 'h-4 w-4') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<p class="mt-4 flex items-start gap-2 text-[11px] leading-relaxed text-ink/40">
    <?= icon('info', 'mt-0.5 h-3.5 w-3.5 shrink-0') ?>
    Audit log mencatat aksi penting seperti perubahan kandidat, ekspor data, dan perubahan pengaturan.
</p>
<?= $this->endSection() ?>