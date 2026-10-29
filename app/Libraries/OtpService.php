<?php

namespace App\Libraries;

use App\Models\OtpChallengeModel;

class OtpService
{
    public const EXPIRY_MINUTES = 5;
    public const MAX_ATTEMPTS = 5;
    public const MAX_REQUESTS_PER_WINDOW = 3;
    public const WINDOW_MINUTES = 10;
    public const COOLDOWN_SECONDS = 60;

    protected OtpChallengeModel $challenges;
    protected WhatsAppService $whatsapp;

    public function __construct()
    {
        $this->challenges = new OtpChallengeModel();
        $this->whatsapp = new WhatsAppService();
    }

    /**
     * @return array{ok: bool, message: string, demo_otp?: string}
     */
    public function request(array $student, string $purpose, string $ip): array
    {
        $studentId = (int) $student['id'];
        $now = time();

        $latest = $this->challenges
            ->where('student_id', $studentId)
            ->where('purpose', $purpose)
            ->orderBy('id', 'DESC')
            ->first();

        if ($latest !== null) {
            $age = $now - strtotime((string) $latest['created_at']);
            if ($age < self::COOLDOWN_SECONDS) {
                return [
                    'ok' => false,
                    'message' => 'Tunggu ' . (self::COOLDOWN_SECONDS - $age) . ' detik sebelum meminta kode baru.',
                ];
            }
        }

        $windowStart = date('Y-m-d H:i:s', $now - (self::WINDOW_MINUTES * 60));
        $recent = $this->challenges
            ->where('student_id', $studentId)
            ->where('purpose', $purpose)
            ->where('created_at >=', $windowStart)
            ->countAllResults();

        if ($recent >= self::MAX_REQUESTS_PER_WINDOW) {
            return [
                'ok' => false,
                'message' => 'Batas permintaan OTP tercapai. Coba lagi dalam beberapa menit.',
            ];
        }

        $recentIp = $this->challenges
            ->where('requested_ip', $ip)
            ->where('created_at >=', $windowStart)
            ->countAllResults();
        if ($recentIp >= 15) {
            return [
                'ok' => false,
                'message' => 'Terlalu banyak permintaan dari jaringan ini. Coba lagi nanti.',
            ];
        }

        $this->challenges
            ->where('student_id', $studentId)
            ->where('purpose', $purpose)
            ->where('consumed_at', null)
            ->set('consumed_at', date('Y-m-d H:i:s'))
            ->update();

        $otp = (string) random_int(100000, 999999);
        $this->challenges->insert([
            'student_id' => $studentId,
            'purpose' => $purpose,
            'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', $now + (self::EXPIRY_MINUTES * 60)),
            'attempt_count' => 0,
            'requested_ip' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $message = "*CAKRA 2026*\n\nKode OTP Anda: *{$otp}*\n\nBerlaku "
            . self::EXPIRY_MINUTES . " menit dan hanya dapat digunakan sekali. "
            . "Jangan bagikan kode ini kepada siapa pun, termasuk panitia.";

        $sent = $this->whatsapp->send((string) $student['whatsapp_number'], $message);
        $demoMode = filter_var(env('cakra.demoMode', false), FILTER_VALIDATE_BOOLEAN);

        if ($sent) {
            return [
                'ok' => true,
                'message' => 'Kode OTP telah dikirim ke WhatsApp yang terdaftar.',
            ];
        }

        if ($demoMode) {
            return [
                'ok' => true,
                'message' => 'Mode demo aktif: OpenWA tidak terhubung, gunakan kode di bawah.',
                'demo_otp' => $otp,
            ];
        }

        $this->invalidateLatest($studentId, $purpose);

        return [
            'ok' => false,
            'message' => 'Layanan WhatsApp sedang tidak tersedia. Hubungi panitia untuk bantuan.',
        ];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function verify(int $studentId, string $purpose, string $code): array
    {
        $challenge = $this->challenges->latestActive($studentId, $purpose);

        if ($challenge === null) {
            return ['ok' => false, 'message' => 'Kode OTP tidak valid atau sudah digunakan. Minta kode baru.'];
        }

        if (strtotime((string) $challenge['expires_at']) < time()) {
            $this->challenges->update($challenge['id'], ['consumed_at' => date('Y-m-d H:i:s')]);

            return ['ok' => false, 'message' => 'Kode OTP sudah kedaluwarsa. Minta kode baru.'];
        }

        if ((int) $challenge['attempt_count'] >= self::MAX_ATTEMPTS) {
            $this->challenges->update($challenge['id'], ['consumed_at' => date('Y-m-d H:i:s')]);

            return ['ok' => false, 'message' => 'Terlalu banyak percobaan. Minta kode OTP baru.'];
        }

        $this->challenges->update($challenge['id'], [
            'attempt_count' => (int) $challenge['attempt_count'] + 1,
        ]);

        if (! password_verify(trim($code), (string) $challenge['otp_hash'])) {
            return ['ok' => false, 'message' => 'Kode OTP salah atau sudah kedaluwarsa.'];
        }

        $this->challenges->update($challenge['id'], [
            'consumed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => true, 'message' => 'Verifikasi berhasil.'];
    }

    protected function invalidateLatest(int $studentId, string $purpose): void
    {
        $this->challenges
            ->where('student_id', $studentId)
            ->where('purpose', $purpose)
            ->where('consumed_at', null)
            ->set('consumed_at', date('Y-m-d H:i:s'))
            ->update();
    }
}