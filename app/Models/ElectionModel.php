<?php

namespace App\Models;

use CodeIgniter\Model;

class ElectionModel extends Model
{
    protected $table = 'elections';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'type', 'slug', 'title', 'description', 'start_at', 'end_at', 'result_publish_at',
        'status', 'timezone', 'result_mode', 'vote_weight', 'assessment_weight', 'created_by',
    ];

    public function publicElections(): array
    {
        return $this->where('status !=', 'draft')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function publicBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->where('status !=', 'draft')->first();
    }

    public function defaultPublic(): ?array
    {
        $election = $this->where('type', 'osis')
            ->where('status !=', 'draft')
            ->orderBy('id', 'ASC')
            ->first();

        return $election ?? $this->where('status !=', 'draft')->orderBy('id', 'ASC')->first();
    }

    public function current(): ?array
    {
        return $this->defaultPublic() ?? $this->where('type', 'osis')->first() ?? $this->orderBy('id', 'ASC')->first();
    }
}