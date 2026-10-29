# PRD CAKRA 2026

## Product Requirements Document --- Versi 1.1

**Nama aplikasi:** CAKRA 2026\
**Kepanjangan:** Calon Ketua Aspiratif, Kreatif, Responsif, dan Amanah\
**Tagline:** *Pilih dengan Hati, Pimpin dengan Aksi.*\
**Jenis produk:** Aplikasi pemilihan Ketua OSIS berbasis web\
**Prinsip utama:** Paperless, mudah diakses, aman, transparan dalam
proses, dan menjaga kerahasiaan pilihan siswa.

------------------------------------------------------------------------

## 1. Ringkasan Produk

CAKRA 2026 adalah aplikasi pemilihan Ketua OSIS berbasis web yang
memungkinkan siswa melihat informasi kandidat dan memberikan suara
melalui ponsel, baik saat berada di sekolah maupun dari lokasi lain
selama periode pemilihan berlangsung.

Aplikasi dirancang untuk mengurangi penggunaan kertas dan memudahkan
panitia mengelola data pemilih, kandidat, jadwal, proses pemungutan
suara, serta laporan hasil. Sistem menggunakan satu QR code umum yang
mengarah ke halaman resmi CAKRA, bukan QR unik untuk setiap siswa.

### 1.1 Tujuan

-   Mendigitalisasi rangkaian pemilihan Ketua OSIS.
-   Memudahkan siswa mengakses informasi kandidat dan memberikan suara.
-   Memastikan setiap siswa yang memenuhi syarat hanya dapat memilih
    satu kali untuk setiap pemilihan.
-   Memudahkan panitia mengelola data siswa dan hasil pemilihan.
-   Mengurangi pencetakan formulir, surat suara, dan dokumen pendukung.
-   Menyediakan jejak audit untuk tindakan administratif tanpa mencatat
    pilihan siswa pada identitas pemilih.

### 1.2 Prinsip Desain

1.  **Paperless:** proses utama dilakukan secara digital.
2.  **Mobile-first:** antarmuka dioptimalkan untuk ponsel.
3.  **One eligible voter, one vote:** satu pemilih yang memenuhi syarat
    hanya dapat mengirim satu suara pada satu pemilihan.
4.  **Privacy by design:** data identitas pemilih dipisahkan dari data
    pilihan.
5.  **Secure by default:** validasi penting dilakukan di server, bukan
    hanya di browser.
6.  **Accessible:** halaman mudah dipahami dan digunakan oleh siswa.
7.  **Auditable:** aktivitas penting administrasi dapat ditelusuri tanpa
    merekam pilihan yang diberikan oleh siswa.

------------------------------------------------------------------------

## 2. Pengguna dan Hak Akses

### 2.1 Admin

Admin mengelola konfigurasi aplikasi, akun panitia, profil sekolah,
pengaturan sistem, dan hak akses.

### 2.2 Panitia

Panitia mengelola data siswa, kandidat, jadwal pemilihan, daftar pemilih
yang memenuhi syarat, serta laporan sesuai hak akses yang diberikan.

### 2.3 Siswa/Pemilih

Siswa dapat mengklaim akun, memverifikasi nomor WhatsApp terdaftar
melalui OTP, melihat kandidat, memberikan suara ketika pemilihan dibuka,
dan melihat status bahwa suaranya telah tercatat tanpa mengetahui atau
menampilkan kembali pilihan kandidat.

### 2.4 Pengunjung Publik

Pengunjung dapat melihat halaman utama, informasi pemilihan, visi-misi
sekolah, kandidat, jadwal, panduan, dan hasil yang telah dipublikasikan.

------------------------------------------------------------------------

## 3. Teknologi

-   **Backend:** CodeIgniter 4
-   **Frontend:** Tailwind CSS
-   **Interaksi antarmuka:** Alpine.js
-   **Database:** MySQL 8
-   **Pengiriman OTP WhatsApp:** OpenWA yang di-host sendiri
-   **Import Excel:** PhpSpreadsheet
-   **QR code:** satu QR code umum yang mengarah ke URL resmi CAKRA
-   **Deployment:** HTTPS wajib digunakan pada lingkungan produksi

