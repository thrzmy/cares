<?php

declare(strict_types=1);

final class AdmissionController
{
    public static function dashboard(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/dashboard', ['title' => 'Admission Dashboard']);
    }

    public static function encode(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/encode', ['title' => 'Encode Test Results']);
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/results', ['title' => 'Course Recommendations']);
    }

    public static function storage(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/storage', ['title' => 'Result Storage']);
    }

    public static function students(): void
    {
        RoleMiddleware::requireRole('admission');

        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $params = [];
        $where = "WHERE is_deleted = 0";
        if ($q !== '') {
            $where .= " AND (name LIKE :q OR email LIKE :q OR id_number LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }
        if (in_array($status, ['pending', 'admitted', 'rejected', 'waitlisted'], true)) {
            $where .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT id, id_number, name, email, status, created_at
                FROM students
                $where
                ORDER BY created_at DESC";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $students = $st->fetchAll();

        View::render('admission/students', [
            'title' => 'Students',
            'students' => $students,
            'q' => $q,
            'statusFilter' => $status,
        ]);
    }

    public static function editStudent(): void
    {
        RoleMiddleware::requireRole('admission');

        $id = (int)($_GET['id'] ?? 0);
        $st = Database::pdo()->prepare("SELECT id, id_number, name, email, status FROM students WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('students/form', [
            'title' => 'Edit Student',
            'mode' => 'edit',
            'action' => '/admission/students/edit',
            'student' => $student,
            'error' => null,
        ]);
    }

    public static function updateStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idNumber = trim((string)($_POST['id_number'] ?? ''));
        $status = (string)($_POST['status'] ?? 'pending');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderStudentForm('Please enter a valid name and email.', [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'id_number' => $idNumber,
                'status' => $status,
            ]);
            return;
        }

        if (!in_array($status, ['pending', 'admitted', 'rejected', 'waitlisted'], true)) {
            $status = 'pending';
        }

        if ($status === 'admitted' && $idNumber === '') {
            self::renderStudentForm('ID number is required for admitted students.', [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'id_number' => $idNumber,
                'status' => $status,
            ]);
            return;
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare("SELECT id FROM students WHERE (email = :email OR id_number = :id_number) AND id <> :id AND is_deleted = 0 LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':id_number' => $idNumber === '' ? null : $idNumber,
            ':id' => $id,
        ]);
        if ($check->fetch()) {
            self::renderStudentForm('Email or ID number is already in use.', [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'id_number' => $idNumber,
                'status' => $status,
            ]);
            return;
        }

        $pdo->prepare("UPDATE students
                       SET id_number = :id_number,
                           name = :name,
                           email = :email,
                           status = :status,
                           updated_by = :updated_by
                       WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':id_number' => $idNumber === '' ? null : $idNumber,
                ':name' => $name,
                ':email' => $email,
                ':status' => $status,
                ':updated_by' => (int)($_SESSION['user_id'] ?? 0),
                ':id' => $id,
            ]);

        Logger::log(currentUserId(), 'UPDATE_STUDENT', 'students', $id, 'Admission updated student record');
        flash('success', 'Student updated.');
        redirect('/admission/students');
    }

    private static function renderStudentForm(string $error, array $student): void
    {
        View::render('students/form', [
            'title' => 'Edit Student',
            'mode' => 'edit',
            'action' => '/admission/students/edit',
            'student' => $student,
            'error' => $error,
        ]);
    }
}
