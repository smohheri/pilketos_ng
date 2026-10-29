<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolProfileModel extends Model
{
    protected $table = 'school_profiles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'school_name', 'short_name', 'logo_path', 'description', 'vision', 'mission',
        'official_website', 'official_contact', 'address', 'hero_image_path',
        'footer_text', 'updated_by',
    ];

    public function active(): ?array
    {
        return $this->orderBy('id', 'ASC')->first();
    }
}