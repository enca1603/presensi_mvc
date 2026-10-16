<?php
$role = $currentUser['role_slug'] ?? null;
$isAdmin = in_array($role, ['super_admin', 'admin_tu'], true);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-box">SP</div>
        <div>
            <div class="name"><?= e(config('app.name')) ?></div>
            <div class="sub">Manajemen Presensi</div>
        </div>
    </div>

    <div class="nav-section">
        <a class="nav-item <?= active_menu('/dashboard') ?>" href="/dashboard">
            <i data-lucide="layout-dashboard"></i> Dashboard
        </a>
    </div>

    <?php if ($isAdmin): ?>
    <div class="nav-section">
        <div class="nav-section-title">Master Data</div>
        <a class="nav-item <?= active_menu('/students') ?>" href="/students"><i data-lucide="users"></i> Siswa</a>
        <a class="nav-item <?= active_menu('/teachers') ?>" href="/teachers"><i data-lucide="user-round"></i> Guru</a>
        <a class="nav-item <?= active_menu('/guardians') ?>" href="/guardians"><i data-lucide="heart-handshake"></i> Wali Murid</a>
        <a class="nav-item <?= active_menu('/classes') ?>" href="/classes"><i data-lucide="school"></i> Kelas</a>
        <a class="nav-item <?= active_menu('/majors') ?>" href="/majors"><i data-lucide="graduation-cap"></i> Jurusan</a>
        <a class="nav-item <?= active_menu('/academic-years') ?>" href="/academic-years"><i data-lucide="calendar-range"></i> Tahun Ajaran</a>
    </div>
    <?php endif; ?>

    <div class="nav-section">
        <div class="nav-section-title">Presensi</div>
        <a class="nav-item <?= active_menu('/kiosk') ?>" href="/kiosk/login" target="_blank"><i data-lucide="monitor-check"></i> Kiosk Presensi</a>
        <?php if ($isAdmin): ?>
        <a class="nav-item <?= active_menu('/attendance/students') ?>" href="/attendance/students"><i data-lucide="clipboard-check"></i> Presensi Siswa</a>
        <a class="nav-item <?= active_menu('/attendance/teachers') ?>" href="/attendance/teachers"><i data-lucide="clipboard-list"></i> Presensi Guru</a>
        <a class="nav-item <?= active_menu('/attendance/manual') ?>" href="/attendance/manual"><i data-lucide="pencil-line"></i> Presensi Manual</a>
        <a class="nav-item <?= active_menu('/attendance/corrections') ?>" href="/attendance/corrections"><i data-lucide="rotate-ccw"></i> Koreksi Presensi</a>
        <?php else: ?>
        <a class="nav-item <?= active_menu('/attendance/history') ?>" href="/attendance/history"><i data-lucide="history"></i> Riwayat Presensi</a>
        <?php endif; ?>
    </div>

    <?php if ($isAdmin): ?>
    <div class="nav-section">
        <div class="nav-section-title">QR Code</div>
        <a class="nav-item <?= active_menu('/qr/students') ?>" href="/qr/students"><i data-lucide="qr-code"></i> QR Siswa</a>
        <a class="nav-item <?= active_menu('/qr/teachers') ?>" href="/qr/teachers"><i data-lucide="qr-code"></i> QR Guru</a>
        <a class="nav-item <?= active_menu('/idcard') ?>" href="/idcard"><i data-lucide="printer"></i> Cetak Kartu</a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Laporan</div>
        <a class="nav-item <?= active_menu('/reports/daily') ?>" href="/reports/daily"><i data-lucide="file-text"></i> Rekap Harian</a>
        <a class="nav-item <?= active_menu('/reports/monthly') ?>" href="/reports/monthly"><i data-lucide="calendar-days"></i> Rekap Bulanan</a>
        <a class="nav-item <?= active_menu('/reports/students') ?>" href="/reports/students"><i data-lucide="user-check"></i> Rekap Siswa</a>
        <a class="nav-item <?= active_menu('/reports/teachers') ?>" href="/reports/teachers"><i data-lucide="user-cog"></i> Rekap Guru</a>
        <a class="nav-item <?= active_menu('/reports/classes') ?>" href="/reports/classes"><i data-lucide="layers"></i> Rekap Kelas</a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Notifikasi</div>
        <a class="nav-item <?= active_menu('/whatsapp/send') ?>" href="/whatsapp/send"><i data-lucide="message-circle"></i> WhatsApp</a>
        <a class="nav-item <?= active_menu('/whatsapp/logs') ?>" href="/whatsapp/logs"><i data-lucide="list-checks"></i> Log WhatsApp</a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Pengaturan</div>
        <a class="nav-item <?= active_menu('/settings/school') ?>" href="/settings/school"><i data-lucide="school-2"></i> Sekolah</a>
        <a class="nav-item <?= active_menu('/settings/attendance-hours') ?>" href="/settings/attendance-hours"><i data-lucide="clock"></i> Jam Presensi</a>
        <a class="nav-item <?= active_menu('/settings/school-days') ?>" href="/settings/school-days"><i data-lucide="calendar-check"></i> Hari Sekolah</a>
        <a class="nav-item <?= active_menu('/settings/whatsapp') ?>" href="/settings/whatsapp"><i data-lucide="settings-2"></i> WhatsApp</a>
        <a class="nav-item <?= active_menu('/settings/kiosks') ?>" href="/settings/kiosks"><i data-lucide="monitor-cog"></i> Perangkat Kiosk</a>
        <a class="nav-item <?= active_menu('/settings/users') ?>" href="/settings/users"><i data-lucide="user-cog"></i> User</a>
        <a class="nav-item <?= active_menu('/settings/roles') ?>" href="/settings/roles"><i data-lucide="shield"></i> Role</a>
        <a class="nav-item <?= active_menu('/settings/audit-log') ?>" href="/settings/audit-log"><i data-lucide="scroll-text"></i> Audit Log</a>
    </div>
    <?php else: ?>
    <div class="nav-section">
        <div class="nav-section-title">Akun Saya</div>
        <a class="nav-item <?= active_menu('/profile') ?>" href="/profile"><i data-lucide="user"></i> Profil &amp; QR Saya</a>
    </div>
    <?php endif; ?>
</aside>
