# CAKRA 2026

CAKRA 2026 adalah aplikasi pemilihan ketua OSIS dan ketua Poltar berbasis web. Aplikasi mendukung satu atau dua pemilihan sekaligus, pemungutan suara berurutan, verifikasi bukti suara anonim, penilaian kandidat, serta laporan hasil dalam format PDF dan Excel.

Repositori: <https://github.com/smohheri/pilketos_ng>

## Fitur utama

- Multi-pemilihan: Ketua OSIS dan Ketua Poltar dalam satu aplikasi.
- Admin dapat mengaktifkan salah satu atau keduanya melalui status pemilihan.
- Alur voting berurutan: setelah memilih OSIS, pemilih diarahkan ke Poltar jika masih tersedia.
- Satu pemilih hanya dapat memberikan satu suara per pemilihan.
- Suara anonim: data pilihan tidak disimpan bersama identitas pemilih.
- Kode bukti suara acak dan halaman verifikasi `/bukti`.
- OTP WhatsApp melalui gateway OpenWA, dengan mode demo untuk pengembangan.
- Manajemen kandidat, jadwal, pemilih, siswa, panitia, FAQ, dan profil sekolah.
- Mode hasil `vote_only` atau `combined` dengan bobot suara dan penilaian.
- Impor siswa dari template Excel dan ekspor data siswa.
- Laporan hasil PDF dan Excel.
- Audit log untuk aktivitas administratif.
- Modal konfirmasi in-app, tanpa popup bawaan browser.
- Antarmuka responsif berbasis Tailwind CSS dan Alpine.js.

## Peran pengguna

| Peran | Akses |
| --- | --- |
| Admin | Semua pemilihan, pengaturan aplikasi, pengguna, audit, dan konfigurasi WhatsApp |
| Panitia | Data operasional sesuai pemilihan yang ditugaskan |
| Pemilih | Aktivasi akun, OTP, voting, status suara, dan verifikasi bukti |
| Publik | Informasi pemilihan, kandidat, jadwal, dan hasil yang sudah dipublikasikan |

## Teknologi

- PHP 8.2+ dan CodeIgniter 4.7
- MySQL/MariaDB
- Tailwind CSS 3 dan Alpine.js 3
- Dompdf dan PhpSpreadsheet
- OpenWA untuk pengiriman OTP WhatsApp

## Kebutuhan sistem

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm untuk membangun aset CSS
- MySQL atau MariaDB
- Ekstensi PHP: `intl`, `mbstring`, `json`, `mysqli`, `curl`, `openssl`, `fileinfo`, `zip`, dan `dom`
- Ekstensi `sqlite3` jika menjalankan test bawaan

## Instalasi

```powershell
composer install
npm install
Copy-Item env .env
php spark key:generate
```

Buat database MySQL, lalu sesuaikan konfigurasi di `.env`:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = cakra2026
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Jalankan migrasi:

```powershell
php spark migrate
```

`php spark migrate` sekaligus membuat data awal yang dibutuhkan aplikasi: akun admin dan panitia, profil sekolah, pemilihan OSIS dan Poltar, serta pengaturan sistem.

Opsional, isi data demo tambahan (kandidat, jadwal, FAQ, siswa, dan suara):

```powershell
php spark db:seed DatabaseSeeder
```

Bangun aset dan jalankan server:

```powershell
npm run build
php spark serve --host localhost --port 8080
```

Buka `http://localhost:8080/`.

## Akun awal