Versi PHP dan dependensi harus dipastikan kompatibel dengan versi
CodeIgniter 4 dan PhpSpreadsheet yang dipilih. Kunci API, kredensial
OpenWA, dan rahasia aplikasi disimpan di konfigurasi lingkungan (`.env`)
atau secret manager, bukan di source code.

------------------------------------------------------------------------

## 4. Ruang Lingkup Fitur

### 4.1 Halaman Publik/Landing Page

Landing page menjadi halaman awal bagi siswa dan pengunjung. Konten yang
diperlukan:

-   Logo sekolah dan logo/nama CAKRA 2026.
-   Navbar: Beranda, Tentang, Visi-Misi Sekolah, Kandidat, Jadwal, dan
    Panduan.
-   Hero section dengan nama pemilihan, tagline, serta tombol menuju
    informasi kandidat atau halaman pemungutan suara.
-   Status pemilihan: belum dimulai, sedang berlangsung, atau telah
    selesai.
-   Tanggal dan waktu pelaksanaan.
-   Tentang CAKRA 2026.
-   Visi dan misi sekolah.
-   Daftar kandidat berisi nomor urut, foto, nama, kelas, serta
    visi-misi/program kandidat.
-   Jadwal tahapan pemilihan.
-   Panduan menggunakan aplikasi.
-   QR code umum menuju halaman resmi CAKRA.
-   FAQ dan kontak bantuan.
-   Footer berisi identitas sekolah, informasi kontak, dan teks footer.

Tombol untuk memberikan suara harus mengarahkan pengguna ke alur
autentikasi terlebih dahulu. Landing page tidak boleh mengungkap data
pribadi siswa.

### 4.2 Pengelolaan Profil Sekolah

Admin dapat mengelola informasi sekolah yang ditampilkan di landing
page.

Tabel yang disarankan: `school_profiles`.

  Kolom                Keterangan
  -------------------- -------------------------------
  `id`                 Primary key
  `school_name`        Nama resmi sekolah
  `logo_path`          Lokasi file logo
  `description`        Deskripsi singkat sekolah
  `vision`             Visi sekolah
  `mission`            Misi sekolah
  `official_website`   Situs resmi sekolah, opsional
  `official_contact`   Kontak resmi, opsional
  `hero_image_path`    Gambar hero, opsional
  `footer_text`        Teks footer
  `updated_by`         Admin yang terakhir mengubah
  `created_at`         Waktu pembuatan
  `updated_at`         Waktu perubahan

Jika aplikasi hanya digunakan satu sekolah, implementasi dapat membatasi
satu profil aktif. Input teks harus di-escape ketika ditampilkan untuk
mencegah XSS.

### 4.3 Pengelolaan Kandidat

Panitia dapat:

-   Menambahkan, mengubah, mengaktifkan, atau menonaktifkan data
    kandidat.
-   Mengatur nomor urut kandidat.
-   Mengunggah foto kandidat.
-   Mengisi nama, kelas, ringkasan profil, visi, misi, dan program
    kerja.
-   Menampilkan pratinjau informasi kandidat.
-   Menentukan kandidat mana yang tampil pada pemilihan tertentu.

Perubahan kandidat setelah pemilihan dimulai harus dibatasi dan dicatat
pada audit log. Kandidat yang sudah memiliki suara tidak boleh dihapus
secara permanen dari catatan historis pemilihan.

### 4.4 Pengelolaan Jadwal Pemilihan

Panitia dapat mengatur:

-   Periode publikasi informasi kandidat.
-   Waktu mulai pemungutan suara.
-   Waktu berakhirnya pemungutan suara.
-   Waktu pengumuman hasil.
-   Status pemilihan.
-   Zona waktu yang digunakan, misalnya `Asia/Makassar`.

Server harus menjadi sumber waktu yang otoritatif. Sistem tidak boleh
mengandalkan jam perangkat siswa untuk memutuskan apakah pemungutan
suara masih dibuka.

