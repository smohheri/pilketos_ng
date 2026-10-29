<?= $this->extend('layouts/admin') ?>

<?php
$pageTitle = 'Impor Data Siswa';
$pageSubtitle = 'Unggah data siswa dari file Excel atau CSV sesuai template.';
?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-4xl">
    <a href="<?= base_url('admin/siswa') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-ink/50 transition hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Kembali ke data siswa</a>

    <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_0.8fr]">
        <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sun/40"><?= icon('upload', 'h-5 w-5') ?></span>
                <div>
                    <h2 class="text-lg font-bold tracking-[-0.02em]">Unggah file</h2>
                    <p class="text-xs text-ink/45">Format .xlsx atau .csv, maksimal 2 MB.</p>
                </div>
            </div>

            <form action="<?= base_url('admin/siswa/import/preview') ?>" method="post" enctype="multipart/form-data" class="mt-5" x-data="{ fileName: '' }">
                <?= csrf_field() ?>
                <label for="file" class="flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-ink/20 bg-cream/60 px-5 py-10 text-center transition hover:border-cobalt/40 hover:bg-sky/10">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white text-cobalt"><?= icon('upload', 'h-6 w-6') ?></span>
                    <span class="mt-3 font-display text-sm font-semibold" x-show="!fileName">Pilih file Excel/CSV</span>
                    <span class="mt-3 break-all font-mono text-xs font-semibold text-cobalt" x-show="fileName" x-cloak x-text="fileName"></span>
                    <span class="mt-1 text-[11px] text-ink/40">Klik untuk memilih dari perangkat</span>
                    <input type="file" name="file" id="file" accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="sr-only" required @change="fileName = $event.target.files[0]?.name || ''">
                </label>

                <button type="submit" class="btn btn-primary btn-block mt-4"><?= icon('eye', 'h-4 w-4') ?> Validasi &amp; pratinjau</button>
            </form>

            <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-ink/10 bg-cream px-4 py-3 text-[11px] leading-relaxed text-ink/50">
                <?= icon('info', 'h-4 w-4 mt-0.5 shrink-0 text-cobalt') ?>
                <p>File akan divalidasi terlebih dahulu. Tidak ada data yang disimpan sebelum kamu mengonfirmasi pratinjau.</p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <h2 class="text-lg font-bold tracking-[-0.02em]">Template resmi</h2>
                <p class="mt-1 text-xs leading-relaxed text-ink/50">Gunakan template agar urutan dan nama kolom sesuai. Jangan mengubah baris header.</p>
                <a href="<?= base_url('admin/siswa/import/template') ?>" class="btn btn-dark btn-sm btn-block mt-4"><?= icon('download', 'h-4 w-4') ?> Unduh template</a>
            </div>

            <div class="rounded-4xl border-2 border-ink/10 bg-cream p-5 sm:p-6">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Kolom wajib</p>
                <ol class="mt-3 space-y-2 text-xs text-ink/55">
                    <?php foreach (['NIS', 'Nama Lengkap', 'Tanggal Lahir (YYYY-MM-DD)', 'Kelas', 'WhatsApp'] as $index => $column): ?>
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white font-display text-[9px] font-bold"><?= $index + 1 ?></span>
                            <span><?= esc($column) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>