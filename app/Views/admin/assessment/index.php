<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Penilaian';
$pageSubtitle = 'Susun variabel penilaian dan isi nilai setiap kandidat.';
$meta = phase_meta($phase);
$report = $report ?? null;
$reportCandidates = $reportCandidates ?? [];
$criteria = $criteria ?? [];
$activeCriteria = $activeCriteria ?? [];
$candidates = $candidates ?? [];
$scores = $scores ?? [];
$oldScores = old('scores');
$oldScores = is_array($oldScores) ? $oldScores : [];
$combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
$voteWeight = (float) ($report['vote_weight'] ?? 50);
$assessmentWeight = (float) ($report['assessment_weight'] ?? 50);
$completeCount = count(array_filter($reportCandidates, static fn (array $candidate): bool => (bool) ($candidate['assessment_complete'] ?? false)));
$activeCount = count(array_filter($candidates, static fn (array $candidate): bool => (int) $candidate['is_active'] === 1));
$weightsTotal = 0.0;
foreach ($activeCriteria as $criterion) {
    $weightsTotal += (float) $criterion['weight'];
}
$nextSort = 1;
foreach ($criteria as $criterion) {
    $nextSort = max($nextSort, (int) $criterion['sort_order'] + 1);
}
$scoreValue = static function (int $candidateId, int $criterionId) use ($oldScores, $scores): string {
    if (array_key_exists($candidateId, $oldScores) && is_array($oldScores[$candidateId]) && array_key_exists($criterionId, $oldScores[$candidateId])) {
        return (string) $oldScores[$candidateId][$criterionId];
    }

    return isset($scores[$candidateId][$criterionId]) ? (string) (float) $scores[$candidateId][$criterionId]['score'] : '';
};
$number = static fn ($value, int $decimals = 2): string => $value === null
    ? '-'
    : number_format((float) $value, $decimals, ',', '.');
?>