### 4.5 Import Data Siswa melalui Excel

Admin/panitia dapat mengimpor data siswa melalui file Excel menggunakan
PhpSpreadsheet.

Kolom template yang disarankan:

  Kolom             Keterangan
  ----------------- -------------------------------------------------------
  `NIS`             Nomor Induk Siswa, wajib
  `Nama Lengkap`    Nama siswa, wajib
  `Tanggal Lahir`   Tanggal lahir, wajib untuk proses klaim akun
  `Kelas`           Kelas siswa, wajib
  `WhatsApp`        Nomor WhatsApp yang telah diverifikasi sekolah, wajib

#### Alur import

1.  Admin mengunduh template Excel.
2.  Admin mengisi data siswa dan mengunggah file.
3.  Sistem memvalidasi tipe file, ukuran, header, format, dan isi setiap
    baris.
4.  Sistem menampilkan pratinjau data yang valid dan daftar kesalahan.
5.  Admin memperbaiki file atau mengonfirmasi proses import.
6.  Sistem menyimpan data dalam transaksi database jika seluruh validasi
    wajib lolos.
7.  Sistem menampilkan ringkasan jumlah data berhasil, ditolak, dan
    duplikat.

#### Aturan keamanan import

-   Batasi ekstensi, MIME type, dan ukuran file.
-   Terima hanya format yang memang diperlukan, misalnya `.xlsx`
    dan/atau `.csv`.
-   Jangan mengeksekusi macro atau formula dari file unggahan.
-   Validasi header dan setiap nilai pada server.
-   Tolak atau tandai NIS duplikat sesuai kebijakan yang ditetapkan.
-   Normalisasi nomor WhatsApp ke format yang konsisten, misalnya format
    internasional.
-   Perlakukan NIS dan nomor telepon sebagai teks agar angka nol di awal
    tidak hilang.
-   Validasi tanggal lahir dengan format yang ditetapkan.
-   Simpan file sementara di lokasi yang tidak dapat diakses publik dan
    hapus setelah proses selesai.
-   Batasi akses hanya untuk peran yang berwenang.
-   Catat hasil import dan identitas administrator yang melakukan
    proses, tetapi jangan memasukkan data pribadi berlebihan ke log.

### 4.6 Klaim Akun Siswa dan OTP WhatsApp

Siswa tidak membuat akun hanya dengan memasukkan nomor WhatsApp baru.
Nomor yang menerima OTP harus berasal dari data yang sudah diimpor dan
diverifikasi oleh sekolah.

#### Alur aktivasi

1.  Siswa membuka halaman resmi CAKRA melalui URL atau QR code umum.
2.  Siswa memilih menu aktivasi akun.
3.  Siswa memasukkan NIS dan tanggal lahir.
4.  Server mencocokkan data dengan data siswa yang telah diimpor.
5.  Jika cocok, sistem mengirim OTP ke nomor WhatsApp yang sudah
    tersimpan pada data siswa.
6.  Siswa memasukkan OTP.
7.  Server memvalidasi OTP, masa berlaku, jumlah percobaan, dan status
    pengguna.
8.  Jika valid, akun pemilih diaktifkan atau proses klaim akun
    diselesaikan.
9.  Sistem menampilkan konfirmasi aktivasi tanpa mengungkap nomor
    WhatsApp lengkap.

Tanggal lahir dan NIS merupakan data pencocokan, bukan faktor
autentikasi yang kuat karena dapat diketahui atau ditebak oleh pihak
lain. OTP membuktikan akses ke nomor WhatsApp yang terdaftar, bukan
dengan sendirinya membuktikan identitas siswa. Sekolah harus memastikan
nomor yang tersimpan benar-benar milik atau berada dalam kendali siswa
yang bersangkutan.

#### Alur login

1.  Siswa memasukkan nomor WhatsApp yang terdaftar.
2.  Server mencari akun yang sesuai dan mengirim OTP apabila akun
    memenuhi syarat.
3.  Siswa memasukkan OTP.
4.  Server memvalidasi OTP dan membuat sesi login yang aman.

