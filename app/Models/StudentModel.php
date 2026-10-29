<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nis', 'full_name', 'date_of_birth', 'class_name', 'whatsapp_number', 'is_active',
    ];

    public function findByNis(string $nis): ?array
    {
        return $this->where('nis', trim($nis))->first();
    }

    public function findByWhatsapp(string $number): ?array
    {
        return $this->where('whatsapp_number', $number)->where('is_active', 1)->first();
    }

    public function search(string $keyword = '', ?int $active = null): self
    {
        if ($keyword !== '') {
            $this->groupStart()
                ->like('nis', $keyword)
                ->orLike('full_name', $keyword)
                ->orLike('class_name', $keyword)
                ->groupEnd();
        }
        if ($active !== null) {
            $this->where('is_active', $active);
        }

        return $this->orderBy('class_name', 'ASC')->orderBy('full_name', 'ASC');
    }

    public function activeCount(): int
    {
        return $this->where('is_active', 1)->countAllResults();
    }
}