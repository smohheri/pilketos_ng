<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SystemSettingModel;

class Content extends BaseController
{
    public function index()
    {
        return view('admin/content/index', [
            'groups' => $this->fields(),
            'appName' => app_setting('app_name', 'CAKRA 2026'),
            'pageTitle' => 'Konten Landing',
            'pageSubtitle' => 'Atur copywriting utama halaman beranda yang tersimpan di database.',
        ]);
    }

    public function update()
    {
        $settings = new SystemSettingModel();
        $userId = (int) session('admin_id');
        $saved = 0;

        foreach ($this->fieldKeys() as $key) {
            $value = trim((string) $this->request->getPost($key));
            $settings->put('landing_' . $key, $value, $userId);
            $saved++;
        }

        $this->audit('content.update', 'system_setting', null, ['fields' => $saved]);

        return redirect()->to('/admin/konten')->with('success', 'Konten landing berhasil diperbarui.');
    }

    private function fieldKeys(): array
    {
        $keys = [];
        foreach ($this->fields() as $group) {
            foreach ($group['fields'] as $field) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    }

    private function fields(): array
    {
        return [
            'hero' => [
                'title' => 'Hero',
                'fields' => [
                    ['key' => 'hero_eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'maxlength' => 120, 'default' => 'Pemilihan Ketua OSIS & Poltar 2026'],
                    ['key' => 'hero_heading_line_1', 'label' => 'Judul baris 1', 'type' => 'text', 'maxlength' => 120, 'default' => 'Pilih dengan Hati,'],
                    ['key' => 'hero_heading_line_2', 'label' => 'Judul baris 2', 'type' => 'text', 'maxlength' => 120, 'default' => 'Pimpin dengan Aksi.'],
                    ['key' => 'hero_subtitle', 'label' => 'Subjudul (dipakai saat deskripsi pemilihan kosong)', 'type' => 'textarea', 'default' => 'Satu siswa, satu suara. Saatnya memilih pemimpin yang mendengar, bergerak, dan membawa sekolah kita lebih baik.'],
                    ['key' => 'hero_cta_secondary', 'label' => 'Tombol kedua', 'type' => 'text', 'maxlength' => 60, 'default' => 'Dua Pemilihan'],
                ],
            ],
            'sections' => [
                'title' => 'Judul section',
                'fields' => [
                    ['key' => 'pemilihan_heading', 'label' => 'Heading "pemilihan"', 'type' => 'text', 'maxlength' => 160, 'default' => 'Satu sekolah, dua pilihan.'],
                    ['key' => 'pemilihan_subtext', 'label' => 'Subtext "pemilihan"', 'type' => 'textarea', 'default' => 'Ketua OSIS dan Ketua Poltar dipilih pada pemilihan terpisah, dengan kandidat, jadwal, dan panitia masing-masing.'],
                    ['key' => 'tentang_heading', 'label' => 'Heading "tentang"', 'type' => 'text', 'maxlength' => 160, 'default' => 'Demokrasi kecil yang berdampak besar.'],
                    ['key' => 'tentang_tagline', 'label' => 'Tagline', 'type' => 'text', 'maxlength' => 160, 'default' => 'Pilih dengan Hati, Pimpin dengan Aksi.'],
                    ['key' => 'tentang_tagline_note', 'label' => 'Keterangan tagline', 'type' => 'text', 'maxlength' => 160, 'default' => '— Tagline resmi CAKRA 2026'],
                    ['key' => 'kandidat_heading', 'label' => 'Heading "kandidat"', 'type' => 'text', 'maxlength' => 160, 'default' => 'Kenali pilihanmu.'],
                    ['key' => 'kandidat_subtext', 'label' => 'Subtext "kandidat"', 'type' => 'textarea', 'default' => 'Baca visi, misi, dan program kerja setiap kandidat. Pilih yang paling sesuai dengan harapanmu untuk sekolah.'],
                    ['key' => 'jadwal_heading', 'label' => 'Heading "jadwal"', 'type' => 'text', 'maxlength' => 160, 'default' => 'Setiap tahap, transparan.'],
                    ['key' => 'jadwal_subtext', 'label' => 'Subtext "jadwal"', 'type' => 'textarea', 'default' => 'Ikuti rangkaian pemilihan dari publikasi kandidat hingga pengumuman hasil.'],
                    ['key' => 'panduan_heading', 'label' => 'Heading "panduan"', 'type' => 'text', 'maxlength' => 160, 'default' => 'Memilih itu mudah.'],
                    ['key' => 'panduan_subtext', 'label' => 'Subtext "panduan"', 'type' => 'textarea', 'default' => 'Empat langkah singkat, tanpa kertas, tanpa ribet. Pastikan nomor WhatsApp-mu aktif.'],
                ],
            ],
            'pillars' => [
                'title' => 'Pilar nilai',
                'fields' => [
                    ['key' => 'pillar_1_title', 'label' => 'Pilar 1 — Judul', 'type' => 'text', 'maxlength' => 60, 'default' => 'Aspiratif'],
                    ['key' => 'pillar_1_text', 'label' => 'Pilar 1 — Teks', 'type' => 'textarea', 'default' => 'Mendengar semua suara, bukan hanya yang paling lantang.'],
                    ['key' => 'pillar_2_title', 'label' => 'Pilar 2 — Judul', 'type' => 'text', 'maxlength' => 60, 'default' => 'Kreatif'],
                    ['key' => 'pillar_2_text', 'label' => 'Pilar 2 — Teks', 'type' => 'textarea', 'default' => 'Menghadirkan ide segar dan cara baru untuk sekolah.'],
                    ['key' => 'pillar_3_title', 'label' => 'Pilar 3 — Judul', 'type' => 'text', 'maxlength' => 60, 'default' => 'Responsif'],
                    ['key' => 'pillar_3_text', 'label' => 'Pilar 3 — Teks', 'type' => 'textarea', 'default' => 'Cepat tanggap terhadap kebutuhan dan masalah siswa.'],
                    ['key' => 'pillar_4_title', 'label' => 'Pilar 4 — Judul', 'type' => 'text', 'maxlength' => 60, 'default' => 'Amanah'],
                    ['key' => 'pillar_4_text', 'label' => 'Pilar 4 — Teks', 'type' => 'textarea', 'default' => 'Menjaga kepercayaan, transparansi, dan integritas.'],
                ],
            ],
            'steps' => [
                'title' => 'Langkah panduan',
                'fields' => [
                    ['key' => 'step_1_title', 'label' => 'Langkah 1 — Judul', 'type' => 'text', 'maxlength' => 80, 'default' => 'Aktivasi akun'],
                    ['key' => 'step_1_text', 'label' => 'Langkah 1 — Teks', 'type' => 'textarea', 'default' => 'Masukkan NIS dan tanggal lahir yang terdaftar di sekolah.'],
                    ['key' => 'step_2_title', 'label' => 'Langkah 2 — Judul', 'type' => 'text', 'maxlength' => 80, 'default' => 'Verifikasi OTP'],
                    ['key' => 'step_2_text', 'label' => 'Langkah 2 — Teks', 'type' => 'textarea', 'default' => 'Kode 6 digit dikirim ke WhatsApp resmi yang tersimpan.'],
                    ['key' => 'step_3_title', 'label' => 'Langkah 3 — Judul', 'type' => 'text', 'maxlength' => 80, 'default' => 'Pilih kandidat'],
                    ['key' => 'step_3_text', 'label' => 'Langkah 3 — Teks', 'type' => 'textarea', 'default' => 'Pelajari visi-misi, lalu pilih satu kandidat favoritmu.'],
                    ['key' => 'step_4_title', 'label' => 'Langkah 4 — Judul', 'type' => 'text', 'maxlength' => 80, 'default' => 'Konfirmasi & bukti'],
                    ['key' => 'step_4_text', 'label' => 'Langkah 4 — Teks', 'type' => 'textarea', 'default' => 'Periksa pilihan, kirim suara, dan simpan kode bukti acak.'],
                ],
            ],
            'privacy' => [
                'title' => 'Catatan privasi',
                'fields' => [
                    ['key' => 'privacy_heading', 'label' => 'Judul', 'type' => 'text', 'maxlength' => 120, 'default' => 'Pilihanmu rahasia.'],
                    ['key' => 'privacy_text', 'label' => 'Teks', 'type' => 'textarea', 'default' => 'Data pemilih dan data suara disimpan terpisah. Tidak ada yang bisa melihat kandidat yang kamu pilih.'],
                ],
            ],
        ];
    }
}