#### Ketentuan OTP

-   OTP dibuat dan diverifikasi oleh aplikasi CI4.
-   OpenWA digunakan sebagai sarana pengiriman pesan, bukan sebagai
    sumber kebenaran status OTP.
-   Simpan hash OTP, bukan OTP dalam bentuk teks biasa.
-   OTP hanya berlaku dalam waktu singkat, misalnya 5 menit.
-   Batasi jumlah percobaan verifikasi dan permintaan OTP.
-   Terapkan jeda/rate limit berdasarkan akun, nomor, alamat IP,
    dan/atau perangkat dengan mempertimbangkan privasi.
-   OTP hanya dapat digunakan satu kali.
-   OTP lama dibatalkan ketika OTP baru diterbitkan sesuai kebijakan.
-   Jangan mencatat OTP atau rahasia autentikasi ke log.
-   Berikan pesan kesalahan generik agar sistem tidak mudah digunakan
    untuk enumerasi akun.
-   Sediakan prosedur pemulihan akun melalui panitia ketika siswa
    berganti nomor atau tidak dapat mengakses WhatsApp.
-   Tetapkan kebijakan untuk nomor yang digunakan bersama oleh lebih
    dari satu siswa. Jangan menganggap satu nomor sebagai satu identitas
    siswa tanpa kebijakan eksplisit.

### 4.7 Pemungutan Suara

#### Alur pemilihan

1.  Siswa login dan membuka halaman pemungutan suara.
2.  Server memeriksa status akun dan kelayakan siswa untuk pemilihan
    terkait.
3.  Sistem menampilkan kandidat yang sah untuk pemilihan tersebut.
4.  Siswa memilih satu kandidat.
5.  Sistem menampilkan konfirmasi pilihan sebelum pengiriman.
6.  Siswa mengonfirmasi pilihan.
7.  Server memeriksa ulang jadwal, kelayakan, dan status bahwa siswa
    belum memilih.
8.  Sistem mencatat suara dan menandai kelayakan pemilih telah digunakan
    secara atomik dalam transaksi database.
9.  Siswa melihat konfirmasi bahwa suara telah tercatat.

#### Aturan wajib

-   Satu siswa hanya dapat memberikan satu suara untuk satu pemilihan.
-   Validasi dilakukan kembali di server pada saat suara dikirim.
-   Gunakan transaksi database dan constraint unik agar permintaan
    bersamaan tidak menghasilkan suara ganda.
-   Jangan mempercayai `candidate_id`, status kelayakan, atau status
    pemilihan yang hanya dikirim dari browser tanpa validasi server.
-   Permintaan berulang akibat koneksi lambat harus ditangani secara
    idempoten dan tidak boleh membuat suara tambahan.
-   Setelah suara berhasil dicatat, siswa tidak dapat mengubah pilihan
    kecuali terdapat prosedur resmi yang ditetapkan sebelum pemilihan.
-   Jangan menyimpan `student_id`, `account_id`, atau
    `election_voter_id` pada tabel suara.
-   Jangan menampilkan pilihan kandidat pada halaman riwayat siswa atau
    bukti suara.
-   QR code umum hanya mengarah ke halaman resmi CAKRA. QR code tidak
    boleh memuat NIS, tanggal lahir, OTP, token login, atau data pribadi
    lain.

### 4.8 Privasi dan Kerahasiaan Suara

Sistem harus memisahkan informasi tentang siapa yang sudah memilih dari
informasi tentang kandidat yang dipilih.

Tabel kelayakan pemilih dapat mencatat bahwa seorang siswa telah
menggunakan hak pilihnya. Tabel suara hanya mencatat pemilihan,
kandidat, waktu pencatatan yang diperlukan, dan kode/receipt acak yang
tidak mengidentifikasi siswa.

**Penting:** memisahkan tabel saja belum menjamin anonimitas yang kuat.
Waktu pencatatan yang sangat presisi, log aplikasi, backup, atau akses
administrator ke beberapa tabel dapat memungkinkan korelasi. Jika
persyaratannya adalah administrator tidak boleh dapat menghubungkan
identitas siswa dengan pilihannya, desain perlu ditingkatkan dengan
mekanisme pemungutan suara anonim yang lebih kuat dan ditinjau secara
independen sebelum digunakan.

