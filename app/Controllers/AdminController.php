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
            $recommendations = self::getRecommendationsForStudents($studentIds, 1);
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
        $courseSummaries = self::getCourseRecommendationsForStudent($id);

        View::render('admin/view_scores', [
            'title' => 'View Scores',
            'student' => $student,
            'parts' => $parts,
            'scoresMap' => $scoresMap,
            'courseSummaries' => $courseSummaries,
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
            $hasChanges = WeightsService::saveMatrix($matrix, $userId);
            if ($hasChanges) {
                Logger::log($userId, 'UPDATED_WEIGHTS', 'weights', null, 'Updated weights matrix');
                flash('success', 'Matrix saved successfully.');
            } else {
                flash('success', 'No changes detected in the matrix.');
            }
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
        $q = trim((string)($_GET['q'] ?? ''));
        $action = trim((string)($_GET['action'] ?? ''));
        $startDate = trim((string)($_GET['start_date'] ?? ''));
        $endDate = trim((string)($_GET['end_date'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $actionList = Database::pdo()
            ->query("SELECT DISTINCT action FROM logs ORDER BY action")
            ->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array($action, $actionList, true)) {
            $action = '';
        }
        $startDate = self::normalizeDateInput($startDate);
        $endDate = self::normalizeDateInput($endDate);

        $where = "WHERE 1=1";
        $params = [];

        if ($action !== '') {
            $where .= " AND l.action = :action";
            $params[':action'] = $action;
        }
        if ($q !== '') {
            $where .= " AND (
                u.name LIKE :q_user_name OR
                u.email LIKE :q_user_email OR
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
            $params[':q_user_name'] = $like;
            $params[':q_user_email'] = $like;
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

        View::render('admin/logs', [
            'title' => 'Activity Logs',
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
                'basePath' => '/administrator/logs',
                'query' => [
                    'q' => $q,
                    'action' => $action,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
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

    public static function reports(): void
    {
        RoleMiddleware::requireRole('administrator');
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

        $usersParams = [];
        $usersWhere = "WHERE is_deleted = 0";
        $usersWhere .= $addDateFilter('created_at', $usersParams);

        $usersTotalSt = Database::pdo()->prepare("SELECT COUNT(*) FROM users {$usersWhere}");
        $usersTotalSt->execute($usersParams);
        $usersTotal = (int)$usersTotalSt->fetchColumn();

        $usersActiveSt = Database::pdo()->prepare("SELECT COALESCE(SUM(is_active = 1), 0) FROM users {$usersWhere}");
        $usersActiveSt->execute($usersParams);
        $usersActive = (int)$usersActiveSt->fetchColumn();

        $roleSt = Database::pdo()->prepare("SELECT role, COUNT(*) AS total FROM users {$usersWhere} GROUP BY role ORDER BY total DESC");
        $roleSt->execute($usersParams);
        $userRoleCounts = $roleSt->fetchAll();

        $statusSt = Database::pdo()->prepare("SELECT account_status, COUNT(*) AS total FROM users {$usersWhere} GROUP BY account_status ORDER BY total DESC");
        $statusSt->execute($usersParams);
        $userStatusCounts = $statusSt->fetchAll();

        $statusMap = [];
        foreach ($userStatusCounts as $row) {
            $statusMap[(string)$row['account_status']] = (int)$row['total'];
        }

        $studentsParams = [];
        $studentsWhere = "WHERE is_deleted = 0";
        $studentsWhere .= $addDateFilter('created_at', $studentsParams);

        $studentsTotalSt = Database::pdo()->prepare("SELECT COUNT(*) FROM students {$studentsWhere}");
        $studentsTotalSt->execute($studentsParams);
        $studentsTotal = (int)$studentsTotalSt->fetchColumn();

        $studentStatusSt = Database::pdo()->prepare("SELECT status, COUNT(*) AS total FROM students {$studentsWhere} GROUP BY status ORDER BY total DESC");
        $studentStatusSt->execute($studentsParams);
        $studentStatusCounts = $studentStatusSt->fetchAll();

        $studentStatusMap = [];
        foreach ($studentStatusCounts as $row) {
            $studentStatusMap[(string)$row['status']] = (int)$row['total'];
        }

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

        $logsParams = [];
        $logsWhere = "WHERE 1=1";
        $logsWhere .= $addDateFilter('l.created_at', $logsParams);

        $logEntriesSt = Database::pdo()->prepare("SELECT COUNT(*) FROM logs l {$logsWhere}");
        $logEntriesSt->execute($logsParams);
        $logEntries = (int)$logEntriesSt->fetchColumn();

        $topActionsSt = Database::pdo()->prepare(
            "SELECT l.action, COUNT(*) AS total
             FROM logs l
             {$logsWhere}
             GROUP BY l.action
             ORDER BY total DESC
             LIMIT 6"
        );
        $topActionsSt->execute($logsParams);
        $topActions = $topActionsSt->fetchAll();

        $recParams = [];
        $recDateFilter = $addDateFilter('ses.created_at', $recParams);
        $recommendationsSt = Database::pdo()->prepare(
            "WITH course_scores AS (
                SELECT
                    ses.student_id,
                    c.id AS course_id,
                    c.course_code,
                    c.course_name,
                    SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score
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
            SELECT c.course_code,
                   c.course_name,
                   COUNT(DISTINCT cs.student_id) AS student_count,
                   AVG(cs.total_score) AS avg_score
            FROM courses c
            LEFT JOIN course_scores cs
              ON cs.course_id = c.id
            WHERE c.is_deleted = 0
            GROUP BY c.id
            ORDER BY (AVG(cs.total_score) IS NULL), AVG(cs.total_score) DESC, student_count DESC, c.course_name ASC"
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

        View::render('admin/reports', [
            'title' => 'System Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
            'summary' => [
                'users_total' => $usersTotal,
                'users_active' => $usersActive,
                'students_total' => $studentsTotal,
                'score_entries' => $scoreEntries,
                'students_with_scores' => $studentsWithScores,
                'students_without_scores' => $studentsWithoutScores,
                'students_with_recommendations' => $studentsWithRecommendations,
                'log_entries' => $logEntries,
            ],
            'userRoleCounts' => $userRoleCounts,
            'userStatusCounts' => $userStatusCounts,
            'studentStatusCounts' => $studentStatusCounts,
            'examParts' => $examParts,
            'topActions' => $topActions,
            'topRecommendations' => $topRecommendations,
        ]);
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

    private static function getRecommendationsForStudents(array $studentIds, int $limit): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $sql = "WITH ranked AS (
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

        $st = Database::pdo()->prepare($sql);
        $st->execute(array_merge($studentIds, [$limit]));
        $rows = $st->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $studentId = (int)$row['student_id'];
            if (!isset($result[$studentId])) {
                $result[$studentId] = [];
            }
            $result[$studentId][] = [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'total_score' => (float)$row['total_score'],
                'rank' => (int)$row['rn'],
            ];
        }

        return $result;
    }

    private static function getCourseRecommendationsForStudent(int $studentId): array
    {
        $sql = "SELECT
                    c.course_code,
                    c.course_name,
                    SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score
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
                ORDER BY total_score DESC, c.course_name ASC";

        $st = Database::pdo()->prepare($sql);
        $st->execute([':student_id' => $studentId]);

        return array_map(static function (array $row): array {
            return [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'total_score' => (float)$row['total_score'],
            ];
        }, $st->fetchAll());
    }
}
