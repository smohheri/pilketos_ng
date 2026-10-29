<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpChallengeModel extends Model
{
    protected $table = 'otp_challenges';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'student_id', 'purpose', 'otp_hash', 'expires_at', 'attempt_count',
        'consumed_at', 'requested_ip', 'created_at',
    ];

    public function latestActive(int $studentId, string $purpose): ?array
    {
        return $this->where('student_id', $studentId)
            ->where('purpose', $purpose)
            ->where('consumed_at', null)
            ->orderBy('id', 'DESC')
            ->first();
    }
}