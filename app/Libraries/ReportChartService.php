<?php

namespace App\Libraries;

class ReportChartService
{
    private array $palette = ['#F45B4F', '#FFC93C', '#2F5BFF', '#7BDCB5', '#C9B6FF', '#A8D8FF'];

    public function candidateBars(array $candidates, string $mode = 'vote_only'): string
    {
        $combined = $mode === 'combined';
        $width = 1200;
        $rowHeight = 112;
        $top = 42;
        $bottom = 30;
        $height = $top + max(1, count($candidates)) * $rowHeight + $bottom;
        $image = $this->canvas($width, $height);

        $font = $this->font();
        $bold = $this->font(true);
        $maxVotes = 1;
        foreach ($candidates as $candidate) {
            $maxVotes = max($maxVotes, (int) $candidate['votes']);
        }
        $maxValue = $combined ? 100.0 : (float) $maxVotes;

        if ($candidates === []) {
            imagettftext($image, 22, 0, 42, 84, $this->color($image, '#101828'), $font, 'Belum ada kandidat.');
        }

        foreach ($candidates as $index => $candidate) {
            $y = $top + $index * $rowHeight;
            $color = $this->color($image, $this->palette[$index % count($this->palette)]);
            $track = $this->color($image, '#EEE7DA');
            $ink = $this->color($image, '#101828');
            $muted = $this->color($image, '#6B7280');

            $number = str_pad((string) $candidate['candidate_number'], 2, '0', STR_PAD_LEFT);
            $name = $this->fitText($font, 22, $number . '  ' . $candidate['full_name'], 355);
            imagettftext($image, 22, 0, 36, $y + 34, $ink, $font, $name);
            imagettftext($image, 18, 0, 36, $y + 64, $muted, $font, (string) $candidate['class_name']);

            $barX = 400;
            $barY = $y + 18;
            $barWidth = 640;
            $barHeight = 34;
            $this->roundedRect($image, $barX, $barY, $barX + $barWidth, $barY + $barHeight, 12, $track);

            $value = $combined ? (float) ($candidate['final_score'] ?? 0) : (float) $candidate['votes'];
            $fillWidth = (int) round(($value / max(0.01, $maxValue)) * $barWidth);
            if ($value > 0) {
                $fillWidth = max(12, $fillWidth);
                $this->roundedRect($image, $barX, $barY, $barX + min($barWidth, $fillWidth), $barY + $barHeight, 12, $color);
            }

            if ($combined) {
                $scoreText = number_format((float) $candidate['final_score'], 2, ',', '.');
                $votesText = 'Nilai akhir · ' . number_format((int) $candidate['votes'], 0, ',', '.')
                    . ' suara (' . number_format((float) $candidate['share'], 1, ',', '.') . '%)';
                $assessmentText = 'Penilaian ' . number_format((float) $candidate['assessment_score'], 2, ',', '.')
                    . ' · Peringkat ' . (int) $candidate['rank'];
                imagettftext($image, 23, 0, 1164 - $this->textWidth($bold, 23, $scoreText), $y + 36, $ink, $bold, $scoreText);
                imagettftext($image, 14, 0, 1164 - $this->textWidth($font, 14, $votesText), $y + 62, $muted, $font, $votesText);
                imagettftext($image, 14, 0, 1164 - $this->textWidth($font, 14, $assessmentText), $y + 84, $muted, $font, $assessmentText);
            } else {
                $votesText = number_format((int) $candidate['votes'], 0, ',', '.') . ' suara';
                $shareText = number_format((float) $candidate['share'], 1, ',', '.') . '%';
                imagettftext($image, 21, 0, 1164 - $this->textWidth($bold, 21, $votesText), $y + 35, $ink, $bold, $votesText);
                imagettftext($image, 18, 0, 1164 - $this->textWidth($font, 18, $shareText), $y + 63, $muted, $font, $shareText);
            }
        }

        return $this->dataUri($image);
    }

