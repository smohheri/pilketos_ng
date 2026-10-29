<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Data Siswa';
$pageSubtitle = 'Kelola daftar siswa, akun pemilih, dan status partisipasi.';
$totalPages = max(1, (int) ceil($total / $perPage));
$query = $keyword !== '' ? ['q' => $keyword] : [];
?>

<?= $this->section('content') ?>
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <?php foreach ([
        ['Total siswa', $stats['total'], 'users', 'bg-paper'],
        ['Siswa aktif', $stats['active'], 'check-circle', 'bg-mint/25'],
        ['Akun aktif', $stats['activated'], 'key', 'bg-sun/25'],
        ['Sudah memilih', $stats['voted'], 'ballot', 'bg-ink text-cream'],
    ] as $card): ?>
        <div class="rounded-3xl border-2 border-ink/10 p-4 <?= $card[3] ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl border border-current/10 bg-white/20 <?= $card[3] === 'bg-ink text-cream' ? 'text-sun' : 'text-ink/50' ?>"><?= icon($card[2], 'h-4 w-4') ?></span>
            <p class="mt-3 font-display text-2xl font-bold tracking-tight"><?= number_format((int) $card[1], 0, ',', '.') ?></p>
            <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] <?= $card[3] === 'bg-ink text-cream' ? 'text-cream/45' : 'text-ink/40' ?>"><?= esc($card[0]) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-5 rounded-4xl border-2 border-ink/10 bg-paper">
    <div class="flex flex-col gap-3 border-b-2 border-ink/10 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
        <form action="<?= base_url('admin/siswa') ?>" method="get" class="flex w-full max-w-md items-center gap-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/30"><?= icon('search', 'h-4 w-4') ?></span>
                <input type="search" name="q" value="<?= esc($keyword) ?>" class="input py-2.5 pl-10 text-sm" placeholder="Cari NIS, nama, atau kelas...">
            </div>
            <button type="submit" class="btn btn-dark btn-sm">Cari</button>
            <?php if ($keyword !== ''): ?>
                <a href="<?= base_url('admin/siswa') ?>" class="rounded-full p-2 text-ink/40 transition hover:bg-ink/5 hover:text-ink" aria-label="Hapus pencarian"><?= icon('x', 'h-4 w-4') ?></a>
            <?php endif; ?>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('admin/siswa/import') ?>" class="btn btn-primary btn-sm"><?= icon('upload', 'h-4 w-4') ?> Impor</a>
            <a href="<?= base_url('admin/siswa/export') ?>" class="btn btn-light btn-sm"><?= icon('download', 'h-4 w-4') ?> Ekspor</a>
        </div>
    </div>

    <div class="table-wrap rounded-none border-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>WhatsApp</th>
                    <th>Akun</th>
                    <th>Memilih</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream"><?= icon('search', 'h-5 w-5 text-ink/30') ?></span>
                            <p class="mt-3 font-display font-semibold">Tidak ada data siswa.</p>
                            <p class="mt-1 text-xs text-ink/40"><?= $keyword !== '' ? 'Coba kata kunci lain atau hapus pencarian.' : 'Impor data siswa untuk memulai.' ?></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full <?= avatar_tone($row['full_name']) ?> font-display text-[10px] font-bold"><?= esc(initials($row['full_name'])) ?></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-display text-sm font-semibold"><?= esc($row['full_name']) ?></p>
                                        <p class="font-mono text-[10px] text-ink/40"><?= esc($row['nis']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="chip"><?= esc($row['class_name']) ?></span></td>
                            <td class="font-mono text-xs text-ink/55"><?= esc($row['whatsapp_number']) ?></td>
                            <td>
                                <?php $accountActive = ($row['account_status'] ?? '') === 'active'; ?>
                                <span class="<?= $accountActive ? 'badge-live' : 'badge-neutral' ?>"><?= $accountActive ? 'Aktif' : 'Belum' ?></span>
                            </td>
                            <td>
                                <?php if ((int) ($row['has_voted'] ?? 0) === 1): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-ink"><?= icon('check-circle', 'h-4 w-4 text-mint') ?> Sudah</span>
                                <?php else: ?>
                                    <span class="text-xs text-ink/35">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= (int) $row['is_active'] === 1 ? 'badge-live' : 'badge-neutral' ?>"><?= (int) $row['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?></span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= base_url('admin/siswa/' . $row['id'] . '/ubah') ?>" class="rounded-full p-2 text-ink/50 transition hover:bg-sun/30 hover:text-ink" aria-label="Ubah siswa"><?= icon('edit', 'h-4 w-4') ?></a>
                                    <form
                                        action="<?= base_url('admin/siswa/' . $row['id'] . '/status') ?>"
                                        method="post"
                                        data-confirm="Status siswa ini akan diubah. Siswa nonaktif tidak dapat mengikuti pemilihan."
                                        data-confirm-title="Ubah status siswa?"
                                        data-confirm-label="Ubah status"
                                        data-confirm-tone="primary"
                                    >
                                        <?= csrf_field() ?>
                                        <button type="submit" class="rounded-full p-2 transition <?= (int) $row['is_active'] === 1 ? 'text-coral hover:bg-coral/10' : 'text-mint hover:bg-mint/20' ?>" aria-label="<?= (int) $row['is_active'] === 1 ? 'Nonaktifkan siswa' : 'Aktifkan siswa' ?>"><?= icon((int) $row['is_active'] === 1 ? 'x' : 'check', 'h-4 w-4') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t-2 border-ink/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <p class="text-[11px] text-ink/40">Menampilkan <?= count($rows) ?> dari <?= number_format($total, 0, ',', '.') ?> siswa<?= $keyword !== '' ? ' untuk “' . esc($keyword) . '”' : '' ?>.</p>
        <?php if ($totalPages > 1): ?>
            <div class="flex items-center gap-1">
                <a href="<?= base_url('admin/siswa?' . http_build_query(array_merge($query, ['page' => max(1, $page - 1)]))) ?>" class="flex h-8 w-8 items-center justify-center rounded-full border border-ink/10 bg-paper text-ink/60 transition hover:bg-sun <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>" aria-label="Halaman sebelumnya"><?= icon('chevron-left', 'h-4 w-4') ?></a>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?= base_url('admin/siswa?' . http_build_query(array_merge($query, ['page' => $i]))) ?>" class="flex h-8 w-8 items-center justify-center rounded-full border text-[11px] font-semibold transition <?= $i === $page ? 'border-ink bg-ink text-cream' : 'border-ink/10 bg-paper text-ink/60 hover:bg-sun' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= base_url('admin/siswa?' . http_build_query(array_merge($query, ['page' => min($totalPages, $page + 1)]))) ?>" class="flex h-8 w-8 items-center justify-center rounded-full border border-ink/10 bg-paper text-ink/60 transition hover:bg-sun <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>" aria-label="Halaman berikutnya"><?= icon('chevron-right', 'h-4 w-4') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<p class="mt-4 flex items-start gap-2 text-[11px] leading-relaxed text-ink/40">
    <?= icon('shield', 'mt-0.5 h-3.5 w-3.5 shrink-0') ?>
    Data siswa bersifat pribadi. Gunakan fitur ekspor hanya untuk keperluan resmi panitia dan simpan file di tempat aman.
</p>
<?= $this->endSection() ?>