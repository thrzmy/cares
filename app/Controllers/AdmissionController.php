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

        $q = trim((string)($_GET['q'] ?? ''));

        $params = [];
        $where = "WHERE s.is_deleted = 0
                  AND NOT EXISTS (
                      SELECT 1
                      FROM student_exam_scores ses
                      WHERE ses.student_id = s.id AND ses.is_deleted = 0
                  )
                  AND s.status = 'pending'";
        if ($q !== '') {
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.id_number LIKE :q_id)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_id'] = $like;
        }

        $sql = "SELECT s.id, s.id_number, s.name, s.email, s.status, s.created_at
                FROM students s
                $where
                ORDER BY s.created_at DESC";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $students = $st->fetchAll();

        View::render('admission/encode', [
            'title' => 'Encode Test Results',
            'students' => $students,
            'q' => $q,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function editScores(): void
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

        if (ScoresService::hasScores($id)) {
            flash('error', 'May scores na. Please edit in Result Storage.');
            redirect('/admission/encode');
        }

        $parts = WeightsService::getExamParts();
        $scoresMap = ScoresService::getStudentScoresMap($id);

        View::render('admission/encode_form', [
            'title' => 'Encode Test Results',
            'student' => $student,
            'parts' => $parts,
            'scoresMap' => $scoresMap,
            'error' => null,
            'success' => flash('success'),
            'mode' => 'encode',
        ]);
    }

    public static function saveScores(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $id = (int)($_POST['id'] ?? 0);
        $st = Database::pdo()->prepare("SELECT id, id_number, name, email, status FROM students WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $mode = (string)($_POST['mode'] ?? 'encode');
        if ($mode !== 'edit' && ScoresService::hasScores($id)) {
            flash('error', 'May scores na. Please edit in Result Storage.');
            redirect('/admission/encode');
        }

        $scores = $_POST['scores'] ?? [];
        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        try {
            ScoresService::saveStudentScores($id, $scores, $userId);
            Logger::log($userId, 'ENCODE_SCORES', 'students', $id, 'Admission encoded exam part scores');
            flash('success', 'Scores saved successfully.');
            if ($mode === 'edit') {
                redirect('/admission/storage/edit?id=' . $id);
            }
            redirect('/admission/encode/edit?id=' . $id);
        } catch (Throwable $e) {
            $parts = WeightsService::getExamParts();
            $scoresMap = ScoresService::getStudentScoresMap($id);

            View::render('admission/encode_form', [
                'title' => 'Encode Test Results',
                'student' => $student,
                'parts' => $parts,
                'scoresMap' => $scoresMap,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to save scores.',
                'success' => null,
                'mode' => $mode,
            ]);
        }
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/results', ['title' => 'Course Recommendations']);
    }

    public static function storage(): void
    {
        RoleMiddleware::requireRole('admission');

        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $params = [];
        $where = "WHERE s.is_deleted = 0
                  AND EXISTS (
                      SELECT 1
                      FROM student_exam_scores ses
                      WHERE ses.student_id = s.id AND ses.is_deleted = 0
                  )";
        if ($q !== '') {
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.id_number LIKE :q_id)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_id'] = $like;
        }
        if (in_array($status, ['pending', 'admitted', 'rejected', 'waitlisted'], true)) {
            $where .= " AND s.status = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT s.id, s.id_number, s.name, s.email, s.status, s.created_at
                FROM students s
                $where
                ORDER BY s.created_at DESC";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $students = $st->fetchAll();

        View::render('admission/storage', [
            'title' => 'Result Storage',
            'students' => $students,
            'q' => $q,
            'statusFilter' => $status,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function editStoredScores(): void
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

        $parts = WeightsService::getExamParts();
        $scoresMap = ScoresService::getStudentScoresMap($id);

        View::render('admission/encode_form', [
            'title' => 'Edit Scores',
            'student' => $student,
            'parts' => $parts,
            'scoresMap' => $scoresMap,
            'error' => null,
            'success' => flash('success'),
            'mode' => 'edit',
        ]);
    }

    public static function students(): void
    {
        RoleMiddleware::requireRole('admission');

        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $params = [];
        $where = "WHERE is_deleted = 0";
        if ($q !== '') {
            $where .= " AND (name LIKE :q_name OR email LIKE :q_email OR id_number LIKE :q_id)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_id'] = $like;
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
