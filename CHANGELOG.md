# Changelog

Semua perubahan penting pada CAKRA 2026 dicatat di file ini.

Format mengikuti [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/) dan aplikasi menggunakan [Semantic Versioning](https://semver.org/lang/id/).

## [Unreleased]

### Planned

- Peningkatan cakupan test otomatis untuk alur voting berurutan.
- Dokumentasi operasional panitia dan buku panduan pemilih.

## [1.2.0] - 2026-10-30

### Added

- Migrasi `BootstrapInitialData` untuk membuat data awal saat instalasi pertama (akun admin/panitia, profil sekolah, pemilihan OSIS dan Poltar, serta pengaturan sistem) tanpa perlu menjalankan seeder.
- Seeder `DatabaseSeeder` kini hanya menambahkan data demo (kandidat, jadwal, FAQ, siswa, dan suara) dan tidak lagi membuat data dasar.
- Halaman **Konten Landing** (`/admin/konten`) untuk mengedit copywriting utama halaman beranda (hero, judul section, pilar nilai, langkah panduan, dan catatan privasi) yang tersimpan di database.

### Changed

- Daftar pemilihan di admin diurutkan OSIS lebih dulu agar konsisten di instalasi baru.

## [1.1.0] - 2026-10-30

### Added

- Halaman **Mode Aplikasi** (`/admin/mode-aplikasi`) untuk mengaktifkan OSIS saja, Poltar saja, atau keduanya.
- Kolom `elections.is_enabled` untuk menyembunyikan pemilihan yang tidak digunakan tanpa menghapus datanya.
- Nama tampilan OSIS dan Poltar dapat diganti admin melalui Mode Aplikasi (disimpan pada `system_settings`).
- Pengaturan admin **Aktifkan penilaian** untuk mengaktifkan fitur kriteria penilaian; saat aktif, panitia masing-masing dapat menambahkan kriteria dan nilai kandidat pada pemilihannya.

## [1.0.0] - 2026-10-29

### Added

- Dukungan dua pemilihan dalam satu aplikasi: Ketua OSIS dan Ketua Poltar.
- Status pemilihan `draft`, `published`, dan `closed` untuk mengatur pemilihan yang aktif.
- Halaman publik multi-pemilihan dengan tab per pemilihan.
- Alur voting berurutan: OSIS terlebih dahulu, lalu Poltar jika masih tersedia dan belum dipilih.
- Redirect otomatis ke pemilihan berikutnya setelah suara berhasil dikirim.
- Scoping admin dan panitia berdasarkan pemilihan aktif.
- Election switcher untuk admin.
- Hasil publik per pemilihan melalui `/hasil/{slug}`.
- Verifikasi bukti suara anonim melalui `/bukti`.
- Kode bukti unik per pemilihan dengan penyimpanan hash HMAC-SHA256.
- Mode hasil `vote_only` dan `combined` dengan bobot suara serta penilaian kandidat.
- Manajemen kandidat, jadwal pemilihan, pemilih, siswa, pengguna panitia, FAQ, dan profil sekolah.
- Impor siswa dari template Excel dan ekspor data siswa.
- Laporan hasil dalam format PDF dan Excel.
- Aktivasi akun dan login pemilih menggunakan OTP WhatsApp.
- Integrasi OpenWA dan mode demo OTP untuk pengembangan.
- Modal konfirmasi in-app untuk voting dan seluruh aksi admin yang membutuhkan konfirmasi.
- Audit log untuk aktivitas administratif.
- Antarmuka responsif berbasis Tailwind CSS dan Alpine.js.
- Sumber versi aplikasi terpusat pada `app/Config/Cakra.php`.

### Changed

- Login pemilih diarahkan ke pemilihan terbuka yang belum dipilih.
- Halaman akun, kandidat, hasil, dan voting menampilkan data sesuai pemilihan.
- Logout membersihkan sesi OTP, pilihan sementara, dan kode bukti.
- Semua konfirmasi browser diganti dengan modal aplikasi.

### Fixed

- Pemilihan berstatus `draft` tidak tampil di halaman publik.
- Kandidat dari pemilihan `draft` tidak dapat diakses publik.
- Redirect setelah voting tidak lagi berhenti di halaman selesai jika masih ada pemilihan berikutnya.
- Data pemilih dan suara tetap terpisah untuk menjaga anonimitas.

### Security

- Suara tidak menyimpan identitas pemilih.
- Status `has_voted` disimpan terpisah pada `election_voters`.
- OTP disimpan sebagai hash dengan masa berlaku, batas percobaan, cooldown, dan rate limit.
- Form menggunakan CSRF protection.
- Validasi jadwal, status pemilihan, kandidat, dan eligibilitas dilakukan di server.
- Kode bukti tidak dapat dicari berdasarkan nama atau NIS.
