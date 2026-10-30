<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddElectionEnableToggle extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('elections', [
            'is_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 1,
                'after' => 'status',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('elections', 'is_enabled');
    }
}