<?= $this->section('content') ?>
<?php if ($election === null): ?>
    <div class="rounded-4xl border-2 border-ink bg-paper p-8 text-center shadow-hard">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sun/40"><?= icon('star', 'h-7 w-7') ?></span>
        <h2 class="mt-5 text-2xl font-bold">Konfigurasi pemilihan belum ada.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink/50">Buat pemilihan terlebih dahulu sebelum menyusun variabel penilaian.</p>
        <a href="<?= base_url('admin/pemilihan') ?>" class="btn btn-primary mt-6">Buat pemilihan <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-4 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="<?= esc($meta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($meta['dot']) ?>"></span><?= esc($meta['label']) ?></span>
                <span class="<?= $combined ? 'badge-cool' : 'badge-neutral' ?>"><?= icon('star', 'h-3.5 w-3.5') ?> <?= esc($report['mode_label'] ?? 'Suara voting saja') ?></span>
            </div>
            <h2 class="mt-2 truncate text-lg font-bold"><?= esc($election['title']) ?></h2>
            <p class="text-xs text-ink/45"><?= $activeCount ?> kandidat aktif &middot; <?= count($activeCriteria) ?> variabel aktif &middot; total bobot <?= esc($number($weightsTotal)) ?></p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="<?= base_url('admin/pemilihan#mode-hasil') ?>" class="btn btn-light btn-sm"><?= icon('settings', 'h-4 w-4') ?> Atur mode hasil</a>
            <a href="<?= base_url('admin/laporan') ?>" class="btn btn-dark btn-sm"><?= icon('chart', 'h-4 w-4') ?> Lihat laporan</a>
        </div>
    </div>

    <?php if ($report !== null && ! empty($report['mode_fallback'])): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-sun/40 bg-sun/15 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-ink') ?>
            <p class="text-xs leading-relaxed text-ink/65">Mode <strong>Suara + penilaian</strong> dipilih, tetapi belum ada variabel aktif dengan bobot. Hasil sementara memakai suara voting saja. Tambahkan variabel di bawah.</p>
        </div>
    <?php elseif ($combined && $report !== null && ! $report['assessment_complete']): ?>
        <div class="mt-4 flex items-start gap-3 rounded-2xl border-2 border-coral/25 bg-coral/5 px-4 py-3">
            <?= icon('alert', 'h-5 w-5 mt-0.5 shrink-0 text-coral') ?>
            <p class="text-xs leading-relaxed text-ink/65">Penilaian belum lengkap. Nilai yang kosong dihitung <strong>0</strong> pada nilai akhir. Lengkapi seluruh sel agar hasil penilaian adil dan akurat.</p>
        </div>
    <?php endif; ?>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Variabel</p>
                    <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]">Kriteria penilaian</h2>
                </div>
                <span class="chip"><?= icon('star', 'h-3.5 w-3.5') ?> <?= count($criteria) ?> variabel</span>
            </div>

            <?php if ($criteria === []): ?>
                <p class="mt-4 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">Belum ada variabel penilaian. Tambahkan variabel pertama di samping.</p>
            <?php else: ?>
                <div class="mt-4 space-y-3">
                    <?php foreach ($criteria as $criterion): ?>
                        <?php $isActive = (int) $criterion['is_active'] === 1; ?>
                        <div class="rounded-2xl border <?= $isActive ? 'border-ink/10 bg-cream/60' : 'border-dashed border-ink/20 bg-cream/30' ?> px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="font-display text-sm font-semibold"><?= esc($criterion['name']) ?></p>
                                        <?php if ($isActive): ?>
                                            <span class="badge-live px-2 py-0.5 text-[9px]">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-neutral px-2 py-0.5 text-[9px]">Nonaktif</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (! empty($criterion['description'])): ?>
                                        <p class="mt-1 text-[11px] leading-relaxed text-ink/45"><?= esc($criterion['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold text-ink/50">
                                        <span class="rounded-full bg-white px-2 py-1">Bobot <?= esc($number($criterion['weight'])) ?></span>
                                        <span class="rounded-full bg-white px-2 py-1">Maks <?= esc($number($criterion['max_score'])) ?></span>
                                        <span class="rounded-full bg-white px-2 py-1">Urutan <?= (int) $criterion['sort_order'] ?></span>
                                    </div>
                                </div>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white font-display text-[10px] font-bold"><?= (int) $criterion['sort_order'] ?></span>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <details class="min-w-0 flex-1">
                                    <summary class="cursor-pointer text-[11px] font-semibold text-cobalt">Ubah variabel</summary>
                                    <form action="<?= base_url('admin/penilaian/kriteria/' . $criterion['id']) ?>" method="post" class="mt-3 grid gap-2 sm:grid-cols-2">
                                        <?= csrf_field() ?>
                                        <input type="text" name="name" value="<?= esc($criterion['name']) ?>" class="input py-2 text-xs sm:col-span-2" maxlength="120" required>
                                        <input type="number" name="weight" value="<?= esc((string) (float) $criterion['weight']) ?>" class="input py-2 text-xs" min="0.01" max="1000" step="0.01" required>
                                        <input type="number" name="max_score" value="<?= esc((string) (float) $criterion['max_score']) ?>" class="input py-2 text-xs" min="0.01" max="1000" step="0.01" required>
                                        <input type="number" name="sort_order" value="<?= (int) $criterion['sort_order'] ?>" class="input py-2 text-xs" min="0">
                                        <label class="flex items-center gap-2 rounded-xl border border-ink/10 bg-white px-3 py-2 text-[11px] font-semibold">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-ink/20 text-cobalt focus:ring-cobalt" <?= $isActive ? 'checked' : '' ?>>
                                            Aktif
                                        </label>
                                        <textarea name="description" class="textarea min-h-[60px] py-2 text-xs sm:col-span-2" rows="2" placeholder="Keterangan variabel..."><?= esc($criterion['description'] ?? '') ?></textarea>
                                        <button type="submit" class="btn btn-dark btn-sm sm:col-span-2">Simpan variabel</button>
                                    </form>
                                </details>
                                <form action="<?= base_url('admin/penilaian/kriteria/' . $criterion['id'] . '/status') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="rounded-full border border-ink/10 bg-white px-3 py-2 text-[10px] font-semibold text-ink/60 transition hover:border-ink/25" <?= $isActive ? '' : 'data-inactive' ?>><?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                                <form
                                    action="<?= base_url('admin/penilaian/kriteria/' . $criterion['id'] . '/hapus') ?>"
                                    method="post"
                                    data-confirm="Variabel ini beserta seluruh nilai kandidat pada variabel tersebut akan dihapus permanen."
                                    data-confirm-title="Hapus variabel penilaian?"
                                    data-confirm-label="Hapus variabel"
                                >
                                    <?= csrf_field() ?>
                                    <button type="submit" class="rounded-full p-2 text-coral transition hover:bg-coral/10" aria-label="Hapus variabel"><?= icon('trash', 'h-4 w-4') ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-5">
            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cobalt/10 text-cobalt"><?= icon('plus', 'h-5 w-5') ?></span>
                    <div>
                        <h2 class="text-lg font-bold tracking-[-0.02em]">Tambah variabel</h2>
                        <p class="text-xs text-ink/45">Contoh: kepemimpinan, visi-misi, komunikasi.</p>
                    </div>
                </div>

                <form action="<?= base_url('admin/penilaian/kriteria') ?>" method="post" class="mt-4 space-y-3">
                    <?= csrf_field() ?>
                    <div class="field mb-0">
                        <label for="criterion_name" class="field-label">Nama variabel <span class="text-coral">*</span></label>
                        <input type="text" name="name" id="criterion_name" value="<?= esc(old('name')) ?>" class="input" maxlength="120" placeholder="Kepemimpinan" required>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="field mb-0">
                            <label for="criterion_weight" class="field-label">Bobot <span class="text-coral">*</span></label>
                            <input type="number" name="weight" id="criterion_weight" value="<?= esc(old('weight', '1')) ?>" class="input" min="0.01" max="1000" step="0.01" required>
                        </div>
                        <div class="field mb-0">
                            <label for="criterion_max" class="field-label">Skor maksimum <span class="text-coral">*</span></label>
                            <input type="number" name="max_score" id="criterion_max" value="<?= esc(old('max_score', '100')) ?>" class="input" min="0.01" max="1000" step="0.01" required>
                        </div>
                    </div>
                    <div class="field mb-0">
                        <label for="criterion_order" class="field-label">Urutan</label>
                        <input type="number" name="sort_order" id="criterion_order" value="<?= esc(old('sort_order', (string) $nextSort)) ?>" class="input" min="0">
                    </div>
                    <div class="field mb-0">
                        <label for="criterion_description" class="field-label">Keterangan</label>
                        <textarea name="description" id="criterion_description" class="textarea" rows="2" placeholder="Apa yang dinilai dari variabel ini?"><?= esc(old('description')) ?></textarea>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2 rounded-xl border border-ink/10 bg-cream px-3 py-2 text-xs font-semibold">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-ink/20 text-cobalt focus:ring-cobalt" <?= old('is_active') === null || old('is_active') === '1' ? 'checked' : '' ?>>
                        Aktifkan variabel
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Tambah variabel</button>
                </form>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Basis hasil</p>
                        <h2 class="mt-1 text-lg font-bold tracking-[-0.02em]"><?= esc($report['mode_label'] ?? 'Suara voting saja') ?></h2>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl <?= $combined ? 'bg-sun/40' : 'bg-cream' ?>"><?= icon($combined ? 'star' : 'ballot', 'h-5 w-5') ?></span>
                </div>
                <?php if ($combined): ?>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="rounded-2xl border border-ink/10 bg-cream px-3 py-3">
                            <p class="font-display text-2xl font-bold"><?= esc($number($voteWeight)) ?>%</p>
                            <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-ink/40">Bobot suara</p>
                        </div>
                        <div class="rounded-2xl border border-ink/10 bg-cream px-3 py-3">
                            <p class="font-display text-2xl font-bold"><?= esc($number($assessmentWeight)) ?>%</p>
                            <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-ink/40">Bobot penilaian</p>
                        </div>
                    </div>
                    <p class="mt-3 text-[11px] leading-relaxed text-ink/45">Nilai akhir = (persentase suara &middot; bobot suara) + (nilai penilaian &middot; bobot penilaian).</p>
                <?php else: ?>
                    <p class="mt-3 text-[11px] leading-relaxed text-ink/45">Peringkat hanya memakai jumlah suara sah. Nilai penilaian tetap disimpan sebagai data pendukung.</p>
                <?php endif; ?>
                <div class="my-4 divider-dots"></div>
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-ink/45">Kandidat lengkap dinilai</span>
                    <span class="font-display font-bold"><?= $completeCount ?>/<?= $activeCount ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Lembar nilai</p>
                <h2 class="mt-1 text-xl font-bold tracking-[-0.02em]">Nilai kandidat per variabel</h2>
                <p class="mt-1 text-xs text-ink/45">Isi skor 0 sampai skor maksimum. Kosongkan sel untuk menghapus nilai tersimpan.</p>
            </div>
            <span class="chip"><?= icon('chart', 'h-3.5 w-3.5') ?> <?= count($candidates) ?> kandidat &middot; <?= count($activeCriteria) ?> variabel</span>
        </div>

        <?php if ($candidates === [] || $activeCriteria === []): ?>
            <p class="mt-5 rounded-2xl bg-cream px-4 py-6 text-center text-sm text-ink/45">
                <?= $candidates === [] ? 'Belum ada kandidat.' : 'Aktifkan atau tambahkan minimal satu variabel penilaian untuk mulai mengisi nilai.' ?>
            </p>
        <?php else: ?>
            <form action="<?= base_url('admin/penilaian/nilai') ?>" method="post" class="mt-5">
                <?= csrf_field() ?>
                <div class="overflow-x-auto rounded-3xl border-2 border-ink/10">
                    <table class="w-full border-collapse text-left" style="min-width: <?= 320 + (count($activeCriteria) * 150) ?>px;">
                        <thead>
                            <tr class="bg-ink text-cream">
                                <th class="sticky left-0 z-20 bg-ink px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em]">Kandidat</th>
                                <?php foreach ($activeCriteria as $criterion): ?>
                                    <th class="min-w-[140px] px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.1em]">
                                        <span class="block normal-case tracking-normal"><?= esc($criterion['name']) ?></span>
                                        <span class="mt-0.5 block text-[9px] font-normal text-cream/50">Bobot <?= esc($number($criterion['weight'])) ?> &middot; maks <?= esc($number($criterion['max_score'])) ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="min-w-[150px] px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.1em]">Nilai penilaian</th>
                                <th class="min-w-[120px] px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.1em]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/10">
                            <?php foreach ($candidates as $candidate): ?>
                                <?php
                                $candidateId = (int) $candidate['id'];
                                $preview = $reportCandidates[$candidateId] ?? null;
                                $isActive = (int) $candidate['is_active'] === 1;
                                ?>
                                <tr class="<?= $isActive ? 'bg-paper' : 'bg-cream/40' ?>">
                                    <td class="sticky left-0 z-10 <?= $isActive ? 'bg-paper' : 'bg-cream/90' ?> px-4 py-3 shadow-[2px_0_0_rgba(16,24,40,0.06)]">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-cream font-display text-[10px] font-bold"><?= str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT) ?></span>
                                            <div class="min-w-0">
                                                <p class="truncate font-display text-xs font-semibold"><?= esc($candidate['full_name']) ?></p>
                                                <p class="text-[10px] text-ink/40"><?= esc($candidate['class_name']) ?><?= $isActive ? '' : ' &middot; nonaktif' ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <?php foreach ($activeCriteria as $criterion): ?>
                                        <?php $value = $scoreValue($candidateId, (int) $criterion['id']); ?>
                                        <td class="px-2 py-3">
                                            <input
                                                type="number"
                                                name="scores[<?= $candidateId ?>][<?= (int) $criterion['id'] ?>]"
                                                value="<?= esc($value) ?>"
                                                min="0"
                                                max="<?= esc((string) (float) $criterion['max_score']) ?>"
                                                step="0.01"
                                                class="input mx-auto w-24 py-1.5 text-center text-xs"
                                                aria-label="Nilai <?= esc($criterion['name']) ?> untuk <?= esc($candidate['full_name']) ?>"
                                            >
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="px-3 py-3 text-center">
                                        <?php if ($preview !== null && $preview['assessment_score'] !== null): ?>
                                            <p class="font-display text-sm font-bold"><?= esc($number($preview['assessment_score'])) ?></p>
                                            <?php if ($combined): ?>
                                                <p class="text-[9px] text-ink/40">Akhir <?= esc($number($preview['final_score'])) ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-xs text-ink/30">-</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <?php if ($preview !== null && ! empty($preview['assessment_complete'])): ?>
                                            <span class="badge-live px-2 py-0.5 text-[9px]">Lengkap</span>
                                        <?php else: ?>
                                            <span class="badge-warm px-2 py-0.5 text-[9px]"><?= (int) ($preview['assessment_scored'] ?? 0) ?>/<?= (int) ($preview['assessment_total'] ?? count($activeCriteria)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-ink/10 bg-cream px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-[11px] leading-relaxed text-ink/50">Perubahan nilai langsung memengaruhi peringkat saat mode <strong>Suara + penilaian</strong> aktif.</p>
                    <button type="submit" class="btn btn-primary btn-sm shrink-0"><?= icon('check', 'h-4 w-4') ?> Simpan semua nilai</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
