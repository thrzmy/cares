<?php

declare(strict_types=1);

final class AdmissionController
{
    private static ?bool $cctChoiceColumnExists = null;

    public static function dashboard(): void
    {
        RoleMiddleware::requireRole('admission');
        View::render('admission/dashboard', ['title' => 'Dashboard']);
    }

    public static function encode(): void
    {
        RoleMiddleware::requireRole('admission');

        $q = trim((string)($_GET['q'] ?? ''));

        $params = [];
        $where = "WHERE s.is_deleted = 0
                  AND COALESCE(s.is_archived, 0) = 0";
        if ($q !== '') {
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.application_number LIKE :q_application_number)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_application_number'] = $like;
        }

        $sql = "SELECT s.id, s.application_number, s.name, s.email, s.application_status, s.screening_status, s.status, s.created_at
                FROM students s
                $where
                ORDER BY s.created_at DESC";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $students = $st->fetchAll();
        if (!empty($students)) {
            $studentIds = array_map(static fn($row): int => (int)$row['id'], $students);
            $examResults = RecommendationService::getExamResultsForStudents($studentIds);
            foreach ($students as &$studentRow) {
                $studentId = (int)($studentRow['id'] ?? 0);
                $studentRow['status'] = $examResults[$studentId] ?? 'pending';
                $studentRow['has_scores'] = ScoresService::hasScores($studentId);
            }
            unset($studentRow);
            $students = array_values(array_filter(
                $students,
                static fn(array $studentRow): bool => (string)($studentRow['status'] ?? 'pending') === 'pending'
            ));
        }

