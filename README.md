# Sistem Manajemen Presensi Sekolah

Aplikasi web presensi sekolah berbasis **PHP Native (MVC) + MySQL**, dibangun tanpa framework (bukan Laravel/CodeIgniter/Symfony). **Seluruh PHASE 1–13 sudah selesai dan berjalan.**

1. Project setup, arsitektur MVC, Router, koneksi Database
2. Autentikasi, Role, Middleware (otorisasi di backend, bukan hanya sembunyikan menu)
3. Master Data: Siswa, Guru, Wali Murid, Kelas, Jurusan, Tahun Ajaran (CRUD penuh, terhubung QR otomatis)
4. QR System: halaman `/qr/students`, `/qr/teachers` — regenerate, download, cetak satuan/massal
5. Attendance Engine: `AttendanceService` (status Hadir/Terlambat dari `attendance_settings`, IN/OUT, anti-duplikat), Presensi Siswa/Guru, Presensi Manual, Koreksi Presensi
6. QR Scanner API: `POST /api/attendance/scan`, `GET /api/health`, `GET /api/kiosk/stats`
7. Kiosk Mode: login kiosk (kode + PIN), scanner fullscreen dengan `html5-qrcode`, state machine, beep sintesis Web Audio API, statistik & scan terakhir real-time
8. WhatsApp: `WhatsAppService` (abstraction, siap dihubungkan ke provider apa pun), antrian non-blocking, Log WhatsApp + retry, pengaturan tersimpan di database
9. Dashboard: 4 grafik Chart.js (tren 7 hari, distribusi status, rekap per kelas, keterlambatan) + counter real-time via polling `/api/dashboard/stats`
10. Laporan: Rekap Harian/Bulanan/Siswa/Guru/Kelas, masing-masing bisa diekspor ke **Excel** (PhpSpreadsheet) dan **PDF** (Dompdf, dengan kop sekolah)
11. Cetak Kartu ID: kartu depan-belakang ukuran fisik **85.60mm × 53.98mm** (bukan px), cetak satuan maupun massal per kelas/jurusan/semua siswa, serta kartu guru
12. Security hardening: security headers, rate limiting login (brute-force protection), proteksi formula-injection Excel, ganti password sendiri + peringatan password default
13. Testing menyeluruh (lihat bagian 14)

## 1. Requirement

- PHP >= 8.2 (diuji dengan PHP 8.4)
- MySQL >= 8.0
- Composer 2.x
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`

## 2. Install Composer Dependencies

```bash
cd /var/www/html/presensi
composer install
```

Library yang digunakan: `vlucas/phpdotenv`, `endroid/qr-code`, `phpoffice/phpspreadsheet`, `dompdf/dompdf` (dompdf dipakai versi `^3.1` karena versi `2.x` memiliki advisory keamanan yang diblokir Composer).

## 3. Setup Database

Buat database dan user MySQL khusus aplikasi:

```sql
CREATE DATABASE school_attendance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'presensi_app'@'localhost' IDENTIFIED BY 'password_anda';
GRANT ALL PRIVILEGES ON school_attendance.* TO 'presensi_app'@'localhost';
FLUSH PRIVILEGES;
```

## 4. Konfigurasi .env

```bash
cp .env.example .env
```

Isi minimal yang wajib disesuaikan:

```
DB_HOST=127.0.0.1
DB_DATABASE=school_attendance
DB_USERNAME=presensi_app
DB_PASSWORD="password_anda"
```

> Jika password mengandung karakter `#`, bungkus dengan tanda kutip ganda karena `#` dianggap awal komentar oleh parser .env.

`APP_KEY` bisa digenerate dengan:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

## 5. Migration (membuat seluruh tabel)

```bash
php database/migrate.php
```

