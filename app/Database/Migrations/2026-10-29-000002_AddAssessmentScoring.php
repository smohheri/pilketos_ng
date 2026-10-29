<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssessmentScoring extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('elections', [
            'result_mode' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'vote_only',
            ],
            'vote_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 50.00,
            ],
            'assessment_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 50.00,
            ],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'election_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'weight' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 1.00],
            'max_score' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 100.00],
            'sort_order' => ['type' => 'INT', 'constraint' => 4, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['election_id', 'sort_order']);
        $this->forge->addForeignKey('election_id', 'elections', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('assessment_criteria', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'candidate_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'criterion_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'score' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0.00],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'assessed_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['candidate_id', 'criterion_id'], 'uq_candidate_criterion');
        $this->forge->addKey('criterion_id');
        $this->forge->addForeignKey('candidate_id', 'candidates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('criterion_id', 'assessment_criteria', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('candidate_assessments', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('candidate_assessments', true);
        $this->forge->dropTable('assessment_criteria', true);
        $this->forge->dropColumn('elections', ['result_mode', 'vote_weight', 'assessment_weight']);
    }
}
