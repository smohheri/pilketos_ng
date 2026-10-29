<?php

if (! function_exists('format_date_id')) {
    function format_date_id(string|int|DateTimeInterface|null $value, bool $withTime = false): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = $value instanceof DateTimeInterface ? $value->getTimestamp() : strtotime((string) $value);
        if ($timestamp === false) {
            return '-';
        }

        $months = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        $text = $days[(int) date('w', $timestamp)] . ', ' . date('j', $timestamp) . ' '
            . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);

        if ($withTime) {
            $text .= ' • ' . date('H.i', $timestamp);
        }

        return $text;
    }
}

if (! function_exists('format_time_id')) {
    function format_time_id(string|int|DateTimeInterface|null $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        $timestamp = $value instanceof DateTimeInterface ? $value->getTimestamp() : strtotime((string) $value);

        return $timestamp === false ? '-' : date('H.i', $timestamp);
    }
}

if (! function_exists('format_date_range')) {
    function format_date_range(?string $start, ?string $end): string
    {
        if ($start === null || $start === '') {
            return '-';
        }
        if ($end === null || $end === '') {
            return format_date_id($start, true);
        }

        $startDay = date('j M Y', strtotime($start));
        $endDay = date('j M Y', strtotime($end));
        if ($startDay === $endDay) {
            return format_date_id($start) . ', ' . format_time_id($start) . '–' . format_time_id($end);
        }

        return format_date_id($start, true) . ' — ' . format_date_id($end, true);
    }
}

if (! function_exists('excerpt')) {
    function excerpt(?string $text, int $limit = 140): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');
        if (mb_strlen($plain) <= $limit) {
            return $plain;
        }

        return mb_substr($plain, 0, $limit - 1) . '…';
    }
}

if (! function_exists('lines')) {
    function lines(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
    }
}

if (! function_exists('meta_description')) {
    function meta_description(?string $text, int $limit = 160): string
    {
        return excerpt($text, $limit);
    }
}