Akses terhadap data pemilih, data suara, laporan, log, dan backup harus
dibatasi berdasarkan peran. Laporan publik hanya boleh menampilkan
agregat yang sesuai dan tidak mengungkap informasi pribadi.

### 4.9 Hasil dan Laporan

Sistem menyediakan laporan sesuai peran:

-   Jumlah siswa terdaftar.
-   Jumlah pemilih yang memenuhi syarat.
-   Jumlah pemilih yang sudah menggunakan hak pilih.
-   Jumlah pemilih yang belum memilih.
-   Tingkat partisipasi.
-   Perolehan suara setiap kandidat.
-   Total suara sah dan, jika berlaku, suara tidak sah.
-   Ringkasan hasil untuk publik setelah waktu pengumuman.
-   Ekspor laporan ke Excel/PDF jika dibutuhkan.

Jumlah suara setiap kandidat harus dihitung dari tabel suara, bukan dari
data profil pemilih. Laporan publik tidak boleh menampilkan NIS, tanggal
lahir, nomor WhatsApp, atau identitas siswa yang memberikan suara.

------------------------------------------------------------------------

## 5. Rancangan Data Awal

Rancangan berikut adalah dasar awal dan perlu ditinjau sebelum
implementasi final.

### 5.1 `students`

Menyimpan data induk siswa yang diimpor dari sekolah.

Kolom yang disarankan:

-   `id` --- primary key internal.
-   `nis` --- nomor induk siswa, unik.
-   `full_name`.
-   `date_of_birth`.
-   `class_name`.
-   `whatsapp_number` --- nomor ternormalisasi dan telah diverifikasi
    sekolah.
-   `is_active`.
-   `created_at`, `updated_at`.

### 5.2 `school_profiles`

Menyimpan identitas dan konten sekolah untuk landing page, sebagaimana
dijelaskan pada bagian 4.2.

### 5.3 `elections`

Menyimpan data pemilihan:

-   `id`.
-   `title`.
-   `description`.
-   `start_at`.
-   `end_at`.
-   `result_publish_at`.
-   `status`.
-   `created_by`.
-   `created_at`, `updated_at`.

### 5.4 `candidates`

Menyimpan kandidat:

-   `id`.
-   `election_id`.
-   `candidate_number`.
-   `full_name`.
-   `class_name`.
-   `photo_path`.
-   `vision`.
-   `mission`.
-   `program_description`.
-   `is_active`.
-   `created_at`, `updated_at`.

### 5.5 `voter_accounts`

Menyimpan informasi akun dan status aktivasi/login siswa:

-   `id`.
-   `student_id` --- unik.
-   `account_status`.
-   `activated_at`.
-   `last_login_at`.
-   `created_at`, `updated_at`.

Jangan menyimpan OTP dalam tabel ini. Jika kebijakan sekolah menetapkan
satu nomor WhatsApp hanya untuk satu akun, terapkan constraint unik pada
nomor ternormalisasi di tempat yang tepat. Jika nomor dapat digunakan
bersama, jangan menerapkan asumsi satu nomor = satu siswa; tetapkan
mekanisme autentikasi dan pemulihan yang sesuai.

### 5.6 `election_voters`

Menyimpan kelayakan dan status penggunaan hak pilih:

-   `id`.
-   `election_id`.
-   `student_id`.
-   `eligibility_status`.
-   `has_voted`.
-   `voted_at` --- hanya jika diperlukan untuk status operasional dan
    setelah analisis privasi.
-   `created_at`, `updated_at`.

Constraint penting: kombinasi `election_id` dan `student_id` harus unik.

Tabel ini tidak boleh menyimpan `candidate_id` atau informasi kandidat
yang dipilih.

### 5.7 `otp_challenges`

Menyimpan tantangan OTP:

