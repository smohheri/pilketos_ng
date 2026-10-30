<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BootstrapInitialData extends Migration
{
    public function up(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $admin = $db->table('users')->where('email', 'admin@cakra.test')->get()->getRowArray();
        if ($admin === null) {
            $db->table('users')->insert([
                'name' => 'Administrator CAKRA',
                'email' => 'admin@cakra.test',
                'password_hash' => password_hash('Cakra2026!', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $adminId = (int) $db->insertID();
        } else {
            $adminId = (int) $admin['id'];
        }

        $panitia = $db->table('users')->where('email', 'panitia@cakra.test')->get()->getRowArray();
        if ($panitia === null) {
            $db->table('users')->insert([
                'name' => 'Panitia Pemilihan',
                'email' => 'panitia@cakra.test',
                'password_hash' => password_hash('Panitia2026!', PASSWORD_DEFAULT),
                'role' => 'panitia',
                'is_active' => 1,
                'election_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($db->table('school_profiles')->countAllResults() === 0) {
            $db->table('school_profiles')->insert([
                'school_name' => 'SMA Negeri 1 Nusantara',
                'short_name' => 'SMAN 1 Nusantara',
                'logo_path' => 'assets/img/logo-cakra.svg',
                'description' => 'SMA Negeri 1 Nusantara adalah sekolah yang tumbuh bersama semangat kolaborasi, kreativitas, dan kepedulian. Kami percaya bahwa setiap siswa memiliki suara dan peran untuk membawa perubahan yang baik.',
                'vision' => 'Terwujudnya generasi Nusantara yang berkarakter, kreatif, berdaya saing global, dan peduli terhadap sesama.',
                'mission' => "Menyelenggarakan pembelajaran yang aktif, menyenangkan, dan bermakna.\nMenumbuhkan budaya kolaborasi, inovasi, dan integritas.\nMembuka ruang partisipasi siswa dalam kehidupan sekolah.\nMenguatkan kepedulian sosial dan lingkungan.",
                'official_website' => 'https://sman1nusantara.sch.id',
                'official_contact' => '0812-3456-7890',
                'address' => 'Jl. Pendidikan Raya No. 17, Kota Nusantara',
                'footer_text' => 'CAKRA 2026 — Pilih dengan Hati, Pimpin dengan Aksi.',
                'updated_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $osis = $db->table('elections')->where('type', 'osis')->orderBy('id', 'ASC')->get()->getRowArray();
        if ($osis === null) {
            $start = date('Y-m-d H:i:s', strtotime('-2 days'));
            $end = date('Y-m-d H:i:s', strtotime('+5 days'));
            $publish = date('Y-m-d H:i:s', strtotime('+6 days'));

            $db->table('elections')->insert([
                'type' => 'osis',
                'slug' => 'osis',
                'title' => 'Pemilihan Ketua OSIS 2026',
                'description' => 'Pemilihan Ketua dan Wakil Ketua OSIS SMA Negeri 1 Nusantara periode 2026/2027. Satu siswa, satu suara, untuk sekolah yang lebih baik.',
                'start_at' => $start,
                'end_at' => $end,
                'result_publish_at' => $publish,
                'status' => 'draft',
                'is_enabled' => 1,
                'timezone' => 'Asia/Makassar',
                'result_mode' => 'vote_only',
                'vote_weight' => 50.0,
                'assessment_weight' => 50.0,
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $osisId = (int) $db->insertID();
        } else {
            $osisId = (int) $osis['id'];
        }

        $poltar = $db->table('elections')->where('type', 'poltar')->orderBy('id', 'ASC')->get()->getRowArray();
        if ($poltar === null) {
            $osis = $db->table('elections')->where('id', $osisId)->get()->getRowArray();

            $db->table('elections')->insert([
                'type' => 'poltar',
                'slug' => 'poltar',
                'title' => 'Pemilihan Ketua Poltar 2026',
                'description' => 'Pemilihan Ketua Poltar dengan panitia tersendiri.',
                'start_at' => $osis['start_at'] ?? date('Y-m-d H:i:s', strtotime('+1 day')),
                'end_at' => $osis['end_at'] ?? date('Y-m-d H:i:s', strtotime('+5 days')),
                'result_publish_at' => $osis['result_publish_at'] ?? date('Y-m-d H:i:s', strtotime('+6 days')),
                'status' => 'draft',
                'is_enabled' => 1,
                'timezone' => $osis['timezone'] ?? 'Asia/Makassar',
                'result_mode' => 'vote_only',
                'vote_weight' => 50.0,
                'assessment_weight' => 50.0,
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $db->table('users')
            ->where('role', 'panitia')
            ->where('election_id', null)
            ->update(['election_id' => $osisId, 'updated_at' => $now]);

        $settings = [
            'app_name' => 'CAKRA 2026',
            'app_tagline' => 'Pilih dengan Hati, Pimpin dengan Aksi.',
            'support_whatsapp' => '6281234567890',
            'support_email' => 'panitia@cakra.test',
            'school_year' => '2025/2026',
            'announcement' => 'Pemungutan suara dibuka hingga 5 hari ke depan. Pastikan suaramu tercatat!',
            'assessment_enabled' => '0',
        ];

        foreach ($settings as $key => $value) {
            if ($db->table('system_settings')->where('setting_key', $key)->countAllResults() === 0) {
                $db->table('system_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Baseline data is intentionally not removed on rollback.
    }
}