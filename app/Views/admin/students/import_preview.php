<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Pratinjau Impor';
$pageSubtitle = 'Periksa hasil validasi sebelum menyimpan data siswa.';
$summary = $parsed['summary'];
$canImport = $parsed['ok'] && ! $parsed['has_fatal'] && $summary['valid'] > 0;
$statusMap = [
    'valid' => ['label' => 'Valid', 'class' => 'badge-live'],
    'duplicate' => ['label' => 'Duplikat', 'class' => 'badge-warm'],
    'invalid' => ['label' => 'Tidak valid', 'class' => 'badge-coral'],
];
?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-6xl">
    <a href="<?= base_url('admin/siswa/import') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Unggah file lain</a>

    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <?php foreach ([
            ['Total baris', $summary['total'], 'list', 'bg-paper'],
            ['Valid', $summary['valid'], 'check-circle', 'bg-mint/25'],
            ['Duplikat', $summary['duplicate'], 'copy', 'bg-sun/25'],
            ['Tidak valid', $summary['invalid'], 'alert', 'bg-coral/15'],
        ] as $card): ?>
            <div class="rounded-3xl border-2 border-ink/10 p-4 <?= $card[3] ?>">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl border border-current/10 bg-white/20 text-ink/50"><?= icon($card[2], 'h-4 w-4') ?></span>
                <p class="mt-3 font-display text-2xl font-bold tracking-tight"><?= number_format((int) $card[1], 0, ',', '.') ?></p>
                <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-ink/40"><?= esc($card[0]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 rounded-4xl border-2 border-ink/10 bg-paper">
        <div class="flex flex-col gap-2 border-b-2 border-ink/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">File</p>
                <p class="truncate font-display text-sm font-semibold"><?= esc($fileName) ?></p>
            </div>
            <span class="<?= $canImport ? 'badge-live' : 'badge-coral' ?>"><?= $canImport ? 'Siap diimpor' : 'Perlu diperbaiki' ?></span>
        </div>

        <?php if (! $canImport): ?>
            <div class="m-4 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3 sm:m-5">
                <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
                <p class="text-xs leading-relaxed text-ink/60">Masih ada baris yang tidak valid atau tidak ada data valid. Perbaiki file lalu unggah ulang. Import tidak dapat dilanjutkan.</p>
            </div>
        <?php endif; ?>

        <div class="table-wrap rounded-none border-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Baris</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Tanggal lahir</th>
                        <th>Kelas</th>
                        <th>WhatsApp</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parsed['rows'] as $row): ?>
                        <?php $status = $statusMap[$row['status']] ?? $statusMap['invalid']; ?>
                        <tr>
                            <td class="font-mono text-[11px] text-ink/40"><?= (int) $row['row'] ?></td>
                            <td class="font-mono text-xs"><?= esc($row['nis']) ?></td>
                            <td>
                                <p class="font-display text-sm font-semibold"><?= esc($row['full_name']) ?></p>
                                <?php if ($row['messages'] !== []): ?>
                                    <p class="mt-0.5 text-[10px] text-coral"><?= esc(implode(' ', $row['messages'])) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="text-xs text-ink/55"><?= esc($row['date_of_birth'] ?: '-') ?></td>
                            <td class="text-xs"><?= esc($row['class_name']) ?></td>
                            <td class="font-mono text-xs text-ink/55"><?= esc($row['whatsapp_number'] ?: '-') ?></td>
                            <td><span class="<?= esc($status['class']) ?>"><?= esc($status['label']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t-2 border-ink/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <p class="text-[11px] leading-relaxed text-ink/40">Baris duplikat akan dilewati. Hanya baris valid yang disimpan.</p>
            <form
                action="<?= base_url('admin/siswa/import/konfirmasi') ?>"
                method="post"
                data-confirm="Sebanyak <?= (int) $summary['valid'] ?> siswa valid akan ditambahkan ke data siswa."
                data-confirm-title="Impor data siswa?"
                data-confirm-label="Impor sekarang"
                data-confirm-tone="primary"
            >
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm" <?= $canImport ? '' : 'disabled' ?>>
                    <?= icon('check', 'h-4 w-4') ?> Impor <?= (int) $summary['valid'] ?> siswa
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>