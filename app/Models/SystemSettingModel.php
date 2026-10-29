<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['setting_key', 'setting_value', 'updated_by'];

    public function value(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();

        return $row['setting_value'] ?? $default;
    }

    public function put(string $key, ?string $value, ?int $userId = null): void
    {
        $row = $this->where('setting_key', $key)->first();
        if ($row === null) {
            $this->insert([
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_by' => $userId,
            ]);

            return;
        }

        $this->update($row['id'], [
            'setting_value' => $value,
            'updated_by' => $userId,
        ]);
    }

    public function allAsArray(): array
    {
        $settings = [];
        foreach ($this->findAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}