<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        if ($db->table('users')->countAllResults() > 0) {
            return;
        }

        $db->table('users')->insertBatch([
            [
                'name' => 'Administrator CAKRA',
                'email' => 'admin@cakra.test',
                'password_hash' => password_hash('Cakra2026!', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Panitia Pemilihan',
                'email' => 'panitia@cakra.test',
                'password_hash' => password_hash('Panitia2026!', PASSWORD_DEFAULT),
                'role' => 'panitia',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

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
            'updated_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $start = date('Y-m-d H:i:s', strtotime('-2 days'));
        $end = date('Y-m-d H:i:s', strtotime('+5 days'));
        $publish = date('Y-m-d H:i:s', strtotime('+6 days'));

        $db->table('elections')->insert([
            'title' => 'Pemilihan Ketua OSIS 2026',
            'description' => 'Pemilihan Ketua dan Wakil Ketua OSIS SMA Negeri 1 Nusantara periode 2026/2027. Satu siswa, satu suara, untuk sekolah yang lebih baik.',
            'start_at' => $start,
            'end_at' => $end,
            'result_publish_at' => $publish,
            'status' => 'published',
            'timezone' => 'Asia/Makassar',
            'created_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $electionId = (int) $db->insertID();

        $candidates = [
            [
                'candidate_number' => 1,
                'full_name' => 'Raka Aditya Pratama',
                'class_name' => 'XI-2',
                'photo_path' => 'assets/img/candidates/01.svg',
                'tagline' => 'Aksi Nyata, Suara Kita.',
                'vision' => 'Mewujudkan OSIS yang terbuka, responsif, dan bergerak nyata bersama seluruh siswa.',
                'mission' => "Membuka kanal aspirasi mingguan yang transparan.\nMenghadirkan festival karya dan minat bakat setiap semester.\nMemperkuat program kesehatan mental dan ruang aman siswa.",
                'program_description' => 'Raka percaya perubahan dimulai dari kebiasaan kecil: mendengar, mencatat, lalu mengeksekusi. Program unggulannya adalah Bank Ide Siswa dan Festival Karya Nusantara.',
            ],
            [
                'candidate_number' => 2,
                'full_name' => 'Naura Zahira Putri',
                'class_name' => 'XI-1',
                'photo_path' => 'assets/img/candidates/02.svg',
                'tagline' => 'Sekolah Ramah, Generasi Hebat.',
                'vision' => 'Menjadikan sekolah sebagai rumah kedua yang aman, ramah, dan menyenangkan bagi semua siswa.',
                'mission' => "Menguatkan program anti-perundungan dan peer support.\nMengadakan kelas keterampilan dan literasi digital.\nMengembangkan gerakan sekolah hijau dan bebas plastik.",
                'program_description' => 'Fokus Naura adalah kesejahteraan siswa: ruang konseling sebaya, kampanye sekolah ramah, serta gerakan Nusantara Hijau yang melibatkan setiap kelas.',
            ],
            [
                'candidate_number' => 3,
                'full_name' => 'Bima Aryasatya',
                'class_name' => 'X-3',
                'photo_path' => 'assets/img/candidates/03.svg',
                'tagline' => 'Berkarya, Berprestasi, Bersama.',
                'vision' => 'Membangun budaya kompetisi yang sehat dan kolaborasi lintas kelas untuk prestasi sekolah.',
                'mission' => "Menghidupkan liga olahraga dan turnamen antar kelas.\nMembentuk komunitas belajar dan mentoring antar angkatan.\nMembuka panggung seni dan kreativitas siswa setiap bulan.",
                'program_description' => 'Bima membawa semangat sportivitas dan kolaborasi melalui Liga Nusantara, Panggung Kreatif Bulanan, dan program kakak asuh antar angkatan.',
            ],
            [
                'candidate_number' => 4,
                'full_name' => 'Keisha Anindya',
                'class_name' => 'XI-3',
                'photo_path' => 'assets/img/candidates/04.svg',
                'tagline' => 'Dengar, Gerak, Berdampak.',
                'vision' => 'Mewujudkan OSIS yang dekat dengan siswa, tanggap terhadap kebutuhan, dan berdampak bagi lingkungan.',
                'mission' => "Membuat sistem aspirasi digital yang mudah diakses.\nMengadakan bakti sosial dan aksi lingkungan rutin.\nMemperkuat kolaborasi OSIS dengan komunitas siswa.",
                'program_description' => 'Keisha mengusung pendekatan berbasis data: setiap aspirasi dicatat, dipetakan, dan ditindaklanjuti. Programnya meliputi Peta Aspirasi dan Aksi Nusantara Peduli.',
            ],
        ];

        foreach ($candidates as $index => $candidate) {
            $candidates[$index] = $candidate + [
                'election_id' => $electionId,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $db->table('candidates')->insertBatch($candidates);

        $db->table('election_schedules')->insertBatch([
            [
                'election_id' => $electionId,
                'title' => 'Pendaftaran & Verifikasi Kandidat',
                'description' => 'Pendaftaran pasangan calon dan verifikasi dokumen oleh panitia.',
                'start_at' => date('Y-m-d 08:00:00', strtotime('-20 days')),
                'end_at' => date('Y-m-d 15:00:00', strtotime('-12 days')),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'election_id' => $electionId,
                'title' => 'Publikasi Kandidat',
                'description' => 'Pengenalan profil, visi, misi, dan program kerja seluruh kandidat.',
                'start_at' => date('Y-m-d 08:00:00', strtotime('-10 days')),
                'end_at' => date('Y-m-d 15:00:00', strtotime('-3 days')),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'election_id' => $electionId,
                'title' => 'Sosialisasi & Debat Kandidat',
                'description' => 'Dialog terbuka bersama kandidat dan seluruh siswa.',
                'start_at' => date('Y-m-d 09:00:00', strtotime('-1 day')),
                'end_at' => date('Y-m-d 12:00:00', strtotime('-1 day')),
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'election_id' => $electionId,
                'title' => 'Pemungutan Suara',
                'description' => 'Pemungutan suara daring melalui aplikasi CAKRA 2026.',
                'start_at' => $start,
                'end_at' => $end,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'election_id' => $electionId,
                'title' => 'Penghitungan & Pengumuman',
                'description' => 'Rekapitulasi suara dan pengumuman hasil pemilihan.',
                'start_at' => $end,
                'end_at' => $publish,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $db->table('faqs')->insertBatch([
            [
                'question' => 'Bagaimana cara memberikan suara?',
                'answer' => 'Aktivasi akun menggunakan NIS dan tanggal lahir, verifikasi OTP WhatsApp, lalu masuk ke bilik suara dan pilih satu kandidat. Ikuti konfirmasi sebelum suara dikirim.',
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Apakah pilihan saya bisa dilihat orang lain?',
                'answer' => 'Tidak. Sistem memisahkan catatan kehadiran memilih dari data suara. Pilihan Anda tidak ditampilkan pada riwayat akun maupun laporan publik.',
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Saya tidak menerima kode OTP, apa yang harus dilakukan?',
                'answer' => 'Pastikan nomor WhatsApp Anda terdaftar di sekolah dan aktif. Tunggu 60 detik untuk meminta ulang. Jika masih gagal, hubungi panitia untuk pemulihan akun.',
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Apakah saya bisa mengubah pilihan setelah mengirim suara?',
                'answer' => 'Tidak. Suara yang telah dikirim bersifat final. Pastikan pilihan Anda pada halaman konfirmasi sebelum menekan tombol kirim.',
                'sort_order' => 4,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Apa itu kode bukti suara?',
                'answer' => 'Kode bukti adalah kode acak yang ditampilkan setelah suara tercatat. Kode ini tidak memuat identitas Anda dan dapat dicek pada halaman Cek Bukti Suara.',
                'sort_order' => 5,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $db->table('system_settings')->insertBatch([
            ['setting_key' => 'app_name', 'setting_value' => 'CAKRA 2026', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'app_tagline', 'setting_value' => 'Pilih dengan Hati, Pimpin dengan Aksi.', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'support_whatsapp', 'setting_value' => '6281234567890', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'support_email', 'setting_value' => 'panitia@cakra.test', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'school_year', 'setting_value' => '2025/2026', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'announcement', 'setting_value' => 'Pemungutan suara dibuka hingga 5 hari ke depan. Pastikan suaramu tercatat!', 'updated_by' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $names = [
            ['2026001', 'Alya Putri Ramadhani', '2008-01-15', 'XI-1', '6281210000001'],
            ['2026002', 'Bagas Dwi Nugroho', '2008-03-22', 'XI-2', '6281210000002'],
            ['2026003', 'Citra Maharani', '2008-05-10', 'XI-1', '6281210000003'],
            ['2026004', 'Dimas Prasetyo', '2008-07-04', 'X-1', '6281210000004'],
            ['2026005', 'Elsa Nur Fadilah', '2008-02-18', 'X-2', '6281210000005'],
            ['2026006', 'Farhan Akbar', '2008-09-09', 'X-3', '6281210000006'],
            ['2026007', 'Gita Larasati', '2008-11-21', 'XI-3', '6281210000007'],
            ['2026008', 'Hafiz Ramadhan', '2008-04-12', 'XI-2', '6281210000008'],
            ['2026009', 'Intan Permata Sari', '2008-06-30', 'XI-1', '6281210000009'],
            ['2026010', 'Jovan Adi Saputra', '2008-08-14', 'X-1', '6281210000010'],
            ['2026011', 'Kirana Wulandari', '2008-12-02', 'X-2', '6281210000011'],
            ['2026012', 'Luthfi Hakim', '2008-03-08', 'X-3', '6281210000012'],
            ['2026013', 'Mira Ayu Lestari', '2008-10-25', 'XI-3', '6281210000013'],
            ['2026014', 'Naufal Rizky', '2008-01-27', 'XI-2', '6281210000014'],
            ['2026015', 'Olivia Putri', '2008-05-19', 'XI-1', '6281210000015'],
            ['2026016', 'Pradipta Yoga', '2008-07-11', 'X-1', '6281210000016'],
            ['2026017', 'Qanita Salsabila', '2008-09-23', 'X-2', '6281210000017'],
            ['2026018', 'Rafi Alfarizi', '2008-11-05', 'X-3', '6281210000018'],
        ];

        foreach ($names as $index => $row) {
            $db->table('students')->insert([
                'nis' => $row[0],
                'full_name' => $row[1],
                'date_of_birth' => $row[2],
                'class_name' => $row[3],
                'whatsapp_number' => $row[4],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $studentId = (int) $db->insertID();

            $active = $index === 0 || $index <= 13;
            $db->table('voter_accounts')->insert([
                'student_id' => $studentId,
                'account_status' => $active ? 'active' : 'pending',
                'activated_at' => $active ? $now : null,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $hasVoted = $index >= 1 && $index <= 12;
            $db->table('election_voters')->insert([
                'election_id' => $electionId,
                'student_id' => $studentId,
                'eligibility_status' => 'eligible',
                'has_voted' => $hasVoted ? 1 : 0,
                'voted_at' => $hasVoted ? date('Y-m-d H:i:s', strtotime('-' . (3 + $index) . ' hours')) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $candidateIds = $db->table('candidates')->where('election_id', $electionId)->orderBy('candidate_number', 'ASC')->get()->getResultArray();
        $seedVotes = [0, 0, 1, 1, 1, 2, 2, 2, 3, 3, 0, 1];
        $voteTime = strtotime('-2 hours');
        foreach ($seedVotes as $index => $candidateIndex) {
            $candidateId = (int) $candidateIds[$candidateIndex]['id'];
            $voteTime += 420;
            $db->table('votes')->insert([
                'election_id' => $electionId,
                'candidate_id' => $candidateId,
                'receipt_code_hash' => hash('sha256', 'SEED-RECEIPT-' . $index . '-' . random_int(1000, 9999)),
                'cast_at' => date('Y-m-d H:i:s', $voteTime),
                'created_at' => date('Y-m-d H:i:s', $voteTime),
            ]);
        }

        $db->table('audit_logs')->insert([
            'actor_id' => 1,
            'actor_role' => 'admin',
            'action' => 'system.seed',
            'entity_type' => 'system',
            'entity_id' => null,
            'metadata' => json_encode(['note' => 'Data demo CAKRA 2026 dibuat.']),
            'created_at' => $now,
        ]);
    }
}