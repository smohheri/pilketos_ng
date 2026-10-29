<?php

if (! function_exists('icon')) {
    function icon(string $name, string $class = 'w-5 h-5'): string
    {
        $icons = [
            'home' => '<path d="M3 10.6 12 3l9 7.6"/><path d="M5.5 9.5V21h13V9.5"/><path d="M9.5 21v-6h5v6"/>',
            'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.6-3.3 2.7-5.2 5.5-5.2s4.9 1.9 5.5 5.2"/><path d="M15.5 5.2a3 3 0 0 1 0 5.6"/><path d="M17.8 14.9c1.6.7 2.5 2.3 2.9 5.1"/>',
            'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 21c.7-4 3.1-6.2 7-6.2s6.3 2.2 7 6.2"/>',
            'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17"/><path d="M8 14h2M14 14h2M8 17.5h2"/>',
            'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1-2.8 2.8-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21h-4v-.2a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1-2.8-2.8.1-.1a1.6 1.6 0 0 0 .3-1.8A1.6 1.6 0 0 0 3.2 14H3v-4h.2a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1 2.8-2.8.1.1A1.6 1.6 0 0 0 9 4.7 1.6 1.6 0 0 0 10 3.2V3h4v.2a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1 2.8 2.8-.1.1a1.6 1.6 0 0 0-.3 1.8 1.6 1.6 0 0 0 1.5 1h.2v4h-.2a1.6 1.6 0 0 0-1.5 1Z"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
            'check' => '<path d="m4.5 12.5 5 5 10-11"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7L16.5 9"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
            'chevron-left' => '<path d="m15 6-6 6 6 6"/>',
            'arrow-right' => '<path d="M4 12h16M14 6l6 6-6 6"/>',
            'arrow-left' => '<path d="M20 12H4M10 6l-6 6 6 6"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'edit' => '<path d="M4 20h4l11-11-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/>',
            'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/>',
            'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
            'shield' => '<path d="M12 3 5 6v5c0 4.5 2.8 8 7 10 4.2-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
            'lock' => '<rect x="4.5" y="10" width="15" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14.5v2.5"/>',
            'sparkles' => '<path d="M12 3.5 13.8 9l5.2 1.8-5.2 1.9-1.8 5.3-1.9-5.3L5 10.8 10.1 9 12 3.5Z"/><path d="M19 3v3M17.5 4.5h3M5 17v3M3.5 18.5h3"/>',
            'ballot' => '<path d="M4 11h16v9H4z"/><path d="m7.5 11 3-7h4l3 7"/><path d="M9 15h6M10 11V8h4v3"/>',
            'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
            'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
            'qr' => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><path d="M14 14h2v2h-2zM18 14h2v6h-2M14 18h2v2h-2"/>',
            'school' => '<path d="m3 9 9-5 9 5"/><path d="M5 9v11h14V9M3 20h18"/><path d="M9 20v-5h6v5M12 4v3"/>',
            'upload' => '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>',
            'download' => '<path d="M12 4v12M7 11l5 5 5-5"/><path d="M4 19h16"/>',
            'file-text' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
            'megaphone' => '<path d="M4 10v4l2 1 2 5h3l-2-5 12-4V5L6 9H4Z"/><path d="M20 8v8"/>',
            'eye' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
            'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l2 2M14 9l2 2"/>',
            'external' => '<path d="M14 4h6v6M20 4l-8.5 8.5"/><path d="M18 14v5a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 19V7.5A1.5 1.5 0 0 1 5 6h5"/>',
            'list' => '<path d="M9 6h12M9 12h12M9 18h12"/><circle cx="3.5" cy="6" r=".8" fill="currentColor"/><circle cx="3.5" cy="12" r=".8" fill="currentColor"/><circle cx="3.5" cy="18" r=".8" fill="currentColor"/>',
            'alert' => '<path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
            'refresh' => '<path d="M20 7v5h-5"/><path d="M4 17v-5h5"/><path d="M6.1 8A7 7 0 0 1 18.8 7M17.9 16A7 7 0 0 1 5.2 17"/>',
            'link' => '<path d="M10 13.5a4 4 0 0 0 5.6 0l2.8-2.8a4 4 0 0 0-5.6-5.6L11.4 6.5"/><path d="M14 10.5a4 4 0 0 0-5.6 0l-2.8 2.8a4 4 0 0 0 5.6 5.6l1.4-1.4"/>',
            'copy' => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"/>',
            'fingerprint' => '<path d="M12 3a9 9 0 0 1 9 9M3 12a9 9 0 0 1 9-9"/><path d="M6 12a6 6 0 0 1 6-6 6 6 0 0 1 6 6c0 4-1 7-2 9"/><path d="M9 12a3 3 0 0 1 6 0c0 3-.5 5-1.5 7"/><path d="M12 12c0 3 0 5-.5 7"/>',
            'star' => '<path d="m12 3 2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.8L12 3Z"/>',
            'play' => '<path d="M8 5v14l11-7-11-7Z"/>',
            'https' => '<path d="M12 2 4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5l-8-3Z"/><path d="M9.5 11.5 8 10M14.5 11.5 16 10M8.5 15c2 1.5 5 1.5 7 0"/>',
        ];

        if ($name === 'whatsapp') {
            return '<svg viewBox="0 0 24 24" class="' . esc($class) . '" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.4 0-.5l-.8-1.7c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.8 4.3 3.8 1.6.6 2.2.7 3 .6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.2-.2-.4-.2Z"/></svg>';
        }

        $path = $icons[$name] ?? $icons['info'];

        return '<svg viewBox="0 0 24 24" class="' . esc($class) . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : '?';
    }
}