Skrip ini menjalankan `database/migrations/001_create_schema.sql` yang membuat 21 tabel (users, roles, permissions, role_permissions, students, teachers, guardians, classes, majors, academic_years, student_qr_codes, teacher_qr_codes, attendance_settings, school_days, kiosk_devices, attendances, attendance_corrections, whatsapp_settings, whatsapp_logs, school_settings, audit_logs) lengkap dengan foreign key, index, dan unique constraint.

## 6. Seeder (data awal + 100 siswa + 10 guru demo)

```bash
php database/seeders/seed.php
```

Seeder ini **mengosongkan ulang (TRUNCATE)** seluruh tabel master sebelum mengisi ulang, sehingga aman dijalankan berkali-kali saat development. Data yang dibuat:

- 4 role: Super Admin, Admin/TU, Guru, Siswa (lengkap dengan permission per role)
- 1 sekolah, 1 tahun ajaran aktif (2025/2026), 3 jurusan, 9 kelas
- 10 guru demo + QR aktif otomatis
- 100 siswa demo + wali murid + QR aktif otomatis
- 4 akun login default (lihat bawah)
- 1 perangkat kiosk demo (`KIOSK-001` / PIN `123456`)

### Login Default

| Role | Username | Password |
|---|---|---|
| Super Admin | `admin` | `admin123` |
| Admin/TU | `tu` | `tu12345` |
| Guru | `guru1` | `guru12345` |
| Siswa | `siswa1` | `siswa12345` |

| Kiosk | Kode | PIN |
|---|---|---|
| Gerbang Utama | `KIOSK-001` | `123456` |

**Wajib**: ganti password default ini setelah login pertama di lingkungan produksi — halaman `/profile` menampilkan peringatan otomatis dan form ganti password selama akun masih memakai password default hasil seeding.

### Data Presensi Demo (opsional, untuk melihat Dashboard & Laporan terisi)

```bash
php database/seeders/seed_attendance_demo.php
```

Menghasilkan riwayat presensi realistis untuk 14 hari sekolah terakhir (~78% hadir, ~12% terlambat, sisanya izin/sakit/alpa) sehingga grafik Dashboard dan halaman Laporan langsung terlihat terisi. Aman dijalankan ulang (menghapus data presensi 14 hari terakhir sebelum mengisi ulang) — jangan jalankan di database produksi yang sudah punya data presensi asli.

## 7. Menjalankan Project

Development server bawaan PHP:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Lalu buka `http://127.0.0.1:8000/login`.

Untuk Apache/Nginx, arahkan document root ke folder `public/` (bukan root project). File `public/.htaccess` sudah disediakan untuk Apache (`mod_rewrite`). Untuk Nginx tambahkan:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 8. Struktur Folder

```
app/
  Controllers/     Menerima request, validasi awal, panggil Service, kembalikan response
  Services/        Business logic (StudentService, TeacherService, QrCodeService, AuditLogService, FileUploadService)
  Models/          Akses data (extends App\Core\Model, PDO prepared statement)
  Middleware/       AuthMiddleware, RoleMiddleware/AdminMiddleware/SuperAdminMiddleware, GuestMiddleware, KioskAuthMiddleware
  Core/            Router, Database (PDO singleton), Controller, Model, Request, Response, View, Session, Auth, Csrf, Validator
  Views/           View per modul (layouts, components, auth, students, teachers, guardians, classes, majors, academic_years, dashboard, settings)
  Helpers/functions.php   Helper global: e(), asset(), old(), csrf_field(), format_date(), dst.
config/            app.php, database.php (baca dari .env)
database/
  migrations/001_create_schema.sql   Skema lengkap
  migrate.php        Runner migrasi
  seeders/seed.php   Runner seeder
routes/            web.php, api.php + file per modul (students.php, teachers.php, dst.)
public/            Document root: index.php (front controller), assets/css/js/images, uploads/
storage/logs/      Log error PHP
```

### Alur MVC

```
Request → public/index.php → Router → Middleware (Auth/Role) → Controller → Service → Model (PDO) → Database
                                                                     ↓
                                                            View (layout + partial)
```

