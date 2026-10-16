<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Guardian;
use App\Models\Student;
use RuntimeException;

class StudentService
{
    public static function paginate(array $filters, int $page, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        [$where, $params] = self::buildFilters($filters);

        $db = Database::connection();

        $countSql = "SELECT COUNT(*) c FROM students s {$where}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $sql = "SELECT s.*, c.name AS class_name, m.name AS major_name, g.name AS guardian_name,
                       (SELECT status FROM student_qr_codes WHERE student_id = s.id AND status = 'active' LIMIT 1) AS qr_status
                FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                LEFT JOIN majors m ON m.id = s.major_id
                LEFT JOIN guardians g ON g.id = s.guardian_id
                {$where}
                ORDER BY s.full_name ASC
                LIMIT :limit OFFSET :offset";

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

    private static function buildFilters(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(s.full_name LIKE :search1 OR s.nis LIKE :search2 OR s.nisn LIKE :search3)';
            $needle = '%' . $filters['search'] . '%';
            $params['search1'] = $needle;
            $params['search2'] = $needle;
            $params['search3'] = $needle;
        }

        if (!empty($filters['class_id'])) {
            $conditions[] = 's.class_id = :class_id';
            $params['class_id'] = $filters['class_id'];
        }

        if (!empty($filters['major_id'])) {
            $conditions[] = 's.major_id = :major_id';
            $params['major_id'] = $filters['major_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 's.status = :status';
            $params['status'] = $filters['status'];
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        return [$where, $params];
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, c.name AS class_name, m.name AS major_name,
                    g.name AS guardian_name, g.phone AS guardian_phone, g.relationship AS guardian_relationship
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN majors m ON m.id = s.major_id
             LEFT JOIN guardians g ON g.id = s.guardian_id
             WHERE s.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data, ?array $photoFile): int
    {
        if (Student::findBy('nis', $data['nis'])) {
            throw new RuntimeException('NIS sudah terdaftar.');
        }
        if (Student::findBy('nisn', $data['nisn'])) {
            throw new RuntimeException('NISN sudah terdaftar.');
        }

        $guardianId = $data['guardian_id'] ?: null;
        if (!$guardianId && !empty($data['guardian_name'])) {
            $guardianId = Guardian::create([
                'name' => $data['guardian_name'],
                'relationship' => $data['guardian_relationship'] ?? 'father',
                'phone' => $data['guardian_phone'] ?? null,
                'email' => $data['guardian_email'] ?? null,
                'address' => $data['guardian_address'] ?? null,
                'status' => 'active',
            ]);
        }

        $photoPath = $photoFile ? FileUploadService::uploadPhoto($photoFile, 'students') : null;

        $studentId = Student::create([
            'nis' => $data['nis'],
            'nisn' => $data['nisn'],
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?: null,
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'] ?: null,
            'birth_date' => $data['birth_date'] ?: null,
            'address' => $data['address'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'photo' => $photoPath,
            'class_id' => $data['class_id'] ?: null,
            'major_id' => $data['major_id'] ?: null,
            'academic_year_id' => $data['academic_year_id'] ?: null,
            'guardian_id' => $guardianId,
            'entry_year' => $data['entry_year'] ?: date('Y'),
            'status' => $data['status'] ?? 'active',
        ]);

        QrCodeService::generateForStudent($studentId);

        return $studentId;
    }

    public static function update(int $id, array $data, ?array $photoFile): void
    {
        $existingNis = Student::findBy('nis', $data['nis']);
        if ($existingNis && (int) $existingNis['id'] !== $id) {
            throw new RuntimeException('NIS sudah digunakan siswa lain.');
        }
        $existingNisn = Student::findBy('nisn', $data['nisn']);
        if ($existingNisn && (int) $existingNisn['id'] !== $id) {
            throw new RuntimeException('NISN sudah digunakan siswa lain.');
        }

        $current = Student::find($id);
        if (!$current) {
            throw new RuntimeException('Siswa tidak ditemukan.');
        }

        $payload = [
            'nis' => $data['nis'],
            'nisn' => $data['nisn'],
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'] ?: null,
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'] ?: null,
            'birth_date' => $data['birth_date'] ?: null,
            'address' => $data['address'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'class_id' => $data['class_id'] ?: null,
            'major_id' => $data['major_id'] ?: null,
            'academic_year_id' => $data['academic_year_id'] ?: null,
            'guardian_id' => $data['guardian_id'] ?: null,
            'entry_year' => $data['entry_year'] ?: null,
            'status' => $data['status'] ?? 'active',
        ];

        if ($photoFile) {
            $payload['photo'] = FileUploadService::uploadPhoto($photoFile, 'students');
            FileUploadService::deletePhoto($current['photo']);
        }

        Student::update($id, $payload);
    }

    public static function delete(int $id): void
    {
        $student = Student::find($id);
        if ($student) {
            FileUploadService::deletePhoto($student['photo']);
        }
        Student::delete($id);
    }
}
