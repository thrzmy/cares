<?php
declare(strict_types = 1)
;

final class SemesterController
{
    public static function index(): void
    {
        RoleMiddleware::requireRole('administrator');
        $pdo = Database::pdo();

        try {
            // Safety check: ensure tables exist
            $pdo->query("SELECT 1 FROM school_years LIMIT 1");
            $pdo->query("SELECT 1 FROM semesters LIMIT 1");

            $success = flash('success');
            $error = flash('error');
            $recordScope = trim((string)($_GET['record_scope'] ?? 'active'));
            if (!in_array($recordScope, ['active', 'archived'], true)) {
                $recordScope = 'active';
            }

            // Get all school years with their semesters
            $allSchoolYears = $pdo->query("
                SELECT id, name, is_active, is_archived, created_at
                FROM school_years
                WHERE is_deleted = 0
                ORDER BY created_at DESC
            ")->fetchAll();

            // Segregate into active (non-archived) and archived
            $activeSchoolYear = null;
            $archivedSchoolYears = [];
            foreach ($allSchoolYears as $sy) {
                if ((int)$sy['is_archived'] === 0) {
                    $activeSchoolYear = $sy;
                } else {
                    $archivedSchoolYears[] = $sy;
                }
            }

            $hasActiveSchoolYear = ($activeSchoolYear !== null);

            // Get semesters for each school year
            $semestersByYear = [];
            if (!empty($allSchoolYears)) {
                $syIds = array_map(fn($sy) => (int)$sy['id'], $allSchoolYears);
                $placeholders = implode(',', array_fill(0, count($syIds), '?'));
                $st = $pdo->prepare("
                    SELECT id, school_year_id, name, is_active, is_archived, created_at
                    FROM semesters
                    WHERE school_year_id IN ({$placeholders}) AND is_deleted = 0
                    ORDER BY FIELD(name, '1st Semester', '2nd Semester', 'Summer')
                ");
                $st->execute($syIds);
                foreach ($st->fetchAll() as $sem) {
                    $semestersByYear[(int)$sem['school_year_id']][] = $sem;
                }
            }

            // Get current active semester info
            $activeSemester = $pdo->query("
                SELECT s.id AS semester_id, s.name AS semester_name, sy.id AS sy_id, sy.name AS sy_name
                FROM semesters s
                JOIN school_years sy ON sy.id = s.school_year_id
                WHERE s.is_active = 1 AND s.is_deleted = 0 AND sy.is_deleted = 0 AND s.is_archived = 0 AND sy.is_archived = 0
                LIMIT 1
            ")->fetch();

            // Pagination for archived years only when viewing archives
            $total = count($archivedSchoolYears);
            $perPage = 6;
            $page = max(1, (int)($_GET['page'] ?? 1));
            $pages = max(1, (int)ceil($total / $perPage));
            $page = min($page, $pages);
            $offset = ($page - 1) * $perPage;
            $archivedSlice = array_slice($archivedSchoolYears, $offset, $perPage);

            $from = $total > 0 ? $offset + 1 : 0;
            $to = min($offset + $perPage, $total);

            if ($recordScope !== 'archived') {
                $archivedSlice = [];
            }

            View::render('admin/semesters', [
                'title' => 'Academic Year and Semester',
                'activeSchoolYear' => $activeSchoolYear,
                'archivedSchoolYears' => $archivedSlice,
                'hasActiveSchoolYear' => $hasActiveSchoolYear,
                'semestersByYear' => $semestersByYear,
                'activeSemester' => $activeSemester,
                'recordScopeFilter' => $recordScope,
                'success' => $success,
                'error' => $error,
                'pagination' => [
                    'page' => $page,
                    'pages' => $pages,
                    'total' => $total,
                    'perPage' => $perPage,
                    'from' => $from,
                    'to' => $to,
                    'basePath' => '/administrator/semesters',
                    'query' => [
                        'record_scope' => $recordScope,
                    ],
                ],
            ]);
        }
        catch (Throwable $e) {
            View::render('admin/semesters', [
                'title' => 'Academic Year and Semester',
                'activeSchoolYear' => null,
                'archivedSchoolYears' => [],
                'hasActiveSchoolYear' => false,
                'semestersByYear' => [],
                'activeSemester' => null,
                'recordScopeFilter' => 'active',
                'success' => null,
                'error' => 'Database error: ' . (APP_DEBUG ? $e->getMessage() : 'Please contact administrator.'),
                'pagination' => ['page' => 1, 'pages' => 1, 'total' => 0, 'perPage' => 10, 'from' => 0, 'to' => 0, 'basePath' => '', 'query' => []]
            ]);
        }
    }

    public static function storeSchoolYear(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $syName = trim((string)($_POST['name'] ?? ''));
        $semName = trim((string)($_POST['semester'] ?? ''));

        if ($syName === '' || $semName === '') {
            flash('error', 'Academic year and semester are required.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();

        try {
            $pdo->beginTransaction();

            // 1. Handle School Year
            $checkSy = $pdo->prepare("SELECT id, is_deleted FROM school_years WHERE name = :name LIMIT 1");
            $checkSy->execute([':name' => $syName]);
            $existingSy = $checkSy->fetch();

            if ($existingSy) {
                $syId = (int)$existingSy['id'];
                if ($existingSy['is_deleted']) {
                    // Restore deleted SY
                    $pdo->prepare("UPDATE school_years SET is_deleted = 0, is_archived = 0 WHERE id = :id")
                        ->execute([':id' => $syId]);
                }
            }
            else {
                // Prevent creating a new academic year if an unarchived one already exists
                $checkActive = $pdo->query("SELECT id FROM school_years WHERE is_archived = 0 AND is_deleted = 0 LIMIT 1")->fetch();
                if ($checkActive) {
                    flash('error', 'Cannot create a new Academic Year while another is currently active. Please archive the current one first.');
                    $pdo->rollBack();
                    redirect('/administrator/semesters');
                    return;
                }

                $pdo->prepare("INSERT INTO school_years (name, created_by, is_active) VALUES (:name, :uid, 0)")
                    ->execute([':name' => $syName, ':uid' => currentUserId()]);
                $syId = (int)$pdo->lastInsertId();
            }

            // 2. Handle Semester
            $checkSem = $pdo->prepare("SELECT id, is_deleted FROM semesters WHERE school_year_id = :sy_id AND name = :name LIMIT 1");
            $checkSem->execute([':sy_id' => $syId, ':name' => $semName]);
            $existingSem = $checkSem->fetch();

            if ($existingSem) {
                if (!$existingSem['is_deleted']) {
                    flash('error', "Semester '{$semName}' already exists for Academic Year '{$syName}'.");
                    $pdo->rollBack();
                    redirect('/administrator/semesters');
                    return;
                }
                // Restore deleted semester
                $pdo->prepare("UPDATE semesters SET is_deleted = 0, is_archived = 0 WHERE id = :id")
                    ->execute([':id' => (int)$existingSem['id']]);
                $semId = (int)$existingSem['id'];
            }
            else {
                $pdo->prepare("INSERT INTO semesters (school_year_id, name, created_by, is_active) VALUES (:sy_id, :name, :uid, 0)")
                    ->execute([':sy_id' => $syId, ':name' => $semName, ':uid' => currentUserId()]);
                $semId = (int)$pdo->lastInsertId();
            }

            $pdo->commit();

            Logger::log(currentUserId(), 'ADD_SEMESTER', 'semesters', $semId, "Added semester '{$semName}' to Academic Year '{$syName}'");
            flash('success', "Semester '{$semName}' added to Academic Year '{$syName}'.");
        }
        catch (Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to add semester.');
        }

        redirect('/administrator/semesters');
    }

    public static function updateSchoolYear(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));

        if ($id <= 0 || $name === '') {
            flash('error', 'Invalid academic year.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();

        // Check duplicate (excluding self, but check ALL records)
        $check = $pdo->prepare("SELECT id, is_deleted FROM school_years WHERE name = :name AND id <> :id LIMIT 1");
        $check->execute([':name' => $name, ':id' => $id]);
        $existing = $check->fetch();
        if ($existing) {
            if ($existing['is_deleted']) {
                flash('error', 'This name is taken by a deleted academic year record. Please use a different name.');
            }
            else {
                flash('error', 'Academic year name already in use.');
            }
            redirect('/administrator/semesters');
        }

        $pdo->prepare("UPDATE school_years SET name = :name, updated_by = :uid WHERE id = :id AND is_deleted = 0")
            ->execute([':name' => $name, ':uid' => currentUserId(), ':id' => $id]);

        Logger::log(currentUserId(), 'UPDATE_ACADEMIC_YEAR', 'school_years', $id, "Updated academic year: {$name}");
        flash('success', "Academic year updated to {$name}.");
        redirect('/administrator/semesters');
    }

    public static function archiveSchoolYear(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid academic year.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();

        // Get name for logging
        $sy = $pdo->prepare("SELECT name, is_active FROM school_years WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $sy->execute([':id' => $id]);
        $schoolYear = $sy->fetch();

        if (!$schoolYear) {
            flash('error', 'Academic year not found.');
            redirect('/administrator/semesters');
        }

        $pdo->beginTransaction();
        try {
            // Legacy safety net: if older student records were created before
            // semester assignment was enforced, tie unassigned active students
            // to a semester in this academic year so the archive includes them.
            $pdo->prepare("
                UPDATE students
                SET semester_id = (
                    SELECT s.id
                    FROM semesters s
                    WHERE s.school_year_id = :sy_id
                      AND s.is_deleted = 0
                    ORDER BY s.is_active DESC, s.id ASC
                    LIMIT 1
                )
                WHERE semester_id IS NULL
                  AND is_deleted = 0
                  AND COALESCE(is_archived, 0) = 0
            ")->execute([':sy_id' => $id]);

            // Archive and deactivate SY
            $pdo->prepare("UPDATE school_years SET is_archived = 1, is_active = 0 WHERE id = :id")->execute([':id' => $id]);

            // Archive and deactivate all its semesters
            $pdo->prepare("UPDATE semesters SET is_archived = 1, is_active = 0 WHERE school_year_id = :sy_id")->execute([':sy_id' => $id]);

            // Archive all students in those semesters
            $pdo->prepare("UPDATE students SET is_archived = 1 WHERE semester_id IN (SELECT id FROM semesters WHERE school_year_id = :sy_id) AND is_deleted = 0")
                ->execute([':sy_id' => $id]);

            $pdo->commit();

            Logger::log(currentUserId(), 'ARCHIVE_ACADEMIC_YEAR', 'school_years', $id, "Archived academic year and all its records: {$schoolYear['name']}");
            flash('success', "Academic year {$schoolYear['name']} and all associated records have been archived.");
        }
        catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to archive academic year.');
        }

        redirect('/administrator/semesters');
    }

    public static function setActive(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $semesterId = (int)($_POST['semester_id'] ?? 0);
        if ($semesterId <= 0) {
            flash('error', 'Invalid semester.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();

        // Get the semester info
        $sem = $pdo->prepare("SELECT s.id, s.name, s.is_archived, sy.is_archived AS sy_archived, s.school_year_id, sy.name AS sy_name
                              FROM semesters s
                              JOIN school_years sy ON sy.id = s.school_year_id
                              WHERE s.id = :id AND s.is_deleted = 0
                              LIMIT 1");
        $sem->execute([':id' => $semesterId]);
        $semester = $sem->fetch();

        if (!$semester) {
            flash('error', 'Semester not found.');
            redirect('/administrator/semesters');
        }

        if ((int)$semester['is_archived'] === 1 || (int)$semester['sy_archived'] === 1) {
            flash('error', 'Cannot activate an archived semester or academic year.');
            redirect('/administrator/semesters');
        }

        $pdo->beginTransaction();
        try {
            // Deactivate all
            $pdo->exec("UPDATE school_years SET is_active = 0");
            $pdo->exec("UPDATE semesters SET is_active = 0");

            // Activate selected
            $pdo->prepare("UPDATE school_years SET is_active = 1 WHERE id = :id")->execute([':id' => (int)$semester['school_year_id']]);
            $pdo->prepare("UPDATE semesters SET is_active = 1 WHERE id = :id")->execute([':id' => $semesterId]);

            $pdo->commit();

            $label = $semester['sy_name'] . ' - ' . $semester['name'];
            Logger::log(currentUserId(), 'SET_ACTIVE_SEMESTER', 'semesters', $semesterId, "Set active: {$label}");
            flash('success', "Active semester set to: {$label}");
        }
        catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to set active semester.');
        }

        redirect('/administrator/semesters');
    }

    public static function restoreSchoolYear(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid academic year.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();
        $sy = $pdo->prepare("SELECT id, name FROM school_years WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $sy->execute([':id' => $id]);
        $schoolYear = $sy->fetch();

        if (!$schoolYear) {
            flash('error', 'Academic year not found.');
            redirect('/administrator/semesters');
        }

        $activeYearSt = $pdo->prepare("SELECT id FROM school_years WHERE is_deleted = 0 AND is_archived = 0 AND id <> :id LIMIT 1");
        $activeYearSt->execute([':id' => $id]);
        if ($activeYearSt->fetch()) {
            flash('error', 'Archive the current academic year first before restoring another one.');
            redirect('/administrator/semesters');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE school_years SET is_archived = 0, is_active = 0 WHERE id = :id")
                ->execute([':id' => $id]);

            $pdo->prepare("UPDATE semesters SET is_archived = 0, is_active = 0 WHERE school_year_id = :sy_id AND is_deleted = 0")
                ->execute([':sy_id' => $id]);

            $pdo->prepare("
                UPDATE students
                SET is_archived = 0
                WHERE semester_id IN (
                    SELECT id FROM semesters WHERE school_year_id = :sy_id AND is_deleted = 0
                )
                  AND is_deleted = 0
            ")->execute([':sy_id' => $id]);

            $pdo->commit();

            Logger::log(currentUserId(), 'RESTORE_ACADEMIC_YEAR', 'school_years', $id, "Restored academic year and all its records: {$schoolYear['name']}");
            flash('success', "Academic year {$schoolYear['name']} and its records were restored.");
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to restore academic year.');
        }

        redirect('/administrator/semesters');
    }

    public static function archiveSemester(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $semesterId = (int)($_POST['semester_id'] ?? 0);
        if ($semesterId <= 0) {
            flash('error', 'Invalid semester.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();

        // Get info for logging
        $sem = $pdo->prepare("SELECT s.id, s.name, s.is_active, sy.name AS sy_name
                              FROM semesters s
                              JOIN school_years sy ON sy.id = s.school_year_id
                              WHERE s.id = :id AND s.is_deleted = 0 LIMIT 1");
        $sem->execute([':id' => $semesterId]);
        $semester = $sem->fetch();

        if (!$semester) {
            flash('error', 'Semester not found.');
            redirect('/administrator/semesters');
        }

        if ((int)$semester['is_active'] === 1) {
            flash('error', 'Cannot archive the currently active semester. Please set another semester active first.');
            redirect('/administrator/semesters');
        }

        $pdo->beginTransaction();
        try {
            // Archive the semester
            $pdo->prepare("UPDATE semesters SET is_archived = 1 WHERE id = :id")
                ->execute([':id' => $semesterId]);

            // Archive all students in this semester
            $pdo->prepare("UPDATE students SET is_archived = 1 WHERE semester_id = :sem_id AND is_deleted = 0")
                ->execute([':sem_id' => $semesterId]);

            $pdo->commit();

            $label = $semester['sy_name'] . ' - ' . $semester['name'];
            Logger::log(currentUserId(), 'ARCHIVE_SEMESTER', 'semesters', $semesterId, "Archived semester and its students: {$label}");
            flash('success', "Semester {$label} and all its associated students have been archived.");
        }
        catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to archive semester.');
        }

        redirect('/administrator/semesters');
    }

    public static function restoreSemester(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('administrator');

        $semesterId = (int)($_POST['semester_id'] ?? 0);
        if ($semesterId <= 0) {
            flash('error', 'Invalid semester.');
            redirect('/administrator/semesters');
        }

        $pdo = Database::pdo();
        $sem = $pdo->prepare("
            SELECT s.id, s.name, sy.name AS sy_name, sy.is_archived AS sy_archived
            FROM semesters s
            JOIN school_years sy ON sy.id = s.school_year_id
            WHERE s.id = :id AND s.is_deleted = 0
            LIMIT 1
        ");
        $sem->execute([':id' => $semesterId]);
        $semester = $sem->fetch();

        if (!$semester) {
            flash('error', 'Semester not found.');
            redirect('/administrator/semesters');
        }

        if ((int)$semester['sy_archived'] === 1) {
            flash('error', 'Restore the academic year first before restoring its semester.');
            redirect('/administrator/semesters');
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE semesters SET is_archived = 0, is_active = 0 WHERE id = :id")
                ->execute([':id' => $semesterId]);

            $pdo->prepare("UPDATE students SET is_archived = 0 WHERE semester_id = :sem_id AND is_deleted = 0")
                ->execute([':sem_id' => $semesterId]);

            $pdo->commit();

            $label = $semester['sy_name'] . ' - ' . $semester['name'];
            Logger::log(currentUserId(), 'RESTORE_SEMESTER', 'semesters', $semesterId, "Restored semester and its students: {$label}");
            flash('success', "Semester {$label} and its students were restored.");
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('error', APP_DEBUG ? $e->getMessage() : 'Failed to restore semester.');
        }

        redirect('/administrator/semesters');
    }
}