-   `id`.
-   `student_id` atau referensi akun yang diperlukan.
-   `purpose` --- misalnya aktivasi atau login.
-   `otp_hash`.
-   `expires_at`.
-   `attempt_count`.
-   `consumed_at`.
-   `created_at`.

Terapkan pembatasan permintaan dan percobaan OTP, masa berlaku,
penggunaan satu kali, serta pembersihan data kedaluwarsa sesuai
kebijakan retensi.

### 5.8 `votes`

Menyimpan suara secara terpisah dari identitas pemilih:

-   `id`.
-   `election_id`.
-   `candidate_id`.
-   `receipt_code_hash` atau kode acak yang tidak memuat identitas.
-   `cast_at` --- gunakan presisi dan retensi yang telah dipertimbangkan
    dari sisi privasi.
-   `created_at`.

**Dilarang menambahkan relasi langsung ke siswa atau akun pemilih**
seperti `student_id`, `account_id`, atau `election_voter_id` pada tabel
ini jika tujuan desain adalah memisahkan identitas dan pilihan.

Pencegahan suara ganda tetap perlu dijamin melalui alur transaksi dan
kontrol kelayakan pemilih. Desain yang lebih kuat dapat memerlukan token
pemungutan suara sekali pakai yang tidak dapat ditautkan kembali ke
identitas setelah diterbitkan.

### 5.9 `audit_logs`

Mencatat tindakan administratif penting:

-   `id`.
-   `actor_id`.
-   `action`.
-   `entity_type`.
-   `entity_id`.
-   `created_at`.
-   Metadata minimum yang diperlukan untuk investigasi.

Jangan mencatat OTP, token sesi, kredensial, atau kandidat yang dipilih
oleh siswa. Hindari menulis data pribadi lengkap ke metadata log.

### 5.10 `system_settings`

Menyimpan konfigurasi yang dapat dikelola melalui aplikasi jika sesuai,
seperti nama aplikasi, zona waktu, dan pengaturan tampilan. Rahasia
seperti token OpenWA tidak boleh disimpan sebagai pengaturan biasa yang
dapat dibaca pengguna aplikasi; simpan pada konfigurasi lingkungan atau
secret manager.

------------------------------------------------------------------------

## 6. Keamanan Aplikasi

-   Gunakan HTTPS pada semua halaman dan endpoint produksi.
-   Gunakan proteksi CSRF pada form yang relevan.
-   Terapkan validasi server-side untuk seluruh input.
-   Gunakan query builder atau parameter binding untuk menghindari SQL
    injection.
-   Escape output untuk mencegah XSS.
-   Terapkan autentikasi dan otorisasi berbasis peran.
-   Gunakan cookie sesi yang aman, `HttpOnly`, `Secure`, dan kebijakan
    `SameSite` yang sesuai.
-   Atur regenerasi session ID setelah login.
-   Batasi percobaan login dan permintaan OTP.
-   Lindungi endpoint import dan unggahan file.
-   Validasi kepemilikan dan status pemilihan pada setiap permintaan
    suara.
-   Gunakan transaksi dan constraint database untuk mencegah suara
    ganda.
-   Simpan rahasia di `.env` yang tidak masuk version control.
-   Pisahkan akses operasional, akses laporan, dan akses database
    produksi.
-   Batasi retensi data pribadi dan log sesuai kebutuhan sekolah serta
    ketentuan yang berlaku.
-   Siapkan backup terenkripsi, uji pemulihan, dan kontrol akses backup.
-   Lakukan pengujian keamanan sebelum aplikasi digunakan untuk
    pemilihan nyata.

------------------------------------------------------------------------

## 7. Kebutuhan Nonfungsional

### Performa

-   Halaman utama dan halaman kandidat harus ringan untuk perangkat
    seluler.
-   Proses pengiriman suara harus memberi respons yang jelas.
-   Sistem harus menangani permintaan bersamaan pada awal/akhir periode
    pemilihan.
-   Uji beban perlu disesuaikan dengan jumlah siswa dan pola akses yang
    diperkirakan.

### Kompatibilitas