## 9. Keamanan yang Sudah Diterapkan

- **PDO prepared statements** di seluruh query (tidak ada string concatenation SQL)
- **CSRF token** per session, wajib di semua form POST (`Csrf::verify`, helper `csrf_field()`)
- **Password hashing** dengan `password_hash()` / `password_verify()`
- **Session security**: `session_regenerate_id(true)` setelah login, cookie `HttpOnly`, `SameSite=Lax`
- **Otorisasi di backend**: setiap route admin dibungkus `AuthMiddleware` + `AdminMiddleware`/`SuperAdminMiddleware`, bukan sekadar menyembunyikan menu di sidebar (guru/siswa yang mengakses langsung URL admin akan mendapat HTTP 403)
- **Upload validation**: MIME check via `finfo`, whitelist ekstensi (jpg/jpeg/png/webp), maksimal 2MB, nama file diacak (`random_bytes`), lihat `FileUploadService`
- **QR token**: token acak kriptografis (`random_bytes(20)`) dengan prefix `STU|`/`TEA|`, **bukan** NIS/NIP mentah; regenerate QR otomatis me-revoke token lama dalam satu transaction
- **Output escaping**: helper `e()` (htmlspecialchars) dipakai di semua view untuk mencegah XSS
- **Audit log**: login, logout, create/update/delete master data, generate/regenerate QR, scan presensi, presensi manual, koreksi, kiosk login/logout, retry WhatsApp, ganti password, update pengaturan — semuanya tercatat di tabel `audit_logs` dengan old/new data, IP, dan user agent
- **Rate limiting login**: 5 kali gagal login untuk username yang sama dalam 15 menit akan diblokir sementara (`LoginThrottleService`), mencegah brute-force tanpa perlu infrastruktur tambahan (memanfaatkan data `audit_logs` yang sudah ada)
- **Security headers**: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(self)` (kamera hanya boleh diakses dari origin sendiri, dibutuhkan kiosk)
- **Proteksi formula-injection Excel**: nilai sel yang diawali `=`, `+`, `-`, atau `@` otomatis diberi prefix aman sebelum ditulis ke file `.xlsx` (`ReportExportService::sanitizeCell`)
- **Ganti password sendiri**: setiap user (`/profile`) dapat mengganti password sendiri setelah verifikasi password lama; halaman Profil & Dashboard menampilkan peringatan otomatis jika akun masih memakai password default hasil seeding

## 10. Konfigurasi WhatsApp

Buka **Pengaturan > WhatsApp** (`/settings/whatsapp`, hanya Super Admin/Admin TU) untuk mengisi API URL, API Key, dan nomor pengirim provider WhatsApp Anda — konfigurasi ini disimpan di tabel `whatsapp_settings`, **bukan** di `.env`, sehingga bisa diubah kapan saja tanpa deploy ulang. `.env` hanya dipakai sebagai nilai default awal.

`WhatsAppService::sendMessage()` adalah satu-satunya titik yang boleh memanggil API provider (Controller/Service lain tidak pernah memanggil API WhatsApp langsung). Jika API belum dikonfigurasi, pesan tetap dicatat di **Log WhatsApp** (`/whatsapp/logs`) dengan status `failed` dan alasan yang jelas — presensi tidak pernah gagal karena WhatsApp.

Alur pengiriman **non-blocking**: saat scan berhasil, API `/api/attendance/scan` mengirim response ke kiosk terlebih dahulu (`Response::sendAndContinue()` + `fastcgi_finish_request()`/flush manual), baru kemudian memproses pengiriman WhatsApp di background proses yang sama — kiosk tidak pernah menunggu WhatsApp selesai terkirim.

Gunakan halaman **Notifikasi > WhatsApp** (`/whatsapp/send`) untuk uji kirim manual, dan tombol **Retry** di Log WhatsApp untuk mengirim ulang pesan yang gagal.

## 11. Konfigurasi Scanner & Kiosk

1. Login sebagai admin, buka **Pengaturan > Perangkat Kiosk** (`/settings/kiosks`) → Tambah Kiosk (nama, kode unik seperti `KIOSK-001`, PIN 4–12 digit). Data demo `KIOSK-001` / PIN `123456` sudah dibuat oleh seeder.
2. Di perangkat kiosk (laptop/tablet di gerbang), buka `/kiosk/login`, masukkan kode + PIN.
3. Klik **MULAI KIOSK** — browser akan meminta izin kamera dan fullscreen, lalu scanner langsung siap (state machine `IDLE → SCANNING → PROCESSING → SUCCESS/ERROR → RESETTING`, lihat `public/assets/js/kiosk.js`).
4. Kamera diprioritaskan otomatis (facingMode `environment` di HP), atau pilih manual dari dropdown jika ada beberapa kamera.
5. Bunyi beep dihasilkan langsung oleh Web Audio API (bukan file mp3) — beep berbeda untuk sukses/terlambat/error, bisa dimatikan lewat toggle "Suara ON/OFF".
6. Untuk keluar dari mode kiosk: klik logo 5x lalu masukkan PIN perangkat.
7. Jam presensi (kapan dianggap Hadir/Terlambat, jam presensi pulang) diatur di **Pengaturan > Jam Presensi** (`/settings/attendance-hours`) — dibaca dari tabel `attendance_settings`, tidak di-hardcode. Hari sekolah aktif diatur di **Pengaturan > Hari Sekolah** (`/settings/school-days`); di luar hari aktif, scan akan ditolak dengan status `not_school_day`.

Endpoint API yang digunakan kiosk: `POST /api/attendance/scan` (scan QR), `GET /api/kiosk/stats` (statistik real-time), `GET /api/health` (indikator SERVER ONLINE/OFFLINE, di-poll tiap 30 detik).

## 12. Cetak QR

QR Code aktif setiap siswa/guru bisa dilihat dan diunduh dari halaman detail (`/students/{id}`, `/teachers/{id}`) maupun dari halaman khusus **QR Code > QR Siswa/QR Guru** (`/qr/students`, `/qr/teachers`), yang juga menyediakan regenerate dan cetak (satuan maupun massal via `/qr/students/print-bulk?ids=1,2,3`). QR digenerate otomatis (`QrCodeService`) saat data dibuat; regenerate akan me-revoke QR lama dalam satu transaction sehingga tidak pernah ada dua QR aktif untuk orang yang sama.

## 13. Dashboard & Laporan

Dashboard (`/dashboard`) menampilkan 8 kartu statistik hari ini (Total Siswa/Guru, Hadir, Terlambat, Izin, Sakit, Alpa, Belum Hadir) yang **diperbarui otomatis setiap 15 detik tanpa reload** (polling `GET /api/dashboard/stats`), plus 4 grafik Chart.js berbasis data 7 hari terakhir: tren kehadiran, distribusi status, rekap per kelas, dan tren keterlambatan.

Menu **Laporan** menyediakan 5 jenis rekap, semuanya bisa diekspor ke **Excel** (`?format=excel`) atau **PDF** (`?format=pdf`, dengan kop nama sekolah + alamat + judul + periode + tabel + tanggal cetak):

| Laporan | URL | Filter |
|---|---|---|
| Rekap Harian | `/reports/daily` | tanggal, kelas, jurusan |
| Rekap Bulanan | `/reports/monthly` | bulan, tahun, kelas |
| Rekap Siswa | `/reports/students` → pilih siswa | rentang tanggal |
| Rekap Guru | `/reports/teachers` → pilih guru | rentang tanggal |
| Rekap Kelas | `/reports/classes` → pilih kelas | rentang tanggal |

## 14. Cetak Kartu ID

Menu **QR Code > Cetak Kartu** (`/idcard`) mencetak kartu identitas fisik ukuran kartu ATM/kredit sebenarnya, diatur lewat CSS `@page { size: 85.60mm 53.98mm; margin: 0; }` (satuan mm, bukan px, sesuai standar ID-1). Setiap kartu terdiri dari dua sisi:

- **Depan**: logo & nama sekolah, judul "KARTU IDENTITAS SISWA/GURU", foto, nama, NIS/NIP, badge kelas/jabatan — gaya neo-brutalism border tebal + offset shadow.
- **Belakang**: nama sekolah, QR Code besar dengan whitespace cukup (tanpa dekorasi menutupi area QR), nama, NIS/NIP, label "KARTU PRESENSI".

Opsi cetak:
- **Satuan** — tombol "Cetak Kartu ID" di halaman detail Siswa/Guru (`/idcard/students/{id}`, `/idcard/teachers/{id}`).
- **Massal** — dari `/idcard`: semua siswa aktif, per kelas, per jurusan, atau semua guru aktif. Setiap kartu menggunakan `page-break-after` + `break-inside: avoid` agar tidak terpotong saat dicetak berurutan.

## 15. Troubleshooting

| Masalah | Penyebab / Solusi |
|---|---|
| `Access denied for user` saat migrasi | Password di `.env` mengandung `#` tanpa tanda kutip — bungkus dengan `"..."` |
| `SQLSTATE[HY093]: Invalid parameter number` | Jangan gunakan named placeholder PDO yang sama dua kali dalam satu query (`PDO::ATTR_EMULATE_PREPARES` di-nonaktifkan) |
| CSS/JS 404 saat pakai `php -S` | Pastikan `public/index.php` memiliki guard `PHP_SAPI === 'cli-server'` yang mengembalikan `false` untuk file statis (sudah ditangani) |
| Foto tidak tampil | Pastikan folder `public/uploads/students` dan `public/uploads/teachers` writable oleh web server |
| Error "Cannot add foreign key constraint" saat re-run schema manual | Jalankan `php database/migrate.php` dari database kosong, jangan menjalankan ulang skrip di database yang sudah berisi tabel lama dengan struktur berbeda |

