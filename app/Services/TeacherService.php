<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Teacher;
use RuntimeException;

class TeacherService
{
    public static function paginate(array $filters, int $page, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(full_name LIKE :search1 OR nip LIKE :search2 OR nuptk LIKE :search3)';
            $needle = '%' . $filters['search'] . '%';
            $params['search1'] = $needle;
            $params['search2'] = $needle;
            $params['search3'] = $needle;
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $db = Database::connection();

        $countStmt = $db->prepare("SELECT COUNT(*) c FROM teachers {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $sql = "SELECT t.*, (SELECT status FROM teacher_qr_codes WHERE teacher_id = t.id AND status = 'active' LIMIT 1) AS qr_status
                FROM teachers t {$where} ORDER BY full_name ASC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public static function create(array $data, ?array $photoFile): int
    {
        if (!empty($data['nip']) && Teacher::findBy('nip', $data['nip'])) {
            throw new RuntimeException('NIP sudah terdaftar.');
        }

        $photoPath = $photoFile ? FileUploadService::uploadPhoto($photoFile, 'teachers') : null;

        $teacherId = Teacher::create([
            'nip' => $data['nip'] ?: null,
            'nuptk' => $data['nuptk'] ?: null,
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'] ?: null,
            'birth_date' => $data['birth_date'] ?: null,
            'address' => $data['address'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'position' => $data['position'] ?: null,
            'subject' => $data['subject'] ?: null,
            'photo' => $photoPath,
            'status' => $data['status'] ?? 'active',
        ]);

        QrCodeService::generateForTeacher($teacherId);

        return $teacherId;
    }

    public static function update(int $id, array $data, ?array $photoFile): void
    {
        $existing = !empty($data['nip']) ? Teacher::findBy('nip', $data['nip']) : null;
        if ($existing && (int) $existing['id'] !== $id) {
            throw new RuntimeException('NIP sudah digunakan guru lain.');
        }

        $current = Teacher::find($id);
        if (!$current) {
            throw new RuntimeException('Guru tidak ditemukan.');
        }

        $payload = [
            'nip' => $data['nip'] ?: null,
            'nuptk' => $data['nuptk'] ?: null,
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'] ?: null,
            'birth_date' => $data['birth_date'] ?: null,
            'address' => $data['address'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'position' => $data['position'] ?: null,
            'subject' => $data['subject'] ?: null,
            'status' => $data['status'] ?? 'active',
        ];

        if ($photoFile) {
            $payload['photo'] = FileUploadService::uploadPhoto($photoFile, 'teachers');
            FileUploadService::deletePhoto($current['photo']);
        }

        Teacher::update($id, $payload);
    }

    public static function delete(int $id): void
    {
        $teacher = Teacher::find($id);
        if ($teacher) {
            FileUploadService::deletePhoto($teacher['photo']);
        }
        Teacher::delete($id);
    }
}
