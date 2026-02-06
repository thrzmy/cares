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
        View::render('admin/scores', ['title' => 'Encode Test Results']);
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('administrator');
        View::render('admin/results', ['title' => 'Course Recommendations']);
    }

    public static function matrix(): void
    {
        RoleMiddleware::requireRole('administrator');

        $courses = WeightsService::getCourses();
        $parts = WeightsService::getExamParts();
        $weightsMap = WeightsService::getWeightsMap();

        View::render('admin/matrix', [
            'title' => 'Matrix Management',
            'courses' => $courses,
            'parts' => $parts,
            'weightsMap' => $weightsMap,
            'success' => null,
            'error' => null,
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

        try {
            WeightsService::saveMatrix($matrix, $userId);
            Logger::log($userId, 'UPDATED_WEIGHTS', 'weights', null, 'Updated weights matrix');

            flash('success', 'Matrix saved successfully.');
            redirect('/administrator/matrix');
        } catch (Throwable $e) {
            $courses = WeightsService::getCourses();
            $parts = WeightsService::getExamParts();
            $weightsMap = WeightsService::getWeightsMap();

            View::render('admin/matrix', [
                'title' => 'Matrix Management',
                'courses' => $courses,
                'parts' => $parts,
                'weightsMap' => $weightsMap,
                'success' => null,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to save matrix.',
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