## 16. Testing Manual yang Sudah Diverifikasi

**Auth & Master Data**
- [x] Login berhasil untuk 4 role (super_admin, admin_tu, guru, siswa)
- [x] Password salah ditolak dengan pesan error; logout menghapus session
- [x] Role restriction: guru/siswa yang mengakses `/students` langsung via URL mendapat HTTP 403 (bukan hanya redirect)
- [x] CRUD Siswa/Guru (+auto QR), Wali Murid, Kelas, Jurusan, Tahun Ajaran — create/update/delete/search/filter/pagination
- [x] Dashboard menampilkan statistik real dari database

**QR & Attendance Engine**
- [x] QR generate otomatis saat create, regenerate (QR lama revoked, QR baru active), download PNG, cetak satuan & massal
- [x] `POST /api/attendance/scan`: sukses (present), sukses terlambat (late), IN lalu OUT berurutan, `already_completed` setelah IN+OUT, `already_attended`, `invalid_qr`, `not_school_day`, `window_closed` — seluruhnya diuji langsung ke endpoint dan hasilnya sesuai
- [x] Jam Hadir/Terlambat dan hari sekolah dibaca dari database (`attendance_settings`, `school_days`), bukan hardcode — diverifikasi dengan mengubah pengaturan dan melihat perubahan hasil scan
- [x] Presensi Manual tersimpan + tercatat di audit log
- [x] Koreksi Presensi: mengubah status/jam presensi tersimpan di `attendances` **dan** riwayat old/new/reason tersimpan di `attendance_corrections`

