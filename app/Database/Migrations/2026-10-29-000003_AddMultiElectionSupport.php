<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMultiElectionSupport extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('elections', [
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'osis',
                'after' => 'id',
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'type',
            ],
        ]);

        $this->db->query("UPDATE elections SET type = 'osis' WHERE type IS NULL OR type = ''");
        $this->db->query("UPDATE elections SET slug = CONCAT('osis-', id) WHERE slug IS NULL OR slug = ''");
        $this->db->query(
            "UPDATE elections
             SET slug = 'osis'
             WHERE id = (SELECT id FROM (SELECT MIN(id) AS id FROM elections) AS first_election)"
        );
        $this->db->query('ALTER TABLE elections ADD UNIQUE KEY uq_elections_slug (slug)');

        $this->forge->addColumn('users', [
            'election_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'role',
            ],
        ]);
        $this->db->query(
            'ALTER TABLE users
             ADD CONSTRAINT fk_users_election
             FOREIGN KEY (election_id) REFERENCES elections(id)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );

        $osis = $this->db->table('elections')->where('type', 'osis')->orderBy('id', 'ASC')->get()->getRowArray();

        $this->db->table('users')
            ->where('role', 'panitia')
            ->where('election_id', null)
            ->update(['election_id' => $osis['id'] ?? null]);

        if ($this->db->table('elections')->where('type', 'poltar')->countAllResults() === 0) {
            $now = date('Y-m-d H:i:s');
            $start = $osis['start_at'] ?? $now;
            $end = $osis['end_at'] ?? date('Y-m-d H:i:s', strtotime($now . ' +1 day'));
            $publish = $osis['result_publish_at'] ?? date('Y-m-d H:i:s', strtotime($now . ' +2 days'));

            $this->db->table('elections')->insert([
                'type' => 'poltar',
                'slug' => 'poltar',
                'title' => 'Pemilihan Ketua Poltar 2026',
                'description' => 'Pemilihan Ketua Poltar dengan penilaian dinamis dan panitia tersendiri.',
                'start_at' => $start,
                'end_at' => $end,
                'result_publish_at' => $publish,
                'status' => 'draft',
                'timezone' => $osis['timezone'] ?? 'Asia/Makassar',
                'result_mode' => 'vote_only',
                'vote_weight' => 50.00,
                'assessment_weight' => 50.00,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_election');
        $this->forge->dropColumn('users', 'election_id');
        $this->db->query('ALTER TABLE elections DROP INDEX uq_elections_slug');
        $this->forge->dropColumn('elections', ['slug', 'type']);
    }
}
