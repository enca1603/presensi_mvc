<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\KioskDevice;
use App\Services\AuditLogService;

class KioskDeviceController extends Controller
{
    public function index(Request $request): void
    {
        $devices = KioskDevice::all('name');
        $this->view('settings.kiosks.index', ['title' => 'Perangkat Kiosk', 'devices' => $devices]);
    }

    public function create(Request $request): void
    {
        $this->view('settings.kiosks.form', ['title' => 'Tambah Kiosk', 'device' => null]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'device_code', 'location', 'pin', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:100',
            'device_code' => 'required|max:50',
            'pin' => 'required|min:4|max:12',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/settings/kiosks/create');
            return;
        }

        if (KioskDevice::findBy('device_code', $data['device_code'])) {
            Session::flash('error', 'Kode kiosk sudah digunakan.');
            $this->redirect('/settings/kiosks/create');
            return;
        }

        $id = KioskDevice::create([
            'name' => $data['name'],
            'device_code' => strtoupper($data['device_code']),
            'location' => $data['location'] ?: null,
            'pin_hash' => password_hash($data['pin'], PASSWORD_DEFAULT),
            'status' => $data['status'] ?? 'active',
        ]);

        AuditLogService::log('create', 'kiosk_devices', (string) $id, "Menambahkan kiosk {$data['name']}");
        Session::flash('success', 'Perangkat kiosk berhasil ditambahkan.');
        $this->redirect('/settings/kiosks');
    }

    public function edit(Request $request, string $id): void
    {
        $device = KioskDevice::find((int) $id);
        if (!$device) {
            Session::flash('error', 'Perangkat kiosk tidak ditemukan.');
            $this->redirect('/settings/kiosks');
            return;
        }

        $this->view('settings.kiosks.form', ['title' => 'Edit Kiosk', 'device' => $device]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'device_code', 'location', 'pin', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:100',
            'device_code' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect("/settings/kiosks/{$id}/edit");
            return;
        }

        $existing = KioskDevice::findBy('device_code', $data['device_code']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            Session::flash('error', 'Kode kiosk sudah digunakan perangkat lain.');
            $this->redirect("/settings/kiosks/{$id}/edit");
            return;
        }

        $payload = [
            'name' => $data['name'],
            'device_code' => strtoupper($data['device_code']),
            'location' => $data['location'] ?: null,
            'status' => $data['status'] ?? 'active',
        ];

        if (!empty($data['pin'])) {
            $payload['pin_hash'] = password_hash($data['pin'], PASSWORD_DEFAULT);
        }

        KioskDevice::update((int) $id, $payload);
        AuditLogService::log('update', 'kiosk_devices', $id, "Memperbarui kiosk {$data['name']}");
        Session::flash('success', 'Perangkat kiosk berhasil diperbarui' . (!empty($data['pin']) ? ' dan PIN direset.' : '.'));
        $this->redirect('/settings/kiosks');
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $device = KioskDevice::find((int) $id);
        if ($device) {
            KioskDevice::delete((int) $id);
            AuditLogService::log('delete', 'kiosk_devices', $id, "Menghapus kiosk {$device['name']}");
            Session::flash('success', 'Perangkat kiosk berhasil dihapus.');
        }

        $this->redirect('/settings/kiosks');
    }
}