**Kiosk**
- [x] Login kiosk dengan kode + PIN (`KIOSK-001` / `123456` demo)
- [x] Tombol "MULAI KIOSK" mengaktifkan fullscreen + kamera; saat kamera tidak tersedia, tampil "Kamera tidak tersedia." + tombol "COBA LAGI" (diverifikasi di lingkungan tanpa webcam)
- [x] Jam, tanggal, status SERVER ONLINE, dan statistik (Hadir/Terlambat/Sudah Pulang/Belum Hadir) tampil real-time dari API

**WhatsApp**
- [x] Presensi masuk memicu antrian WhatsApp (`whatsapp_logs` status `pending` → diproses langsung tanpa memblokir response scan)
- [x] Tanpa API dikonfigurasi, log berstatus `failed` dengan alasan jelas, presensi tetap sukses
- [x] Retry dari halaman Log WhatsApp menambah `attempt_count` dan mencoba ulang
- [x] Pengaturan WhatsApp tersimpan di database dan langsung dipakai (tanpa perlu ubah `.env`)

**Settings & Audit**
- [x] Pengaturan Sekolah, Jam Presensi, Hari Sekolah tersimpan dan langsung mempengaruhi logic presensi
- [x] Manajemen User (tambah/edit/reset password) dan Role & Permission (matrix checkbox per role) berfungsi
- [x] Audit log tercatat untuk seluruh aksi di atas (login, CRUD, regenerate QR, scan, manual attendance, correction, kiosk login/logout, whatsapp retry, update settings, change password)

