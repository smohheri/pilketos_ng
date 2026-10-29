<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Pemilihan & Jadwal';
$pageSubtitle = 'Atur periode pemungutan suara dan tahapan resmi.';
$meta = phase_meta($phase);
$election = $election ?? [];
$typeLabel = election_type_label($election['type'] ?? null);
$local = static function (?string $value): string {
    return $value !== null && $value !== '' ? date('Y-m-d\TH:i', strtotime($value)) : '';
};
$value = static fn (string $key, string $default = ''): string => (string) (old($key) ?? ($election[$key] ?? $default));
$timezones = array_values(array_unique(array_filter([
    'Asia/Makassar', 'Asia/Jakarta', 'Asia/Jayapura', $election['timezone'] ?? null,
])));
?>

<?= $this->section('content') ?>
<div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
    <form action="<?= base_url('admin/pemilihan') ?>" method="post" class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <?= csrf_field() ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sun/40"><?= icon('ballot', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Konfigurasi <?= esc($typeLabel) ?></h2>
                    <p class="text-xs text-ink/45">Periode, status, dan mode hasil pemungutan suara.</p>
                </div>
            </div>
            <?php if (! empty($election)): ?>
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
            <?php endif; ?>
        </div>

        <div class="mt-5 space-y-4">
            <div class="field mb-0">
                <label for="title" class="field-label">Judul pemilihan <span class="text-coral">*</span></label>
                <input type="text" name="title" id="title" value="<?= esc($value('title', 'Pemilihan Ketua OSIS CAKRA 2026')) ?>" class="input" required>
            </div>

            <div class="field mb-0">
                <label for="description" class="field-label">Deskripsi</label>
                <textarea name="description" id="description" class="textarea" rows="3" placeholder="Tujuan dan catatan pemilihan..."><?= esc($value('description')) ?></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="field mb-0">
                    <label for="start_at" class="field-label">Mulai <span class="text-coral">*</span></label>
                    <input type="datetime-local" name="start_at" id="start_at" value="<?= esc(old('start_at') ?? $local($election['start_at'] ?? null)) ?>" class="input" required>
                </div>
                <div class="field mb-0">
                    <label for="end_at" class="field-label">Selesai <span class="text-coral">*</span></label>
                    <input type="datetime-local" name="end_at" id="end_at" value="<?= esc(old('end_at') ?? $local($election['end_at'] ?? null)) ?>" class="input" required>
                </div>
                <div class="field mb-0">
                    <label for="result_publish_at" class="field-label">Pengumuman hasil <span class="text-coral">*</span></label>
                    <input type="datetime-local" name="result_publish_at" id="result_publish_at" value="<?= esc(old('result_publish_at') ?? $local($election['result_publish_at'] ?? null)) ?>" class="input" required>
                </div>
                <div class="field mb-0">
                    <label for="status" class="field-label">Status <span class="text-coral">*</span></label>
                    <select name="status" id="status" class="select">
                        <?php foreach (['draft' => 'Draft (belum tampil)', 'published' => 'Publikasikan', 'closed' => 'Tutup pemungutan'] as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $value('status', 'draft') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field mb-0 sm:col-span-2">
                    <label for="timezone" class="field-label">Zona waktu</label>
                    <select name="timezone" id="timezone" class="select">
                        <?php foreach ($timezones as $tz): ?>
                            <option value="<?= esc($tz) ?>" <?= $value('timezone', 'Asia/Makassar') === $tz ? 'selected' : '' ?>><?= esc($tz) ?></option>
                        <?php endforeach; ?>
                        <?php if (! in_array($value('timezone', 'Asia/Makassar'), $timezones, true)): ?>
                            <option value="<?= esc($value('timezone')) ?>" selected><?= esc($value('timezone')) ?></option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <?php
            $initialMode = $value('result_mode', 'vote_only');
            if (! in_array($initialMode, ['vote_only', 'combined'], true)) {
                $initialMode = 'vote_only';
            }
            $initialVoteWeight = old('vote_weight') ?? ($election['vote_weight'] ?? 50);
            $initialAssessmentWeight = old('assessment_weight') ?? ($election['assessment_weight'] ?? 50);
            ?>
            <div id="mode-hasil" class="rounded-3xl border-2 border-ink/10 bg-cream/60 p-4 sm:p-5" x-data="{ mode: '<?= esc($initialMode, 'js') ?>', voteWeight: <?= (float) $initialVoteWeight ?>, assessmentWeight: <?= (float) $initialAssessmentWeight ?> }">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cobalt/10 text-cobalt"><?= icon('star', 'h-4 w-4') ?></span>
                    <div>
                        <p class="font-display text-sm font-bold">Mode hasil pemilihan</p>
                        <p class="mt-0.5 text-[11px] leading-relaxed text-ink/45">Pilih apakah pemenang hanya ditentukan suara voting atau digabung dengan nilai penilaian.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-2xl border-2 p-3 transition" :class="mode === 'vote_only' ? 'border-ink bg-paper' : 'border-ink/10 bg-paper/60'">
                        <input type="radio" name="result_mode" value="vote_only" class="sr-only" x-model="mode" <?= $initialMode === 'vote_only' ? 'checked' : '' ?>>
                        <span class="flex items-center gap-2 font-display text-xs font-bold"><?= icon('ballot', 'h-4 w-4') ?> Suara voting saja</span>
                        <span class="mt-1 block text-[10px] leading-relaxed text-ink/45">Peringkat memakai jumlah suara sah.</span>
                    </label>
                    <label class="cursor-pointer rounded-2xl border-2 p-3 transition" :class="mode === 'combined' ? 'border-ink bg-paper' : 'border-ink/10 bg-paper/60'">
                        <input type="radio" name="result_mode" value="combined" class="sr-only" x-model="mode" <?= $initialMode === 'combined' ? 'checked' : '' ?>>
                        <span class="flex items-center gap-2 font-display text-xs font-bold"><?= icon('star', 'h-4 w-4') ?> Suara + penilaian</span>
                        <span class="mt-1 block text-[10px] leading-relaxed text-ink/45">Peringkat memakai nilai akhir gabungan.</span>
                    </label>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2" x-show="mode === 'combined'" x-cloak>
                    <div class="field mb-0">
                        <label for="vote_weight" class="field-label">Bobot suara (%)</label>
                        <input type="number" name="vote_weight" id="vote_weight" min="0" max="100" step="0.01" class="input" x-model.number="voteWeight">
                    </div>
                    <div class="field mb-0">
                        <label for="assessment_weight" class="field-label">Bobot penilaian (%)</label>
                        <input type="number" name="assessment_weight" id="assessment_weight" min="0" max="100" step="0.01" class="input" x-model.number="assessmentWeight">
                    </div>
                    <p class="sm:col-span-2 rounded-xl border border-ink/10 bg-paper px-3 py-2 text-[10px] leading-relaxed text-ink/50">
                        Total bobot: <span class="font-bold text-ink" x-text="(Number(voteWeight) + Number(assessmentWeight)).toFixed(2)"></span>%
                        &middot; Nilai akhir = (persentase suara &times; bobot suara) + (nilai penilaian &times; bobot penilaian).
                        <a href="<?= base_url('admin/penilaian') ?>" class="font-semibold text-cobalt underline decoration-cobalt/30 underline-offset-2">Kelola variabel penilaian</a>.
                    </p>
                </div>

                <p class="mt-3 text-[10px] leading-relaxed text-ink/40" x-show="mode === 'vote_only'">Nilai penilaian tetap dapat diisi, tetapi tidak memengaruhi peringkat selama mode ini aktif.</p>
            </div>

            <div class="rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-[11px] leading-relaxed text-ink/50">
                <p><span class="font-semibold text-ink">Status publikasi:</span> draft menyembunyikan pemilihan, published membuka halaman sesuai jadwal, closed menghentikan suara dan menampilkan hasil setelah waktu pengumuman.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="submit" class="btn btn-primary btn-sm"><?= icon('check', 'h-4 w-4') ?> Simpan konfigurasi</button>
                <?php if (! empty($election)): ?>
                    <a href="<?= base_url('/') ?>" target="_blank" rel="noopener" class="btn btn-light btn-sm">Lihat halaman publik <?= icon('external', 'h-3.5 w-3.5') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="space-y-5">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-mint/40"><?= icon('users', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Daftar pemilih</h2>
                    <p class="text-xs text-ink/45">Sinkronkan siswa aktif menjadi pemilih.</p>
                </div>
            </div>
            <p class="mt-4 text-xs leading-relaxed text-ink/50">Sinkronisasi menambahkan siswa aktif yang belum terdaftar sebagai pemilih pada pemilihan ini. Data yang sudah ada tidak diubah.</p>
            <form
                action="<?= base_url('admin/pemilihan/sinkron') ?>"
                method="post"
                class="mt-4"
                data-confirm="Seluruh siswa aktif yang belum terdaftar akan ditambahkan ke daftar pemilih pemilihan ini."
                data-confirm-title="Sinkronkan pemilih?"
                data-confirm-label="Sinkronkan"
                data-confirm-tone="primary"
            >
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-dark btn-sm btn-block"><?= icon('refresh', 'h-4 w-4') ?> Sinkronkan pemilih</button>
            </form>
        </div>

        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-bold tracking-[-0.02em]">Alur singkat</h2>
            <ol class="mt-4 space-y-3 text-xs text-ink/55">
                <?php foreach (['Simpan konfigurasi dan status published.', 'Tambahkan kandidat serta data siswa.', 'Pantau partisipasi di dashboard.', 'Tutup pemungutan dan publikasikan hasil.'] as $index => $step): ?>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-cream font-display text-[10px] font-bold"><?= $index + 1 ?></span>
                        <span class="pt-1"><?= esc($step) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</div>

<div class="mt-5 grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Tahapan</p>
                <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]">Jadwal pemilihan</h2>
            </div>
            <span class="chip"><?= icon('calendar', 'h-3.5 w-3.5') ?> <?= count($schedules) ?> tahap</span>
        </div>

        <?php if ($schedules === []): ?>
            <p class="mt-4 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada tahapan. Tambahkan tahapan pertama di samping.</p>
        <?php else: ?>
            <div class="mt-4 space-y-3">
                <?php foreach ($schedules as $schedule): ?>
                    <div class="rounded-2xl border border-ink/10 bg-cream/60 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-display text-sm font-semibold"><?= esc($schedule['title']) ?></p>
                                <p class="mt-0.5 text-[11px] text-ink/45"><?= esc(format_date_range($schedule['start_at'], $schedule['end_at'])) ?></p>
                                <?php if (! empty($schedule['description'])): ?>
                                    <p class="mt-1 text-[11px] leading-relaxed text-ink/40"><?= esc($schedule['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white font-display text-[10px] font-bold"><?= (int) $schedule['sort_order'] ?></span>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <details class="flex-1">
                                <summary class="cursor-pointer text-[11px] font-semibold text-cobalt">Ubah tahapan</summary>
                                <form action="<?= base_url('admin/pemilihan/jadwal/' . $schedule['id']) ?>" method="post" class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <?= csrf_field() ?>
                                    <input type="text" name="title" value="<?= esc($schedule['title']) ?>" class="input py-2 text-xs sm:col-span-2" required>
                                    <input type="datetime-local" name="start_at" value="<?= esc($local($schedule['start_at'])) ?>" class="input py-2 text-xs" required>
                                    <input type="datetime-local" name="end_at" value="<?= esc($local($schedule['end_at'])) ?>" class="input py-2 text-xs">
                                    <input type="number" name="sort_order" value="<?= (int) $schedule['sort_order'] ?>" class="input py-2 text-xs" min="0">
                                    <textarea name="description" class="textarea min-h-[60px] py-2 text-xs sm:col-span-2" rows="2"><?= esc($schedule['description'] ?? '') ?></textarea>
                                    <button type="submit" class="btn btn-dark btn-sm">Simpan</button>
                                </form>
                            </details>
                            <form
                                action="<?= base_url('admin/pemilihan/jadwal/' . $schedule['id'] . '/hapus') ?>"
                                method="post"
                                data-confirm="Tahapan ini akan dihapus dari jadwal pemilihan."
                                data-confirm-title="Hapus tahapan?"
                                data-confirm-label="Hapus tahapan"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="rounded-full p-2 text-coral transition hover:bg-coral/10" aria-label="Hapus tahapan"><?= icon('trash', 'h-4 w-4') ?></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cobalt/10 text-cobalt"><?= icon('plus', 'h-5 w-5') ?></span>
            <div>
                <h2 class="text-lg font-bold tracking-[-0.02em]">Tambah tahapan</h2>
                <p class="text-xs text-ink/45">Contoh: masa kampanye, debat, pemungutan.</p>
            </div>
        </div>

        <?php if ($election === []): ?>
            <p class="mt-4 rounded-2xl bg-cream px-4 py-5 text-center text-xs text-ink/45">Simpan konfigurasi pemilihan terlebih dahulu.</p>
        <?php else: ?>
            <form action="<?= base_url('admin/pemilihan/jadwal') ?>" method="post" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <div class="field mb-0">
                    <label for="schedule_title" class="field-label">Judul tahapan <span class="text-coral">*</span></label>
                    <input type="text" name="title" id="schedule_title" value="<?= esc(old('title')) ?>" class="input" placeholder="Masa kampanye" required>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="field mb-0">
                        <label for="schedule_start" class="field-label">Mulai <span class="text-coral">*</span></label>
                        <input type="datetime-local" name="start_at" id="schedule_start" value="<?= esc(old('start_at')) ?>" class="input" required>
                    </div>
                    <div class="field mb-0">
                        <label for="schedule_end" class="field-label">Selesai</label>
                        <input type="datetime-local" name="end_at" id="schedule_end" value="<?= esc(old('end_at')) ?>" class="input">
                    </div>
                </div>
                <div class="field mb-0">
                    <label for="schedule_order" class="field-label">Urutan</label>
                    <input type="number" name="sort_order" id="schedule_order" value="<?= esc(old('sort_order', (string) (count($schedules) + 1))) ?>" class="input" min="0">
                </div>
                <div class="field mb-0">
                    <label for="schedule_description" class="field-label">Deskripsi</label>
                    <textarea name="description" id="schedule_description" class="textarea" rows="2" placeholder="Keterangan singkat..."><?= esc(old('description')) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm btn-block">Tambah tahapan</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>