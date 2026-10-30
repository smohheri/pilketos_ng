<?= $this->extend('layouts/public') ?>

<?php
$meta = phase_meta($phase);
$isLive = $phase === 'ongoing';
$student = current_student();
$elections = $elections ?? [];
$candidateCount = count($candidates);
$totalCandidates = array_sum(array_map(static fn (array $group): int => count($group['candidates'] ?? []), $elections));
$combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
$scoreNumber = static fn ($value, int $decimals = 2): string => $value === null ? '-' : number_format((float) $value, $decimals, ',', '.');
$countdownTarget = null;
$countdownLabel = '';
if ($election !== null) {
    if ($phase === 'ongoing') {
        $countdownTarget = strtotime((string) $election['end_at']);
        $countdownLabel = 'Menuju penutupan suara';
    } elseif ($phase === 'scheduled') {
        $countdownTarget = strtotime((string) $election['start_at']);
        $countdownLabel = 'Menuju pembukaan bilik suara';
    } elseif ($phase === 'closed') {
        $countdownTarget = strtotime((string) $election['result_publish_at']);
        $countdownLabel = 'Hasil diumumkan dalam';
    }
}

$primaryHref = base_url('aktivasi');
$primaryLabel = 'Aktivasi Akun';
if ($student !== null) {
    $primaryHref = election_voting_url($election);
    $primaryLabel = 'Masuk Bilik Suara';
}
if ($phase === 'results') {
    $primaryHref = election_results_url($election);
    $primaryLabel = 'Lihat Hasil Pemilihan';
} elseif ($phase === 'closed') {
    $primaryHref = base_url('#jadwal');
    $primaryLabel = 'Lihat Jadwal Pengumuman';
}

$missionLines = lines($profile['mission'] ?? '');
$now = time();
$scheduleStatus = static function (array $stage) use ($now): string {
    $start = strtotime((string) $stage['start_at']);
    $end = ! empty($stage['end_at']) ? strtotime((string) $stage['end_at']) : null;
    if ($end !== null && $end < $now) {
        return 'done';
    }
    if ($start <= $now && ($end === null || $end >= $now)) {
        return 'active';
    }

    return 'upcoming';
};

$values = [
    ['icon' => 'megaphone', 'title' => landing_text('pillar_1_title', 'Aspiratif'), 'text' => landing_text('pillar_1_text', 'Mendengar semua suara, bukan hanya yang paling lantang.'), 'color' => 'bg-coral text-white'],
    ['icon' => 'sparkles', 'title' => landing_text('pillar_2_title', 'Kreatif'), 'text' => landing_text('pillar_2_text', 'Menghadirkan ide segar dan cara baru untuk sekolah.'), 'color' => 'bg-sun text-ink'],
    ['icon' => 'refresh', 'title' => landing_text('pillar_3_title', 'Responsif'), 'text' => landing_text('pillar_3_text', 'Cepat tanggap terhadap kebutuhan dan masalah siswa.'), 'color' => 'bg-cobalt text-white'],
    ['icon' => 'shield', 'title' => landing_text('pillar_4_title', 'Amanah'), 'text' => landing_text('pillar_4_text', 'Menjaga kepercayaan, transparansi, dan integritas.'), 'color' => 'bg-mint text-ink'],
];

$steps = [
    ['icon' => 'key', 'color' => 'bg-sun text-ink', 'title' => landing_text('step_1_title', 'Aktivasi akun'), 'text' => landing_text('step_1_text', 'Masukkan NIS dan tanggal lahir yang terdaftar di sekolah.')],
    ['icon' => 'whatsapp', 'color' => 'bg-mint text-ink', 'title' => landing_text('step_2_title', 'Verifikasi OTP'), 'text' => landing_text('step_2_text', 'Kode 6 digit dikirim ke WhatsApp resmi yang tersimpan.')],
    ['icon' => 'ballot', 'color' => 'bg-coral text-white', 'title' => landing_text('step_3_title', 'Pilih kandidat'), 'text' => landing_text('step_3_text', 'Pelajari visi-misi, lalu pilih satu kandidat favoritmu.')],
    ['icon' => 'fingerprint', 'color' => 'bg-cobalt text-white', 'title' => landing_text('step_4_title', 'Konfirmasi & bukti'), 'text' => landing_text('step_4_text', 'Periksa pilihan, kirim suara, dan simpan kode bukti acak.')],
];
?>

