<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionScheduleModel extends Model
{
    protected $table = 'election_schedules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'election_id', 'title', 'description', 'start_at', 'end_at', 'sort_order',
    ];

    public function forElection(int $electionId): array
    {
        return $this->where('election_id', $electionId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('start_at', 'ASC')
            ->findAll();
    }
}