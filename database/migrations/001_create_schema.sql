-- =====================================================================
-- Sistem Manajemen Presensi Sekolah - Database Schema
-- Database: school_attendance
-- Engine: InnoDB, Charset: utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- roles
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- permissions
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    module VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permissions_slug (slug),
    KEY idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- role_permissions
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- academic_years
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS academic_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL COMMENT 'contoh: 2025/2026',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_academic_years_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- majors (jurusan)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS majors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_majors_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- guardians (wali murid)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS guardians (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    relationship ENUM('father','mother','guardian') NOT NULL DEFAULT 'father',
    phone VARCHAR(20) NULL COMMENT 'format 628xxxxxxxxxx',
    email VARCHAR(150) NULL,
    address TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_guardians_phone (phone),
    KEY idx_guardians_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- teachers (guru)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) NULL,
    nuptk VARCHAR(30) NULL,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('L','P') NOT NULL,
    birth_place VARCHAR(100) NULL,
    birth_date DATE NULL,
    address TEXT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    position VARCHAR(100) NULL,
    subject VARCHAR(100) NULL,
    photo VARCHAR(255) NULL,
    status ENUM('active','inactive','resigned') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_teachers_nip (nip),
    UNIQUE KEY uq_teachers_nuptk (nuptk),
    KEY idx_teachers_status (status),
    KEY idx_teachers_full_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- classes (kelas)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT 'contoh: X RPL 1',
    grade VARCHAR(10) NOT NULL COMMENT 'contoh: X, XI, XII',
    major_id INT UNSIGNED NULL,
    academic_year_id INT UNSIGNED NOT NULL,
    homeroom_teacher_id INT UNSIGNED NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_classes_name_year (name, academic_year_id),
    KEY idx_classes_major (major_id),
    KEY idx_classes_academic_year (academic_year_id),
    KEY idx_classes_homeroom (homeroom_teacher_id),
    CONSTRAINT fk_classes_major FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL,
    CONSTRAINT fk_classes_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    CONSTRAINT fk_classes_homeroom FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    photo VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    student_id INT UNSIGNED NULL COMMENT 'linked student account for role siswa',
    teacher_id INT UNSIGNED NULL COMMENT 'linked teacher account for role guru',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    KEY idx_users_status (status),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- students (siswa)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(30) NOT NULL,
    nisn VARCHAR(30) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    nickname VARCHAR(50) NULL,
    gender ENUM('L','P') NOT NULL,
    birth_place VARCHAR(100) NULL,
    birth_date DATE NULL,
    address TEXT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    photo VARCHAR(255) NULL,
    class_id INT UNSIGNED NULL,
    major_id INT UNSIGNED NULL,
    academic_year_id INT UNSIGNED NULL,
    guardian_id INT UNSIGNED NULL,
    entry_year YEAR NULL,
    status ENUM('active','graduated','transferred','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_students_nis (nis),
    UNIQUE KEY uq_students_nisn (nisn),
    KEY idx_students_class (class_id),
    KEY idx_students_guardian (guardian_id),
    KEY idx_students_status (status),
    KEY idx_students_full_name (full_name),
    CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_major FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_academic_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_guardian FOREIGN KEY (guardian_id) REFERENCES guardians(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- student_qr_codes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_qr_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    status ENUM('active','revoked') NOT NULL DEFAULT 'active',
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_qr_token (token),
    KEY idx_student_qr_student (student_id),
    KEY idx_student_qr_status (status),
    CONSTRAINT fk_student_qr_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satu QR aktif per siswa dijamin oleh QrCodeService (transaction: revoke lama, insert baru).

-- ---------------------------------------------------------------------
-- teacher_qr_codes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS teacher_qr_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    status ENUM('active','revoked') NOT NULL DEFAULT 'active',
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_teacher_qr_token (token),
    KEY idx_teacher_qr_teacher (teacher_id),
    KEY idx_teacher_qr_status (status),
    CONSTRAINT fk_teacher_qr_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satu QR aktif per guru dijamin oleh QrCodeService (transaction: revoke lama, insert baru).

-- ---------------------------------------------------------------------
-- attendance_settings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('student','teacher') NOT NULL,
    day_start_time TIME NOT NULL DEFAULT '06:30:00' COMMENT 'jam paling awal presensi masuk dihitung',
    late_after_time TIME NOT NULL DEFAULT '07:00:00' COMMENT 'setelah jam ini status menjadi late',
    day_end_time TIME NOT NULL DEFAULT '10:00:00' COMMENT 'batas akhir presensi masuk dicatat',
    out_enabled TINYINT(1) NOT NULL DEFAULT 1,
    out_start_time TIME NOT NULL DEFAULT '13:00:00',
    out_end_time TIME NOT NULL DEFAULT '17:00:00',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance_settings_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- school_days (hari sekolah aktif)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS school_days (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Minggu .. 6=Sabtu',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_school_days_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- kiosk_devices
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kiosk_devices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    device_code VARCHAR(50) NOT NULL,
    location VARCHAR(150) NULL,
    pin_hash VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_seen_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_kiosk_device_code (device_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- attendances
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NULL,
    teacher_id INT UNSIGNED NULL,
    attendance_date DATE NOT NULL,
    type ENUM('in','out') NOT NULL,
    scan_time TIME NOT NULL,
    status ENUM('present','late','permission','sick','absent','official_duty') NOT NULL,
    method ENUM('qr','manual') NOT NULL DEFAULT 'qr',
    kiosk_device_id INT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    recorded_by INT UNSIGNED NULL COMMENT 'user id jika input manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_attendance (student_id, attendance_date, type),
    UNIQUE KEY uq_teacher_attendance (teacher_id, attendance_date, type),
    KEY idx_attendances_date (attendance_date),
    KEY idx_attendances_status (status),
    KEY idx_attendances_student (student_id),
    KEY idx_attendances_teacher (teacher_id),
    KEY idx_attendances_kiosk (kiosk_device_id),
    CONSTRAINT fk_attendances_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendances_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendances_kiosk FOREIGN KEY (kiosk_device_id) REFERENCES kiosk_devices(id) ON DELETE SET NULL,
    CONSTRAINT fk_attendances_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_attendances_person CHECK (student_id IS NOT NULL OR teacher_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- attendance_corrections
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_corrections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_id BIGINT UNSIGNED NOT NULL,
    old_data JSON NOT NULL,
    new_data JSON NOT NULL,
    reason VARCHAR(255) NOT NULL,
    changed_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_corrections_attendance (attendance_id),
    KEY idx_corrections_changed_by (changed_by),
    CONSTRAINT fk_corrections_attendance FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE CASCADE,
    CONSTRAINT fk_corrections_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- whatsapp_settings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS whatsapp_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_url VARCHAR(255) NULL,
    api_key VARCHAR(255) NULL,
    sender VARCHAR(30) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    notify_on_in TINYINT(1) NOT NULL DEFAULT 1,
    notify_on_late TINYINT(1) NOT NULL DEFAULT 1,
    notify_on_out TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- whatsapp_logs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS whatsapp_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_id BIGINT UNSIGNED NULL,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    provider_response TEXT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_whatsapp_logs_attendance (attendance_id),
    KEY idx_whatsapp_logs_status (status),
    CONSTRAINT fk_whatsapp_logs_attendance FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- school_settings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS school_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(150) NOT NULL,
    npsn VARCHAR(30) NULL,
    address TEXT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    logo VARCHAR(255) NULL,
    principal_name VARCHAR(150) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- audit_logs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) NULL,
    description VARCHAR(255) NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_logs_user (user_id),
    KEY idx_audit_logs_module (module),
    KEY idx_audit_logs_action (action),
    KEY idx_audit_logs_created (created_at),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
