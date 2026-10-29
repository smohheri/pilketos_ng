<?php

namespace App\Models;

use CodeIgniter\Model;

class AssessmentCriterionModel extends Model
{
    protected $table = 'assessment_criteria';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'election_id', 'name', 'description', 'weight', 'max_score', 'sort_order', 'is_active',
    ];

    public function forElection(int $electionId, bool $activeOnly = true): array
    {
        $builder = $this->where('election_id', $electionId);
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function forElectionAndId(int $id, int $electionId): ?array
    {
        return $this->where('id', $id)->where('election_id', $electionId)->first();
    }

    public function nextSortOrder(int $electionId): int
    {
        $last = $this->where('election_id', $electionId)
            ->orderBy('sort_order', 'DESC')
            ->first();

        return $last === null ? 1 : ((int) $last['sort_order'] + 1);
    }
}