<?= $this->section('head') ?>
<meta property="og:title" content="<?= esc($appName) ?> — Pemilihan Ketua OSIS">
<meta property="og:description" content="<?= esc(meta_description($profile['description'] ?? '')) ?>">
<meta property="og:type" content="website">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section id="beranda" class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-grid-lines opacity-60" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-24 top-32 h-72 w-72 rounded-full border-2 border-ink/10 bg-lilac/40 blur-0" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-0 top-0 h-48 w-48 translate-x-1/3 -translate-y-1/3 rounded-full border-2 border-ink/10 bg-sun/50" aria-hidden="true"></div>

    <div class="shell relative pb-12 pt-10 sm:pt-16 lg:pb-20 lg:pt-20">
        <div class="grid items-center gap-12 lg:grid-cols-[1.04fr_0.96fr]">
            <div class="animate-fade-up">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="eyebrow-solid"><span class="h-2 w-2 rounded-full bg-mint <?= $isLive ? 'animate-pulse' : '' ?>"></span> <?= esc(landing_text('hero_eyebrow', 'Pemilihan Ketua OSIS & Poltar 2026')) ?></span>
                    <span class="<?= esc($meta['class']) ?>"><?= esc($meta['label']) ?></span>
                </div>

                <h1 class="mt-6 font-display text-[2.75rem] font-bold leading-[0.95] tracking-[-0.045em] text-ink sm:text-6xl lg:text-[4.6rem]">
                    <?= esc(landing_text('hero_heading_line_1', 'Pilih dengan Hati,')) ?><br>
                    <span class="relative inline-block text-coral"><?= esc(landing_text('hero_heading_line_2', 'Pimpin dengan Aksi.')) ?><span class="absolute -bottom-1 left-0 h-3 w-full rounded-full bg-sun/80 -z-10"></span></span>
                </h1>

                <p class="mt-6 max-w-xl text-base leading-relaxed text-ink/65 sm:text-lg">
                    <?= esc(landing_text('hero_subtitle', $election['description'] ?? 'Satu siswa, satu suara. Saatnya memilih pemimpin yang mendengar, bergerak, dan membawa sekolah kita lebih baik.')) ?>
                </p>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="<?= esc($primaryHref) ?>" class="btn btn-primary">
                        <?= icon('ballot', 'h-4 w-4') ?> <?= esc($primaryLabel) ?>
                    </a>
                    <a href="#pemilihan" class="btn btn-light">
                        <?= esc(landing_text('hero_cta_secondary', 'Dua Pemilihan')) ?> <?= icon('arrow-right', 'h-4 w-4') ?>
                    </a>
                    <a href="#panduan" class="hidden items-center gap-1 px-2 font-display text-sm font-semibold text-ink/60 underline decoration-ink/20 decoration-2 underline-offset-4 transition hover:text-ink sm:inline-flex">
                        Cara memilih
                    </a>
                </div>

                <?php if ($election !== null): ?>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center gap-3 rounded-2xl border-2 border-ink/10 bg-paper/80 px-4 py-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-ink text-cream"><?= icon('calendar', 'h-5 w-5') ?></span>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/40">Periode pemungutan</p>
                                <p class="font-display text-sm font-semibold"><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-ink/50">
                            <span class="flex -space-x-2">
                                <?php foreach (array_slice($candidates, 0, 4) as $candidate): ?>
                                    <?php if (! empty($candidate['photo_path'])): ?>
                                        <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="Foto <?= esc($candidate['full_name']) ?>" title="<?= esc($candidate['full_name']) ?>" class="h-7 w-7 rounded-full border-2 border-cream object-cover" decoding="async">
                                    <?php else: ?>
                                        <span title="<?= esc($candidate['full_name']) ?>" class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-cream text-[9px] font-bold <?= avatar_tone($candidate['full_name']) ?>"><?= esc(initials($candidate['full_name'])) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </span>
                            <span><?= $totalCandidates ?> kandidat siap dipilih</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="relative mx-auto w-full max-w-[34rem]">
                <div class="absolute -inset-4 rotate-2 rounded-5xl border-2 border-ink/10 bg-paper/60" aria-hidden="true"></div>
                <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-cream p-2 shadow-hard" aria-hidden="true">
                    <svg viewBox="0 0 640 620" class="h-auto w-full" role="img" aria-label="Ilustrasi siswa memasukkan surat suara ke kotak suara">
                        <rect width="640" height="620" rx="34" fill="#F6F0E4"/>
                        <circle cx="486" cy="134" r="118" fill="#FFC93C"/>
                        <path d="M-20 360c80-80 190-80 270 0s190 80 270 0v280H-20z" fill="#BEE3FF" opacity=".55"/>
                        <rect x="452" y="276" width="140" height="140" rx="30" fill="#72E6B7" stroke="#101828" stroke-width="7"/>
                        <path d="M492 300l32 64 58 8-42 40 10 58-58-30-58 30 10-58-42-40 58-8z" fill="#FFFCF5" stroke="#101828" stroke-width="6" stroke-linejoin="round" transform="translate(50 10) scale(.78)"/>

                        <g stroke="#101828" stroke-width="8" stroke-linejoin="round">
                            <path d="M92 620V470c0-88 46-142 108-142s108 54 108 142v150" fill="#F45B4F"/>
                            <path d="M168 330h64v58c0 17-15 30-32 30s-32-13-32-30z" fill="#E6B48F"/>
                            <ellipse cx="200" cy="244" rx="66" ry="76" fill="#F6C7A6"/>
                            <path d="M134 238c0-52 30-86 66-86s66 34 66 86c-18-20-38-30-62-30-20 0-36 5-50 16-8 7-14 12-20 14z" fill="#101828"/>
                            <path d="M96 470l-31-85 32-12 28 77" fill="#F45B4F"/>
                            <circle cx="69" cy="370" r="26" fill="#F6C7A6"/>
                            <path d="M340 620V452c0-88 46-142 108-142s108 54 108 142v168" fill="#2F5BFF"/>
                            <path d="M416 326h64v58c0 17-15 30-32 30s-32-13-32-30z" fill="#D9A57E"/>
                            <ellipse cx="448" cy="240" rx="64" ry="76" fill="#E8B992"/>
                            <path d="M384 232c0-48 30-80 64-80s64 32 64 80c-12-26-36-42-64-42s-52 16-64 42z" fill="#101828"/>
                            <path d="M548 470l30-74 32 14-32 82" fill="#2F5BFF"/>
                            <circle cx="587" cy="378" r="24" fill="#E8B992"/>
                        </g>
                        <g fill="#101828">
                            <circle cx="178" cy="244" r="6"/>
                            <circle cx="224" cy="244" r="6"/>
                            <circle cx="426" cy="240" r="6"/>
                            <circle cx="470" cy="240" r="6"/>
                        </g>
                        <path d="M184 274c10 9 24 9 34 0M430 270c10 9 22 9 32 0" fill="none" stroke="#101828" stroke-width="6" stroke-linecap="round"/>

                        <g stroke="#101828" stroke-width="8" stroke-linejoin="round">
                            <path d="M226 620V506c0-22 18-40 40-40h212c22 0 40 18 40 40v114z" fill="#FFC93C"/>
                            <rect x="294" y="432" width="156" height="18" rx="9" fill="#101828"/>
                            <path d="M336 420l40-114 88 30-40 114z" fill="#FFFCF5"/>
                            <path d="M389 358l18 16 35-37" fill="none" stroke="#101828" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="260" y="540" width="264" height="58" rx="14" fill="#F45B4F"/>
                        </g>
                        <text x="392" y="581" text-anchor="middle" font-family="'Space Grotesk', sans-serif" font-size="23" font-weight="700" fill="#FFFCF5">CAKRA 2026</text>

                        <g transform="translate(52 96) rotate(-7)">
                            <rect width="132" height="52" rx="12" fill="#FFFCF5" stroke="#101828" stroke-width="6"/>
                            <text x="66" y="22" text-anchor="middle" font-family="'Space Grotesk', sans-serif" font-size="11" font-weight="700" fill="#101828">SATU SISWA</text>
                            <text x="66" y="39" text-anchor="middle" font-family="'Space Grotesk', sans-serif" font-size="11" font-weight="700" fill="#F45B4F">SATU SUARA</text>
                        </g>
                        <g transform="translate(430 486) rotate(6)">
                            <rect width="140" height="42" rx="10" fill="#101828"/>
                            <text x="70" y="27" text-anchor="middle" font-family="'Space Grotesk', sans-serif" font-size="14" font-weight="700" fill="#FFC93C">SUARAMU BERARTI</text>
                        </g>
                        <g fill="#101828" opacity=".55">
                            <circle cx="72" cy="520" r="5"/>
                            <circle cx="100" cy="552" r="3"/>
                            <circle cx="560" cy="470" r="4"/>
                            <circle cx="590" cy="514" r="3"/>
                        </g>
                    </svg>
                </div>

                <?php if ($election !== null && $countdownTarget !== null): ?>
                    <div class="absolute -bottom-5 left-3 right-3 sm:-left-6 sm:right-auto sm:w-[22rem]" x-data="countdown('<?= esc(date('c', $countdownTarget)) ?>')">
                        <div class="rounded-2xl border-2 border-ink bg-paper px-4 py-3 shadow-hard-sm">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/45"><?= esc($countdownLabel) ?></p>
                                <span class="badge-live !px-2 !py-0.5 !text-[9px]"><?= icon('clock', 'h-3 w-3') ?> Server</span>
                            </div>
                            <div class="mt-2 flex items-end gap-1.5 font-display font-bold tabular-nums">
                                <span class="text-2xl" x-text="days">00</span><span class="mb-1 text-[10px] text-ink/40">hari</span>
                                <span class="text-2xl" x-text="hours">00</span><span class="mb-1 text-[10px] text-ink/40">jam</span>
                                <span class="text-2xl" x-text="minutes">00</span><span class="mb-1 text-[10px] text-ink/40">mnt</span>
                                <span class="text-2xl text-coral" x-text="seconds">00</span><span class="mb-1 text-[10px] text-ink/40">dtk</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="relative border-y-2 border-ink bg-ink py-3 text-cream">
        <div class="overflow-hidden">
            <div class="marquee-track font-display text-xs font-semibold uppercase tracking-[0.22em] text-cream/70">
                <?php for ($i = 0; $i < 2; $i++): ?>
                    <span class="flex items-center gap-8">
                        <span>Paperless</span><span class="text-sun">✦</span>
                        <span>Satu siswa satu suara</span><span class="text-coral">✦</span>
                        <span>Pilihanmu rahasia</span><span class="text-mint">✦</span>
                        <span>Transparan &amp; teraudit</span><span class="text-lilac">✦</span>
                        <span>Pilih dengan hati</span><span class="text-sun">✦</span>
                        <span>Pimpin dengan aksi</span><span class="text-coral">✦</span>
                    </span>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<section class="shell -mt-1">
    <div class="grid grid-cols-2 gap-px overflow-hidden rounded-b-4xl border-x-2 border-b-2 border-ink/10 bg-ink/10 sm:grid-cols-4">
        <?php
        $stats = [
            [$candidateCount . '', 'Kandidat', 'bg-paper'],
            ['1:1', 'Siswa & suara', 'bg-paper'],
            ['100%', 'Paperless', 'bg-paper'],
            ['3', 'Langkah memilih', 'bg-paper'],
        ];
        foreach ($stats as $stat): ?>
            <div class="<?= $stat[2] ?> px-5 py-5 sm:px-6">
                <p class="font-display text-2xl font-bold tracking-tight sm:text-3xl"><?= esc($stat[0]) ?></p>
                <p class="mt-1 text-[11px] font-medium uppercase tracking-[0.14em] text-ink/45"><?= esc($stat[1]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="pemilihan" class="shell py-8 sm:py-10">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="eyebrow"><?= icon('ballot', 'h-3.5 w-3.5') ?> Dua pemilihan</span>
            <h2 class="mt-3 text-3xl font-bold tracking-[-0.03em] sm:text-4xl"><?= esc(landing_text('pemilihan_heading', 'Satu sekolah, dua pilihan.')) ?></h2>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink/55"><?= esc(landing_text('pemilihan_subtext', 'Ketua OSIS dan Ketua Poltar dipilih pada pemilihan terpisah, dengan kandidat, jadwal, dan panitia masing-masing.')) ?></p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <?php foreach ($elections as $group): ?>
            <?php
            $item = $group['election'];
            $itemPhase = $group['phase'];
            $itemMeta = $group['meta'];
            $itemReport = $group['report'];
            $itemCombined = $group['combined'];
            $itemCandidates = count($group['candidates']);
            $candidateAnchor = ($item['type'] ?? '') === 'osis' ? '#kandidat' : '#kandidat-' . ($item['slug'] ?? $item['id']);
            $itemHref = $itemPhase === 'results' && $itemReport !== null
                ? election_results_url($item)
                : ($itemPhase === 'ongoing' ? election_voting_url($item) : $candidateAnchor);
            $itemCta = $itemPhase === 'results' && $itemReport !== null
                ? 'Lihat hasil'
                : ($itemPhase === 'ongoing' ? 'Masuk bilik suara' : 'Lihat kandidat');
            ?>
            <div class="relative overflow-hidden rounded-4xl border-2 border-ink/10 bg-paper p-5 sm:p-6">
                <div class="pointer-events-none absolute -right-8 -top-10 h-28 w-28 rounded-full border-[18px] <?= ($item['type'] ?? '') === 'poltar' ? 'border-sun/50' : 'border-cobalt/20' ?>" aria-hidden="true"></div>
                <div class="relative">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="<?= esc(election_type_badge($item['type'] ?? null)) ?>"><?= esc(election_type_label($item['type'] ?? null)) ?></span>
                        <span class="<?= esc($itemMeta['class']) ?>"><span class="h-1.5 w-1.5 rounded-full <?= esc($itemMeta['dot']) ?>"></span><?= esc($itemMeta['label']) ?></span>
                        <?php if ($itemReport !== null): ?>
                            <span class="<?= $itemCombined ? 'badge-cool' : 'badge-neutral' ?>"><?= esc($itemReport['mode_label']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="mt-4 font-display text-xl font-bold leading-tight"><?= esc($item['title']) ?></h3>
                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink/55"><?= esc($item['description'] ?: 'Pemilihan resmi dengan kandidat dan penilaian dinamis.') ?></p>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] text-ink/45">
                        <span class="chip"><?= icon('users', 'h-3.5 w-3.5') ?> <?= $itemCandidates ?> kandidat</span>
                        <span class="chip"><?= icon('calendar', 'h-3.5 w-3.5') ?> <?= esc(format_date_range($item['start_at'], $item['end_at'])) ?></span>
                    </div>
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <a href="<?= esc($itemHref) ?>" class="btn btn-primary btn-sm"><?= esc($itemCta) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a>
                        <?php if ($itemPhase === 'scheduled' || $itemPhase === 'draft'): ?>
                            <a href="<?= esc($candidateAnchor) ?>" class="btn btn-light btn-sm">Kenali kandidat</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="tentang" class="section-pad">
    <div class="shell">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div>
                <span class="eyebrow">Tentang CAKRA 2026</span>
                <h2 class="mt-5 text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl"><?= esc(landing_text('tentang_heading', 'Demokrasi kecil yang berdampak besar.')) ?></h2>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-ink/65">
                    <?= esc($profile['description'] ?? 'CAKRA 2026 menghadirkan pemilihan Ketua OSIS yang lebih praktis, modern, dan paperless. Semua siswa dapat melihat kandidat, memahami gagasan, dan memberikan suara dengan aman.') ?>
                </p>
                <div class="mt-8 rounded-3xl border-2 border-ink bg-sun p-6 shadow-hard-sm">
                    <p class="font-display text-2xl font-bold leading-snug">“<?= esc(landing_text('tentang_tagline', 'Pilih dengan Hati, Pimpin dengan Aksi.')) ?>”</p>
                    <p class="mt-2 text-sm text-ink/60"><?= esc(landing_text('tentang_tagline_note', '— Tagline resmi CAKRA 2026')) ?></p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <?php foreach ($values as $index => $value): ?>
                    <div class="card-lift rounded-3xl border-2 border-ink/10 bg-paper p-6">
                        <div class="flex items-start justify-between">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-ink/10 <?= $value['color'] ?>">
                                <?= icon($value['icon'], 'h-6 w-6') ?>
                            </span>
                            <span class="font-display text-xs font-bold text-ink/25">0<?= $index + 1 ?></span>
                        </div>
                        <h3 class="mt-5 font-display text-xl font-bold"><?= esc($value['title']) ?></h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink/55"><?= esc($value['text']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section id="visi-misi" class="section-pad pt-0">
    <div class="shell">
        <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-ink p-7 text-cream sm:p-10 lg:p-14">
            <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border-[40px] border-cobalt/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute bottom-0 left-1/3 h-40 w-40 translate-y-1/2 rounded-full bg-coral/30 blur-2xl" aria-hidden="true"></div>

            <div class="relative grid gap-10 lg:grid-cols-[1fr_1fr] lg:gap-16">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border-2 border-cream/20 px-4 py-1.5 font-display text-[11px] font-semibold uppercase tracking-[0.18em] text-cream/70"><?= icon('school', 'h-3.5 w-3.5 text-sun') ?> Visi &amp; Misi Sekolah</span>
                    <h2 class="mt-6 text-3xl font-bold leading-[1.08] tracking-[-0.03em] sm:text-5xl">Sekolah yang tumbuh bersama siswanya.</h2>
                    <div class="mt-7 border-l-2 border-sun pl-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-sun">Visi</p>
                        <p class="mt-2 text-lg leading-relaxed text-cream/85"><?= esc($profile['vision'] ?? 'Menjadi sekolah yang membentuk generasi berkarakter, kreatif, dan peduli.') ?></p>
                    </div>
                    <p class="mt-7 max-w-md text-sm leading-relaxed text-cream/45"><?= esc($profile['short_name'] ?? 'SMA Negeri 1 Nusantara') ?> percaya bahwa ruang partisipasi siswa adalah bagian penting dari proses belajar.</p>
                </div>

                <div class="grid gap-3 self-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cream/40">Misi sekolah</p>
                    <?php if ($missionLines !== []): ?>
                        <?php foreach ($missionLines as $index => $mission): ?>
                            <div class="flex gap-4 rounded-2xl border border-cream/10 bg-cream/5 p-4">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sun font-display text-xs font-bold text-ink"><?= $index + 1 ?></span>
                                <p class="text-sm leading-relaxed text-cream/75"><?= esc($mission) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="rounded-2xl border border-cream/10 bg-cream/5 p-4 text-sm text-cream/60">Misi sekolah belum diisi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="kandidat" class="section-pad pt-0">
    <div class="shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="eyebrow"><?= icon('users', 'h-3.5 w-3.5') ?> Kandidat</span>
                <h2 class="mt-4 text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl"><?= esc(landing_text('kandidat_heading', 'Kenali pilihanmu.')) ?></h2>
                <p class="mt-3 max-w-2xl text-base text-ink/60"><?= esc(landing_text('kandidat_subtext', 'Baca visi, misi, dan program kerja setiap kandidat. Pilih yang paling sesuai dengan harapanmu untuk sekolah.')) ?></p>
            </div>
            <?php if ($candidateCount > 0): ?>
                <span class="chip !border-ink/15 !bg-paper !px-4 !py-2"><?= $candidateCount ?> kandidat · <?= esc($election['title'] ?? '') ?></span>
            <?php endif; ?>
        </div>

        <?php if ($candidates === []): ?>
            <div class="mt-8 rounded-4xl border-2 border-dashed border-ink/20 bg-paper/70 p-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sun/50"><?= icon('users', 'h-7 w-7') ?></span>
                <p class="mt-4 font-display text-xl font-bold">Kandidat belum dipublikasikan</p>
                <p class="mt-1 text-sm text-ink/55">Pantau halaman ini pada periode publikasi kandidat.</p>
            </div>
        <?php else: ?>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <?php foreach ($candidates as $index => $candidate): ?>
                    <?php
                    $accents = ['bg-coral text-white', 'bg-sun text-ink', 'bg-cobalt text-white', 'bg-mint text-ink'];
                    $accent = $accents[$index % count($accents)];
                    $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT);
                    ?>
                    <a href="<?= base_url('kandidat/' . $candidate['id']) ?>" class="group card-lift overflow-hidden rounded-4xl border-2 border-ink/10 bg-paper">
                        <div class="relative">
                            <div class="candidate-photo !rounded-none !border-0">
                                <?php if (! empty($candidate['photo_path'])): ?>
                                    <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="Foto <?= esc($candidate['full_name']) ?>" class="transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?>">
                                        <span class="font-display text-5xl font-bold"><?= esc(initials($candidate['full_name'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="absolute left-4 top-4 flex h-11 w-11 items-center justify-center rounded-full border-2 border-ink font-display text-sm font-bold shadow-hard-sm <?= $accent ?>"><?= esc($number) ?></span>
                            <span class="absolute right-4 top-4 rounded-full border border-ink/10 bg-paper/90 px-2.5 py-1 font-display text-[10px] font-semibold uppercase tracking-wide text-ink/60"><?= esc($candidate['class_name']) ?></span>
                        </div>
                        <div class="p-5">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Kandidat <?= esc($number) ?></p>
                            <h3 class="mt-1.5 font-display text-xl font-bold leading-tight"><?= esc($candidate['full_name']) ?></h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink/55"><?= esc($candidate['tagline'] ?: excerpt($candidate['vision'], 90)) ?></p>
                            <div class="mt-4 flex items-center justify-between border-t-2 border-ink/10 pt-3">
                                <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-ink/40">Visi &amp; program</span>
                                <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-ink/15 bg-white transition group-hover:border-ink group-hover:bg-sun"><?= icon('arrow-right', 'h-4 w-4') ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php foreach ($elections as $group): ?>
    <?php if ((int) ($group['election']['id'] ?? 0) === (int) ($election['id'] ?? 0)) continue; ?>
    <?php $item = $group['election']; $itemCandidates = $group['candidates']; ?>
    <section id="kandidat-<?= esc($item['slug'] ?? $item['id']) ?>" class="section-pad pt-0 scroll-mt-24">
        <div class="shell">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="<?= esc(election_type_badge($item['type'] ?? null)) ?>"><?= esc(election_type_label($item['type'] ?? null)) ?></span>
                    <h2 class="mt-3 text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl"><?= esc($item['title']) ?></h2>
                    <p class="mt-3 max-w-2xl text-base text-ink/60"><?= esc($item['description'] ?: 'Baca visi, misi, dan program kerja setiap kandidat sebelum memilih.') ?></p>
                </div>
                <?php if ($itemCandidates !== []): ?>
                    <span class="chip !border-ink/15 !bg-paper !px-4 !py-2"><?= count($itemCandidates) ?> kandidat</span>
                <?php endif; ?>
            </div>

            <?php if ($itemCandidates === []): ?>
                <div class="mt-8 rounded-4xl border-2 border-dashed border-ink/20 bg-paper/70 p-10 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sun/50"><?= icon('users', 'h-7 w-7') ?></span>
                    <p class="mt-4 font-display text-xl font-bold">Kandidat belum dipublikasikan</p>
                    <p class="mt-1 text-sm text-ink/55">Pantau halaman ini pada periode publikasi kandidat.</p>
                </div>
            <?php else: ?>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($itemCandidates as $index => $candidate): ?>
                        <?php
                        $accents = ['bg-coral text-white', 'bg-sun text-ink', 'bg-cobalt text-white', 'bg-mint text-ink'];
                        $accent = $accents[$index % count($accents)];
                        $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT);
                        ?>
                        <a href="<?= base_url('kandidat/' . $candidate['id']) ?>" class="group card-lift overflow-hidden rounded-4xl border-2 border-ink/10 bg-paper">
                            <div class="relative">
                                <div class="candidate-photo !rounded-none !border-0">
                                    <?php if (! empty($candidate['photo_path'])): ?>
                                        <img src="<?= esc(media_url($candidate['photo_path'])) ?>" alt="Foto <?= esc($candidate['full_name']) ?>" class="transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center <?= avatar_tone($candidate['full_name']) ?>">
                                            <span class="font-display text-5xl font-bold"><?= esc(initials($candidate['full_name'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="absolute left-4 top-4 flex h-11 w-11 items-center justify-center rounded-full border-2 border-ink font-display text-sm font-bold shadow-hard-sm <?= $accent ?>"><?= esc($number) ?></span>
                                <span class="absolute right-4 top-4 rounded-full border border-ink/10 bg-paper/90 px-2.5 py-1 font-display text-[10px] font-semibold uppercase tracking-wide text-ink/60"><?= esc($candidate['class_name']) ?></span>
                            </div>
                            <div class="p-5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">Kandidat <?= esc($number) ?></p>
                                <h3 class="mt-1.5 font-display text-xl font-bold leading-tight"><?= esc($candidate['full_name']) ?></h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink/55"><?= esc($candidate['tagline'] ?: excerpt($candidate['vision'], 90)) ?></p>
                                <div class="mt-4 flex items-center justify-between border-t-2 border-ink/10 pt-3">
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.12em] text-ink/40">Visi &amp; program</span>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-ink/15 bg-white transition group-hover:border-ink group-hover:bg-sun"><?= icon('arrow-right', 'h-4 w-4') ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<section id="jadwal" class="section-pad bg-paper/60 pt-0">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.72fr] lg:gap-14">
            <div>
                <span class="eyebrow"><?= icon('calendar', 'h-3.5 w-3.5') ?> Jadwal</span>
                <h2 class="mt-4 text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl"><?= esc(landing_text('jadwal_heading', 'Setiap tahap, transparan.')) ?></h2>
                <p class="mt-3 max-w-xl text-base text-ink/60"><?= esc(landing_text('jadwal_subtext', 'Ikuti rangkaian pemilihan dari publikasi kandidat hingga pengumuman hasil.')) ?></p>

                <?php if ($schedules === []): ?>
                    <p class="mt-8 rounded-2xl border-2 border-dashed border-ink/20 bg-paper p-6 text-sm text-ink/55">Jadwal tahapan belum tersedia.</p>
                <?php else: ?>
                    <div class="mt-8 space-y-3">
                        <?php foreach ($schedules as $stage): ?>
                            <?php
                            $status = $scheduleStatus($stage);
                            $statusLabel = ['done' => 'Selesai', 'active' => 'Berlangsung', 'upcoming' => 'Akan datang'][$status];
                            $statusClass = ['done' => 'badge-neutral', 'active' => 'badge-live', 'upcoming' => 'badge-warm'][$status];
                            ?>
                            <div class="relative overflow-hidden rounded-3xl border-2 <?= $status === 'active' ? 'border-ink bg-sun/25' : 'border-ink/10 bg-paper' ?> p-5 sm:p-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex gap-4">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border-2 <?= $status === 'active' ? 'border-ink bg-ink text-sun' : 'border-ink/10 bg-cream text-ink/50' ?>">
                                            <?= $status === 'done' ? icon('check', 'h-5 w-5') : icon('calendar', 'h-5 w-5') ?>
                                        </span>
                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35"><?= esc(format_date_range($stage['start_at'], $stage['end_at'])) ?></p>
                                            <h3 class="mt-1 font-display text-lg font-bold"><?= esc($stage['title']) ?></h3>
                                            <?php if (! empty($stage['description'])): ?>
                                                <p class="mt-1 text-sm leading-relaxed text-ink/55"><?= esc($stage['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="<?= $statusClass ?> shrink-0 self-start"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($elections as $group): ?>
                    <?php if ((int) ($group['election']['id'] ?? 0) === (int) ($election['id'] ?? 0)) continue; ?>
                    <?php $item = $group['election']; $itemSchedules = $group['schedules']; ?>
                    <div class="mt-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="<?= esc(election_type_badge($item['type'] ?? null)) ?>"><?= esc(election_type_label($item['type'] ?? null)) ?></span>
                            <p class="font-display text-sm font-bold"><?= esc($item['title']) ?></p>
                        </div>
                        <?php if ($itemSchedules === []): ?>
                            <p class="mt-3 rounded-2xl border-2 border-dashed border-ink/20 bg-paper p-4 text-sm text-ink/55">Jadwal tahapan <?= esc(election_type_label($item['type'] ?? null)) ?> belum tersedia.</p>
                        <?php else: ?>
                            <div class="mt-3 space-y-3">
                                <?php foreach ($itemSchedules as $stage): ?>
                                    <?php
                                    $status = $scheduleStatus($stage);
                                    $statusLabel = ['done' => 'Selesai', 'active' => 'Berlangsung', 'upcoming' => 'Akan datang'][$status];
                                    $statusClass = ['done' => 'badge-neutral', 'active' => 'badge-live', 'upcoming' => 'badge-warm'][$status];
                                    ?>
                                    <div class="rounded-2xl border-2 <?= $status === 'active' ? 'border-ink bg-sun/25' : 'border-ink/10 bg-paper' ?> p-4">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35"><?= esc(format_date_range($stage['start_at'], $stage['end_at'])) ?></p>
                                                <h3 class="mt-1 font-display text-base font-bold"><?= esc($stage['title']) ?></h3>
                                            </div>
                                            <span class="<?= $statusClass ?> shrink-0 self-start"><?= esc($statusLabel) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="lg:pt-14">
                <div class="sticky top-28 space-y-4">
                    <?php if ($election !== null): ?>
                        <div class="rounded-4xl border-2 border-ink bg-cobalt p-6 text-white shadow-hard">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full border border-white/25 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]">Status pemilihan</span>
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10"><?= icon('clock', 'h-5 w-5') ?></span>
                            </div>
                            <p class="mt-5 font-display text-2xl font-bold leading-tight"><?= esc($meta['label']) ?></p>
                            <p class="mt-2 text-sm leading-relaxed text-white/65"><?= esc(format_date_range($election['start_at'], $election['end_at'])) ?></p>
                            <div class="my-5 border-t border-white/15"></div>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-white/55">Pengumuman hasil</span><span class="text-right font-medium"><?= esc(format_date_id($election['result_publish_at'], true)) ?></span></div>
                                <div class="flex justify-between gap-4"><span class="text-white/55">Zona waktu</span><span class="font-medium"><?= esc($election['timezone']) ?></span></div>
                            </div>
                            <a href="<?= esc($primaryHref) ?>" class="btn btn-sun btn-block btn-sm mt-6"><?= esc($primaryLabel) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-mint/40"><?= icon('fingerprint', 'h-5 w-5') ?></span>
                        <p class="mt-4 font-display text-lg font-bold">Sudah memilih?</p>
                        <p class="mt-1 text-sm leading-relaxed text-ink/55">Cek kode bukti suara acak yang kamu simpan setelah mengirim pilihan.</p>
                        <a href="<?= base_url('bukti') ?>" class="link-underline mt-4 inline-flex items-center gap-1.5 text-sm">Cek bukti suara <?= icon('arrow-right', 'h-4 w-4') ?></a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section id="panduan" class="section-pad pt-16">
    <div class="shell">
        <div class="text-center">
            <span class="eyebrow"><?= icon('list', 'h-3.5 w-3.5') ?> Panduan</span>
            <h2 class="mx-auto mt-4 max-w-2xl text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl"><?= esc(landing_text('panduan_heading', 'Memilih itu mudah.')) ?></h2>
            <p class="mx-auto mt-3 max-w-xl text-base text-ink/60"><?= esc(landing_text('panduan_subtext', 'Empat langkah singkat, tanpa kertas, tanpa ribet. Pastikan nomor WhatsApp-mu aktif.')) ?></p>
        </div>

        <div class="relative mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="pointer-events-none absolute left-[12%] right-[12%] top-10 hidden border-t-2 border-dashed border-ink/15 xl:block" aria-hidden="true"></div>
            <?php foreach ($steps as $index => $step): ?>
                <div class="relative rounded-3xl border-2 border-ink/10 bg-paper p-6">
                    <div class="flex items-center justify-between">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl border-2 border-ink/10 <?= $step['color'] ?>"><?= icon($step['icon'], 'h-6 w-6') ?></span>
                        <span class="font-display text-4xl font-bold text-ink/10">0<?= $index + 1 ?></span>
                    </div>
                    <h3 class="mt-5 font-display text-lg font-bold"><?= esc($step['title']) ?></h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/55"><?= esc($step['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 flex flex-col items-start justify-between gap-4 rounded-3xl border-2 border-ink bg-mint/30 p-5 sm:flex-row sm:items-center sm:p-6">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink text-cream"><?= icon('lock', 'h-4 w-4') ?></span>
                <div>
                    <p class="font-display text-sm font-bold"><?= esc(landing_text('privacy_heading', 'Pilihanmu rahasia.')) ?></p>
                    <p class="mt-0.5 text-sm text-ink/60"><?= esc(landing_text('privacy_text', 'Data pemilih dan data suara disimpan terpisah. Tidak ada yang bisa melihat kandidat yang kamu pilih.')) ?></p>
                </div>
            </div>
            <a href="<?= base_url('aktivasi') ?>" class="btn btn-dark btn-sm shrink-0">Mulai aktivasi <?= icon('arrow-right', 'h-4 w-4') ?></a>
        </div>
    </div>
</section>

<?php if ($report !== null): ?>
    <section class="section-pad pt-0">
        <div class="shell">
            <div class="overflow-hidden rounded-5xl border-2 border-ink bg-paper p-7 shadow-hard sm:p-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="eyebrow-solid"><?= icon('chart', 'h-3.5 w-3.5') ?> Hasil resmi</span>
                        <h2 class="mt-4 text-3xl font-bold tracking-[-0.03em] sm:text-4xl"><?= $combined ? 'Nilai akhir sudah dihitung.' : 'Suara sudah dihitung.' ?></h2>
                        <p class="mt-2 text-sm text-ink/55">Partisipasi <?= percent($report['participation'], 1) ?> · <?= number_format($report['voted'], 0, ',', '.') ?> dari <?= number_format($report['eligible'], 0, ',', '.') ?> pemilih</p>
                    </div>
                    <a href="<?= esc(election_results_url($election)) ?>" class="btn btn-light btn-sm">Laporan lengkap <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
                <div class="mt-7 grid gap-3 sm:grid-cols-2">
                    <?php foreach (array_slice($report['candidates'], 0, 4) as $candidate): ?>
                        <div class="rounded-2xl border-2 <?= $candidate['is_leader'] ? 'border-ink bg-sun/25' : 'border-ink/10 bg-white/60' ?> p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-display font-semibold"><span class="mr-2 text-ink/35">0<?= (int) $candidate['candidate_number'] ?></span><?= esc($candidate['full_name']) ?></p>
                                <span class="font-display text-lg font-bold"><?= $combined ? esc($scoreNumber($candidate['final_score'])) : number_format($candidate['votes'], 0, ',', '.') ?></span>
                            </div>
                            <p class="mt-1 text-[10px] text-ink/40"><?= $combined ? 'Nilai akhir &middot; ' : '' ?><?= number_format($candidate['votes'], 0, ',', '.') ?> suara &middot; <?= percent($candidate['share'], 1) ?></p>
                            <div class="mt-3 progress"><div class="progress-bar" style="width: <?= esc((string) ($combined ? $candidate['final_score'] : $candidate['share'])) ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section id="faq" class="section-pad pt-0">
    <div class="shell">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1fr] lg:gap-14">
            <div>
                <div class="sticky top-28 space-y-5">
                    <div class="relative overflow-hidden rounded-4xl border-2 border-ink bg-ink p-6 text-cream shadow-hard sm:p-7">
                        <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-cobalt/40" aria-hidden="true"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full border border-cream/20 px-3 py-1 font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-cream/60"><?= icon('qr', 'h-3.5 w-3.5 text-sun') ?> Akses cepat</span>
                                <h2 class="mt-4 text-2xl font-bold leading-tight">Scan &amp; buka halaman resmi CAKRA.</h2>
                            </div>
                        </div>
                        <div class="relative mt-6 flex flex-col items-center gap-4 sm:flex-row">
                            <div class="qr-card shrink-0 !border-cream/20 !p-3">
                                <img src="<?= base_url('qr.svg') ?>?v=<?= substr(md5(base_url('/')), 0, 8) ?>" alt="QR code halaman resmi CAKRA 2026" class="h-40 w-40 rounded-xl bg-white">
                            </div>
                            <div class="w-full">
                                <p class="text-xs leading-relaxed text-cream/55">Satu QR code umum menuju halaman resmi. QR tidak memuat NIS, tanggal lahir, OTP, atau data pribadi.</p>
                                <div class="mt-4 rounded-xl border border-cream/15 bg-cream/5 p-2 pl-3" x-data="copyText()">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate font-mono text-[11px] text-cream/60"><?= esc(base_url('/')) ?></span>
                                        <button type="button" @click="copy('<?= esc(base_url('/'), 'js') ?>')" class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cream/10 text-cream transition hover:bg-sun hover:text-ink" aria-label="Salin tautan">
                                            <span x-show="!copied"><?= icon('copy', 'h-3.5 w-3.5') ?></span>
                                            <span x-show="copied" x-cloak><?= icon('check', 'h-3.5 w-3.5') ?></span>
                                        </button>
                                    </div>
                                    <p class="mt-1.5 text-[10px] text-mint" x-show="copied" x-cloak>Tautan disalin.</p>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                    <div class="rounded-4xl border-2 border-ink/10 bg-paper p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sun/50"><?= icon('whatsapp', 'h-5 w-5') ?></span>
                        <p class="mt-4 font-display text-lg font-bold">Butuh bantuan?</p>
                        <p class="mt-1 text-sm leading-relaxed text-ink/55">Hubungi panitia jika nomor WhatsApp berubah, OTP tidak masuk, atau akun tidak dapat diakses.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php $supportWa = app_setting('support_whatsapp', $profile['official_contact'] ?? ''); ?>
                            <?php if ($supportWa): ?>
                                <a href="https://wa.me/<?= esc(preg_replace('/\D+/', '', $supportWa)) ?>" target="_blank" rel="noopener" class="btn btn-mint btn-sm"><?= icon('whatsapp', 'h-4 w-4') ?> WhatsApp panitia</a>
                            <?php endif; ?>
                            <?php if (app_setting('support_email')): ?>
                                <a href="mailto:<?= esc(app_setting('support_email')) ?>" class="btn btn-light btn-sm"><?= icon('link', 'h-4 w-4') ?> Email</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <span class="eyebrow"><?= icon('info', 'h-3.5 w-3.5') ?> FAQ</span>
                <h2 class="mt-4 text-4xl font-bold leading-[1.02] tracking-[-0.03em] sm:text-5xl">Pertanyaan yang sering muncul.</h2>
                <div class="mt-7 space-y-3" x-data="{ open: 0 }">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="overflow-hidden rounded-2xl border-2 border-ink/10 bg-paper transition" :class="open === <?= $index ?> ? 'border-ink' : ''">
                            <button type="button" class="flex w-full items-center gap-4 px-5 py-4 text-left" @click="open = open === <?= $index ?> ? null : <?= $index ?>">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-cream font-display text-xs font-bold text-ink/50"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="flex-1 font-display text-[15px] font-semibold leading-snug"><?= esc($faq['question']) ?></span>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-ink/10 transition" :class="open === <?= $index ?> ? 'rotate-180 bg-ink text-cream' : ''"><?= icon('chevron-down', 'h-3.5 w-3.5') ?></span>
                            </button>
                            <div x-show="open === <?= $index ?>" x-transition.opacity x-cloak>
                                <p class="px-5 pb-5 pl-[4.25rem] text-sm leading-relaxed text-ink/60"><?= esc($faq['answer']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($faqs === []): ?>
                        <p class="rounded-2xl border-2 border-dashed border-ink/20 p-6 text-sm text-ink/55">FAQ belum tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shell pb-16">
    <div class="relative overflow-hidden rounded-5xl border-2 border-ink bg-coral p-8 text-white sm:p-12 lg:p-16">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border-[36px] border-white/10" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/2 h-40 w-40 -translate-x-1/2 translate-y-1/2 rounded-full bg-sun/70" aria-hidden="true"></div>
        <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_auto]">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border-2 border-white/25 px-4 py-1.5 font-display text-[11px] font-semibold uppercase tracking-[0.18em] text-white/75"><?= icon('sparkles', 'h-3.5 w-3.5') ?> Saatnya bersuara</span>
                <h2 class="mt-5 max-w-2xl text-4xl font-bold leading-[0.98] tracking-[-0.035em] sm:text-6xl">Suaramu menentukan arah sekolah.</h2>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-white/75">Jangan biarkan orang lain memilih untukmu. Luangkan satu menit, pilih dengan sadar, dan jadi bagian dari perubahan.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                <a href="<?= esc($primaryHref) ?>" class="btn btn-sun"><?= esc($primaryLabel) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a>
                <a href="#kandidat" class="btn btn-light">Lihat kandidat</a>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Alpine is loaded globally. No page-specific JavaScript required. -->
<?= $this->endSection() ?>