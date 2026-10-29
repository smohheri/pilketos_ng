<?php

namespace App\Models;

use CodeIgniter\Model;

class VoteModel extends Model
{
    protected $table = 'votes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'election_id', 'candidate_id', 'receipt_code_hash', 'cast_at', 'created_at',
    ];

    public function countsByCandidate(int $electionId): array
    {
        return $this->select('candidate_id, COUNT(*) AS total', false)
            ->where('election_id', $electionId)
            ->groupBy('candidate_id')
            ->findAll();
    }

    public function total(int $electionId): int
    {
        return $this->where('election_id', $electionId)->countAllResults();
    }

    public function findByReceiptHash(string $hash): ?array
    {
        return $this->where('receipt_code_hash', $hash)->first();
    }
}