<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Pengguna';
$pageSubtitle = 'Kelola akun admin dan panitia pemilihan.';
$selfId = (int) session('admin_id');
?>

<?= $this->section('content') ?>
<div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Akses panel</p>
                <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]">Daftar akun</h2>
            </div>
            <span class="chip"><?= icon('key', 'h-3.5 w-3.5') ?> <?= count($users) ?> akun</span>
        </div>

        <div class="mt-5 space-y-3">
            <?php foreach ($users as $user): ?>
                <?php $isSelf = (int) $user['id'] === $selfId; ?>
                <div class="rounded-2xl border-2 <?= (int) $user['is_active'] === 1 ? 'border-ink/10' : 'border-dashed border-ink/20' ?> bg-cream/50 p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full <?= avatar_tone($user['name']) ?> font-display text-xs font-bold"><?= esc(initials($user['name'])) ?></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-display text-sm font-semibold"><?= esc($user['name']) ?></p>
                                <span class="<?= $user['role'] === 'admin' ? 'badge-cool' : 'badge-neutral' ?>"><?= esc(role_label($user['role'])) ?></span>
                                <?php if ($user['role'] === 'panitia' && ! empty($user['election_id'])): ?>
                                    <span class="badge-warm"><?= esc($electionTypes[(int) $user['election_id']] ?? 'Pemilihan') ?></span>
                                <?php endif; ?>
                                <?php if ($isSelf): ?><span class="badge-warm">Anda</span><?php endif; ?>
                                <?php if ((int) $user['is_active'] !== 1): ?><span class="badge-coral">Nonaktif</span><?php endif; ?>
                            </div>
                            <p class="truncate text-xs text-ink/45"><?= esc($user['email']) ?></p>
                            <p class="mt-1 text-[10px] text-ink/35">Masuk terakhir: <?= esc(format_date_id($user['last_login_at'] ?? null, true)) ?></p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        <details class="flex-1">
                            <summary class="cursor-pointer text-[11px] font-semibold text-cobalt">Ubah akun</summary>
                            <form action="<?= base_url('admin/pengguna/' . $user['id']) ?>" method="post" class="mt-3 grid gap-2 sm:grid-cols-2">
                                <?= csrf_field() ?>
                                <input type="text" name="name" value="<?= esc($user['name']) ?>" class="input py-2 text-xs" required>
                                <input type="email" name="email" value="<?= esc($user['email']) ?>" class="input py-2 text-xs" required>
                                <?php if ($isSelf): ?><input type="hidden" name="role" value="admin"><?php endif; ?>
                                <select name="role" class="select py-2 text-xs" <?= $isSelf ? 'disabled' : '' ?>>
                                    <option value="panitia" <?= $user['role'] === 'panitia' ? 'selected' : '' ?>>Panitia</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <select name="election_id" class="select py-2 text-xs sm:col-span-2">
                                    <option value="">Pemilihan panitia (wajib untuk panitia)</option>
                                    <?php foreach ($elections as $option): ?>
                                        <option value="<?= (int) $option['id'] ?>" <?= (int) ($user['election_id'] ?? 0) === (int) $option['id'] ? 'selected' : '' ?>><?= esc($option['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="password" name="password" class="input py-2 text-xs" placeholder="Kata sandi baru (opsional)" minlength="8">
                                <input type="hidden" name="is_active" value="0">
                                <label class="flex items-center gap-1.5 text-[11px] sm:col-span-2">
                                    <input type="checkbox" name="is_active" value="1" class="h-3.5 w-3.5 rounded border-ink/20 text-cobalt" <?= (int) $user['is_active'] === 1 || $isSelf ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>> Akun aktif
                                </label>
                                <button type="submit" class="btn btn-dark btn-sm sm:col-span-2">Simpan perubahan</button>
                            </form>
                        </details>
                        <?php if (! $isSelf): ?>
                            <form
                                action="<?= base_url('admin/pengguna/' . $user['id'] . '/status') ?>"
                                method="post"
                                data-confirm="Status akun ini akan diubah. Akun yang nonaktif tidak dapat masuk ke panel."
                                data-confirm-title="Ubah status akun?"
                                data-confirm-label="Ubah status"
                                data-confirm-tone="primary"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="rounded-full p-2 transition <?= (int) $user['is_active'] === 1 ? 'text-coral hover:bg-coral/10' : 'text-mint hover:bg-mint/20' ?>" aria-label="<?= (int) $user['is_active'] === 1 ? 'Nonaktifkan akun' : 'Aktifkan akun' ?>"><?= icon((int) $user['is_active'] === 1 ? 'x' : 'check', 'h-4 w-4') ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-mint/40"><?= icon('plus', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Tambah akun</h2>
                <p class="text-xs text-ink/45">Beri akses sesuai kebutuhan.</p>
            </div>
        </div>

        <form action="<?= base_url('admin/pengguna') ?>" method="post" class="mt-5 space-y-3">
            <?= csrf_field() ?>
            <div class="field mb-0">
                <label for="name" class="field-label">Nama lengkap <span class="text-coral">*</span></label>
                <input type="text" name="name" id="name" value="<?= esc(old('name')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="email" class="field-label">Email <span class="text-coral">*</span></label>
                <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" class="input" required>
            </div>
            <div class="field mb-0">
                <label for="password" class="field-label">Kata sandi <span class="text-coral">*</span></label>
                <input type="password" name="password" id="password" class="input" minlength="8" required>
                <p class="field-hint">Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol.</p>
            </div>
            <div class="field mb-0">
                <label for="role" class="field-label">Peran <span class="text-coral">*</span></label>
                <select name="role" id="role" class="select">
                    <option value="panitia" <?= old('role') === 'panitia' ? 'selected' : '' ?>>Panitia</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="field mb-0">
                <label for="election_id" class="field-label">Pemilihan</label>
                <select name="election_id" id="election_id" class="select">
                    <option value="">Pilih pemilihan untuk panitia</option>
                    <?php foreach ($elections as $option): ?>
                        <option value="<?= (int) $option['id'] ?>" <?= (int) old('election_id') === (int) $option['id'] ? 'selected' : '' ?>><?= esc($option['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="field-hint">Panitia hanya dapat mengakses pemilihan yang dipilih. Admin tetap dapat mengelola semua pemilihan.</p>
            </div>
            <button type="submit" class="btn btn-primary btn-sm btn-block">Buat akun</button>
        </form>

        <div class="mt-4 rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-[11px] leading-relaxed text-ink/45">
            <p><span class="font-semibold text-ink">Admin</span> memiliki akses penuh termasuk audit dan pengaturan. <span class="font-semibold text-ink">Panitia</span> mengelola konten, siswa, dan laporan.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>