<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceClosedException;
use App\Services\AttendanceCompletedException;
use App\Services\AttendanceDuplicateException;
use App\Services\AttendanceInactiveException;
use App\Services\AttendanceInvalidQrException;
use App\Services\AttendanceNotSchoolDayException;
use App\Services\AttendanceService;
use App\Services\NotificationService;

class AttendanceApiController extends Controller
{
    private const MIN_INTERVAL_MS = 350;

    public function scan(Request $request): void
    {
        $token = trim((string) ($request->isJson() ? $request->input('token') : $request->input('token')));

        if ($token === '') {
            Response::error('Token QR tidak boleh kosong.', 'invalid_request', 422);
            return;
        }

        if (!$this->throttleOk()) {
            Response::error('Terlalu cepat, coba lagi sebentar.', 'rate_limited', 429);
            return;
        }

        $kioskDeviceId = Session::get('kiosk_device_id');
        $method = $kioskDeviceId ? 'qr' : 'qr';

        try {
            $result = AttendanceService::processScan($token, $kioskDeviceId, $method);

            // Send the response to the scanner immediately, then process
            // the WhatsApp queue afterwards so the kiosk never waits on it.
            Response::sendAndContinue([
                'success' => true,
                'status' => $result['attendance']['status'],
                'message' => $result['attendance']['status'] === 'late' ? 'Presensi berhasil, namun tercatat terlambat.' : 'Presensi berhasil',
                'person_type' => $result['person_type'],
                'person' => $this->formatPerson($result['person_type'], $result['person']),
                'attendance' => $result['attendance'],
                'notification' => $result['notification'],
            ], 200);

            if ($result['notification']['status'] === 'queued') {
                NotificationService::dispatchPending(3);
            }
            exit;
        } catch (AttendanceInvalidQrException $e) {
            Response::error($e->getMessage(), 'invalid_qr', 422);
        } catch (AttendanceInactiveException $e) {
            Response::error($e->getMessage(), 'inactive', 403);
        } catch (AttendanceDuplicateException $e) {
            Response::error($e->getMessage(), 'already_attended', 409);
        } catch (AttendanceCompletedException $e) {
            Response::error($e->getMessage(), 'already_completed', 409);
        } catch (AttendanceClosedException $e) {
            Response::error($e->getMessage(), 'window_closed', 422);
        } catch (AttendanceNotSchoolDayException $e) {
            Response::error($e->getMessage(), 'not_school_day', 422);
        } catch (\Throwable $e) {
            error_log('[SCAN ERROR] ' . $e->getMessage());
            Response::error('Terjadi kesalahan pada server.', 'server_error', 500);
        }
    }

    private function formatPerson(string $type, array $person): array
    {
        if ($type === 'student') {
            return [
                'id' => (int) $person['id'],
                'name' => $person['full_name'],
                'nis' => $person['nis'],
                'class' => $this->classNameFor((int) $person['class_id']),
                'photo' => $person['photo'],
            ];
        }

        return [
            'id' => (int) $person['id'],
            'name' => $person['full_name'],
            'nip' => $person['nip'],
            'position' => $person['position'],
            'photo' => $person['photo'],
        ];
    }

    private function classNameFor(?int $classId): ?string
    {
        if (!$classId) {
            return null;
        }
        $stmt = \App\Core\Database::connection()->prepare('SELECT name FROM classes WHERE id = :id');
        $stmt->execute(['id' => $classId]);
        $row = $stmt->fetch();
        return $row['name'] ?? null;
    }

    private function throttleOk(): bool
    {
        $now = (int) (microtime(true) * 1000);
        $last = Session::get('_last_scan_ms', 0);
        Session::set('_last_scan_ms', $now);
        return ($now - $last) >= self::MIN_INTERVAL_MS;
    }
}
