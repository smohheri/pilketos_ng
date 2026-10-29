<?php

namespace App\Models;

use CodeIgniter\Model;

class VoterAccountModel extends Model
{
    protected $table = 'voter_accounts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'student_id', 'account_status', 'activated_at', 'last_login_at',
    ];

    public function forStudent(int $studentId): ?array
    {
        return $this->where('student_id', $studentId)->first();
    }
}