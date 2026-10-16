<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Services\QrCodeService;

class IdCardController extends Controller
{
    public function bulkForm(Request $request): void
    {
        $this->view('idcard.bulk_form', [
            'title' => 'Cetak Kartu ID',
            'classes' => ClassRoom::all('name'),
            'majors' => Major::all('name'),
        ]);
    }

    public function studentCard(Request $request, string $id): void
    {
        $student = $this->fetchStudent((int) $id);
        if (!$student) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/qr/students');
            return;
        }

        $school = $this->schoolInfo();
        $this->view('idcard.print', [
            'title' => 'Cetak Kartu Siswa',
            'school' => $school,
            'items' => [$this->studentCardData($student)],
        ], null);
    }

    public function teacherCard(Request $request, string $id): void
    {
        $teacher = $this->fetchTeacher((int) $id);
        if (!$teacher) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/qr/teachers');
            return;
        }

        $school = $this->schoolInfo();
        $this->view('idcard.print', [
            'title' => 'Cetak Kartu Guru',
            'school' => $school,
            'items' => [$this->teacherCardData($teacher)],
        ], null);
    }

    public function bulkPrint(Request $request): void
    {
        $type = $request->query('type', 'students');
        $db = Database::connection();
        $items = [];
        $school = $this->schoolInfo();

        if ($type === 'teachers') {
            $stmt = $db->query("SELECT * FROM teachers WHERE status = 'active' ORDER BY full_name");
            foreach ($stmt->fetchAll() as $teacher) {
                $items[] = $this->teacherCardData($teacher);
            }
        } else {
            $scope = $request->query('scope', 'selected');
            $conditions = ["status = 'active'"];
            $params = [];

            if ($scope === 'class') {
                $conditions[] = 'class_id = :class_id';
                $params['class_id'] = $request->query('class_id');
            } elseif ($scope === 'major') {
                $conditions[] = 'major_id = :major_id';
                $params['major_id'] = $request->query('major_id');
            } elseif ($scope === 'selected') {
                $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));
                if (empty($ids)) {
                    Session::flash('error', 'Pilih minimal satu siswa.');
                    $this->redirect('/idcard');
                    return;
                }
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $conditions[] = "id IN ({$placeholders})";
                $params = $ids;
            }
            // scope === 'all' uses no extra condition

            $where = implode(' AND ', $conditions);
            $stmt = $db->prepare("SELECT * FROM students WHERE {$where} ORDER BY full_name");
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $student) {
                $items[] = $this->studentCardData($student);
            }
        }

        if (empty($items)) {
            Session::flash('error', 'Tidak ada data untuk dicetak.');
            $this->redirect('/idcard');
            return;
        }

        $this->view('idcard.print', [
            'title' => 'Cetak Kartu Massal',
            'school' => $school,
            'items' => $items,
        ], null);
    }

    private function fetchStudent(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private function fetchTeacher(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private function studentCardData(array $student): array
    {
        $classStmt = Database::connection()->prepare('SELECT name FROM classes WHERE id = :id');
        $classStmt->execute(['id' => $student['class_id']]);
        $class = $classStmt->fetch();

        return [
            'type' => 'student',
            'name' => $student['full_name'],
            'code' => $student['nis'],
            'code_label' => 'NIS',
            'sub' => $class['name'] ?? '-',
            'photo' => $student['photo'],
            'qr_image' => "/students/{$student['id']}/qr-image",
        ];
    }

    private function teacherCardData(array $teacher): array
    {
        return [
            'type' => 'teacher',
            'name' => $teacher['full_name'],
            'code' => $teacher['nip'] ?: '-',
            'code_label' => 'NIP',
            'sub' => $teacher['position'] ?? '-',
            'photo' => $teacher['photo'],
            'qr_image' => "/teachers/{$teacher['id']}/qr-image",
        ];
    }

    private function schoolInfo(): array
    {
        $school = Database::connection()->query('SELECT * FROM school_settings ORDER BY id LIMIT 1')->fetch();
        return $school ?: ['school_name' => 'Sistem Presensi Sekolah', 'logo' => null];
    }
}