-   Mendukung browser modern pada Android, iOS, dan desktop.
-   Antarmuka responsif untuk layar ponsel.
-   Tidak mensyaratkan instalasi aplikasi native.

### Ketersediaan dan Pemulihan

-   Jadwalkan backup database.
-   Dokumentasikan prosedur pemulihan.
-   Pantau kegagalan pengiriman OTP dan error aplikasi.
-   Sediakan prosedur penanganan ketika OpenWA atau koneksi internet
    tidak tersedia.

### Aksesibilitas dan Kemudahan

-   Gunakan bahasa yang jelas dan instruksi singkat.
-   Pastikan tombol dan formulir nyaman digunakan di layar sentuh.
-   Sediakan status proses, pesan kesalahan yang dapat ditindaklanjuti,
    dan konfirmasi sebelum suara dikirim.

------------------------------------------------------------------------

## 8. Kriteria Penerimaan

1.  Admin dapat mengelola profil sekolah dan konten landing page.
2.  Admin dapat mengimpor data siswa dan melihat kesalahan validasi
    sebelum menyimpan.
3.  NIS duplikat tidak membuat data siswa ganda.
4.  Siswa hanya dapat menerima OTP pada nomor yang terdaftar di data
    sekolah.
5.  OTP kedaluwarsa, sudah digunakan, atau salah tidak dapat digunakan
    untuk aktivasi/login.
6.  Permintaan OTP dan percobaan verifikasi dibatasi.
7.  Siswa yang belum memenuhi syarat tidak dapat memberikan suara.
8.  Siswa tidak dapat memberikan suara ketika pemilihan belum dimulai
    atau sudah berakhir.
9.  Satu siswa tidak dapat mengirim lebih dari satu suara pada pemilihan
    yang sama, termasuk saat dua permintaan masuk hampir bersamaan.
10. Pengulangan request setelah suara berhasil tidak menghasilkan suara
    tambahan.
11. Tabel suara tidak memiliki foreign key langsung ke identitas
    siswa/akun pemilih.
12. Landing page menampilkan kandidat, jadwal, visi-misi sekolah,
    panduan, dan QR code umum.
13. Laporan hasil menampilkan agregat dan tidak membocorkan data pribadi
    pemilih.
14. Aktivitas administratif penting tercatat tanpa merekam OTP atau
    pilihan kandidat yang terkait dengan identitas siswa.
15. Hak akses setiap peran diuji dan tidak dapat dilewati melalui
    manipulasi request.
16. Backup dan prosedur pemulihan telah diuji.
17. Pengujian keamanan dan pengujian beban yang sesuai telah dilakukan
    sebelum pelaksanaan.

------------------------------------------------------------------------

## 9. Rencana Tahapan Implementasi

### Tahap 1 --- Fondasi

-   Setup CodeIgniter 4, Tailwind CSS, Alpine.js, dan MySQL 8.
-   Konfigurasi lingkungan, sesi, CSRF, dan struktur proyek.
-   Implementasi autentikasi admin dan RBAC.
-   Pembuatan migration dan seeder awal.

### Tahap 2 --- Landing Page dan Konten

-   Implementasi landing page responsif.
-   Modul profil sekolah.
-   Modul kandidat dan jadwal.
-   QR code umum dan panduan pemilih.

### Tahap 3 --- Data Siswa

-   Template Excel.
-   Import, validasi, pratinjau, dan laporan kesalahan.
-   Pengelolaan data siswa dan kelayakan pemilih.

### Tahap 4 --- OTP dan Akun Siswa

-   Integrasi OpenWA.
-   Pembuatan dan verifikasi OTP di CI4.
-   Aktivasi akun, login OTP, rate limiting, dan pemulihan akun.

### Tahap 5 --- Pemungutan Suara

-   Tampilan kandidat untuk pemilih.
-   Konfirmasi pilihan.
-   Transaksi pencatatan suara dan pencegahan duplikasi.
-   Pemisahan data identitas pemilih dan suara.
-   Konfirmasi suara tercatat.

### Tahap 6 --- Laporan dan Pengujian

