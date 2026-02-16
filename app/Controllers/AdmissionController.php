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
        $viewMode = (string)($_GET['view'] ?? '') === '1';
        $st = Database::pdo()->prepare("SELECT id, id_number, name, email, status FROM students WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        if (ScoresService::hasScores($id)) {
            if (!$viewMode) {
                flash('error', 'Scores already encoded. Please edit in Result Storage.');
                redirect('/admission/encode');
            }

            $parts = WeightsService::getExamParts();
            $scoresMap = ScoresService::getStudentScoresMap($id);
            $recommendations = self::getTopRecommendationsForStudent($id, 3);

            View::render('admission/encode_form', [
                'title' => 'View Results',
                'student' => $student,
                'parts' => $parts,
                'scoresMap' => $scoresMap,
                'recommendations' => $recommendations,
                'error' => null,
                'success' => flash('success'),
                'mode' => 'view',
            ]);
            return;
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
            flash('error', 'Scores already encoded');
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
            redirect('/admission/encode/edit?id=' . $id . '&view=1');
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

        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

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

        $countSql = "SELECT COUNT(*)
                     FROM students s
                     $where";
        $countSt = Database::pdo()->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT s.id, s.id_number, s.name, s.email, s.status, s.created_at
                FROM students s
                $where
                ORDER BY s.created_at DESC
                LIMIT :limit OFFSET :offset";
        $st = Database::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $students = $st->fetchAll();

        $recommendations = [];
        if (!empty($students)) {
            $studentIds = array_map(
                static fn($row) => (int)$row['id'],
                $students
            );
            $recommendations = self::getRecommendationsForStudents($studentIds, 3);
        }

        View::render('admission/results', [
            'title' => 'Results & Recommendations',
            'students' => $students,
            'recommendations' => $recommendations,
            'q' => $q,
            'statusFilter' => $status,
            'success' => flash('success'),
            'error' => flash('error'),
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'perPage' => $perPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                'basePath' => '/admission/results',
                'query' => [
                    'q' => $q,
                    'status' => $status,
                ],
            ],
        ]);
    }

    public static function viewScores(): void
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

        View::render('admission/view_scores', [
            'title' => 'View Scores',
            'student' => $student,
            'parts' => $parts,
            'scoresMap' => $scoresMap,
        ]);
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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

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

        $countSql = "SELECT COUNT(*)
                     FROM students
                     $where";
        $countSt = Database::pdo()->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, id_number, name, email, status, created_at
                FROM students
                $where
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        $st = Database::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $students = $st->fetchAll();

        View::render('admission/students', [
            'title' => 'Student Management',
            'students' => $students,
            'q' => $q,
            'statusFilter' => $status,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'perPage' => $perPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                'basePath' => '/admission/students',
                'query' => [
                    'q' => $q,
                    'status' => $status,
                ],
            ],
        ]);
    }

    public static function createStudent(): void
    {
        RoleMiddleware::requireRole('admission');

        View::render('students/form', [
            'title' => 'Create Student',
            'mode' => 'create',
            'action' => '/admission/students/create',
            'student' => ['name' => '', 'email' => '', 'id_number' => '', 'status' => 'pending'],
            'error' => null,
        ]);
    }

    public static function storeStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idNumber = trim((string)($_POST['id_number'] ?? ''));
        $status = (string)($_POST['status'] ?? 'pending');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderStudentFormMode('create', 'Please enter a valid name and email.', [
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
            self::renderStudentFormMode('create', 'ID number is required for admitted students.', [
                'name' => $name,
                'email' => $email,
                'id_number' => $idNumber,
                'status' => $status,
            ]);
            return;
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare("SELECT id FROM students WHERE (email = :email OR id_number = :id_number) AND is_deleted = 0 LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':id_number' => $idNumber === '' ? null : $idNumber,
        ]);
        if ($check->fetch()) {
            self::renderStudentFormMode('create', 'Email or ID number is already in use.', [
                'name' => $name,
                'email' => $email,
                'id_number' => $idNumber,
                'status' => $status,
            ]);
            return;
        }

        $pdo->prepare("INSERT INTO students (id_number, name, email, status, created_by)
                       VALUES (:id_number, :name, :email, :status, :created_by)")
            ->execute([
                ':id_number' => $idNumber === '' ? null : $idNumber,
                ':name' => $name,
                ':email' => $email,
                ':status' => $status,
                ':created_by' => (int)($_SESSION['user_id'] ?? 0),
            ]);

        Logger::log(currentUserId(), 'CREATE_STUDENT', 'students', (int)$pdo->lastInsertId(), 'Created student record');
        flash('success', 'Student created.');
        redirect('/admission/students');
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
            self::renderStudentFormMode('edit', 'Please enter a valid name and email.', [
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
            self::renderStudentFormMode('edit', 'ID number is required for admitted students.', [
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
            self::renderStudentFormMode('edit', 'Email or ID number is already in use.', [
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

    public static function logs(): void
    {
        RoleMiddleware::requireRole('admission');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $q = trim((string)($_GET['q'] ?? ''));
        $action = trim((string)($_GET['action'] ?? ''));
        $startDate = trim((string)($_GET['start_date'] ?? ''));
        $endDate = trim((string)($_GET['end_date'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $actionListSt = Database::pdo()->prepare("SELECT DISTINCT action FROM logs WHERE user_id = :user_id ORDER BY action");
        $actionListSt->execute([':user_id' => $userId]);
        $actionList = $actionListSt->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array($action, $actionList, true)) {
            $action = '';
        }
        $startDate = self::normalizeDateInput($startDate);
        $endDate = self::normalizeDateInput($endDate);

        $where = "WHERE l.user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($action !== '') {
            $where .= " AND l.action = :action";
            $params[':action'] = $action;
        }
        if ($q !== '') {
            $where .= " AND (
                l.details LIKE :q_details OR
                s.name LIKE :q_student_name OR
                s.id_number LIKE :q_student_id_number OR
                eu.name LIKE :q_entity_user_name OR
                eu.email LIKE :q_entity_user_email OR
                ses_student.name LIKE :q_score_student_name OR
                ses_student.id_number LIKE :q_score_student_id_number OR
                ses_part.name LIKE :q_score_part_name OR
                wc.course_name LIKE :q_weight_course_name OR
                wc.course_code LIKE :q_weight_course_code OR
                wep.name LIKE :q_weight_part_name
            )";
            $like = '%' . $q . '%';
            $params[':q_details'] = $like;
            $params[':q_student_name'] = $like;
            $params[':q_student_id_number'] = $like;
            $params[':q_entity_user_name'] = $like;
            $params[':q_entity_user_email'] = $like;
            $params[':q_score_student_name'] = $like;
            $params[':q_score_student_id_number'] = $like;
            $params[':q_score_part_name'] = $like;
            $params[':q_weight_course_name'] = $like;
            $params[':q_weight_course_code'] = $like;
            $params[':q_weight_part_name'] = $like;
        }
        if ($startDate !== '') {
            $where .= " AND l.created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }
        if ($endDate !== '') {
            $where .= " AND l.created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $countSql = "SELECT COUNT(*)
                     FROM logs l
                     LEFT JOIN students s
                        ON l.entity = 'students' AND s.id = l.entity_id
                     LEFT JOIN users eu
                        ON l.entity = 'users' AND eu.id = l.entity_id
                     LEFT JOIN student_exam_scores ses
                        ON l.entity = 'student_exam_scores' AND ses.id = l.entity_id
                     LEFT JOIN students ses_student
                        ON ses_student.id = ses.student_id
                     LEFT JOIN exam_parts ses_part
                        ON ses_part.id = ses.exam_part_id
                     LEFT JOIN weights w
                        ON l.entity = 'weights' AND w.id = l.entity_id
                     LEFT JOIN courses wc
                        ON wc.id = w.course_id
                     LEFT JOIN exam_parts wep
                        ON wep.id = w.exam_part_id
                     $where";
        $countSt = Database::pdo()->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT l.id,
                       l.user_id,
                       l.action,
                       l.entity,
                       l.entity_id,
                       l.details,
                       l.created_at,
                       u.name AS user_name,
                       u.email AS user_email,
                       CASE
                           WHEN l.entity = 'weights' AND l.entity_id IS NULL THEN 'Weights Matrix'
                           WHEN l.entity = 'student_exam_scores' THEN ses_student.name
                           ELSE COALESCE(s.name, eu.name, wc.course_name)
                       END AS entity_name,
                       CASE
                           WHEN l.entity = 'student_exam_scores'
                               THEN CONCAT(
                                   COALESCE(ses_student.id_number, 'No ID'),
                                   ' / ',
                                   COALESCE(ses_part.name, 'Exam Part')
                               )
                           WHEN l.entity = 'weights' AND l.entity_id IS NULL THEN 'All courses'
                           WHEN l.entity = 'weights'
                               THEN CONCAT(
                                   COALESCE(wc.course_code, 'Course'),
                                   ' / ',
                                   COALESCE(wep.name, 'Exam Part')
                               )
                           ELSE COALESCE(s.id_number, eu.email)
                       END AS entity_ref
                FROM logs l
                LEFT JOIN users u ON u.id = l.user_id
                LEFT JOIN students s
                    ON l.entity = 'students' AND s.id = l.entity_id
                LEFT JOIN users eu
                    ON l.entity = 'users' AND eu.id = l.entity_id
                LEFT JOIN student_exam_scores ses
                    ON l.entity = 'student_exam_scores' AND ses.id = l.entity_id
                LEFT JOIN students ses_student
                    ON ses_student.id = ses.student_id
                LEFT JOIN exam_parts ses_part
                    ON ses_part.id = ses.exam_part_id
                LEFT JOIN weights w
                    ON l.entity = 'weights' AND w.id = l.entity_id
                LEFT JOIN courses wc
                    ON wc.id = w.course_id
                LEFT JOIN exam_parts wep
                    ON wep.id = w.exam_part_id
                $where
                ORDER BY l.created_at DESC
                LIMIT :limit OFFSET :offset";
        $st = Database::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $logs = $st->fetchAll();

        View::render('admission/logs', [
            'title' => 'My Activity Logs',
            'logs' => $logs,
            'q' => $q,
            'actionFilter' => $action,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'actionList' => $actionList,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'perPage' => $perPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                'basePath' => '/admission/logs',
                'query' => [
                    'q' => $q,
                    'action' => $action,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    public static function reports(): void
    {
        RoleMiddleware::requireRole('admission');

        $startDate = self::normalizeDateInput(trim((string)($_GET['start_date'] ?? '')));
        $endDate = self::normalizeDateInput(trim((string)($_GET['end_date'] ?? '')));

        if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $addDateFilter = static function (string $column, array &$params) use ($startDate, $endDate): string {
            $clause = '';
            if ($startDate !== '') {
                $clause .= " AND {$column} >= :start_date";
                $params[':start_date'] = $startDate . ' 00:00:00';
            }
            if ($endDate !== '') {
                $clause .= " AND {$column} <= :end_date";
                $params[':end_date'] = $endDate . ' 23:59:59';
            }
            return $clause;
        };

        $studentsParams = [];
        $studentsWhere = "WHERE is_deleted = 0";
        $studentsWhere .= $addDateFilter('created_at', $studentsParams);

        $studentsTotalSt = Database::pdo()->prepare("SELECT COUNT(*) FROM students {$studentsWhere}");
        $studentsTotalSt->execute($studentsParams);
        $studentsTotal = (int)$studentsTotalSt->fetchColumn();

        $studentStatusSt = Database::pdo()->prepare("SELECT status, COUNT(*) AS total FROM students {$studentsWhere} GROUP BY status ORDER BY total DESC");
        $studentStatusSt->execute($studentsParams);
        $studentStatusCounts = $studentStatusSt->fetchAll();

        $scoresParams = [];
        $scoresWhere = "WHERE ses.is_deleted = 0";
        $scoresWhere .= $addDateFilter('ses.created_at', $scoresParams);

        $scoreEntriesSt = Database::pdo()->prepare("SELECT COUNT(*) FROM student_exam_scores ses {$scoresWhere}");
        $scoreEntriesSt->execute($scoresParams);
        $scoreEntries = (int)$scoreEntriesSt->fetchColumn();

        $studentsWithScoresSt = Database::pdo()->prepare(
            "SELECT COUNT(DISTINCT s.id)
             FROM students s
             INNER JOIN student_exam_scores ses
               ON ses.student_id = s.id AND ses.is_deleted = 0
             WHERE s.is_deleted = 0" . $addDateFilter('ses.created_at', $scoresParams)
        );
        $studentsWithScoresSt->execute($scoresParams);
        $studentsWithScores = (int)$studentsWithScoresSt->fetchColumn();
        $studentsWithoutScores = max(0, $studentsTotal - $studentsWithScores);

        $examParams = [];
        $examWhere = $addDateFilter('ses.created_at', $examParams);
        $examPartsSt = Database::pdo()->prepare(
            "SELECT ep.name,
                    ep.max_score,
                    COUNT(ses.id) AS entries,
                    AVG(ses.score) AS avg_score
             FROM exam_parts ep
             LEFT JOIN student_exam_scores ses
               ON ses.exam_part_id = ep.id
              AND ses.is_deleted = 0
              {$examWhere}
             WHERE ep.is_deleted = 0
             GROUP BY ep.id
             ORDER BY ep.name"
        );
        $examPartsSt->execute($examParams);
        $examParts = $examPartsSt->fetchAll();

        $recParams = [];
        $recDateFilter = $addDateFilter('ses.created_at', $recParams);
        $recommendationsSt = Database::pdo()->prepare(
            "WITH ranked AS (
                SELECT
                    ses.student_id,
                    c.course_code,
                    c.course_name,
                    SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
                    ROW_NUMBER() OVER (
                        PARTITION BY ses.student_id
                        ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
                    ) AS rn
                FROM student_exam_scores ses
                INNER JOIN exam_parts ep
                    ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
                INNER JOIN weights w
                    ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
                INNER JOIN courses c
                    ON c.id = w.course_id AND c.is_deleted = 0
                WHERE ses.is_deleted = 0
                {$recDateFilter}
                GROUP BY ses.student_id, c.id
            )
            SELECT course_code,
                   course_name,
                   COUNT(*) AS student_count,
                   AVG(total_score) AS avg_score
            FROM ranked
            WHERE rn = 1
            GROUP BY course_code, course_name
            ORDER BY student_count DESC, avg_score DESC
            LIMIT 3"
        );
        $recommendationsSt->execute($recParams);
        $topRecommendations = $recommendationsSt->fetchAll();

        $studentsWithRecommendationsSt = Database::pdo()->prepare(
            "WITH ranked AS (
                SELECT
                    ses.student_id,
                    SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
                    ROW_NUMBER() OVER (
                        PARTITION BY ses.student_id
                        ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
                    ) AS rn
                FROM student_exam_scores ses
                INNER JOIN exam_parts ep
                    ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
                INNER JOIN weights w
                    ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
                WHERE ses.is_deleted = 0
                {$recDateFilter}
                GROUP BY ses.student_id
            )
            SELECT COUNT(*) FROM ranked WHERE rn = 1"
        );
        $studentsWithRecommendationsSt->execute($recParams);
        $studentsWithRecommendations = (int)$studentsWithRecommendationsSt->fetchColumn();

        $periodLabel = 'All time';
        if ($startDate !== '' || $endDate !== '') {
            $startLabel = $startDate !== '' ? date('M j, Y', strtotime($startDate)) : 'Beginning';
            $endLabel = $endDate !== '' ? date('M j, Y', strtotime($endDate)) : 'Present';
            $periodLabel = $startLabel . ' to ' . $endLabel;
        }

        View::render('admission/reports', [
            'title' => 'System Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
            'summary' => [
                'students_total' => $studentsTotal,
                'score_entries' => $scoreEntries,
                'students_without_scores' => $studentsWithoutScores,
                'students_with_recommendations' => $studentsWithRecommendations,
            ],
            'studentStatusCounts' => $studentStatusCounts,
            'examParts' => $examParts,
            'topRecommendations' => $topRecommendations,
        ]);
    }

    public static function profile(): void
    {
        RoleMiddleware::requireRole('admission');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $st = Database::pdo()->prepare("SELECT id, name, email FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $userId]);
        $user = $st->fetch();

        View::render('admission/profile', [
            'title' => 'My Profile',
            'user' => $user,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function updateProfile(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid name and email.');
            redirect('/admission/profile');
        }

        $check = Database::pdo()->prepare("SELECT id FROM users WHERE email = :email AND id <> :id AND is_deleted = 0 LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':id' => $userId,
        ]);
        if ($check->fetch()) {
            flash('error', 'Email is already in use by another account.');
            redirect('/admission/profile');
        }

        Database::pdo()->prepare("UPDATE users SET name = :name, email = :email, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':name' => $name,
                ':email' => $email,
                ':updated_by' => $userId,
                ':id' => $userId,
            ]);

        $_SESSION['name'] = $name;
        Logger::log($userId, 'UPDATE_PROFILE', 'users', $userId, 'Admission updated profile');
        flash('success', 'Profile updated.');
        redirect('/admission/profile');
    }

    public static function updatePassword(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $current = (string)($_POST['current_password'] ?? '');
        $password = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($current === '' || $password === '' || $confirm === '') {
            flash('error', 'Please complete all password fields.');
            redirect('/admission/profile');
        }

        if (strlen($password) < 8) {
            flash('error', 'New password must be at least 8 characters.');
            redirect('/admission/profile');
        }

        if ($password !== $confirm) {
            flash('error', 'New password confirmation does not match.');
            redirect('/admission/profile');
        }

        $st = Database::pdo()->prepare("SELECT password FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $userId]);
        $row = $st->fetch();
        if (!$row || !password_verify($current, (string)$row['password'])) {
            flash('error', 'Current password is incorrect.');
            redirect('/admission/profile');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::pdo()->prepare("UPDATE users SET password = :password, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':password' => $hash,
                ':updated_by' => $userId,
                ':id' => $userId,
            ]);

        Logger::log($userId, 'UPDATE_PASSWORD', 'users', $userId, 'Admission updated own password');
        flash('success', 'Password updated.');
        redirect('/admission/profile');
    }

    private static function normalizeDateInput(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }
        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            return '';
        }
        return $value;
    }

    private static function getRecommendationsForStudents(array $studentIds, int $limit): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $recSql = "WITH ranked AS (
                      SELECT
                          ses.student_id,
                          c.course_code,
                          c.course_name,
                          SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
                          ROW_NUMBER() OVER (
                              PARTITION BY ses.student_id
                              ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
                          ) AS rn
                      FROM student_exam_scores ses
                      INNER JOIN exam_parts ep
                          ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
                      INNER JOIN weights w
                          ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
                      INNER JOIN courses c
                          ON c.id = w.course_id AND c.is_deleted = 0
                      WHERE ses.is_deleted = 0
                        AND ses.student_id IN ($placeholders)
                      GROUP BY ses.student_id, c.id
                    )
                    SELECT student_id, course_code, course_name, total_score, rn
                    FROM ranked
                    WHERE rn <= ?
                    ORDER BY student_id, rn";

        $recSt = Database::pdo()->prepare($recSql);
        $recSt->execute(array_merge($studentIds, [$limit]));
        $recRows = $recSt->fetchAll();

        $recommendations = [];
        foreach ($recRows as $row) {
            $sid = (int)$row['student_id'];
            if (!isset($recommendations[$sid])) {
                $recommendations[$sid] = [];
            }
            $recommendations[$sid][] = [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'total_score' => (float)$row['total_score'],
                'rank' => (int)$row['rn'],
            ];
        }

        return $recommendations;
    }

    private static function getTopRecommendationsForStudent(int $studentId, int $limit): array
    {
        $sql = "WITH ranked AS (
                  SELECT
                      c.course_code,
                      c.course_name,
                      SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
                      ROW_NUMBER() OVER (
                          ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
                      ) AS rn
                  FROM student_exam_scores ses
                  INNER JOIN exam_parts ep
                      ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
                  INNER JOIN weights w
                      ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
                  INNER JOIN courses c
                      ON c.id = w.course_id AND c.is_deleted = 0
                  WHERE ses.is_deleted = 0
                    AND ses.student_id = :student_id
                  GROUP BY c.id
                )
                SELECT course_code, course_name, total_score, rn
                FROM ranked
                WHERE rn <= :limit
                ORDER BY rn";

        $st = Database::pdo()->prepare($sql);
        $st->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetchAll();
        $recommendations = [];
        foreach ($rows as $row) {
            $recommendations[] = [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'total_score' => (float)$row['total_score'],
                'rank' => (int)$row['rn'],
            ];
        }

        return $recommendations;
    }

    private static function renderStudentFormMode(string $mode, string $error, array $student): void
    {
        View::render('students/form', [
            'title' => $mode === 'create' ? 'Create Student' : 'Edit Student',
            'mode' => $mode,
            'action' => $mode === 'create' ? '/admission/students/create' : '/admission/students/edit',
            'student' => $student,
            'error' => $error,
        ]);
    }
}
