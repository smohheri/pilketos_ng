<?php

namespace App\Libraries;

use App\Models\SystemSettingModel;
use Config\Services;

class WhatsAppService
{
    protected array $config;

    public function __construct()
    {
        $stored = (new SystemSettingModel())->allAsArray();
        $read = static function (string $key, string $envKey, mixed $default = '') use ($stored): mixed {
            $settingKey = 'openwa_' . $key;

            return array_key_exists($settingKey, $stored)
                ? $stored[$settingKey]
                : env($envKey, $default);
        };

        $payloadStyle = (string) $read('payload_style', 'openwa.payloadStyle', 'args');
        $sessionId = trim((string) $read('session_id', 'openwa.sessionId', ''));

        $this->config = [
            'enabled' => filter_var($read('enabled', 'openwa.enabled', false), FILTER_VALIDATE_BOOLEAN),
            'base_url' => rtrim(trim((string) $read('base_url', 'openwa.baseURL', '')), '/'),
            'session_id' => $sessionId,
            'session_mode' => $this->isSessionUuid($sessionId),
            'api_key' => trim((string) $read('api_key', 'openwa.apiKey', '')),
            'user' => trim((string) $read('user', 'openwa.user', '')),
            'pass_auth' => (string) $read('pass_auth', 'openwa.passAuth', ''),
            'endpoint' => trim((string) $read('endpoint', 'openwa.endpoint', '/sendText')) ?: '/sendText',
            'payload_style' => $payloadStyle === 'flat' ? 'flat' : 'args',
            'verify_ssl' => filter_var($read('verify_ssl', 'openwa.verifySSL', true), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function config(): array
    {
        return $this->config;
    }

    public function enabled(): bool
    {
        return $this->config['enabled'] && $this->config['base_url'] !== '';
    }

    /**
     * Sends a WhatsApp text through the self-hosted OpenWA gateway.
     * Returns true when the gateway accepted the request.
     */
    public function send(string $number, string $message): bool
    {
        if (! $this->enabled()) {
            log_message('info', 'OpenWA disabled; WhatsApp message not sent.');

            return false;
        }

        if (trim($number) === '') {
            return false;
        }

        $chatId = $this->chatId($number);

        if ($this->config['session_mode']) {
            $url = $this->apiBase() . '/sessions/' . rawurlencode($this->config['session_id']) . '/messages/send-text';
            $payload = [
                'chatId' => $chatId,
                'text' => $message,
            ];
        } else {
            $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($this->config['endpoint'], '/');
            $payload = $this->config['payload_style'] === 'flat'
                ? ['to' => $chatId, 'message' => $message]
                : ['args' => ['to' => $chatId, 'content' => $message]];
        }

        try {
            $client = Services::curlrequest($this->requestOptions(), null, null, false);
            $response = $client->post($url, ['json' => $payload]);
            $status = $response->getStatusCode();
            $ok = $status >= 200 && $status < 300;

            if (! $ok) {
                log_message('error', 'OpenWA send failed with status {status}: {body}', [
                    'status' => $status,
                    'body' => $this->shortBody((string) $response->getBody()),
                ]);
            }

            return $ok;
        } catch (\Throwable $e) {
            log_message('error', 'OpenWA send exception: {message}', ['message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if ($this->config['base_url'] === '') {
            return ['ok' => false, 'message' => 'URL server OpenWA belum diisi.'];
        }

        if (! $this->config['session_mode']) {
            return ['ok' => false, 'message' => 'Isi Session ID berformat UUID untuk menguji koneksi OpenWA.'];
        }

        $url = $this->apiBase() . '/sessions/' . rawurlencode($this->config['session_id']);

        try {
            $client = Services::curlrequest($this->requestOptions(), null, null, false);
            $response = $client->get($url);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($status < 200 || $status >= 300) {
                return [
                    'ok' => false,
                    'message' => 'OpenWA menolak koneksi (HTTP ' . $status . '). ' . $this->shortBody($body),
                ];
            }

            $data = json_decode($body, true);
            $state = is_array($data) ? (string) ($data['status'] ?? $data['state'] ?? '') : '';
            $suffix = $state !== '' ? ' Status sesi: ' . $state . '.' : '';
            $message = 'Terhubung ke OpenWA (HTTP ' . $status . ').' . $suffix;

            if ($state !== '' && $state !== 'ready') {
                $message .= ' Sesi belum siap mengirim pesan.';
            }

            return ['ok' => true, 'message' => $message];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Tidak dapat terhubung ke OpenWA: ' . $e->getMessage()];
        }
    }

    protected function isSessionUuid(string $sessionId): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $sessionId) === 1;
    }

    protected function apiBase(): string
    {
        $base = rtrim($this->config['base_url'], '/');

        return preg_match('#/api$#i', $base) ? $base : $base . '/api';
    }

    protected function chatId(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits . '@c.us';
    }

    protected function requestOptions(): array
    {
        $options = [
            'timeout' => 12,
            'connect_timeout' => 5,
            'http_errors' => false,
            'headers' => ['Accept' => 'application/json'],
        ];

        if ($this->config['api_key'] !== '') {
            $options['headers']['X-API-Key'] = $this->config['api_key'];
            $options['headers']['api_key'] = $this->config['api_key'];
        }

        if ($this->config['user'] !== '' || $this->config['pass_auth'] !== '') {
            $options['headers']['Authorization'] = 'Basic '
                . base64_encode($this->config['user'] . ':' . $this->config['pass_auth']);
        }

        if (! $this->config['verify_ssl']) {
            $options['verify'] = false;
        } else {
            $caBundle = WRITEPATH . 'certs/cacert.pem';
            if (is_file($caBundle)) {
                $options['verify'] = $caBundle;
            }
        }

        return $options;
    }

    protected function shortBody(string $body): string
    {
        $body = trim(preg_replace('/\s+/', ' ', $body) ?? '');

        return mb_substr($body, 0, 180);
    }
}
