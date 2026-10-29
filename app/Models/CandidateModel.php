<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateModel extends Model
{
    protected $table = 'candidates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'election_id', 'candidate_number', 'full_name', 'class_name', 'photo_path',
        'tagline', 'vision', 'mission', 'program_description', 'is_active',
    ];

    public function forElection(int $electionId, bool $activeOnly = true): array
    {
        $builder = $this->where('election_id', $electionId);
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->orderBy('candidate_number', 'ASC')->findAll();
    }

    public function findForElection(int $id, int $electionId): ?array
    {
        return $this->where('id', $id)->where('election_id', $electionId)->first();
    }
}