        View::render('admission/encode', [
            'title' => 'Encode Test Results',
            'students' => $students,
            'q' => $q,
            'activeSemester' => self::getActiveSemester(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function editScores(): void
    {
        RoleMiddleware::requireRole('admission');

        $id = (int)($_GET['id'] ?? 0);
        $viewMode = (string)($_GET['view'] ?? '') === '1';
        $st = Database::pdo()->prepare("SELECT id, application_number, name, email, first_choice, second_choice, application_status, screening_status, status FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        if (ScoresService::hasScores($id)) {
            if (!$viewMode) {
                flash('error', 'Scores already encoded. Please edit them in Results & Recommendation.');
                redirect('/admission/encode');
            }

            $parts = WeightsService::getExamParts();
            $groupedParts = WeightsService::getExamPartsGrouped();
            $scoresMap = ScoresService::getStudentScoresMap($id);
            $recommendations = RecommendationService::getQualifiedRecommendationsForStudent($id, 3);

            View::render('admission/encode_form', [
                'title' => 'View Results',
                'student' => $student,
                'parts' => $parts,
                'groupedParts' => $groupedParts,
                'scoresMap' => $scoresMap,
                'recommendations' => $recommendations,
                'activeSemester' => self::getActiveSemester(),
                'error' => null,
                'success' => flash('success'),
                'mode' => 'view',
            ]);
            return;
        }

        $parts = WeightsService::getExamParts();
        $groupedParts = WeightsService::getExamPartsGrouped();
        $scoresMap = ScoresService::getStudentScoresMap($id);

        View::render('admission/encode_form', [
            'title' => 'Encode Test Results',
            'student' => self::decorateStudentChoiceLabels($student),
            'parts' => $parts,
            'groupedParts' => $groupedParts,
            'scoresMap' => $scoresMap,
            'activeSemester' => self::getActiveSemester(),
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
        $st = Database::pdo()->prepare("SELECT id, application_number, name, email, first_choice, second_choice, application_status, screening_status, status FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $mode = (string)($_POST['mode'] ?? 'encode');
        if (!in_array($mode, ['edit', 'summary-edit'], true) && ScoresService::hasScores($id)) {
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
            RecommendationService::syncStudentOutcome($id);
            Logger::log($userId, 'ENCODE_SCORES', 'students', $id, 'Admission encoded exam part scores');
            flash('success', 'Scores saved successfully.');
            if ($mode === 'edit') {
                redirect('/admission/results/view?id=' . $id);
            }
            if ($mode === 'summary-edit') {
                redirect('/admission/results/view?id=' . $id);
            }
            redirect('/admission/encode/edit?id=' . $id . '&view=1');
        } catch (Throwable $e) {
            $parts = WeightsService::getExamParts();
            $groupedParts = WeightsService::getExamPartsGrouped();
            $scoresMap = ScoresService::getStudentScoresMap($id);

            View::render('admission/encode_form', [
                'title' => 'Encode Test Results',
                'student' => $student,
                'parts' => $parts,
                'groupedParts' => $groupedParts,
                'scoresMap' => $scoresMap,
                'activeSemester' => self::getActiveSemester(),
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to save scores.',
                'success' => null,
                'mode' => $mode,
            ]);
        }
    }

    public static function bulkUploadScores(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $activeSemester = self::getActiveSemester();
        if ($activeSemester === null) {
            flash('error', 'Set an active semester first before using bulk upload.');
            redirect('/admission/encode');
        }

        $upload = $_FILES['bulk_file'] ?? null;
        if (!is_array($upload) || (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', 'Please choose a valid CSV file.');
            redirect('/admission/encode');
        }

        $originalName = (string)($upload['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'xlsx'], true)) {
            flash('error', 'Unsupported file type. Please upload a CSV file.');
            redirect('/admission/encode');
        }

        $tmpPath = (string)($upload['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_file($tmpPath)) {
            flash('error', 'Uploaded file could not be read.');
            redirect('/admission/encode');
        }

        try {
            $rows = self::parseBulkUploadFile($tmpPath, $extension);
            [$headerIndex, $headers] = self::locateBulkUploadHeader($rows);
            $records = self::normalizeBulkUploadRecords($rows, $headerIndex, $headers);
            $requiredColumns = self::requiredBulkUploadColumns();
            $missingColumns = array_values(array_diff($requiredColumns, array_keys($headers)));
            if (!empty($missingColumns)) {
                throw new RuntimeException('Missing required column(s): ' . implode(', ', $missingColumns));
            }

            $parts = WeightsService::getExamParts();
            $partMap = self::buildBulkExamPartHeaderMap($parts, $headers);

            $pdo = Database::pdo();
            $pdo->beginTransaction();

            $inserted = 0;
            $updated = 0;
            $scored = 0;

            foreach ($records as $index => $record) {
                $rowNumber = $headerIndex + $index + 2;
                if (self::isBulkRecordEmpty($record)) {
                    continue;
                }

                $studentPayload = self::mapBulkStudentRecord($record);
                if ($studentPayload['application_number'] === null) {
                    throw new RuntimeException("Row {$rowNumber}: Application Number is required.");
                }

                $studentPayload['application_status'] = self::mapBulkApplicationStatus((string)($record['Application Status'] ?? ''));
                $studentPayload['screening_status'] = self::mapBulkScreeningStatus(
                    (string)($record['Screening Status'] ?? ''),
                    (string)($record['Status'] ?? '')
                );
                $studentPayload['status'] = self::mapBulkExamResult(
                    (string)($record['Exam Result'] ?? ''),
                    (string)($record['Status'] ?? '')
                );
                $studentPayload['semester_id'] = (int)$activeSemester['id'];
                $studentPayload['name'] = self::buildStudentName(
                    (string)$studentPayload['last_name'],
                    (string)$studentPayload['first_name'],
                    (string)($studentPayload['middle_name'] ?? '')
                );

                $studentResult = self::upsertBulkStudentRecord($pdo, $studentPayload, $userId);
                if ($studentResult['created']) {
                    $inserted++;
                } else {
                    $updated++;
                }

                $scores = self::mapBulkScoresRecord($record, $partMap, $rowNumber);
                self::saveBulkStudentScores($pdo, (int)$studentResult['id'], $scores, $userId);
                RecommendationService::syncStudentOutcome((int)$studentResult['id']);
                $scored++;
            }

            $pdo->commit();

            Logger::log($userId, 'BULK_UPLOAD_SCORES', 'students', null, "Bulk imported {$scored} student score record(s)");
            flash('success', "Bulk upload complete. {$inserted} student(s) created, {$updated} updated, {$scored} score record(s) imported.");
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', APP_DEBUG ? $e->getMessage() : 'Bulk upload failed. Please check the file format and try again.');
        }

        redirect('/admission/encode');
    }

    public static function downloadBulkUploadTemplate(): void
    {
        RoleMiddleware::requireRole('admission');

        $headers = [
            'Count',
            'Rank',
            'Last Name',
            'First Name',
            'Middle Name',
            'Application Number',
            'Application Status',
            'City',
            'Province',
            'SHS Strand',
            'GPA',
            'Physical Requirement',
            'Honors Awards',
            'Residence',
            'Others',
            '1st Choice',
            '2nd Choice',
            'Score',
            'Exam Result',
            'Screening Status',
            'C-CAT 001 - English',
            'C-CAT 002 - Filipino',
            'C-CAT 003 - Humanities',
            'C-CAT 004 - Literature',
            'C-CAT 005 - Mathematics',
            'C-CAT 006 - Science',
            'C-CAT 007 - Social Studies',
            'C-CAT 008 - Spatial',
            'C-CAT 009 - Verbal',
            'C-CAT 010 - Interpersonal',
            'C-CAT 011 - Environmental',
            'C-CAT 012 - Customer Relations',
            'C-CAT 013 - Teaching',
            'C-CAT 014 -Entrepreneurial',
            'C-CAT 015 - Clerical.',
            'C-CAT 016 - Coding',
            'C-CAT 017 - Speed and Accuracy',
            'C-CAT 018 - Realistic',
            'C-CAT 019 - Investigative',
            'C-CAT 020 - Artistic',
            'C-CAT 021 - Social',
            'C-CAT 022 - Enterprising',
            'C-CAT 023 - Conventional',
            'C-CAT 024 - Openness',
            'C-CAT 025 - Conscientiousness',
            'C-CAT 026 - Extraversion',
            'C-CAT 027 - Agreeableness',
            'C-CAT 028 - Neuroticism',
        ];

        $activeSemester = self::getActiveSemester();
        $title = 'CITY COLLEGE OF TAGAYTAY - COLLEGE ADMISSION TEST (C-CAT)';
        $termLine = $activeSemester
            ? strtoupper((string)$activeSemester['semester_name']) . ', A.Y. ' . (string)$activeSemester['school_year_name']
            : 'CURRENT ACTIVE TERM';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bulk-upload-template.csv');

        $out = fopen('php://output', 'wb');
        if ($out === false) {
            return;
        }

        fputcsv($out, [$title]);
        fputcsv($out, [$termLine]);
        fputcsv($out, $headers);
        fclose($out);
        exit;
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('admission');

        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $recordScope = 'active';
        $schoolYearId = 0;
        $semesterId = 0;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

        $params = [];
        $where = "WHERE s.is_deleted = 0
                  AND COALESCE(s.is_archived, 0) = 0
                  AND EXISTS (
                      SELECT 1
                      FROM student_exam_scores ses
                      WHERE ses.student_id = s.id AND ses.is_deleted = 0
                  )";
        if ($q !== '') {
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.application_number LIKE :q_application_number)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_application_number'] = $like;
        }
        $archivedSchoolYears = [];
        $archivedSemesters = [];
        $archivedSemestersByYear = [];

        $sql = "SELECT s.id, s.application_number, s.name, s.email, s.application_status, s.screening_status, s.status, s.created_at,
                       s.is_deleted, s.is_archived,
                       sem.name AS semester_name,
                       sy.name AS school_year_name
                FROM students s
                LEFT JOIN semesters sem ON sem.id = s.semester_id
                LEFT JOIN school_years sy ON sy.id = sem.school_year_id
                $where
                ORDER BY s.created_at DESC";
        $st = Database::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->execute();
        $students = $st->fetchAll();

        $recommendations = [];
        if (!empty($students)) {
            $studentIds = array_map(
                static fn($row) => (int)$row['id'],
                $students
            );
            $recommendations = RecommendationService::getQualifiedRecommendationsForStudents($studentIds);
            $examResults = RecommendationService::getExamResultsForStudents($studentIds);
            foreach ($students as &$studentRow) {
                $studentId = (int)($studentRow['id'] ?? 0);
                if ($studentId > 0 && isset($examResults[$studentId])) {
                    $studentRow['status'] = $examResults[$studentId];
                }
            }
            unset($studentRow);
        if (in_array($status, ['passed', 'failed'], true)) {
            $students = array_values(array_filter(
                $students,
                static fn(array $studentRow): bool => (string)($studentRow['status'] ?? 'pending') === $status
            ));
        } else {
            $students = array_values(array_filter(
                $students,
                static fn(array $studentRow): bool => in_array((string)($studentRow['status'] ?? 'pending'), ['passed', 'failed'], true)
            ));
        }
        foreach ($students as &$studentRow) {
            $studentId = (int)($studentRow['id'] ?? 0);
            $studentRow['screening_status'] = $studentId > 0 && !empty($recommendations[$studentId]) ? 'qualified' : 'not_qualified';
        }
        unset($studentRow);
        $recommendations = array_intersect_key(
            $recommendations,
            array_flip(array_map(static fn(array $row): int => (int)$row['id'], $students))
        );
        }

        $total = count($students);
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;
        $students = array_slice($students, $offset, $perPage);
        $recommendations = array_intersect_key(
            $recommendations,
            array_flip(array_map(static fn(array $row): int => (int)$row['id'], $students))
        );

        View::render('admission/results', [
            'title' => 'Results & Recommendation',
            'students' => $students,
            'recommendations' => $recommendations,
            'q' => $q,
            'statusFilter' => $status,
            'recordScopeFilter' => $recordScope,
            'schoolYearFilter' => $schoolYearId,
            'semesterFilter' => $semesterId,
            'archivedSchoolYears' => $archivedSchoolYears,
            'archivedSemesters' => $archivedSemesters,
            'archivedSemestersByYear' => $archivedSemestersByYear,
            'activeSemester' => self::getActiveSemester(),
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
                    'record_scope' => '',
                    'school_year_id' => '',
                    'semester_id' => '',
                ],
            ],
        ]);
    }

    public static function viewScores(): void
    {
        RoleMiddleware::requireRole('admission');

        $id = (int)($_GET['id'] ?? 0);
        $st = Database::pdo()->prepare("SELECT id, application_number, name, email, first_choice, second_choice, application_status, screening_status, status FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $parts = WeightsService::getExamParts();
        $groupedParts = WeightsService::getExamPartsGrouped();
        $scoresMap = ScoresService::getStudentScoresMap($id);
        $courseSummaries = RecommendationService::getCourseEvaluationsForStudent($id);
        $student['status'] = RecommendationService::getExamResultForStudent($id);
        $student['first_choice_label'] = self::resolveCourseChoiceLabel((string)($student['first_choice'] ?? ''));
        $student['second_choice_label'] = self::resolveCourseChoiceLabel((string)($student['second_choice'] ?? ''));

        View::render('admission/view_scores', [
            'title' => 'View Scores',
            'student' => $student,
            'parts' => $parts,
            'groupedParts' => $groupedParts,
            'scoresMap' => $scoresMap,
            'courseSummaries' => $courseSummaries,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function storage(): void
    {
        RoleMiddleware::requireRole('admission');
        redirect('/admission/results');
    }

    public static function editStoredScores(): void
    {
        RoleMiddleware::requireRole('admission');

        $id = (int)($_GET['id'] ?? 0);
        $st = Database::pdo()->prepare("SELECT id, application_number, name, email, application_status, screening_status, status FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $student = $st->fetch();

        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $parts = WeightsService::getExamParts();
        $groupedParts = WeightsService::getExamPartsGrouped();
        $scoresMap = ScoresService::getStudentScoresMap($id);

        View::render('admission/encode_form', [
            'title' => 'Edit Scores',
            'student' => $student,
            'parts' => $parts,
            'groupedParts' => $groupedParts,
            'scoresMap' => $scoresMap,
            'activeSemester' => self::getActiveSemester(),
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
        $recordScope = trim((string)($_GET['record_scope'] ?? 'active'));
        $schoolYearId = max(0, (int)($_GET['school_year_id'] ?? 0));
        $semesterId = max(0, (int)($_GET['semester_id'] ?? 0));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

        $params = [];
        if ($recordScope === 'archived') {
            $where = "WHERE s.is_deleted = 1";
        } elseif ($recordScope === 'all') {
            $where = "WHERE 1=1";
        } else {
            $recordScope = 'active';
            $where = "WHERE s.is_deleted = 0 AND COALESCE(s.is_archived, 0) = 0";
        }
        if ($q !== '') {
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.application_number LIKE :q_application_number)";
            $like = '%' . $q . '%';
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
            $params[':q_application_number'] = $like;
        }
        if (in_array($status, ['pending', 'passed', 'failed'], true)) {
            $where .= " AND s.status = :status";
            $params[':status'] = $status;
        }
        if ($recordScope === 'archived' && $schoolYearId > 0) {
            $where .= " AND sy.id = :school_year_id";
            $params[':school_year_id'] = $schoolYearId;
        }
        if ($recordScope === 'archived' && $semesterId > 0) {
            $where .= " AND sem.id = :semester_id";
            $params[':semester_id'] = $semesterId;
        }

        $archivedSchoolYears = [];
        $archivedSemesters = [];
        $archivedSemestersByYear = [];
        if ($recordScope === 'archived') {
            $archivedSchoolYears = Database::pdo()->query("
                SELECT id, name
                FROM school_years
                WHERE is_deleted = 0 AND COALESCE(is_archived, 0) = 1
                ORDER BY created_at DESC
            ")->fetchAll();

            $allSemesterRows = Database::pdo()->query("
                SELECT id, school_year_id, name
                FROM semesters
                WHERE is_deleted = 0
                ORDER BY FIELD(name, '1st Semester', '2nd Semester', 'Summer')
            ")->fetchAll();
            foreach ($allSemesterRows as $semesterRow) {
                $archivedSemestersByYear[(int)$semesterRow['school_year_id']][] = [
                    'id' => (int)$semesterRow['id'],
                    'name' => (string)$semesterRow['name'],
                ];
            }

            if ($schoolYearId > 0) {
                $archivedSemesters = $archivedSemestersByYear[$schoolYearId] ?? [];
            }
        }

        $countSql = "SELECT COUNT(*)
                     FROM students s
                     LEFT JOIN semesters sem ON sem.id = s.semester_id
                     LEFT JOIN school_years sy ON sy.id = sem.school_year_id
                     $where";
        $countSt = Database::pdo()->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT s.id, s.application_number, s.name, s.email, s.application_status, s.screening_status, s.status, s.is_deleted, s.deleted_at, s.created_at,
                       sem.name AS semester_name,
                       sy.name AS school_year_name
                FROM students s
                LEFT JOIN semesters sem ON sem.id = s.semester_id
                LEFT JOIN school_years sy ON sy.id = sem.school_year_id
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

        View::render('admission/students', [
            'title' => 'Student Management',
            'students' => $students,
            'q' => $q,
            'statusFilter' => $status,
            'recordScopeFilter' => $recordScope,
            'activeSemester' => self::getActiveSemester(),
            'schoolYearFilter' => $schoolYearId,
            'semesterFilter' => $semesterId,
            'archivedSchoolYears' => $archivedSchoolYears,
            'archivedSemesters' => $archivedSemesters,
            'archivedSemestersByYear' => $archivedSemestersByYear,
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
                    'record_scope' => $recordScope,
                    'school_year_id' => $schoolYearId > 0 ? $schoolYearId : '',
                    'semester_id' => $semesterId > 0 ? $semesterId : '',
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
            'student' => [
                'first_name' => '',
                'last_name' => '',
                'middle_name' => '',
                'application_number' => '',
                'email' => '',
                'city' => '',
                'province' => '',
                'shs_strand' => '',
                'gpa' => '',
                'physical_requirement_status' => 'pending',
                'honors_awards_points' => '',
                'residence_points' => '',
                'other_screening_points' => '',
                'cct_choice' => 'first',
                'first_choice' => '',
                'second_choice' => '',
                'application_status' => 'new_student',
                'screening_status' => 'pending',
                'status' => 'pending',
            ],
            'activeSemester' => self::getActiveSemester(),
            'courseOptions' => self::studentCourseOptions(),
            'courseSummaries' => [],
            'error' => null,
        ]);
    }

    public static function storeStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $lastName = trim((string)($_POST['last_name'] ?? ''));
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $middleName = trim((string)($_POST['middle_name'] ?? ''));
        $applicationNumber = trim((string)($_POST['application_number'] ?? ''));
        $rawEmail = (string)($_POST['email'] ?? '');
        $email = self::normalizeStudentEmail($rawEmail);
        $city = trim((string)($_POST['city'] ?? ''));
        $province = trim((string)($_POST['province'] ?? ''));
        $shsStrand = trim((string)($_POST['shs_strand'] ?? ''));
        $gpaInput = trim((string)($_POST['gpa'] ?? ''));
        $gpa = $gpaInput === '' ? null : (float)$gpaInput;
        $physicalRequirementStatus = (string)($_POST['physical_requirement_status'] ?? 'pending');
        $honorsAwardsInput = trim((string)($_POST['honors_awards_points'] ?? ''));
        $honorsAwardsPoints = $honorsAwardsInput === '' ? null : (float)$honorsAwardsInput;
        $residenceInput = trim((string)($_POST['residence_points'] ?? ''));
        $residencePoints = $residenceInput === '' ? null : (float)$residenceInput;
        $otherScreeningInput = trim((string)($_POST['other_screening_points'] ?? ''));
        $otherScreeningPoints = $otherScreeningInput === '' ? null : (float)$otherScreeningInput;
        $cctChoice = (string)($_POST['cct_choice'] ?? 'first');
        $firstChoice = self::normalizeCourseChoice((string)($_POST['first_choice'] ?? ''));
        $secondChoice = self::normalizeCourseChoice((string)($_POST['second_choice'] ?? ''));
        $applicationStatus = (string)($_POST['application_status'] ?? 'new_student');
        $screeningStatus = (string)($_POST['screening_status'] ?? 'pending');
        $status = (string)($_POST['status'] ?? 'pending');
        $name = self::buildStudentName($lastName, $firstName, $middleName);

        $validationError = self::validateStudentInput($applicationNumber, $firstName, $lastName, $shsStrand, $cctChoice, $firstChoice, $secondChoice);
        if ($validationError !== null) {
            self::renderStudentFormMode('create', $validationError, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'physical_requirement_status' => $physicalRequirementStatus,
                'honors_awards_points' => $honorsAwardsInput,
                'residence_points' => $residenceInput,
                'other_screening_points' => $otherScreeningInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        if ($gpaInput !== '' && !is_numeric($gpaInput)) {
            self::renderStudentFormMode('create', 'General average must be a valid number.', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'physical_requirement_status' => $physicalRequirementStatus,
                'honors_awards_points' => $honorsAwardsInput,
                'residence_points' => $residenceInput,
                'other_screening_points' => $otherScreeningInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        if (!in_array($status, ['pending', 'passed', 'failed'], true)) {
            $status = 'pending';
        }
        if (!in_array($screeningStatus, ['pending', 'qualified', 'not_qualified'], true)) {
            $screeningStatus = 'pending';
        }
        if (!in_array($applicationStatus, ['new_student', 'transferee', 'returning_student', 'adult_learner', 'old_curriculum', 'als_passer'], true)) {
            $applicationStatus = 'new_student';
        }
        if (!in_array($physicalRequirementStatus, ['pending', 'met', 'not_met'], true)) {
            $physicalRequirementStatus = 'pending';
        }
        if (!in_array($cctChoice, ['first', 'second', 'none'], true)) {
            $cctChoice = 'first';
        }

        $activeSemester = self::getActiveSemester();
        if ($activeSemester === null) {
            self::renderStudentFormMode('create', 'Set an active semester first before creating students.', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'physical_requirement_status' => $physicalRequirementStatus,
                'honors_awards_points' => $honorsAwardsInput,
                'residence_points' => $residenceInput,
                'other_screening_points' => $otherScreeningInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare("SELECT id
                                FROM students
                                WHERE is_deleted = 0
                                  AND (
                                        (:email_check IS NOT NULL AND email = :email_match)
                                     OR (:application_number_check <> '' AND application_number = :application_number_match)
                                  )
                                LIMIT 1");
        $check->execute([
            ':email_check' => $email,
            ':email_match' => $email,
            ':application_number_check' => $applicationNumber,
            ':application_number_match' => $applicationNumber,
        ]);
        if ($check->fetch()) {
            self::renderStudentFormMode('create', 'Email or application number is already in use.', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        $insertSql = "INSERT INTO students (
                            application_number, name, first_name, last_name, middle_name,
                            email, city, province, shs_strand, gpa, physical_requirement_status,
                            honors_awards_points, residence_points, other_screening_points,"
            . (self::hasCctChoiceColumn() ? " cct_choice," : "")
            . " first_choice, second_choice,
                            application_status, screening_status, status, semester_id, created_by
                       ) VALUES (
                            :application_number, :name, :first_name, :last_name, :middle_name,
                            :email, :city, :province, :shs_strand, :gpa, :physical_requirement_status,
                            :honors_awards_points, :residence_points, :other_screening_points,"
            . (self::hasCctChoiceColumn() ? " :cct_choice," : "")
            . " :first_choice, :second_choice,
                            :application_status, :screening_status, :status, :semester_id, :created_by
                       )";
        $insertParams = [
                ':application_number' => $applicationNumber === '' ? null : $applicationNumber,
                ':name' => $name,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':middle_name' => $middleName === '' ? null : $middleName,
                ':email' => $email,
                ':city' => $city === '' ? null : $city,
                ':province' => $province === '' ? null : $province,
                ':shs_strand' => $shsStrand === '' ? null : $shsStrand,
                ':gpa' => $gpa,
                ':physical_requirement_status' => $physicalRequirementStatus,
                ':honors_awards_points' => $honorsAwardsPoints,
                ':residence_points' => $residencePoints,
                ':other_screening_points' => $otherScreeningPoints,
                ':first_choice' => $firstChoice,
                ':second_choice' => $secondChoice,
                ':application_status' => $applicationStatus,
                ':screening_status' => $screeningStatus,
                ':status' => $status,
                ':semester_id' => (int)$activeSemester['id'],
                ':created_by' => (int)($_SESSION['user_id'] ?? 0),
            ];
        if (self::hasCctChoiceColumn()) {
            $insertParams[':cct_choice'] = $cctChoice;
        }
        $pdo->prepare($insertSql)->execute($insertParams);

        Logger::log(currentUserId(), 'CREATE_STUDENT', 'students', (int)$pdo->lastInsertId(), 'Created student record');
        flash('success', 'Student created.');
        redirect('/admission/students');
    }

    public static function editStudent(): void
    {
        RoleMiddleware::requireRole('admission');

        $id = (int)($_GET['id'] ?? 0);
        $st = Database::pdo()->prepare("SELECT id, application_number, name, first_name, last_name, middle_name, email, city, province, shs_strand, gpa, physical_requirement_status, honors_awards_points, residence_points, other_screening_points,"
            . (self::hasCctChoiceColumn() ? " cct_choice," : " 'first' AS cct_choice,")
            . " first_choice, second_choice, application_status, screening_status, status, semester_id
            FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
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
            'activeSemester' => self::getActiveSemester(),
            'courseOptions' => self::studentCourseOptions(),
            'courseSummaries' => ScoresService::hasScores($id) ? RecommendationService::getCourseEvaluationsForStudent($id) : [],
            'error' => null,
        ]);
    }

    public static function updateStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $id = (int)($_POST['id'] ?? 0);
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $middleName = trim((string)($_POST['middle_name'] ?? ''));
        $applicationNumber = trim((string)($_POST['application_number'] ?? ''));
        $rawEmail = (string)($_POST['email'] ?? '');
        $email = self::normalizeStudentEmail($rawEmail);
        $city = trim((string)($_POST['city'] ?? ''));
        $province = trim((string)($_POST['province'] ?? ''));
        $shsStrand = trim((string)($_POST['shs_strand'] ?? ''));
        $gpaInput = trim((string)($_POST['gpa'] ?? ''));
        $gpa = $gpaInput === '' ? null : (float)$gpaInput;
        $physicalRequirementStatus = (string)($_POST['physical_requirement_status'] ?? 'pending');
        $honorsAwardsInput = trim((string)($_POST['honors_awards_points'] ?? ''));
        $honorsAwardsPoints = $honorsAwardsInput === '' ? null : (float)$honorsAwardsInput;
        $residenceInput = trim((string)($_POST['residence_points'] ?? ''));
        $residencePoints = $residenceInput === '' ? null : (float)$residenceInput;
        $otherScreeningInput = trim((string)($_POST['other_screening_points'] ?? ''));
        $otherScreeningPoints = $otherScreeningInput === '' ? null : (float)$otherScreeningInput;
        $cctChoice = (string)($_POST['cct_choice'] ?? 'first');
        $firstChoice = self::normalizeCourseChoice((string)($_POST['first_choice'] ?? ''));
        $secondChoice = self::normalizeCourseChoice((string)($_POST['second_choice'] ?? ''));
        $applicationStatus = (string)($_POST['application_status'] ?? 'new_student');
        $screeningStatus = (string)($_POST['screening_status'] ?? 'pending');
        $status = (string)($_POST['status'] ?? 'pending');
        $name = self::buildStudentName($lastName, $firstName, $middleName);

        $validationError = self::validateStudentInput($applicationNumber, $firstName, $lastName, $shsStrand, $cctChoice, $firstChoice, $secondChoice);
        if ($validationError !== null) {
            self::renderStudentFormMode('edit', $validationError, [
                'id' => $id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'physical_requirement_status' => $physicalRequirementStatus,
                'honors_awards_points' => $honorsAwardsInput,
                'residence_points' => $residenceInput,
                'other_screening_points' => $otherScreeningInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        if ($gpaInput !== '' && !is_numeric($gpaInput)) {
            self::renderStudentFormMode('edit', 'General average must be a valid number.', [
                'id' => $id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        if (!in_array($status, ['pending', 'passed', 'failed'], true)) {
            $status = 'pending';
        }
        if (!in_array($screeningStatus, ['pending', 'qualified', 'not_qualified'], true)) {
            $screeningStatus = 'pending';
        }
        if (!in_array($applicationStatus, ['new_student', 'transferee', 'returning_student', 'adult_learner', 'old_curriculum', 'als_passer'], true)) {
            $applicationStatus = 'new_student';
        }
        if (!in_array($physicalRequirementStatus, ['pending', 'met', 'not_met'], true)) {
            $physicalRequirementStatus = 'pending';
        }
        if (!in_array($cctChoice, ['first', 'second', 'none'], true)) {
            $cctChoice = 'first';
        }

        $pdo = Database::pdo();
        $currentStudentSt = $pdo->prepare("SELECT semester_id FROM students WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0 LIMIT 1");
        $currentStudentSt->execute([':id' => $id]);
        $currentStudent = $currentStudentSt->fetch();
        if (!$currentStudent) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $semesterId = (int)($currentStudent['semester_id'] ?? 0);
        if ($semesterId <= 0) {
            $activeSemester = self::getActiveSemester();
            if ($activeSemester === null) {
                self::renderStudentFormMode('edit', 'Set an active semester first before saving this student.', [
                    'id' => $id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => $middleName,
                    'application_number' => $applicationNumber,
                    'email' => $email,
                    'city' => $city,
                    'province' => $province,
                    'shs_strand' => $shsStrand,
                    'gpa' => $gpaInput,
                    'physical_requirement_status' => $physicalRequirementStatus,
                    'honors_awards_points' => $honorsAwardsInput,
                    'residence_points' => $residenceInput,
                    'other_screening_points' => $otherScreeningInput,
                    'cct_choice' => $cctChoice,
                    'first_choice' => $firstChoice,
                    'second_choice' => $secondChoice,
                    'application_status' => $applicationStatus,
                    'screening_status' => $screeningStatus,
                    'status' => $status,
                ]);
                return;
            }
            $semesterId = (int)$activeSemester['id'];
        }

        $check = $pdo->prepare("SELECT id
                                FROM students
                                WHERE id <> :id
                                  AND is_deleted = 0
                                  AND (
                                        (:email_check IS NOT NULL AND email = :email_match)
                                     OR (:application_number_check <> '' AND application_number = :application_number_match)
                                  )
                                LIMIT 1");
        $check->execute([
            ':email_check' => $email,
            ':email_match' => $email,
            ':application_number_check' => $applicationNumber,
            ':application_number_match' => $applicationNumber,
            ':id' => $id,
        ]);
        if ($check->fetch()) {
            self::renderStudentFormMode('edit', 'Email or application number is already in use.', [
                'id' => $id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
                'application_number' => $applicationNumber,
                'email' => $email,
                'city' => $city,
                'province' => $province,
                'shs_strand' => $shsStrand,
                'gpa' => $gpaInput,
                'physical_requirement_status' => $physicalRequirementStatus,
                'honors_awards_points' => $honorsAwardsInput,
                'residence_points' => $residenceInput,
                'other_screening_points' => $otherScreeningInput,
                'cct_choice' => $cctChoice,
                'first_choice' => $firstChoice,
                'second_choice' => $secondChoice,
                'application_status' => $applicationStatus,
                'screening_status' => $screeningStatus,
                'status' => $status,
            ]);
            return;
        }

        $updateSql = "UPDATE students
                       SET application_number = :application_number,
                           name = :name,
                           first_name = :first_name,
                           last_name = :last_name,
                           middle_name = :middle_name,
                           email = :email,
                           city = :city,
                           province = :province,
                           shs_strand = :shs_strand,
                           gpa = :gpa,
                           physical_requirement_status = :physical_requirement_status,
                           honors_awards_points = :honors_awards_points,
                           residence_points = :residence_points,
                           other_screening_points = :other_screening_points,
                           " . (self::hasCctChoiceColumn() ? "cct_choice = :cct_choice," : "") . "
                           first_choice = :first_choice,
                           second_choice = :second_choice,
                           application_status = :application_status,
                           screening_status = :screening_status,
                           status = :status,
                           semester_id = :semester_id,
                           updated_by = :updated_by
                       WHERE id = :id AND is_deleted = 0 AND COALESCE(is_archived, 0) = 0";
        $updateParams = [
                ':application_number' => $applicationNumber === '' ? null : $applicationNumber,
                ':name' => $name,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':middle_name' => $middleName === '' ? null : $middleName,
                ':email' => $email,
                ':city' => $city === '' ? null : $city,
                ':province' => $province === '' ? null : $province,
                ':shs_strand' => $shsStrand === '' ? null : $shsStrand,
                ':gpa' => $gpa,
                ':physical_requirement_status' => $physicalRequirementStatus,
                ':honors_awards_points' => $honorsAwardsPoints,
                ':residence_points' => $residencePoints,
                ':other_screening_points' => $otherScreeningPoints,
                ':first_choice' => $firstChoice,
                ':second_choice' => $secondChoice,
                ':application_status' => $applicationStatus,
                ':screening_status' => $screeningStatus,
                ':status' => $status,
                ':semester_id' => $semesterId,
                ':updated_by' => (int)($_SESSION['user_id'] ?? 0),
                ':id' => $id,
            ];
        if (self::hasCctChoiceColumn()) {
            $updateParams[':cct_choice'] = $cctChoice;
        }
        $pdo->prepare($updateSql)->execute($updateParams);

        if (ScoresService::hasScores($id)) {
            RecommendationService::syncStudentOutcome($id);
        }

        Logger::log(currentUserId(), 'UPDATE_STUDENT', 'students', $id, 'Admission updated student record');
        flash('success', 'Student updated.');
        redirect('/admission/students');
    }

    public static function archiveStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $id = (int)($_POST['id'] ?? 0);
        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        Database::pdo()->prepare("UPDATE students
                                  SET is_deleted = 1,
                                      deleted_at = NOW(),
                                      updated_by = :updated_by
                                  WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':id' => $id,
                ':updated_by' => $userId,
            ]);

        Logger::log($userId, 'ARCHIVE_STUDENT', 'students', $id, 'Admission archived student record');
        flash('success', 'Student archived.');
        redirect('/admission/students');
    }

    public static function restoreStudent(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('admission');

        $id = (int)($_POST['id'] ?? 0);
        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        Database::pdo()->prepare("UPDATE students
                                  SET is_deleted = 0,
                                      deleted_at = NULL,
                                      updated_by = :updated_by
                                  WHERE id = :id AND is_deleted = 1")
            ->execute([
                ':id' => $id,
                ':updated_by' => $userId,
            ]);

        Logger::log($userId, 'RESTORE_STUDENT', 'students', $id, 'Admission restored student record');
        flash('success', 'Student restored.');
        redirect('/admission/students?record_scope=archived');
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
                s.application_number LIKE :q_student_application_number OR
                eu.name LIKE :q_entity_user_name OR
                eu.email LIKE :q_entity_user_email OR
                ses_student.name LIKE :q_score_student_name OR
                ses_student.application_number LIKE :q_score_student_application_number OR
                ses_part.name LIKE :q_score_part_name OR
                wc.course_name LIKE :q_weight_course_name OR
                wc.course_code LIKE :q_weight_course_code OR
                wep.name LIKE :q_weight_part_name
            )";
            $like = '%' . $q . '%';
            $params[':q_details'] = $like;
            $params[':q_student_name'] = $like;
            $params[':q_student_application_number'] = $like;
            $params[':q_entity_user_name'] = $like;
            $params[':q_entity_user_email'] = $like;
            $params[':q_score_student_name'] = $like;
            $params[':q_score_student_application_number'] = $like;
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
                                   COALESCE(ses_student.application_number, 'No Application Number'),
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
                           ELSE COALESCE(s.application_number, eu.email)
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
        $studentsWhere = "WHERE is_deleted = 0 AND COALESCE(is_archived, 0) = 0";
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
             WHERE s.is_deleted = 0 AND COALESCE(s.is_archived, 0) = 0" . $addDateFilter('ses.created_at', $scoresParams)
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

        View::render('admission/reports', [
            'title' => 'Report Management',
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

    public static function printReport(): void
    {
        RoleMiddleware::requireRole('admission');

        $reportType = trim((string)($_GET['report_type'] ?? 'course_recommendation'));
        $allowedTypes = ['applicant_list', 'test_results', 'course_recommendation'];
        if (!in_array($reportType, $allowedTypes, true)) {
            $reportType = 'course_recommendation';
        }

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
        $studentsWhere = "WHERE is_deleted = 0 AND COALESCE(is_archived, 0) = 0";
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
             WHERE s.is_deleted = 0 AND COALESCE(s.is_archived, 0) = 0" . $addDateFilter('ses.created_at', $scoresParams)
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
                INNER JOIN students s
                    ON s.id = ses.student_id
                WHERE ses.is_deleted = 0
                  AND s.is_deleted = 0
                  AND COALESCE(s.is_archived, 0) = 0" . $addDateFilter('ses.created_at', $scoresParams) . "
                GROUP BY ses.student_id
            )
            SELECT COUNT(*) FROM ranked WHERE rn = 1"
        );
        $studentsWithRecommendationsSt->execute($scoresParams);
        $studentsWithRecommendations = (int)$studentsWithRecommendationsSt->fetchColumn();

        $reportData = self::getPrintableReportData($reportType, $startDate, $endDate);

        $periodLabel = 'All time';
        if ($startDate !== '' || $endDate !== '') {
            $startLabel = $startDate !== '' ? date('M j, Y', strtotime($startDate)) : 'Beginning';
            $endLabel = $endDate !== '' ? date('M j, Y', strtotime($endDate)) : 'Present';
            $periodLabel = $startLabel . ' to ' . $endLabel;
        }

        $activeSemester = self::getActiveSemester();
        $semesterName = $activeSemester['label'] ?? 'All Semesters';

        View::renderStandalone('admission/reports_print', [
            'title' => 'Print Report',
            'reportType' => $reportType,
            'reportData' => $reportData,
            'summary' => [
                'students_total' => $studentsTotal,
                'score_entries' => $scoreEntries,
                'students_without_scores' => $studentsWithoutScores,
                'students_with_recommendations' => $studentsWithRecommendations,
            ],
            'studentStatusCounts' => $studentStatusCounts,
            'examParts' => $examParts,
            'periodLabel' => $periodLabel,
            'semesterName' => $semesterName,
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
        return RecommendationService::getQualifiedRecommendationsForStudents($studentIds, $limit);
    }

    private static function getTopRecommendationsForStudent(int $studentId, int $limit): array
    {
        return RecommendationService::getQualifiedRecommendationsForStudent($studentId, $limit);
    }

    private static function getCourseRecommendationsForStudent(int $studentId): array
    {
        return RecommendationService::getCourseEvaluationsForStudent($studentId);
    }

    private static function getPrintableReportData(string $reportType, string $startDate, string $endDate): array
    {
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

        if ($reportType === 'applicant_list') {
            $params = [];
            $where = "WHERE s.is_deleted = 0 AND COALESCE(s.is_archived, 0) = 0";
            $where .= $addDateFilter('s.created_at', $params);

            $st = Database::pdo()->prepare(
                "SELECT s.application_number,
                        s.name,
                        s.email,
                        s.status,
                        sy.name AS school_year_name,
                        sem.name AS semester_name,
                        COUNT(ses.id) AS score_entries
                 FROM students s
                 LEFT JOIN semesters sem ON sem.id = s.semester_id
                 LEFT JOIN school_years sy ON sy.id = sem.school_year_id
                 LEFT JOIN student_exam_scores ses
                   ON ses.student_id = s.id
                  AND ses.is_deleted = 0
                 {$where}
                 GROUP BY s.id
                 ORDER BY s.name ASC"
            );
            $st->execute($params);
            return $st->fetchAll();
        }

        if ($reportType === 'test_results') {
            $params = [];
            $dateFilter = $addDateFilter('ses.created_at', $params);
            $st = Database::pdo()->prepare(
                "SELECT s.application_number,
                        s.name,
                        s.status,
                        ROUND(SUM(ses.score), 2) AS total_exam_score,
                        MAX(ses.created_at) AS exam_date
                 FROM students s
                 INNER JOIN student_exam_scores ses
                    ON ses.student_id = s.id
                   AND ses.is_deleted = 0
                 WHERE s.is_deleted = 0
                   AND COALESCE(s.is_archived, 0) = 0
                   {$dateFilter}
                 GROUP BY s.id
                 ORDER BY exam_date DESC, s.name ASC"
            );
            $st->execute($params);
            return $st->fetchAll();
        }

        $params = [];
        $dateFilter = $addDateFilter('ses.created_at', $params);
        $st = Database::pdo()->prepare(
            "WITH ranked AS (
                SELECT
                    s.id,
                    s.application_number,
                    s.name,
                    sy.name AS school_year_name,
                    sem.name AS semester_name,
                    c.course_code,
                    c.course_name,
                    SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS final_score,
                    ROW_NUMBER() OVER (
                        PARTITION BY s.id
                        ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC, c.course_name ASC
                    ) AS rn
                FROM students s
                INNER JOIN student_exam_scores ses
                    ON ses.student_id = s.id
                   AND ses.is_deleted = 0
                INNER JOIN exam_parts ep
                    ON ep.id = ses.exam_part_id
                   AND ep.is_deleted = 0
                INNER JOIN weights w
                    ON w.exam_part_id = ses.exam_part_id
                   AND w.is_deleted = 0
                INNER JOIN courses c
                    ON c.id = w.course_id
                   AND c.is_deleted = 0
                LEFT JOIN semesters sem
                    ON sem.id = s.semester_id
                LEFT JOIN school_years sy
                    ON sy.id = sem.school_year_id
                WHERE s.is_deleted = 0
                  AND COALESCE(s.is_archived, 0) = 0
                  {$dateFilter}
                GROUP BY s.id, c.id
            )
            SELECT application_number,
                   name,
                   school_year_name,
                   semester_name,
                   course_code,
                   course_name,
                   final_score
            FROM ranked
            WHERE rn = 1
            ORDER BY final_score DESC, name ASC"
        );
        $st->execute($params);
        return array_map(static function (array $row): array {
            $row['recommendation'] = [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'final_score' => (float)$row['final_score'],
            ];
            return $row;
        }, $st->fetchAll());
    }

    private static function renderStudentFormMode(string $mode, string $error, array $student): void
    {
        View::render('students/form', [
            'title' => $mode === 'create' ? 'Create Student' : 'Edit Student',
            'mode' => $mode,
            'action' => $mode === 'create' ? '/admission/students/create' : '/admission/students/edit',
            'student' => $student,
            'activeSemester' => self::getActiveSemester(),
            'courseOptions' => self::studentCourseOptions(),
            'courseSummaries' => !empty($student['id']) && ScoresService::hasScores((int)$student['id'])
                ? RecommendationService::getCourseEvaluationsForStudent((int)$student['id'])
                : [],
            'error' => $error,
        ]);
    }

    private static function studentCourseOptions(): array
    {
        $courses = WeightsService::getAllCourses();
        return array_map(static function (array $course): array {
            return [
                'id' => (int)$course['id'],
                'code' => (string)$course['course_code'],
                'label' => (string)$course['course_code'] . ': ' . (string)($course['course_category'] ?? '') . ' - ' . (string)$course['course_name'],
            ];
        }, $courses);
    }

    private static function normalizeCourseChoice(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach (self::studentCourseOptions() as $courseOption) {
            if ($value === (string)$courseOption['id'] || strcasecmp($value, (string)$courseOption['code']) === 0) {
                return (string)$courseOption['id'];
            }
        }

        return null;
    }

    private static function resolveCourseChoiceLabel(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return 'Not selected';
        }

        foreach (WeightsService::getAllCourses() as $course) {
            if ($value === (string)$course['id'] || strcasecmp($value, (string)$course['course_code']) === 0) {
                return (string)$course['course_code'] . ' - ' . (string)$course['course_name'];
            }
        }

        return $value;
    }

    private static function decorateStudentChoiceLabels(array $student): array
    {
        $student['first_choice_label'] = self::resolveCourseChoiceLabel((string)($student['first_choice'] ?? ''));
        $student['second_choice_label'] = self::resolveCourseChoiceLabel((string)($student['second_choice'] ?? ''));
        return $student;
    }

    private static function validateStudentInput(
        string $applicationNumber,
        string $firstName,
        string $lastName,
        string $shsStrand,
        string $cctChoice,
        ?string $firstChoice,
        ?string $secondChoice
    ): ?string {
        if ($applicationNumber === '') {
            return 'Please enter the application number.';
        }

        if ($firstName === '' || $lastName === '') {
            return 'Please enter the student first name and last name.';
        }

        if ($shsStrand === '') {
            return 'Please enter the SHS strand.';
        }

        if ($firstChoice === null) {
            return 'Please select the 1st choice program.';
        }

        if ($secondChoice !== null && $secondChoice === $firstChoice) {
            return 'The 2nd choice program must be different from the 1st choice.';
        }

        if ($cctChoice === 'first' && $firstChoice === null) {
            return 'Select a 1st choice program for the chosen CCT choice.';
        }

        return null;
    }

    private static function parseBulkUploadFile(string $filePath, string $extension): array
    {
        return $extension === 'xlsx'
            ? self::parseBulkUploadXlsx($filePath)
            : self::parseBulkUploadCsv($filePath);
    }

    private static function parseBulkUploadCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open uploaded CSV file.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(static fn($value): string => trim((string)$value), $row);
        }
        fclose($handle);

        return $rows;
    }

    private static function parseBulkUploadXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('XLSX upload is not supported on this server. Please upload the CSV version instead.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Unable to open uploaded XLSX file.');
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml !== false && isset($xml->si)) {
                foreach ($xml->si as $item) {
                    if (isset($item->t)) {
                        $sharedStrings[] = (string)$item->t;
                        continue;
                    }

                    $text = '';
                    foreach ($item->r as $run) {
                        $text .= (string)$run->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Uploaded XLSX file does not contain a readable first worksheet.');
        }

        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false || !isset($sheet->sheetData->row)) {
            throw new RuntimeException('Uploaded XLSX file is empty or invalid.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $cellRef = (string)($cell['r'] ?? '');
                $columnLetters = preg_replace('/\d+/', '', $cellRef);
                $columnIndex = self::xlsxColumnLettersToIndex($columnLetters);
                while (count($rowValues) < $columnIndex) {
                    $rowValues[] = '';
                }

                $rawValue = isset($cell->v) ? (string)$cell->v : '';
                $type = (string)($cell['t'] ?? '');
                if ($type === 's') {
                    $value = $sharedStrings[(int)$rawValue] ?? '';
                } else {
                    $value = $rawValue;
                }
                $rowValues[$columnIndex] = trim($value);
            }
            $rows[] = $rowValues;
        }

        return $rows;
    }

    private static function xlsxColumnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private static function locateBulkUploadHeader(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(static fn($value): string => trim((string)$value), $row);
            if (in_array('Application Number', $normalized, true) && in_array('Last Name', $normalized, true)) {
                $headers = [];
                foreach ($normalized as $columnIndex => $header) {
                    if ($header !== '') {
                        $headers[$header] = $columnIndex;
                    }
                }

                return [$index, $headers];
            }
        }

        throw new RuntimeException('Could not find the header row in the uploaded file.');
    }

    private static function normalizeBulkUploadRecords(array $rows, int $headerIndex, array $headers): array
    {
        $records = [];
        for ($i = $headerIndex + 1, $count = count($rows); $i < $count; $i++) {
            $row = $rows[$i];
            $record = [];
            foreach ($headers as $header => $columnIndex) {
                $record[$header] = trim((string)($row[$columnIndex] ?? ''));
            }
            $records[] = $record;
        }

        return $records;
    }

    private static function requiredBulkUploadColumns(): array
    {
        return [
            'Last Name',
            'First Name',
            'Application Number',
        ];
    }

    private static function buildBulkExamPartHeaderMap(array $parts, array $headers): array
    {
        $aliases = [
            'English' => ['C-CAT 001 - English'],
            'Filipino' => ['C-CAT 002 - Filipino'],
            'Humanities' => ['C-CAT 003 - Humanities'],
            'Literature' => ['C-CAT 004 - Literature'],
            'Math' => ['C-CAT 005 - Mathematics'],
            'Science' => ['C-CAT 006 - Science'],
            'Studies' => ['C-CAT 007 - Social Studies'],
            'Non-Verbal Reasoning / Spatial' => ['C-CAT 008 - Spatial'],
            'Verbal Aptitude' => ['C-CAT 009 - Verbal'],
            'Inter-Personal Aptitude' => ['C-CAT 010 - Interpersonal'],
            'Environmental Aptitude' => ['C-CAT 011 - Environmental'],
            'Customer Service' => ['C-CAT 012 - Customer Relations'],
            'Teaching Aptitude' => ['C-CAT 013 - Teaching'],
            'Entrepreneurial' => ['C-CAT 014 -Entrepreneurial', 'C-CAT 014 - Entrepreneurial'],
            'Clerical' => ['C-CAT 015 - Clerical.'],
            'Coding' => ['C-CAT 016 - Coding'],
            'Speed & Accuracy' => ['C-CAT 017 - Speed and Accuracy'],
            'Realistic' => ['C-CAT 018 - Realistic'],
            'Investigative' => ['C-CAT 019 - Investigative'],
            'Artistic' => ['C-CAT 020 - Artistic'],
            'Social' => ['C-CAT 021 - Social'],
            'Enterprising' => ['C-CAT 022 - Enterprising'],
            'Conventional' => ['C-CAT 023 - Conventional'],
            'Openness' => ['C-CAT 024 - Openness'],
            'Conscientiousness' => ['C-CAT 025 - Conscientiousness'],
            'Extraversion' => ['C-CAT 026 - Extraversion'],
            'Agreeableness' => ['C-CAT 027 - Agreeableness'],
            'Neuroticism' => ['C-CAT 028 - Neuroticism'],
        ];

        $partMap = [];
        $missing = [];
        foreach ($parts as $part) {
            $partName = (string)$part['name'];
            $headerOptions = $aliases[$partName] ?? [$partName];
            $matchedHeader = null;
            foreach ($headerOptions as $headerName) {
                if (array_key_exists($headerName, $headers)) {
                    $matchedHeader = $headerName;
                    break;
                }
            }

            if ($matchedHeader === null) {
                $missing[] = $partName;
                continue;
            }

            $partMap[(int)$part['id']] = [
                'name' => $partName,
                'header' => $matchedHeader,
                'max_score' => (float)$part['max_score'],
            ];
        }

        if (!empty($missing)) {
            throw new RuntimeException('The uploaded file is missing score columns for: ' . implode(', ', $missing));
        }

        return $partMap;
    }

    private static function isBulkRecordEmpty(array $record): bool
    {
        foreach ($record as $value) {
            if (trim((string)$value) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function mapBulkStudentRecord(array $record): array
    {
        return [
            'application_number' => self::nullableString($record['Application Number'] ?? ''),
            'first_name' => trim((string)($record['First Name'] ?? '')),
            'last_name' => trim((string)($record['Last Name'] ?? '')),
            'middle_name' => self::nullableString($record['Middle Name'] ?? ''),
            'email' => null,
            'city' => self::nullableString($record['City'] ?? ''),
            'province' => self::nullableString($record['Province'] ?? ''),
            'shs_strand' => self::nullableString($record['SHS Strand'] ?? ''),
            'gpa' => is_numeric(trim((string)($record['GPA'] ?? ''))) ? (float)$record['GPA'] : null,
            'physical_requirement_status' => self::mapBulkPhysicalRequirement((string)($record['Physical Requirement'] ?? '')),
            'honors_awards_points' => is_numeric(trim((string)($record['Honors Awards'] ?? ''))) ? (float)$record['Honors Awards'] : null,
            'residence_points' => is_numeric(trim((string)($record['Residence'] ?? ''))) ? (float)$record['Residence'] : null,
            'other_screening_points' => is_numeric(trim((string)($record['Others'] ?? ''))) ? (float)$record['Others'] : null,
            'cct_choice' => self::mapBulkCctChoice((string)($record['CCT Choice'] ?? '')),
            'first_choice' => self::normalizeCourseChoice((string)($record['1st Choice'] ?? '')),
            'second_choice' => self::normalizeCourseChoice((string)($record['2nd Choice'] ?? '')),
            'application_status' => 'new_student',
            'screening_status' => 'pending',
        ];
    }

    private static function nullableString(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private static function mapBulkApplicationStatus(string $status): string
    {
        $normalized = strtoupper(str_replace(['-', '_'], ' ', trim($status)));
        return match ($normalized) {
            'TRANSFEREE' => 'transferee',
            'RETURNING STUDENT' => 'returning_student',
            'ADULT LEARNER' => 'adult_learner',
            'OLD CURRICULUM' => 'old_curriculum',
            'ALS PASSER' => 'als_passer',
            default => 'new_student',
        };
    }

    private static function mapBulkExamResult(string $examResult, string $legacyStatus): string
    {
        $normalized = strtoupper(str_replace(['-', '_'], ' ', trim($examResult !== '' ? $examResult : $legacyStatus)));
        return match ($normalized) {
            'PASSED', 'QUALIFIED', 'ADMITTED', 'WAITLISTED' => 'passed',
            'FAILED', 'REJECTED', 'DISQUALIFIED' => 'failed',
            default => 'pending',
        };
    }

    private static function mapBulkScreeningStatus(string $screeningStatus, string $legacyStatus): string
    {
        $normalized = strtoupper(str_replace(['-', '_'], ' ', trim($screeningStatus !== '' ? $screeningStatus : $legacyStatus)));
        return match ($normalized) {
            'QUALIFIED', 'ADMITTED', 'WAITLISTED' => 'qualified',
            'NOT QUALIFIED', 'DISQUALIFIED', 'REJECTED' => 'not_qualified',
            default => 'pending',
        };
    }

    private static function mapBulkPhysicalRequirement(string $value): string
    {
        $normalized = strtoupper(str_replace(['-', '_'], ' ', trim($value)));
        return match ($normalized) {
            'MET', 'PASSED', 'YES', 'COMPLETE' => 'met',
            'NOT MET', 'FAILED', 'NO', 'INCOMPLETE' => 'not_met',
            default => 'pending',
        };
    }

    private static function mapBulkCctChoice(string $value): string
    {
        $normalized = strtoupper(str_replace(['-', '_'], ' ', trim($value)));
        return match ($normalized) {
            '1', 'FIRST', '1ST', 'FIRST CHOICE', '1ST CHOICE' => 'first',
            '2', 'SECOND', '2ND', 'SECOND CHOICE', '2ND CHOICE' => 'second',
            'NONE', 'NOT SELECTED', 'N/A' => 'none',
            default => 'first',
        };
    }

    private static function upsertBulkStudentRecord(PDO $pdo, array $student, int $userId): array
    {
        $find = $pdo->prepare("
            SELECT id
            FROM students
            WHERE is_deleted = 0
              AND (
                    (:application_number_check IS NOT NULL AND application_number = :application_number_match)
              )
            LIMIT 1
        ");
        $find->execute([
            ':application_number_check' => $student['application_number'],
            ':application_number_match' => $student['application_number'],
        ]);
        $existingId = (int)($find->fetchColumn() ?: 0);

        if ($existingId > 0) {
            $updateSql = "
                UPDATE students
                SET application_number = :application_number,
                    name = :name,
                    first_name = :first_name,
                    last_name = :last_name,
                    middle_name = :middle_name,
                    email = :email,
                    city = :city,
                    province = :province,
                    shs_strand = :shs_strand,
                    gpa = :gpa,
                    physical_requirement_status = :physical_requirement_status,
                    honors_awards_points = :honors_awards_points,
                    residence_points = :residence_points,
                    other_screening_points = :other_screening_points,
                    " . (self::hasCctChoiceColumn() ? "cct_choice = :cct_choice," : "") . "
                    first_choice = :first_choice,
                    second_choice = :second_choice,
                    application_status = :application_status,
                    screening_status = :screening_status,
                    status = :status,
                    semester_id = :semester_id,
                    is_archived = 0,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0
            ";
            $updateParams = [
                ':application_number' => $student['application_number'],
                ':name' => $student['name'],
                ':first_name' => $student['first_name'],
                ':last_name' => $student['last_name'],
                ':middle_name' => $student['middle_name'],
                ':email' => $student['email'],
                ':city' => $student['city'],
                ':province' => $student['province'],
                ':shs_strand' => $student['shs_strand'],
                ':gpa' => $student['gpa'],
                ':physical_requirement_status' => $student['physical_requirement_status'],
                ':honors_awards_points' => $student['honors_awards_points'],
                ':residence_points' => $student['residence_points'],
                ':other_screening_points' => $student['other_screening_points'],
                ':first_choice' => $student['first_choice'],
                ':second_choice' => $student['second_choice'],
                ':application_status' => $student['application_status'],
                ':screening_status' => $student['screening_status'],
                ':status' => $student['status'],
                ':semester_id' => $student['semester_id'],
                ':updated_by' => $userId,
                ':id' => $existingId,
            ];
            if (self::hasCctChoiceColumn()) {
                $updateParams[':cct_choice'] = $student['cct_choice'];
            }
            $pdo->prepare($updateSql)->execute($updateParams);

            return ['id' => $existingId, 'created' => false];
        }

        $insertSql = "
            INSERT INTO students (
                application_number, name, first_name, last_name, middle_name,
                email, city, province, shs_strand, gpa, physical_requirement_status,
                honors_awards_points, residence_points, other_screening_points,"
                . (self::hasCctChoiceColumn() ? " cct_choice," : "") . "
                first_choice, second_choice,
                application_status, screening_status, status, semester_id, created_by
            ) VALUES (
                :application_number, :name, :first_name, :last_name, :middle_name,
                :email, :city, :province, :shs_strand, :gpa, :physical_requirement_status,
                :honors_awards_points, :residence_points, :other_screening_points,"
                . (self::hasCctChoiceColumn() ? " :cct_choice," : "") . "
                :first_choice, :second_choice,
                :application_status, :screening_status, :status, :semester_id, :created_by
            )
        ";
        $insertParams = [
            ':application_number' => $student['application_number'],
            ':name' => $student['name'],
            ':first_name' => $student['first_name'],
            ':last_name' => $student['last_name'],
            ':middle_name' => $student['middle_name'],
            ':email' => $student['email'],
            ':city' => $student['city'],
            ':province' => $student['province'],
            ':shs_strand' => $student['shs_strand'],
            ':gpa' => $student['gpa'],
            ':physical_requirement_status' => $student['physical_requirement_status'],
            ':honors_awards_points' => $student['honors_awards_points'],
            ':residence_points' => $student['residence_points'],
            ':other_screening_points' => $student['other_screening_points'],
            ':first_choice' => $student['first_choice'],
            ':second_choice' => $student['second_choice'],
            ':application_status' => $student['application_status'],
            ':screening_status' => $student['screening_status'],
            ':status' => $student['status'],
            ':semester_id' => $student['semester_id'],
            ':created_by' => $userId,
        ];
        if (self::hasCctChoiceColumn()) {
            $insertParams[':cct_choice'] = $student['cct_choice'];
        }
        $pdo->prepare($insertSql)->execute($insertParams);

        return ['id' => (int)$pdo->lastInsertId(), 'created' => true];
    }

    private static function mapBulkScoresRecord(array $record, array $partMap, int $rowNumber): array
    {
        $scores = [];
        foreach ($partMap as $partId => $part) {
            $raw = trim((string)($record[$part['header']] ?? ''));
            if ($raw === '' || !is_numeric($raw)) {
                throw new RuntimeException("Row {$rowNumber}: Missing or invalid score for {$part['name']}.");
            }

            $score = (float)(int)round((float)$raw);
            if ($score < 0 || $score > (float)$part['max_score']) {
                throw new RuntimeException("Row {$rowNumber}: Score out of range for {$part['name']} (0-" . $part['max_score'] . ').');
            }

            $scores[$partId] = $score;
        }

        return $scores;
    }

    private static function saveBulkStudentScores(PDO $pdo, int $studentId, array $scores, int $userId): void
    {
        $sql = "INSERT INTO student_exam_scores (student_id, exam_part_id, score, encoded_by, updated_by)
                VALUES (:student_id, :exam_part_id, :score, :encoded_by, :updated_by)
                ON DUPLICATE KEY UPDATE
                score = VALUES(score),
                updated_by = VALUES(updated_by),
                updated_at = CURRENT_TIMESTAMP,
                is_deleted = 0,
                deleted_at = NULL";
        $stmt = $pdo->prepare($sql);

        foreach ($scores as $partId => $score) {
            $stmt->execute([
                ':student_id' => $studentId,
                ':exam_part_id' => (int)$partId,
                ':score' => $score,
                ':encoded_by' => $userId,
                ':updated_by' => $userId,
            ]);
        }
    }

    private static function buildStudentName(string $lastName, string $firstName, string $middleName): string
    {
        $parts = [
            trim($firstName),
            trim($middleName),
            trim($lastName),
        ];

        return trim(implode(' ', array_values(array_filter($parts, static fn(string $part): bool => $part !== ''))));
    }

    private static function normalizeStudentEmail(string $email): ?string
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private static function getActiveSemester(): ?array
    {
        try {
            $st = Database::pdo()->query("
                SELECT s.id, s.name AS semester_name, sy.name AS school_year_name
                FROM semesters s
                INNER JOIN school_years sy ON sy.id = s.school_year_id
                WHERE s.is_active = 1
                  AND s.is_deleted = 0
                  AND COALESCE(s.is_archived, 0) = 0
                  AND sy.is_deleted = 0
                  AND COALESCE(sy.is_archived, 0) = 0
                LIMIT 1
            ");
            $row = $st->fetch();
            if (!$row) {
                return null;
            }
            $row['label'] = trim((string)$row['school_year_name']) . ' - ' . trim((string)$row['semester_name']);
            return $row;
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function hasCctChoiceColumn(): bool
    {
        if (self::$cctChoiceColumnExists !== null) {
            return self::$cctChoiceColumnExists;
        }

        try {
            $st = Database::pdo()->query("SHOW COLUMNS FROM students LIKE 'cct_choice'");
            self::$cctChoiceColumnExists = (bool)$st->fetch();
        } catch (Throwable) {
            self::$cctChoiceColumnExists = false;
        }

        return self::$cctChoiceColumnExists;
    }
}