**Dashboard & Laporan**
- [x] Dashboard: 4 grafik Chart.js (tren 7 hari, distribusi status, rekap kelas, keterlambatan) tampil dengan data nyata dari 14 hari riwayat demo
- [x] Counter dashboard diperbarui otomatis tiap 15 detik tanpa reload (`GET /api/dashboard/stats`)
- [x] Rekap Harian/Bulanan/Siswa/Guru/Kelas menampilkan data yang benar sesuai filter (tanggal, kelas, jurusan, bulan, rentang tanggal)
- [x] Export Excel menghasilkan file `.xlsx` valid (diverifikasi dengan `file report.xlsx` → "Microsoft Excel 2007+")
- [x] Export PDF menghasilkan dokumen dengan kop sekolah, judul, periode, tabel, dan tanggal cetak (dirender sukses oleh viewer PDF browser)

**Cetak Kartu ID**
- [x] Kartu satuan siswa & guru: ukuran fisik 85.6mm × 53.98mm, sisi depan (foto/nama/NIS/kelas) dan belakang (QR besar + label "KARTU PRESENSI") tampil sesuai desain
- [x] Cetak massal per kelas menghasilkan pasangan depan-belakang berurutan untuk seluruh siswa di kelas tersebut
- [x] Cetak massal guru menghasilkan kartu untuk seluruh guru aktif

**Security Hardening**
- [x] CSRF: diaudit — seluruh method controller yang menangani POST (create/update/delete/regenerate/retry/dsb.) memanggil `requireCsrf()`
- [x] SQL Injection: diaudit — tidak ada string concatenation SQL, seluruh query pakai prepared statement dengan named/positional parameter
- [x] XSS: diaudit — seluruh output data dinamis di view memakai `e()`, `(int)` cast, atau berasal dari domain nilai tetap (enum/label internal)
- [x] Rate limiting login: 5x gagal untuk username sama dalam 15 menit → percobaan ke-6 diblokir dengan pesan waktu tunggu (diuji end-to-end via curl)
- [x] Role restriction diuji ulang untuk seluruh route baru (Reports, ID Card, Kiosk Devices, WhatsApp, User/Role management) — guru mendapat 403 di semuanya; admin_tu bisa akses Reports/IDCard/Kiosk tapi diblokir dari User/Role management (Super Admin saja)
- [x] Ganti password sendiri: password lama salah ditolak, sukses mengganti password dan peringatan "password default" otomatis hilang setelahnya
- [x] Formula-injection Excel: nilai sel yang diawali `=`/`+`/`-`/`@` diberi prefix aman

Semua 13 fase pada spesifikasi awal telah diimplementasikan dan diverifikasi berjalan.
