<?php
declare(strict_types=1);

final class AccountsController
{
    private static ?bool $cctChoiceColumnExists = null;

    public static function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $access = trim((string)($_GET['access'] ?? 'active'));
        $recordScope = trim((string)($_GET['record_scope'] ?? 'active'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

        if ($recordScope === 'archived') {
            $where = "WHERE u.is_deleted = 1";
        } elseif ($recordScope === 'all') {
            $where = "WHERE 1=1";
        } else {
            $recordScope = 'active';
            $where = "WHERE u.is_deleted = 0";
        }
        if (($recordScope === 'archived' || $recordScope === 'all') && !isset($_GET['access'])) {
            $access = 'all';
        }
        $params = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $where .= " AND (u.name LIKE :q_name OR u.email LIKE :q_email)";
            $params[':q_name'] = $like;
            $params[':q_email'] = $like;
        }

        if (in_array($role, ['administrator', 'admission'], true)) {
            $where .= " AND u.role = :role";
            $params[':role'] = $role;
        }

        $where .= " AND u.role IN ('administrator', 'admission')";

        if (in_array($status, ['pending', 'verified', 'rejected'], true)) {
            $where .= " AND u.account_status = :status";
            $params[':status'] = $status;
        }

        // Exclude current logged-in user from the main list
        $where .= " AND u.id != :current_user_id";
        $params[':current_user_id'] = $_SESSION['user_id'];
        if ($access === '' || $access === 'all') {
            $access = 'all';
        } elseif ($access === 'disabled') {
            $where .= " AND u.is_active = 0";
        } else {
            $access = 'active';
            $where .= " AND u.is_active = 1";
        }

        $countSql = "SELECT COUNT(*)
                     FROM users u
                     $where";
        $countSt = Database::pdo()->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.id,
                       u.name,
                       u.email,
                       u.role,
                       u.is_deleted,
                       u.deleted_at,
                       u.account_status,
                       u.rejection_reason,
                       u.is_active,
                       u.created_at,
                       u.verified_at,
                       u.rejected_at,
                       v.name AS verified_by_name,
                       r.name AS rejected_by_name,
                       d.name AS deleted_by_name
                FROM users u
                LEFT JOIN users v ON v.id = u.verified_by
                LEFT JOIN users r ON r.id = u.rejected_by
                LEFT JOIN users d ON d.id = u.deleted_by
                $where
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        $st = Database::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $st->bindValue($key, $value);
        }
        $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $users = $st->fetchAll();

        // Fetch current user details separately for the profile card
        $currentUserSql = "SELECT u.id, u.name, u.email, u.role, u.account_status, u.is_active, u.created_at, u.verified_at
                           FROM users u
                           WHERE u.id = :uid LIMIT 1";
        $cst = Database::pdo()->prepare($currentUserSql);
        $cst->execute([':uid' => $_SESSION['user_id']]);
        $currentUser = $cst->fetch();