    public function participationDonut(float $participation, int $voted, int $notVoted): string
    {
        $size = 760;
        $image = $this->canvas($size, $size);
        $center = (int) ($size / 2);
        $outer = 274;
        $inner = 166;
        $accent = $this->color($image, '#7BDCB5');
        $track = $this->color($image, '#E9E2D5');
        $ink = $this->color($image, '#101828');
        $muted = $this->color($image, '#6B7280');
        $paper = $this->color($image, '#FFFCF5');

        imagefilledellipse($image, $center, $center, $outer * 2, $outer * 2, $track);

        $angle = 360 * max(0, min(100, $participation)) / 100;
        if ($angle >= 360) {
            imagefilledellipse($image, $center, $center, $outer * 2, $outer * 2, $accent);
        } elseif ($angle > 0) {
            imagefilledarc($image, $center, $center, $outer * 2, $outer * 2, -90, (int) round($angle), $accent, IMG_ARC_PIE);
        }

        imagefilledellipse($image, $center, $center, $inner * 2, $inner * 2, $paper);

        $value = number_format($participation, 1, ',', '.') . '%';
        $valueBox = imagettfbbox(42, 0, $this->font(true), $value);
        imagettftext($image, 42, 0, $center - (int) (($valueBox[2] - $valueBox[0]) / 2), $center + 4, $ink, $this->font(true), $value);

        $label = 'partisipasi';
        $labelBox = imagettfbbox(19, 0, $this->font(), $label);
        imagettftext($image, 19, 0, $center - (int) (($labelBox[2] - $labelBox[0]) / 2), $center + 42, $muted, $this->font(), $label);

        $summary = $voted . ' memilih  ·  ' . $notVoted . ' belum';
        $summaryBox = imagettfbbox(16, 0, $this->font(), $summary);
        imagettftext($image, 16, 0, $center - (int) (($summaryBox[2] - $summaryBox[0]) / 2), $center + 76, $muted, $this->font(), $summary);

        return $this->dataUri($image);
    }

    public function classBars(array $classes): string
    {
        $width = 1200;
        $rowHeight = 88;
        $top = 24;
        $bottom = 24;
        $height = $top + max(1, count($classes)) * $rowHeight + $bottom;
        $image = $this->canvas($width, $height);

        $font = $this->font();
        $bold = $this->font(true);
        $ink = $this->color($image, '#101828');
        $muted = $this->color($image, '#6B7280');
        $track = $this->color($image, '#EEE7DA');
        $accent = $this->color($image, '#2F5BFF');

        if ($classes === []) {
            imagettftext($image, 22, 0, 36, 70, $ink, $font, 'Belum ada data kelas.');
        }

        foreach ($classes as $index => $item) {
            $y = $top + $index * $rowHeight;
            $eligible = (int) $item['eligible'];
            $voted = (int) $item['voted'];
            $rate = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0;

            imagettftext($image, 21, 0, 36, $y + 38, $ink, $bold, (string) $item['class_name']);

            $barX = 220;
            $barY = $y + 18;
            $barWidth = 790;
            $barHeight = 32;
            $this->roundedRect($image, $barX, $barY, $barX + $barWidth, $barY + $barHeight, 12, $track);
            if ($rate > 0) {
                $this->roundedRect($image, $barX, $barY, $barX + max(12, (int) round(($rate / 100) * $barWidth)), $barY + $barHeight, 12, $accent);
            }

            imagettftext($image, 18, 0, 1060, $y + 38, $ink, $bold, number_format($rate, 1, ',', '.') . '%');
            imagettftext($image, 15, 0, 220, $y + 66, $muted, $font, $voted . ' dari ' . $eligible . ' pemilih');
        }

        return $this->dataUri($image);
    }

    private function canvas(int $width, int $height): \GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, $this->color($image, '#FFFCF5'));

        return $image;
    }

    private function color(\GdImage $image, string $hex): int
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return imagecolorallocate($image, $r, $g, $b);
    }

    private function font(bool $bold = false): string
    {
        return dirname(__DIR__, 2) . '/vendor/dompdf/dompdf/lib/fonts/DejaVuSans' . ($bold ? '-Bold' : '') . '.ttf';
    }

    private function fitText(string $font, int $size, string $text, int $maxWidth): string
    {
        if ($this->textWidth($font, $size, $text) <= $maxWidth) {
            return $text;
        }

        $suffix = '…';
        $result = $text;
        while (mb_strlen($result) > 0 && $this->textWidth($font, $size, $result . $suffix) > $maxWidth) {
            $result = mb_substr($result, 0, -1);
        }

        return rtrim($result) . $suffix;
    }

    private function textWidth(string $font, int $size, string $text): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return (int) abs($box[2] - $box[0]);
    }

    private function roundedRect(\GdImage $image, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        $radius = max(0, min($radius, (int) floor(min($x2 - $x1, $y2 - $y1) / 2)));
        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    private function dataUri(\GdImage $image): string
    {
        ob_start();
        imagepng($image);
        $data = (string) ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($data);
    }
}
