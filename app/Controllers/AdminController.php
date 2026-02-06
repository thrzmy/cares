<?php
declare(strict_types=1);

final class AdminController
{
    public static function dashboard(): void
    {
        RoleMiddleware::requireRole('administrator');
        View::render('admin/dashboard', ['title' => 'Administrator Dashboard']);
    }

    public static function scores(): void
    {
        RoleMiddleware::requireRole('administrator');

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
                        WHERE rn <= 3
                        ORDER BY student_id, rn";

            $recSt = Database::pdo()->prepare($recSql);
            $recSt->execute($studentIds);
            $recRows = $recSt->fetchAll();

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
        }

        View::render('admin/scores', [
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
                'basePath' => '/administrator/scores',
                'query' => [
                    'q' => $q,
                    'status' => $status,
                ],
            ],
        ]);
    }

    public static function viewScores(): void
    {
        RoleMiddleware::requireRole('administrator');

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

        View::render('admin/view_scores', [
            'title' => 'View Scores',
            'student' => $student,
            'parts' => $parts,
            'scoresMap' => $scoresMap,
        ]);
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('administrator');
        self::scores();
    }

    public static function matrix(): void
    {
        RoleMiddleware::requireRole('administrator');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;
        $total = WeightsService::getCoursesCount();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $courses = WeightsService::getCoursesPage($perPage, $offset);
        $parts = WeightsService::getExamParts();
        $weightsMap = WeightsService::getWeightsMap();

        View::render('admin/matrix', [
            'title' => 'Matrix Management',
            'courses' => $courses,
            'parts' => $parts,
            'weightsMap' => $weightsMap,
            'success' => null,
            'error' => null,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'perPage' => $perPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                'basePath' => '/administrator/matrix',
                'query' => [],
            ],
        ]);
    }

    public static function saveMatrix(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $matrix = $_POST['weights'] ?? [];
        $page = max(1, (int)($_POST['page'] ?? 1));

        try {
            WeightsService::saveMatrix($matrix, $userId);
            Logger::log($userId, 'UPDATED_WEIGHTS', 'weights', null, 'Updated weights matrix');

            flash('success', 'Matrix saved successfully.');
            redirect('/administrator/matrix?page=' . $page);
        } catch (Throwable $e) {
            $perPage = 5;
            $total = WeightsService::getCoursesCount();
            $pages = max(1, (int)ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;
            $courses = WeightsService::getCoursesPage($perPage, $offset);
            $parts = WeightsService::getExamParts();
            $weightsMap = WeightsService::getWeightsMap();

            View::render('admin/matrix', [
                'title' => 'Matrix Management',
                'courses' => $courses,
                'parts' => $parts,
                'weightsMap' => $weightsMap,
                'success' => null,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to save matrix.',
                'pagination' => [
                    'page' => $page,
                    'pages' => $pages,
                    'total' => $total,
                    'perPage' => $perPage,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                    'basePath' => '/administrator/matrix',
                    'query' => [],
                ],
            ]);
        }
    }

    public static function logs(): void
    {
        RoleMiddleware::requireRole('administrator');
        View::render('admin/logs', ['title' => 'Monitor Logs']);
    }

    public static function reports(): void
    {
        RoleMiddleware::requireRole('administrator');
        View::render('admin/reports', ['title' => 'System Reports']);
    }

    public static function profile(): void
    {
        RoleMiddleware::requireRole('administrator');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $st = Database::pdo()->prepare("SELECT id, name, email FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $userId]);
        $user = $st->fetch();

        View::render('admin/profile', [
            'title' => 'My Profile',
            'user' => $user,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function updateProfile(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid name and email.');
            redirect('/administrator/profile');
        }

        $check = Database::pdo()->prepare("SELECT id FROM users WHERE email = :email AND id <> :id AND is_deleted = 0 LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':id' => $userId,
        ]);
        if ($check->fetch()) {
            flash('error', 'Email is already in use by another account.');
            redirect('/administrator/profile');
        }

        Database::pdo()->prepare("UPDATE users SET name = :name, email = :email, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':name' => $name,
                ':email' => $email,
                ':updated_by' => $userId,
                ':id' => $userId,
            ]);

        $_SESSION['name'] = $name;
        Logger::log($userId, 'UPDATE_PROFILE', 'users', $userId, 'Administrator updated profile');
        flash('success', 'Profile updated.');
        redirect('/administrator/profile');
    }

    public static function updatePassword(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $current = (string)($_POST['current_password'] ?? '');
        $password = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($current === '' || $password === '' || $confirm === '') {
            flash('error', 'Please complete all password fields.');
            redirect('/administrator/profile');
        }

        if (strlen($password) < 8) {
            flash('error', 'New password must be at least 8 characters.');
            redirect('/administrator/profile');
        }

        if ($password !== $confirm) {
            flash('error', 'New password confirmation does not match.');
            redirect('/administrator/profile');
        }

        $st = Database::pdo()->prepare("SELECT password FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $userId]);
        $row = $st->fetch();
        if (!$row || !password_verify($current, (string)$row['password'])) {
            flash('error', 'Current password is incorrect.');
            redirect('/administrator/profile');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::pdo()->prepare("UPDATE users SET password = :password, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':password' => $hash,
                ':updated_by' => $userId,
                ':id' => $userId,
            ]);

        Logger::log($userId, 'UPDATE_PASSWORD', 'users', $userId, 'Administrator updated own password');
        flash('success', 'Password updated.');
        redirect('/administrator/profile');
    }
}