if (! function_exists('avatar_tone')) {
    function avatar_tone(string $seed): string
    {
        $tones = [
            'bg-coral text-ink',
            'bg-sun text-ink',
            'bg-mint text-ink',
            'bg-cobalt text-cream',
            'bg-ink text-cream',
            'bg-lilac text-ink',
        ];

        $index = abs(crc32($seed)) % count($tones);

        return $tones[$index];
    }
}

if (! function_exists('role_label')) {
    function role_label(?string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'panitia' => 'Panitia',
            default => 'Pengguna',
        };
    }
}

if (! function_exists('active_if')) {
    function active_if(bool $condition, string $active = 'is-active', string $inactive = ''): string
    {
        return $condition ? $active : $inactive;
    }
}

if (! function_exists('mask_phone')) {
    function mask_phone(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if (strlen($digits) < 8) {
            return '•••• ••••';
        }

        return '+' . substr($digits, 0, 2) . ' ' . substr($digits, 2, 3) . ' •••• ' . substr($digits, -3);
    }
}

if (! function_exists('safe_url')) {
    function safe_url(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '#';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    }
}

if (! function_exists('percent')) {
    function percent(float|int $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.') . '%';
    }
}

if (! function_exists('phase_meta')) {
    function phase_meta(string $phase): array
    {
        return match ($phase) {
            'ongoing' => ['label' => 'Sedang berlangsung', 'class' => 'badge-live', 'dot' => 'bg-mint'],
            'scheduled' => ['label' => 'Belum dimulai', 'class' => 'badge-warm', 'dot' => 'bg-sun'],
            'closed' => ['label' => 'Telah selesai', 'class' => 'badge-neutral', 'dot' => 'bg-ink/40'],
            'results' => ['label' => 'Hasil tersedia', 'class' => 'badge-cool', 'dot' => 'bg-cobalt'],
            default => ['label' => 'Draft', 'class' => 'badge-neutral', 'dot' => 'bg-ink/30'],
        };
    }
}
if (! function_exists('app_setting')) {
    function app_setting(string $key, ?string $default = null): ?string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = (new \App\Models\SystemSettingModel())->allAsArray();
        }

        return $cache[$key] ?? $default;
    }
}

if (! function_exists('normalize_wa_number')) {
    function normalize_wa_number(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        } elseif (str_starts_with($digits, '620')) {
            $digits = '62' . substr($digits, 3);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^62[0-9]{8,13}$/', $digits) ? $digits : '';
    }
}

if (! function_exists('media_url')) {
    function media_url(?string $path, string $fallback = ''): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return $fallback;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return base_url(ltrim($path, '/'));
    }
}

if (! function_exists('school_logo_path')) {
    function school_logo_path(): string
    {
        static $path = null;
        if ($path === null) {
            $profile = (new \App\Models\SchoolProfileModel())->active();
            $path = trim((string) ($profile['logo_path'] ?? ''));
            if ($path === '') {
                $path = 'assets/img/logo-cakra.svg';
            }
        }

        return $path;
    }
}

if (! function_exists('school_logo_url')) {
    function school_logo_url(): string
    {
        return media_url(school_logo_path());
    }
}

if (! function_exists('school_logo_mime')) {
    function school_logo_mime(): string
    {
        $extension = strtolower((string) pathinfo((string) parse_url(school_logo_path(), PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => '',
        };
    }
}
