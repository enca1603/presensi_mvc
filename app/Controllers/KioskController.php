<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;

class KioskController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('kiosk.login', [
            'title' => 'Login Kiosk',
            'error' => Session::getFlash('error'),
        ], null);
    }

    public function login(Request $request): void
    {
        if (!\App\Core\Csrf::verify($request->input('_csrf_token'))) {
            Session::flash('error', 'Sesi form kedaluwarsa, silakan coba lagi.');
            $this->redirect('/kiosk/login');
            return;
        }

        $deviceCode = strtoupper(trim((string) $request->input('device_code')));
        $pin = (string) $request->input('pin');

        $stmt = Database::connection()->prepare('SELECT * FROM kiosk_devices WHERE device_code = :code AND status = "active" LIMIT 1');
        $stmt->execute(['code' => $deviceCode]);
        $device = $stmt->fetch();

        if (!$device || !password_verify($pin, $device['pin_hash'])) {
            Session::flash('error', 'Kode kiosk atau PIN salah.');
            $this->redirect('/kiosk/login');
            return;
        }

        Session::regenerate(true);
        Session::set('kiosk_device_id', (int) $device['id']);
        Session::set('kiosk_device_name', $device['name']);

        $update = Database::connection()->prepare('UPDATE kiosk_devices SET last_seen_at = NOW() WHERE id = :id');
        $update->execute(['id' => $device['id']]);

        AuditLogService::log('kiosk_login', 'kiosk', (string) $device['id'], "Kiosk {$device['name']} login");

        $this->redirect('/kiosk');
    }

    public function show(Request $request): void
    {
        $deviceId = Session::get('kiosk_device_id');
        $stmt = Database::connection()->prepare('SELECT * FROM kiosk_devices WHERE id = :id');
        $stmt->execute(['id' => $deviceId]);
        $device = $stmt->fetch();

        $school = Database::connection()->query('SELECT * FROM school_settings ORDER BY id LIMIT 1')->fetch();

        $this->view('kiosk.index', [
            'title' => 'Kiosk Presensi',
            'device' => $device,
            'school' => $school ?: ['school_name' => 'Sistem Presensi Sekolah'],
        ], null);
    }

    public function verifyExitPin(Request $request): void
    {
        $pin = (string) $request->input('pin');
        $deviceId = Session::get('kiosk_device_id');

        $stmt = Database::connection()->prepare('SELECT pin_hash FROM kiosk_devices WHERE id = :id');
        $stmt->execute(['id' => $deviceId]);
        $device = $stmt->fetch();

        if (!$device || !password_verify($pin, $device['pin_hash'])) {
            $this->json(['success' => false, 'message' => 'PIN salah.'], 401);
            return;
        }

        $this->json(['success' => true, 'message' => 'PIN benar.']);
    }

    public function logout(Request $request): void
    {
        $deviceId = Session::get('kiosk_device_id');
        if ($deviceId) {
            AuditLogService::log('kiosk_logout', 'kiosk', (string) $deviceId, 'Kiosk logout');
        }
        Session::remove('kiosk_device_id');
        Session::remove('kiosk_device_name');
        $this->redirect('/kiosk/login');
    }
}