-   Dashboard partisipasi dan hasil.
-   Ekspor laporan.
-   Pengujian fungsional, keamanan, privasi, konkurensi, dan beban.
-   Simulasi pemilihan sebelum pelaksanaan resmi.

------------------------------------------------------------------------

## 10. Risiko dan Mitigasi

  -----------------------------------------------------------------------
  Risiko                              Mitigasi
  ----------------------------------- -----------------------------------
  Nomor WhatsApp siswa salah atau     Verifikasi data sebelum pemilihan
  tidak aktif                         dan sediakan prosedur koreksi oleh
                                      panitia

  Siswa lain mengetahui NIS dan       OTP dikirim ke nomor terdaftar;
  tanggal lahir                       jangan mengandalkan NIS dan tanggal
                                      lahir sebagai autentikasi tunggal

  OTP tidak terkirim                  Pantau status OpenWA, batasi retry,
                                      dan sediakan prosedur bantuan

  Nomor digunakan bersama             Tetapkan kebijakan nomor bersama
                                      dan proses pemulihan yang aman

  Suara ganda karena request          Transaksi database, constraint
  bersamaan                           unik, dan pemeriksaan kelayakan
                                      atomik

  Admin menghubungkan identitas       Pisahkan tabel, batasi akses,
  dengan pilihan                      minimalkan timestamp/log, dan
                                      lakukan tinjauan anonimitas

  File Excel berbahaya atau salah     Validasi file, ukuran, MIME,
  format                              header, isi, dan penyimpanan
                                      sementara yang aman

  Situs tidak dapat diakses saat      Uji beban, pantau layanan, backup,
  pemilihan                           dan siapkan prosedur kontingensi

  Perubahan kandidat/jadwal tanpa     RBAC, audit log, dan pembatasan
  izin                                perubahan setelah pemungutan suara
                                      dimulai
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 11. Di Luar Ruang Lingkup MVP

Kecuali ditetapkan kemudian, hal berikut tidak termasuk MVP:

-   Aplikasi native Android/iOS.
-   Integrasi SSO sekolah.
-   Pemungutan suara untuk banyak jenis organisasi selain pemilihan yang
    dikonfigurasi.
-   Analitik lanjutan di luar laporan partisipasi dan hasil.
-   Mekanisme kriptografi pemungutan suara anonim tingkat lanjut.

Jika sekolah mensyaratkan jaminan anonimitas yang kuat terhadap
administrator, mekanisme pemungutan suara anonim tingkat lanjut bukan
sekadar fitur tambahan; desain tersebut perlu ditetapkan sebelum
implementasi voting.

------------------------------------------------------------------------

## 12. Definition of Done

CAKRA 2026 dinyatakan siap digunakan setelah:

-   Semua fitur MVP berjalan sesuai kriteria penerimaan.
-   Import data telah diuji dengan data valid, tidak valid, dan
    duplikat.
-   OTP diuji untuk kasus valid, salah, kedaluwarsa, dipakai ulang,
    serta rate limit.
-   Pencegahan suara ganda telah diuji dengan permintaan bersamaan.
-   Pemisahan identitas pemilih dan data suara telah diperiksa pada
    skema, kode, log, dan laporan.
-   Hak akses admin, panitia, siswa, dan publik telah diuji.
-   Halaman responsif di perangkat seluler.
-   Backup dan pemulihan berhasil diuji.
-   Panitia memperoleh panduan penggunaan dan prosedur pemulihan akun.
-   Simulasi pemilihan telah dilakukan sebelum hari pelaksanaan.

------------------------------------------------------------------------

## 13. Identitas dan Pesan Utama

**CAKRA 2026**\
**Calon Ketua Aspiratif, Kreatif, Responsif, dan Amanah**

*Pilih dengan Hati, Pimpin dengan Aksi.*

CAKRA 2026 hadir untuk membantu pemilihan Ketua OSIS berlangsung lebih
praktis, modern, dan paperless, dengan pengalaman yang mudah bagi siswa
serta pengelolaan yang lebih terstruktur bagi panitia.