        View::render('admin/accounts/index', [
            'title' => 'Accounts',
            'users' => $users,
            'currentUser' => $currentUser,
            'q' => $q,
            'roleFilter' => $role,
            'statusFilter' => $status,
            'accessFilter' => $access,
            'recordScopeFilter' => $recordScope,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'perPage' => $perPage,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
                'basePath' => '/administrator/accounts',
                'query' => [
                    'q' => $q,
                    'role' => $role,
                    'status' => $status,
                    'access' => $access,
                    'record_scope' => $recordScope,
                ],
            ],
        ]);
    }

    public static function students(): void
    {
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
            $like = '%' . $q . '%';
            $where .= " AND (s.name LIKE :q_name OR s.email LIKE :q_email OR s.application_number LIKE :q_application_number)";
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

        $sql = "SELECT s.id,
                       s.application_number,
                       s.name,
                       s.email,
                       s.application_status,
                       s.screening_status,
                       s.status,
                       s.is_deleted,
                       s.deleted_at,
                       s.created_at,
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
        $activeSemester = self::getActiveSemester();

        View::render('admin/students', [
            'title' => 'Student Management',
            'students' => $students,
            'q' => $q,
            'statusFilter' => $status,
            'recordScopeFilter' => $recordScope,
            'activeSemester' => $activeSemester,
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
                'basePath' => '/administrator/students',
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
        View::render('students/form', [
            'title' => 'Create Student',
            'mode' => 'create',
            'action' => '/administrator/students/create',
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

        if ($firstName === '' || $lastName === '') {
            self::renderStudentForm('create', 'Please enter the student first name and last name.', [
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
            self::renderStudentForm('create', 'General average must be a valid number.', [
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
            self::renderStudentForm('create', 'Set an active semester first before creating students.', [
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
                                        (:email IS NOT NULL AND email = :email)
                                     OR (:application_number <> '' AND application_number = :application_number)
                                  )
                                LIMIT 1");
        $check->execute([
            ':email' => $email,
            ':application_number' => $applicationNumber,
        ]);
        if ($check->fetch()) {
            self::renderStudentForm('create', 'Email or application number is already in use.', [
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
        redirect('/administrator/students');
    }

    public static function editStudent(): void
    {
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
            'action' => '/administrator/students/edit',
            'student' => self::decorateStudentChoiceLabels($student),
            'activeSemester' => self::getActiveSemester(),
            'courseOptions' => self::studentCourseOptions(),
            'courseSummaries' => ScoresService::hasScores($id) ? RecommendationService::getCourseEvaluationsForStudent($id) : [],
            'error' => null,
        ]);
    }

    public static function updateStudent(): void
    {
        verifyCsrfOrFail();

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

        if ($firstName === '' || $lastName === '') {
            self::renderStudentForm('edit', 'Please enter the student first name and last name.', [
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
            self::renderStudentForm('edit', 'General average must be a valid number.', [
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
                self::renderStudentForm('edit', 'Set an active semester first before saving this student.', [
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
            self::renderStudentForm('edit', 'Email or application number is already in use.', [
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

        Logger::log(currentUserId(), 'UPDATE_STUDENT', 'students', $id, 'Updated student record');
        flash('success', 'Student updated.');
        redirect('/administrator/students');
    }

    private static function renderStudentForm(string $mode, string $error, array $student): void
    {
        View::render('students/form', [
            'title' => $mode === 'create' ? 'Create Student' : 'Edit Student',
            'mode' => $mode,
            'action' => $mode === 'create' ? '/administrator/students/create' : '/administrator/students/edit',
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

        foreach (self::studentCourseOptions() as $courseOption) {
            if ($value === (string)$courseOption['id'] || strcasecmp($value, (string)$courseOption['code']) === 0) {
                return (string)$courseOption['label'];
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

    public static function create(): void
    {
        View::render('admin/accounts/create', [
            'title' => 'Create Account',
            'error' => null,
            'old' => ['name'=>'','email'=>'','role'=>'admission','is_active'=>1],
        ]);
    }

    public static function store(): void
    {
        verifyCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = (string)($_POST['role'] ?? 'admission');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::render('admin/accounts/create', [
                'title' => 'Create Account',
                'error' => 'Please enter a valid name and email.',
                'old' => compact('name','email','role') + ['is_active'=>$isActive],
            ]);
            return;
        }

        if (!in_array($role, ['administrator', 'admission'], true)) {
            $role = 'admission';
        }

        // Limit administrator accounts to maximum 2
        if ($role === 'administrator') {
            $st = Database::pdo()->prepare("SELECT COUNT(*) FROM users WHERE role = 'administrator' AND is_deleted = 0");
            $st->execute();
            if ((int)$st->fetchColumn() >= 2) {
                View::render('admin/accounts/create', [
                    'title' => 'Create Account',
                    'error' => 'Maximum limit of 2 administrator accounts has been reached.',
                    'old' => compact('name', 'email', 'role') + ['is_active' => $isActive],
                ]);
                return;
            }
        }

        $accountStatus = 'verified';
        $isActive = $isActive ? 1 : 0;

        $tempPassword = PasswordService::generateTempPassword();
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (name, email, password, role, account_status, is_active, force_password_change, email_verified_at, verified_by, verified_at, created_by)
                    VALUES (:name, :email, :password, :role, :account_status, :is_active, 1, NOW(), :verified_by, NOW(), :created_by)";
            Database::pdo()->prepare($sql)->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hash,
                ':role' => $role,
                ':account_status' => $accountStatus,
                ':is_active' => $isActive,
                ':verified_by' => (int)($_SESSION['user_id'] ?? 0),
                ':created_by' => (int)($_SESSION['user_id'] ?? 0),
            ]);

            $userId = (int)Database::pdo()->lastInsertId();

            $statusMsg = 'Verified';
            $subject = APP_NAME . ' - Your Temporary Password';
            $html = "
                <p>Hello " . e($name) . ",</p>
                <p>Your account has been created. Use the temporary password below to log in:</p>
                <p><strong>" . e($tempPassword) . "</strong></p>
                <p>Please change your password after logging in.</p>
                ";

            $sent = Mailer::send($email, $name, $subject, $html);

            Logger::log((int)($_SESSION['user_id'] ?? 0), 'CREATE_ACCOUNT', 'users', $userId, $sent ? 'Temporary password emailed' : 'Temporary password email NOT sent (dev fallback)');

            if ($sent) {
                flash('success', "Account created ({$statusMsg}). Temporary password sent to {$email}.");
            } else {
                flash('success', "Account created ({$statusMsg}). Temporary password: {$tempPassword} (DEV fallback: email not sent).");
            }
            redirect('/administrator/accounts');
        } catch (Throwable $e) {
            $msg = APP_DEBUG ? $e->getMessage() : 'Failed to create account (email might already exist).';
            View::render('admin/accounts/create', [
                'title' => 'Create Account',
                'error' => $msg,
                'old' => compact('name','email','role') + ['is_active'=>$isActive],
            ]);
        }
    }

    public static function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $sql = "SELECT u.id,
                       u.name,
                       u.email,
                       u.role,
                       u.account_status,
                       u.rejection_reason,
                       u.is_active,
                       u.verified_at,
                       u.rejected_at,
                       v.name AS verified_by_name,
                       r.name AS rejected_by_name
                FROM users u
                LEFT JOIN users v ON v.id = u.verified_by
                LEFT JOIN users r ON r.id = u.rejected_by
                WHERE u.id = :id AND u.is_deleted = 0
                LIMIT 1";
        $st = Database::pdo()->prepare($sql);
        $st->execute([':id' => $id]);
        $user = $st->fetch();

        if (!$user) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('admin/accounts/edit', [
            'title' => 'Edit Account',
            'error' => null,
            'user' => $user,
        ]);
    }

    public static function update(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = (string)($_POST['role'] ?? 'admission');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderEditWithError($id, 'Please enter a valid name and email.');
            return;
        }
        if (!in_array($role, ['administrator', 'admission'], true)) {
            $role = 'admission';
        }

        // Limit administrator accounts to maximum 2
        if ($role === 'administrator') {
            $checkSt = Database::pdo()->prepare("SELECT role FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
            $checkSt->execute([':id' => $id]);
            $oldRole = (string)$checkSt->fetchColumn();

            if ($oldRole !== 'administrator') {
                $countSt = Database::pdo()->prepare("SELECT COUNT(*) FROM users WHERE role = 'administrator' AND is_deleted = 0");
                $countSt->execute();
                if ((int)$countSt->fetchColumn() >= 2) {
                    self::renderEditWithError($id, 'Maximum limit of 2 administrator accounts has been reached.');
                    return;
                }
            }
        }

        // prevent self demotion/disable accidents (optional)
        $selfId = (int)($_SESSION['user_id'] ?? 0);
        if ($id === $selfId && $isActive !== 1) {
            self::renderEditWithError($id, 'You cannot disable your own account.');
            return;
        }
        $st = Database::pdo()->prepare("SELECT role, account_status FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        $currentRole = (string)($row['role'] ?? '');
        $currentStatus = (string)($row['account_status'] ?? 'pending');

        $isSystemRole = in_array($role, ['administrator', 'admission'], true);
        $wasSystemRole = in_array($currentRole, ['administrator', 'admission'], true);
        $newStatus = $currentStatus;

        if ($isSystemRole && !$wasSystemRole) {
            $newStatus = 'pending';
            $isActive = 0;
        }

        if ($newStatus !== 'verified') {
            $isActive = 0;
        }

        $sql = "UPDATE users
                SET name = :name,
                    email = :email,
                    role = :role,
                    account_status = :account_status,
                    is_active = :is_active,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0";
        try {
            Database::pdo()->prepare($sql)->execute([
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':account_status' => $newStatus,
                ':is_active' => $isActive ? 1 : 0,
                ':updated_by' => $selfId,
                ':id' => $id,
            ]);
            Logger::log($selfId, 'UPDATE_ACCOUNT', 'users', $id, 'Updated account details');
            flash('success', 'Account updated.');
            redirect('/administrator/accounts');
        } catch (Throwable $e) {
            self::renderEditWithError($id, APP_DEBUG ? $e->getMessage() : 'Failed to update account.');
        }
    }

    public static function toggleActive(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $selfId = (int)($_SESSION['user_id'] ?? 0);

        if ($id === $selfId) {
            flash('error', 'You cannot disable your own account.');
            redirect('/administrator/accounts');
        }

        $sql = "UPDATE users
                SET is_active = IF(is_active = 1, 0, 1),
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0";
        Database::pdo()->prepare($sql)->execute([
            ':id' => $id,
            ':updated_by' => $selfId,
        ]);

        flash('success', 'Account status updated.');
        redirect('/administrator/accounts');
    }

    public static function archive(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $selfId = (int)($_SESSION['user_id'] ?? 0);

        if ($id === $selfId) {
            flash('error', 'You cannot archive your own account.');
            redirect('/administrator/accounts');
        }

        $sql = "UPDATE users
                SET is_deleted = 1,
                    deleted_at = NOW(),
                    deleted_by = :deleted_by,
                    is_active = 0,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0";
        Database::pdo()->prepare($sql)->execute([
            ':id' => $id,
            ':deleted_by' => $selfId,
            ':updated_by' => $selfId,
        ]);

        Logger::log($selfId, 'ARCHIVE_ACCOUNT', 'users', $id, 'Archived account');
        flash('success', 'Account archived.');
        redirect('/administrator/accounts');
    }

    public static function restore(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $selfId = (int)($_SESSION['user_id'] ?? 0);

        $sql = "UPDATE users
                SET is_deleted = 0,
                    deleted_at = NULL,
                    deleted_by = NULL,
                    is_active = 1,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 1";
        Database::pdo()->prepare($sql)->execute([
            ':id' => $id,
            ':updated_by' => $selfId,
        ]);

        Logger::log($selfId, 'RESTORE_ACCOUNT', 'users', $id, 'Restored archived account');
        flash('success', 'Account restored.');
        redirect('/administrator/accounts?record_scope=archived');
    }

    public static function verify(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $selfId = (int)($_SESSION['user_id'] ?? 0);

        $st = Database::pdo()->prepare("SELECT email_verified_at FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        if (!$row || empty($row['email_verified_at'])) {
            flash('error', 'User email is not verified yet.');
            redirect('/administrator/accounts');
        }

        $sql = "UPDATE users
                SET account_status = 'verified',
                    is_active = 1,
                    verified_by = :verified_by,
                    verified_at = NOW(),
                    rejected_by = NULL,
                    rejected_at = NULL,
                    rejection_reason = NULL,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0 AND role IN ('administrator', 'admission')";
        Database::pdo()->prepare($sql)->execute([
            ':verified_by' => $selfId,
            ':updated_by' => $selfId,
            ':id' => $id,
        ]);

        Logger::log($selfId, 'VERIFY_ACCOUNT', 'users', $id, 'Verified system account');
        flash('success', 'Account verified and activated.');
        redirect('/administrator/accounts');
    }

    public static function reject(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? 'Not eligible.'));
        $selfId = (int)($_SESSION['user_id'] ?? 0);

        $sql = "UPDATE users
                SET account_status = 'rejected',
                    is_active = 0,
                    rejected_by = :rejected_by,
                    rejected_at = NOW(),
                    rejection_reason = :reason,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0 AND role IN ('administrator', 'admission')";
        Database::pdo()->prepare($sql)->execute([
            ':rejected_by' => $selfId,
            ':reason' => $reason === '' ? 'Not eligible.' : $reason,
            ':updated_by' => $selfId,
            ':id' => $id,
        ]);

        Logger::log($selfId, 'REJECT_ACCOUNT', 'users', $id, 'Rejected system account');
        flash('success', 'Account rejected.');
        redirect('/administrator/accounts');
    }

    public static function resetPassword(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);

        $st = Database::pdo()->prepare("SELECT id, name, email FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $id]);
        $user = $st->fetch();
        if (!$user) {
            flash('error', 'User not found.');
            redirect('/administrator/accounts');
        }

        $tempPassword = PasswordService::generateTempPassword();
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users
                SET password = :password,
                    force_password_change = 1,
                    failed_login_attempts = 0,
                    locked_until = NULL,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0";
        Database::pdo()->prepare($sql)->execute([
            ':password' => $hash,
            ':updated_by' => (int)($_SESSION['user_id'] ?? 0),
            ':id' => $id,
        ]);

        $subject = APP_NAME . ' - Password Reset';
        $html = "
            <p>Hello " . e((string)$user['name']) . ",</p>
            <p>Your password has been reset. Use the temporary password below to log in:</p>
            <p><strong>" . e($tempPassword) . "</strong></p>
            <p>Please change your password after logging in.</p>
            ";

        $sent = Mailer::send((string)$user['email'], (string)$user['name'], $subject, $html);

        Logger::log((int)($_SESSION['user_id'] ?? 0), 'RESET_PASSWORD', 'users', $id, $sent ? 'Temporary password emailed' : 'Temporary password email NOT sent (dev fallback)');

        if ($sent) {
            flash('success', 'Password reset. Temporary password sent to the user email.');
        } else {
            flash('success', "Password reset. Temporary password: {$tempPassword} (DEV fallback: email not sent).");
        }
        redirect('/administrator/accounts');
    }

    private static function renderEditWithError(int $id, string $error): void
    {
        $sql = "SELECT u.id,
                       u.name,
                       u.email,
                       u.role,
                       u.account_status,
                       u.rejection_reason,
                       u.is_active,
                       u.verified_at,
                       u.rejected_at,
                       v.name AS verified_by_name,
                       r.name AS rejected_by_name
                FROM users u
                LEFT JOIN users v ON v.id = u.verified_by
                LEFT JOIN users r ON r.id = u.rejected_by
                WHERE u.id = :id AND u.is_deleted = 0
                LIMIT 1";
        $st = Database::pdo()->prepare($sql);
        $st->execute([':id' => $id]);
        $user = $st->fetch();

        View::render('admin/accounts/edit', [
            'title' => 'Edit Account',
            'error' => $error,
            'user' => $user ?: ['id'=>$id,'name'=>'','email'=>'','role'=>'admission','account_status'=>'pending','is_active'=>1],
        ]);
    }

    public static function archiveStudent(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        Database::pdo()->prepare("UPDATE students
                                  SET is_deleted = 1,
                                      deleted_at = NOW(),
                                      updated_by = :updated_by
                                  WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':id' => $id,
                ':updated_by' => $userId,
            ]);

        Logger::log($userId, 'ARCHIVE_STUDENT', 'students', $id, 'Archived student record');
        flash('success', 'Student archived.');
        redirect('/administrator/students');
    }

    public static function restoreStudent(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        Database::pdo()->prepare("UPDATE students
                                  SET is_deleted = 0,
                                      deleted_at = NULL,
                                      updated_by = :updated_by
                                  WHERE id = :id AND is_deleted = 1")
            ->execute([
                ':id' => $id,
                ':updated_by' => $userId,
            ]);

        Logger::log($userId, 'RESTORE_STUDENT', 'students', $id, 'Restored student record');
        flash('success', 'Student restored.');
        redirect('/administrator/students?record_scope=archived');
    }
}