Akun admin dan panitia dibuat saat `php spark migrate`:

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@cakra.test` | `Cakra2026!` |
| Panitia | `panitia@cakra.test` | `Panitia2026!` |

Akun pemilih menggunakan nomor WhatsApp yang terdaftar dan OTP, bukan kata sandi.

## Konfigurasi penting

### Mode demo OTP

```ini
cakra.demoMode = true
```

Saat mode demo aktif dan gateway OpenWA tidak terhubung, kode OTP ditampilkan di halaman verifikasi. Jangan aktifkan mode ini di produksi.

### OpenWA

Konfigurasi dapat diisi melalui **Admin > Pengaturan > WhatsApp Gateway** atau `.env`:

```ini
openwa.enabled = true
openwa.baseURL = 'http://localhost:2785/'
openwa.apiKey = 'isi-api-key'
openwa.sessionId = 'uuid-sesi-openwa'
```

Untuk keluar dari mode demo:

1. Hubungkan dan tes OpenWA sampai sesi `ready`.
2. Set `cakra.demoMode = false`.
3. Uji login atau aktivasi dengan nomor non-kritis.

Jika `demoMode` dimatikan sebelum gateway berfungsi, permintaan OTP akan gagal.

### Salt bukti suara

```ini
cakra.receiptSalt = 'ganti-dengan-nilai-acak-yang-kuat'
```

Ganti sebelum pemilihan resmi dimulai. Mengganti salt setelah ada suara membuat kode bukti lama tidak dapat diverifikasi.

## Penilaian kandidat

Admin dapat mengaktifkan fitur penilaian di **Admin > Pengaturan > Aktifkan penilaian**.

- Saat aktif, setiap panitia dapat menambahkan kriteria dan mengisi nilai kandidat untuk pemilihan yang ditugaskan melalui menu **Penilaian**.
- Saat nonaktif, form kriteria tidak tampil dan perubahan nilai ditolak, tetapi data penilaian yang sudah ada tetap tersimpan.

## Konten landing

Copywriting utama halaman beranda dapat diedit di **Admin > Konten Landing**, meliputi hero, judul section, pilar nilai, langkah panduan, tagline, dan catatan privasi. Nilai disimpan pada `system_settings` dengan prefix `landing_`. Jika sebuah kolom dikosongkan, aplikasi memakai teks bawaan.

## Mengatur OSIS dan Poltar

**Mode Aplikasi** (`/admin/mode-aplikasi`) di menu **Admin > Mode Aplikasi** menentukan pemilihan mana yang aktif:

- Centang OSIS saja, Poltar saja, atau keduanya.
- Nama tampilan OSIS dan Poltar dapat diganti pada kolom **Nama pemilihan**. Kosongkan untuk kembali ke nama bawaan.
- Pemilihan yang tidak dicentang disembunyikan dari publik, login, dan bilik suara, tetapi datanya tetap tersimpan.
- Minimal satu pemilihan harus aktif.

Status dan jadwal setiap pemilihan tetap diatur di **Admin > Pemilihan**:

- `draft`: pemilihan disembunyikan dan tidak masuk alur voting.
- `published`: pemilihan tampil dan mengikuti jadwal.
- `closed`: pemungutan suara berhenti dan hasil tampil setelah waktu pengumuman.

Kombinasi yang didukung:

- Hanya OSIS: aktifkan OSIS, nonaktifkan Poltar.
- Hanya Poltar: aktifkan Poltar, nonaktifkan OSIS.
- Keduanya: aktifkan keduanya, atur status di jadwal masing-masing.

## Alur pemilih

1. Aktivasi akun menggunakan NIS dan tanggal lahir.
2. Verifikasi OTP WhatsApp.
3. Pilih kandidat pada pemilihan yang sedang dibuka.
4. Konfirmasi pilihan melalui modal in-app.
5. Simpan kode bukti suara.
6. Lanjut ke pemilihan berikutnya jika tersedia.
7. Verifikasi kode melalui `/bukti` jika diperlukan.

Verifikasi bukti tidak memengaruhi keabsahan suara. Suara sudah tercatat saat transaksi voting berhasil. Verifikasi hanya membuktikan bahwa kode bukti terdaftar pada sistem.

## Keamanan dan privasi

- Pilihan suara disimpan tanpa identitas pemilih.
- Status sudah memilih disimpan terpisah pada `election_voters`.
- Kode bukti disimpan sebagai HMAC-SHA256, bukan teks asli.
- OTP disimpan sebagai hash dan memiliki batas waktu, percobaan, serta rate limit.
- Form menggunakan CSRF protection.
- Validasi status pemilihan, jadwal, kandidat, dan eligibilitas dilakukan di server.
- Aktivitas administratif dicatat pada audit log.

## Struktur proyek

```text
app/
  Config/          Konfigurasi aplikasi, termasuk Cakra.php
  Controllers/     Controller publik, voting, dan admin
  Database/        Migrasi dan seeder
  Helpers/         Helper UI, format, auth, election, version
  Libraries/       ElectionService, VotingService, OtpService, WhatsAppService
  Models/          Model data
  Views/           Tampilan publik dan admin
public/
  assets/          CSS, JavaScript, font, gambar
resources/
  css/             Sumber Tailwind
tests/             Test PHPUnit
writable/          Log, session, cache, dan upload
```

## Testing

```powershell
composer test
```

Konfigurasi test default memakai SQLite `:memory:`. Aktifkan ekstensi `sqlite3`, atau atur koneksi `database.tests` pada `phpunit.dist.xml`.

Pemeriksaan tambahan:

```powershell
node --check public/assets/js/app.js
php -l app/Controllers/Vote.php
npm run build
```

## Versioning

Aplikasi menggunakan Semantic Versioning `MAJOR.MINOR.PATCH`.

Sumber versi:

- `app/Config/Cakra.php` untuk versi yang ditampilkan aplikasi.
- `VERSION` untuk versi rilis di tingkat repositori.
- `package.json` untuk metadata aset frontend.
- `CHANGELOG.md` untuk riwayat perubahan.

Saat merilis versi baru:

1. Perbarui `app/Config/Cakra.php`, `VERSION`, dan `package.json`.
2. Tambahkan entri pada `CHANGELOG.md`.
3. Jalankan test dan `npm run build`.
4. Buat tag Git `vX.Y.Z`.

## Dokumentasi

- [PRD CAKRA 2026](PRD_CAKRA_2026.md)
- [Changelog](CHANGELOG.md)
