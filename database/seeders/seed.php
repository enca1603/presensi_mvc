<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

$config = require dirname(__DIR__, 2) . '/config/database.php';
$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $config['driver'], $config['host'], $config['port'], $config['database'], $config['charset']);
$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

function qrToken(string $prefix): string
{
    return $prefix . '|' . bin2hex(random_bytes(20));
}

// -----------------------------------------------------------------
// Truncate in dependency-safe order (DDL causes implicit commit,
// so this must happen before the seeding transaction begins)
// -----------------------------------------------------------------
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ([
    'audit_logs', 'whatsapp_logs', 'attendance_corrections', 'attendances',
    'student_qr_codes', 'teacher_qr_codes', 'users', 'students', 'classes',
    'teachers', 'guardians', 'majors', 'academic_years', 'role_permissions',
    'permissions', 'roles', 'school_settings', 'attendance_settings',
    'school_days', 'whatsapp_settings', 'kiosk_devices',
] as $table) {
    $pdo->exec("TRUNCATE TABLE {$table}");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$pdo->beginTransaction();

try {
    // -----------------------------------------------------------------
    // Roles
    // -----------------------------------------------------------------
    $roles = [
        ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Akses penuh ke seluruh sistem'],
        ['name' => 'Admin / TU', 'slug' => 'admin_tu', 'description' => 'Mengelola data master, presensi, QR, laporan, dan WhatsApp'],
        ['name' => 'Guru', 'slug' => 'guru', 'description' => 'Melihat profil dan presensi pribadi, dapat scan jika diberi izin'],
        ['name' => 'Siswa', 'slug' => 'siswa', 'description' => 'Melihat profil, QR pribadi, dan riwayat presensi'],
    ];
    $roleIds = [];
    $stmt = $pdo->prepare('INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :description)');
    foreach ($roles as $role) {
        $stmt->execute($role);
        $roleIds[$role['slug']] = (int) $pdo->lastInsertId();
    }

    // -----------------------------------------------------------------
    // Permissions
    // -----------------------------------------------------------------
    $permissions = [
        ['students.view', 'Lihat Siswa', 'students'],
        ['students.manage', 'Kelola Siswa', 'students'],
        ['teachers.view', 'Lihat Guru', 'teachers'],
        ['teachers.manage', 'Kelola Guru', 'teachers'],
        ['guardians.manage', 'Kelola Wali Murid', 'guardians'],
        ['classes.manage', 'Kelola Kelas', 'classes'],
        ['majors.manage', 'Kelola Jurusan', 'majors'],
        ['academic_years.manage', 'Kelola Tahun Ajaran', 'academic_years'],
        ['qr.manage', 'Kelola QR Code', 'qr'],
        ['attendance.view', 'Lihat Presensi', 'attendance'],
        ['attendance.manual', 'Input Presensi Manual', 'attendance'],
        ['attendance.correction', 'Koreksi Presensi', 'attendance'],
        ['attendance.kiosk', 'Akses Scan Kiosk', 'attendance'],
        ['reports.view', 'Lihat Laporan', 'reports'],
        ['reports.export', 'Export Laporan', 'reports'],
        ['whatsapp.manage', 'Kelola WhatsApp', 'whatsapp'],
        ['settings.manage', 'Kelola Pengaturan Sekolah', 'settings'],
        ['users.manage', 'Kelola User', 'settings'],
        ['roles.manage', 'Kelola Role & Permission', 'settings'],
        ['audit_log.view', 'Lihat Audit Log', 'settings'],
        ['kiosk.manage', 'Kelola Perangkat Kiosk', 'kiosk'],
    ];
    $permIds = [];
    $stmt = $pdo->prepare('INSERT INTO permissions (slug, name, module) VALUES (:slug, :name, :module)');
    foreach ($permissions as [$slug, $name, $module]) {
        $stmt->execute(['slug' => $slug, 'name' => $name, 'module' => $module]);
        $permIds[$slug] = (int) $pdo->lastInsertId();
    }

    $adminTuPermissions = [
        'students.view', 'students.manage', 'teachers.view', 'teachers.manage',
        'guardians.manage', 'classes.manage', 'majors.manage', 'academic_years.manage',
        'qr.manage', 'attendance.view', 'attendance.manual', 'attendance.correction',
        'attendance.kiosk', 'reports.view', 'reports.export', 'whatsapp.manage',
        'kiosk.manage',
    ];
    $guruPermissions = ['students.view', 'attendance.view'];

    $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
    foreach ($adminTuPermissions as $slug) {
        $stmt->execute(['role_id' => $roleIds['admin_tu'], 'permission_id' => $permIds[$slug]]);
    }
    foreach ($guruPermissions as $slug) {
        $stmt->execute(['role_id' => $roleIds['guru'], 'permission_id' => $permIds[$slug]]);
    }

    // -----------------------------------------------------------------
    // School settings
    // -----------------------------------------------------------------
    $pdo->prepare('INSERT INTO school_settings (school_name, npsn, address, phone, email, principal_name) VALUES (:n, :npsn, :a, :p, :e, :pr)')
        ->execute([
            'n' => 'SMK Negeri 1 Harapan Bangsa',
            'npsn' => '20123456',
            'a' => 'Jl. Pendidikan No. 45, Jakarta',
            'p' => '021-5550123',
            'e' => 'info@smkharapanbangsa.sch.id',
            'pr' => 'Drs. Bambang Sutrisno, M.Pd.',
        ]);

    // -----------------------------------------------------------------
    // Attendance settings
    // -----------------------------------------------------------------
    $pdo->prepare('INSERT INTO attendance_settings (type, day_start_time, late_after_time, day_end_time, out_enabled, out_start_time, out_end_time) VALUES (:t, :s, :l, :e, 1, :os, :oe)')
        ->execute(['t' => 'student', 's' => '06:30:00', 'l' => '07:00:00', 'e' => '10:00:00', 'os' => '13:00:00', 'oe' => '17:00:00']);
    $pdo->prepare('INSERT INTO attendance_settings (type, day_start_time, late_after_time, day_end_time, out_enabled, out_start_time, out_end_time) VALUES (:t, :s, :l, :e, 1, :os, :oe)')
        ->execute(['t' => 'teacher', 's' => '06:15:00', 'l' => '06:45:00', 'e' => '10:00:00', 'os' => '14:00:00', 'oe' => '18:00:00']);

    // -----------------------------------------------------------------
    // School days (Senin - Jumat aktif)
    // -----------------------------------------------------------------
    $stmt = $pdo->prepare('INSERT INTO school_days (day_of_week, is_active) VALUES (:d, :a)');
    for ($d = 0; $d <= 6; $d++) {
        $stmt->execute(['d' => $d, 'a' => ($d >= 1 && $d <= 5) ? 1 : 0]);
    }

    // -----------------------------------------------------------------
    // WhatsApp settings (belum dikonfigurasi)
    // -----------------------------------------------------------------
    $pdo->prepare('INSERT INTO whatsapp_settings (api_url, api_key, sender, is_active) VALUES (NULL, NULL, NULL, 0)')->execute();

    // -----------------------------------------------------------------
    // Academic year
    // -----------------------------------------------------------------
    $pdo->prepare('INSERT INTO academic_years (name, start_date, end_date, is_active) VALUES (:n, :s, :e, 1)')
        ->execute(['n' => '2025/2026', 's' => '2025-07-14', 'e' => '2026-06-19']);
    $academicYearId = (int) $pdo->lastInsertId();

    // -----------------------------------------------------------------
    // Majors
    // -----------------------------------------------------------------
    $majors = [
        ['Rekayasa Perangkat Lunak', 'RPL'],
        ['Teknik Komputer dan Jaringan', 'TKJ'],
        ['Multimedia', 'MM'],
    ];
    $majorIds = [];
    $stmt = $pdo->prepare('INSERT INTO majors (name, code) VALUES (:n, :c)');
    foreach ($majors as [$name, $code]) {
        $stmt->execute(['n' => $name, 'c' => $code]);
        $majorIds[$code] = (int) $pdo->lastInsertId();
    }

    // -----------------------------------------------------------------
    // Teachers (10 demo)
    // -----------------------------------------------------------------
    $teacherFirstNames = ['Agus', 'Budi', 'Siti', 'Dewi', 'Hendra', 'Rina', 'Yusuf', 'Nurul', 'Arief', 'Wulan'];
    $teacherLastNames = ['Santoso', 'Wijaya', 'Pratama', 'Kusuma', 'Hidayat', 'Lestari', 'Setiawan', 'Handayani', 'Nugroho', 'Ramadhani'];
    $subjects = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Pemrograman Web', 'Basis Data', 'Jaringan Komputer', 'Fisika', 'Kimia', 'PKN', 'Produktif RPL'];
    $positions = ['Guru Mata Pelajaran', 'Wali Kelas', 'Kepala Jurusan', 'Guru BK'];

    $teacherIds = [];
    $stmt = $pdo->prepare(
        'INSERT INTO teachers (nip, nuptk, full_name, gender, birth_place, birth_date, address, phone, email, position, subject, status)
         VALUES (:nip, :nuptk, :name, :gender, :bp, :bd, :addr, :phone, :email, :position, :subject, "active")'
    );
    for ($i = 0; $i < 10; $i++) {
        $name = $teacherFirstNames[$i] . ' ' . $teacherLastNames[$i];
        $gender = $i % 2 === 0 ? 'L' : 'P';
        $stmt->execute([
            'nip' => '19800101' . str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
            'nuptk' => '30' . str_pad((string) ($i + 1), 14, '0', STR_PAD_LEFT),
            'name' => $name,
            'gender' => $gender,
            'bp' => 'Jakarta',
            'bd' => sprintf('19%d-0%d-1%d', 75 + $i, ($i % 9) + 1, $i % 9),
            'addr' => 'Jl. Guru No. ' . ($i + 1) . ', Jakarta',
            'phone' => '628' . str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT),
            'email' => strtolower(str_replace(' ', '.', $name)) . '@smkharapanbangsa.sch.id',
            'position' => $positions[$i % count($positions)],
            'subject' => $subjects[$i],
        ]);
        $teacherIds[] = (int) $pdo->lastInsertId();
    }

    // Generate QR for teachers
    $qrStmt = $pdo->prepare('INSERT INTO teacher_qr_codes (teacher_id, token, status) VALUES (:tid, :token, "active")');
    foreach ($teacherIds as $tid) {
        $qrStmt->execute(['tid' => $tid, 'token' => qrToken('TEA')]);
    }

    // -----------------------------------------------------------------
    // Classes: 3 grade x 3 major x 1 rombel = 9 classes
    // -----------------------------------------------------------------
    $grades = ['X', 'XI', 'XII'];
    $classIds = [];
    $stmt = $pdo->prepare('INSERT INTO classes (name, grade, major_id, academic_year_id, homeroom_teacher_id, status) VALUES (:n, :g, :m, :ay, :h, "active")');
    $teacherPointer = 0;
    foreach ($grades as $grade) {
        foreach ($majors as [$name, $code]) {
            $className = "{$grade} {$code} 1";
            $homeroom = $teacherIds[$teacherPointer % count($teacherIds)];
            $teacherPointer++;
            $stmt->execute([
                'n' => $className,
                'g' => $grade,
                'm' => $majorIds[$code],
                'ay' => $academicYearId,
                'h' => $homeroom,
            ]);
            $classIds[] = [
                'id' => (int) $pdo->lastInsertId(),
                'name' => $className,
                'major_id' => $majorIds[$code],
            ];
        }
    }

    // -----------------------------------------------------------------
    // Guardians + Students (100 demo)
    // -----------------------------------------------------------------
    $studentFirstNames = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kiki', 'Lina', 'Made', 'Nia', 'Oscar', 'Putri', 'Qori', 'Rudi', 'Sari', 'Tono', 'Umi', 'Vino', 'Wati', 'Yanto', 'Zahra'];
    $studentLastNames = ['Fauzan', 'Santoso', 'Wijaya', 'Permata', 'Kusuma', 'Ramadhan', 'Anggraini', 'Saputra', 'Maharani', 'Firmansyah', 'Utami', 'Nugraha', 'Pertiwi', 'Setiawan', 'Amelia'];

    $guardianStmt = $pdo->prepare('INSERT INTO guardians (name, relationship, phone, email, address, status) VALUES (:n, :r, :p, :e, :a, "active")');
    $studentStmt = $pdo->prepare(
        'INSERT INTO students (nis, nisn, full_name, nickname, gender, birth_place, birth_date, address, phone, email, class_id, major_id, academic_year_id, guardian_id, entry_year, status)
         VALUES (:nis, :nisn, :name, :nick, :gender, :bp, :bd, :addr, :phone, :email, :class_id, :major_id, :ay, :guardian_id, :entry_year, "active")'
    );

    $studentIds = [];
    $entryYear = 2025;
    for ($i = 1; $i <= 100; $i++) {
        $first = $studentFirstNames[($i - 1) % count($studentFirstNames)];
        $last = $studentLastNames[($i + 3) % count($studentLastNames)];
        $fullName = "{$first} {$last}";
        $gender = ($i % 2 === 0) ? 'P' : 'L';

        $guardianStmt->execute([
            'n' => 'Bapak/Ibu ' . $last,
            'r' => $gender === 'L' ? 'father' : 'mother',
            'p' => '6281' . str_pad((string) $i, 9, '0', STR_PAD_LEFT),
            'e' => null,
            'a' => 'Jl. Wali Murid No. ' . $i . ', Jakarta',
        ]);
        $guardianId = (int) $pdo->lastInsertId();

        $class = $classIds[($i - 1) % count($classIds)];

        $studentStmt->execute([
            'nis' => str_pad((string) (240000 + $i), 8, '0', STR_PAD_LEFT),
            'nisn' => str_pad((string) (30240000000 + $i), 10, '0', STR_PAD_LEFT),
            'name' => $fullName,
            'nick' => $first,
            'gender' => $gender,
            'bp' => 'Jakarta',
            'bd' => sprintf('2008-%02d-%02d', ($i % 12) + 1, ($i % 28) + 1),
            'addr' => 'Jl. Siswa No. ' . $i . ', Jakarta',
            'phone' => null,
            'email' => null,
            'class_id' => $class['id'],
            'major_id' => $class['major_id'],
            'ay' => $academicYearId,
            'guardian_id' => $guardianId,
            'entry_year' => $entryYear,
        ]);
        $studentIds[] = (int) $pdo->lastInsertId();
    }

    // Generate QR for students
    $qrStmt = $pdo->prepare('INSERT INTO student_qr_codes (student_id, token, status) VALUES (:sid, :token, "active")');
    foreach ($studentIds as $sid) {
        $qrStmt->execute(['sid' => $sid, 'token' => qrToken('STU')]);
    }

    // -----------------------------------------------------------------
    // Users
    // -----------------------------------------------------------------
    $userStmt = $pdo->prepare(
        'INSERT INTO users (role_id, username, email, password, full_name, status, student_id, teacher_id)
         VALUES (:role_id, :username, :email, :password, :full_name, "active", :student_id, :teacher_id)'
    );

    $userStmt->execute([
        'role_id' => $roleIds['super_admin'],
        'username' => 'admin',
        'email' => 'admin@smkharapanbangsa.sch.id',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'full_name' => 'Administrator Sistem',
        'student_id' => null,
        'teacher_id' => null,
    ]);

    $userStmt->execute([
        'role_id' => $roleIds['admin_tu'],
        'username' => 'tu',
        'email' => 'tu@smkharapanbangsa.sch.id',
        'password' => password_hash('tu12345', PASSWORD_DEFAULT),
        'full_name' => 'Petugas Tata Usaha',
        'student_id' => null,
        'teacher_id' => null,
    ]);

    $userStmt->execute([
        'role_id' => $roleIds['guru'],
        'username' => 'guru1',
        'email' => null,
        'password' => password_hash('guru12345', PASSWORD_DEFAULT),
        'full_name' => $teacherFirstNames[0] . ' ' . $teacherLastNames[0],
        'student_id' => null,
        'teacher_id' => $teacherIds[0],
    ]);

    $userStmt->execute([
        'role_id' => $roleIds['siswa'],
        'username' => 'siswa1',
        'email' => null,
        'password' => password_hash('siswa12345', PASSWORD_DEFAULT),
        'full_name' => $studentFirstNames[0] . ' ' . $studentLastNames[3],
        'student_id' => $studentIds[0],
        'teacher_id' => null,
    ]);

    // -----------------------------------------------------------------
    // Demo kiosk device (KIOSK-001 / PIN 123456)
    // -----------------------------------------------------------------
    $pdo->prepare('INSERT INTO kiosk_devices (name, device_code, location, pin_hash, status) VALUES (:n, :c, :l, :p, "active")')
        ->execute([
            'n' => 'Gerbang Utama',
            'c' => 'KIOSK-001',
            'l' => 'Gerbang Depan Sekolah',
            'p' => password_hash('123456', PASSWORD_DEFAULT),
        ]);

    $pdo->commit();

    echo "Seeding completed successfully.\n";
    echo "- Roles: " . count($roles) . "\n";
    echo "- Permissions: " . count($permissions) . "\n";
    echo "- Teachers: " . count($teacherIds) . " (QR generated)\n";
    echo "- Classes: " . count($classIds) . "\n";
    echo "- Students: " . count($studentIds) . " (QR generated)\n";
    echo "\nLogin default:\n";
    echo "  Super Admin : admin / admin123\n";
    echo "  Admin TU    : tu / tu12345\n";
    echo "  Guru        : guru1 / guru12345\n";
    echo "  Siswa       : siswa1 / siswa12345\n";
    echo "\nKiosk demo    : KIOSK-001 / PIN 123456\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
