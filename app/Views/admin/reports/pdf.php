<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan <?= esc($election['title']) ?></title>
    <style>
        @page { margin: 26px 28px 30px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #101828; font-family: "DejaVu Sans", sans-serif; font-size: 9px; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo { width: 46px; height: 46px; }
        .brand-name { margin: 0; font-size: 15px; font-weight: bold; letter-spacing: -0.2px; }
        .brand-sub { margin: 2px 0 0; color: #6B7280; font-size: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .report-label { margin: 0; color: #6B7280; font-size: 8px; text-transform: uppercase; letter-spacing: 1.1px; }
        .report-date { margin: 2px 0 0; font-size: 8px; }
        .title-block { margin-top: 10px; padding: 12px 16px; background: #101828; color: #FFFCF5; }
        .title-block h1 { margin: 0; font-size: 17px; line-height: 1.12; letter-spacing: -0.3px; }
        .title-block p { margin: 3px 0 0; color: #D8D4CA; font-size: 8px; }
        .badge { display: inline-block; margin-top: 6px; padding: 3px 8px; background: #FFC93C; color: #101828; font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; }
        .kpi { margin-top: 9px; }
        .kpi td { width: 25%; padding: 8px 10px; border: 1px solid #E7DFD2; background: #FFFCF5; vertical-align: top; }
        .kpi td + td { border-left: 0; }
        .kpi td.dark { background: #101828; border-color: #101828; color: #FFFCF5; }
        .kpi .kpi-label { margin: 0; color: #6B7280; font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; }
        .kpi .dark .kpi-label { color: #AEB5C0; }
        .kpi .kpi-value { margin: 2px 0 0; font-size: 17px; font-weight: bold; letter-spacing: -0.5px; }
        .kpi .kpi-note { margin: 1px 0 0; color: #8A8F98; font-size: 6.5px; }
        .section { margin-top: 7px; page-break-inside: avoid; }
        .section-head { margin: 0 0 5px; }
        .section-head .eyebrow { margin: 0; color: #6B7280; font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .section-head h2 { margin: 1px 0 0; font-size: 13px; letter-spacing: -0.2px; }
        .panel { border: 1px solid #E7DFD2; background: #FFFCF5; padding: 7px; }
        .chart { width: 100%; }
        .summary td { padding: 5px 7px; border-bottom: 1px solid #EAE2D6; vertical-align: top; }
        .summary tr:last-child td { border-bottom: 0; }
        .summary .label { width: 52%; color: #6B7280; }
        .summary .value { font-weight: bold; text-align: right; }
        .data-table th { padding: 3px 5px; background: #101828; color: #FFFCF5; font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.55px; text-align: left; }
        .data-table td { padding: 3px 5px; border-bottom: 1px solid #EAE2D6; vertical-align: middle; }
        .data-table tr:nth-child(even) td { background: #FBF6EC; }
        .rank { display: inline-block; width: 18px; padding: 3px 0; text-align: center; color: #101828; font-size: 7px; font-weight: bold; }
        .muted { color: #6B7280; }
        .bar-track { width: 100%; height: 6px; background: #EEE7DA; }
        .bar-fill { height: 6px; }
        .note { margin-top: 4px; padding: 6px 9px; border: 1px solid #E7DFD2; background: #FFFCF5; color: #6B7280; font-size: 6.5px; }
        .footer { margin-top: 4px; padding-top: 4px; border-top: 1px solid #E7DFD2; color: #8A8F98; font-size: 6.5px; }
        .footer .right { text-align: right; }
        .page-break { page-break-before: always; }
        .two-col { width: 100%; }
        .two-col .left { width: 25%; padding-right: 10px; vertical-align: middle; }
        .two-col .right { width: 75%; vertical-align: middle; }
        .chart-sm { display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <?php
    $phaseMeta = phase_meta($phase);
    $combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
    $modeLabel = $report['mode_label'] ?? 'Suara voting saja';
    $scoreFmt = static fn ($value, int $decimals = 2): string => $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
    ?>
    <table class="header">
        <tr>
            <td style="width: 54px;">
                <?php if ($logo !== null): ?>
                    <img src="<?= esc($logo) ?>" alt="" class="logo">
                <?php endif; ?>
            </td>
            <td>
                <p class="brand-name"><?= esc($profile['school_name'] ?? $appName) ?></p>
                <p class="brand-sub"><?= esc($profile['short_name'] ?? $appName) ?> · Laporan Pemilihan</p>
            </td>
            <td style="text-align: right;">
                <p class="report-label">Dokumen resmi</p>
                <p class="report-date">Dibuat <?= esc($generatedAt) ?></p>
            </td>
        </tr>
    </table>

    <div class="title-block">
        <h1><?= esc($election['title']) ?></h1>
        <p><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?> · <?= esc($election['timezone']) ?></p>
        <span class="badge"><?= esc($phaseMeta['label']) ?></span>
        <span class="badge" style="background: <?= $combined ? '#7BDCB5' : '#E9E2D5' ?>;"><?= esc($modeLabel) ?></span>
    </div>

    <table class="kpi">
        <tr>
            <td>
                <p class="kpi-label">Pemilih terdaftar</p>
                <p class="kpi-value"><?= number_format((int) $report['eligible'], 0, ',', '.') ?></p>
                <p class="kpi-note">Dari <?= number_format((int) $report['registered'], 0, ',', '.') ?> siswa aktif</p>
            </td>
            <td>
                <p class="kpi-label">Sudah memilih</p>
                <p class="kpi-value"><?= number_format((int) $report['voted'], 0, ',', '.') ?></p>
                <p class="kpi-note">Suara tercatat</p>
            </td>
            <td>
                <p class="kpi-label">Belum memilih</p>
                <p class="kpi-value"><?= number_format((int) $report['not_voted'], 0, ',', '.') ?></p>
                <p class="kpi-note">Pemilih tersisa</p>
            </td>
            <td class="dark">
                <p class="kpi-label">Partisipasi</p>
                <p class="kpi-value"><?= number_format((float) $report['participation'], 1, ',', '.') ?>%</p>
                <p class="kpi-note">Tingkat kehadiran</p>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-head">
            <p class="eyebrow">Ringkasan</p>
            <h2>Partisipasi pemilih</h2>
        </div>
        <table class="two-col">
            <tr>
                <td class="left">
                    <div class="panel">
                        <img src="<?= esc($participationChart) ?>" alt="Grafik partisipasi" class="chart">
                    </div>
                </td>
                <td class="right">
                    <div class="panel">
                        <table class="summary">
                            <tr><td class="label">Total suara sah</td><td class="value"><?= number_format((int) $report['total_votes'], 0, ',', '.') ?></td></tr>
                            <?php if ($combined): ?>
                                <tr><td class="label">Mode hasil</td><td class="value"><?= esc($modeLabel) ?></td></tr>
                                <tr><td class="label">Bobot suara / penilaian</td><td class="value"><?= esc($scoreFmt($report['vote_weight'] ?? 0)) ?>% / <?= esc($scoreFmt($report['assessment_weight'] ?? 0)) ?>%</td></tr>
                                <tr><td class="label">Kelengkapan penilaian</td><td class="value"><?= ! empty($report['assessment_complete']) ? 'Lengkap' : 'Belum lengkap' ?></td></tr>
                            <?php else: ?>
                                <tr><td class="label">Siswa terdaftar aktif</td><td class="value"><?= number_format((int) $report['registered'], 0, ',', '.') ?></td></tr>
                                <tr><td class="label">Pemilih memenuhi syarat</td><td class="value"><?= number_format((int) $report['eligible'], 0, ',', '.') ?></td></tr>
                                <tr><td class="label">Sudah memilih</td><td class="value"><?= number_format((int) $report['voted'], 0, ',', '.') ?></td></tr>
                                <tr><td class="label">Belum memilih</td><td class="value"><?= number_format((int) $report['not_voted'], 0, ',', '.') ?></td></tr>
                            <?php endif; ?>
                            <tr><td class="label">Tingkat partisipasi</td><td class="value"><?= number_format((float) $report['participation'], 1, ',', '.') ?>%</td></tr>
                            <tr><td class="label">Waktu pengumuman hasil</td><td class="value"><?= esc(format_date_id($election['result_publish_at'], true)) ?></td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-head">
            <p class="eyebrow">Hasil kandidat</p>
            <h2><?= $combined ? 'Nilai akhir & peringkat' : 'Perolehan suara' ?></h2>
        </div>
        <div class="panel">
            <img src="<?= esc($candidateChart) ?>" alt="<?= $combined ? 'Grafik nilai akhir kandidat' : 'Grafik perolehan suara kandidat' ?>" class="chart chart-sm" style="width: 73%;">
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <p class="eyebrow">Partisipasi kelas</p>
            <h2>Rekap per kelas</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Kelas</th>
                    <th style="width: 18%;">Terdaftar</th>
                    <th style="width: 18%;">Memilih</th>
                    <th style="width: 39%;">Partisipasi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byClass as $item): ?>
                    <?php
                    $eligible = (int) $item['eligible'];
                    $voted = (int) $item['voted'];
                    $rate = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?= esc($item['class_name']) ?></strong></td>
                        <td><?= number_format($eligible, 0, ',', '.') ?></td>
                        <td><?= number_format($voted, 0, ',', '.') ?></td>
                        <td>
                            <table style="width: 100%;"><tr>
                                <td style="width: 78%; padding: 0;"><div class="bar-track"><div class="bar-fill" style="width: <?= max(1, $rate) ?>%; background: #2F5BFF;"></div></div></td>
                                <td style="width: 22%; padding: 0 0 0 5px; text-align: right; font-weight: bold;"><?= number_format($rate, 1, ',', '.') ?>%</td>
                            </tr></table>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($byClass === []): ?>
                    <tr><td colspan="4" class="muted" style="text-align: center; padding: 12px;">Belum ada data kelas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="note">
        Laporan ini hanya memuat data agregat. Sistem tidak menyimpan hubungan antara identitas pemilih dan kandidat yang dipilih.
    </div>

    <table class="footer">
        <tr>
            <td><?= esc($appName) ?> · <?= esc($profile['short_name'] ?? '') ?></td>
            <td class="right">Dicetak <?= esc($generatedAt) ?></td>
        </tr>
    </table>
</body>
</html>
