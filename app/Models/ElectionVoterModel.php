<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionVoterModel extends Model
{
    protected $table = 'election_voters';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'election_id', 'student_id', 'eligibility_status', 'has_voted', 'voted_at',
    ];

    public function forStudent(int $electionId, int $studentId): ?array
    {
        return $this->where('election_id', $electionId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function counts(int $electionId): array
    {
        $row = $this->select('COUNT(*) AS eligible, COALESCE(SUM(has_voted), 0) AS voted', false)
            ->where('election_id', $electionId)
            ->where('eligibility_status', 'eligible')
            ->first();

        $eligible = (int) ($row['eligible'] ?? 0);
        $voted = (int) ($row['voted'] ?? 0);

        return [
            'eligible' => $eligible,
            'voted' => $voted,
            'not_voted' => max(0, $eligible - $voted),
            'participation' => $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0,
        ];
    }
}