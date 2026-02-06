<?php
declare(strict_types=1);

final class AccountsController
{
    public static function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));

        $where = "WHERE is_deleted = 0";
        $params = [];

        if ($q !== '') {
            $where .= " AND (name LIKE :q OR email LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        if (in_array($role, ['admin', 'guidance'], true)) {
            $where .= " AND role = :role";
            $params[':role'] = $role;
        }

        $sql = "SELECT id, name, email, role, is_active, created_at
                FROM users
                $where
                ORDER BY created_at DESC";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $users = $st->fetchAll();

        View::render('admin/accounts/index', [
            'title' => 'Accounts',
            'users' => $users,
            'q' => $q,
            'roleFilter' => $role,
        ]);
    }

    public static function create(): void
    {
        View::render('admin/accounts/create', [
            'title' => 'Create Account',
            'error' => null,
            'old' => ['name'=>'','email'=>'','role'=>'guidance','is_active'=>1],
        ]);
    }

    public static function store(): void
    {
        verifyCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = (string)($_POST['role'] ?? 'guidance');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::render('admin/accounts/create', [
                'title' => 'Create Account',
                'error' => 'Please enter a valid name and email.',
                'old' => compact('name','email','role') + ['is_active'=>$isActive],
            ]);
            return;
        }

        if (!in_array($role, ['admin','guidance'], true)) {
            $role = 'guidance';
        }

        // temp password (simple)
        $tempPassword = 'Temp@1234';
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (name, email, password, role, is_active, force_password_change, created_by)
                    VALUES (:name, :email, :password, :role, :is_active, 1, :created_by)";
            Database::pdo()->prepare($sql)->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hash,
                ':role' => $role,
                ':is_active' => $isActive ? 1 : 0,
                ':created_by' => (int)($_SESSION['user_id'] ?? 0),
            ]);

            flash('success', "Account created. Temporary password: {$tempPassword}");
            redirect('/admin/accounts');
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
        $sql = "SELECT id, name, email, role, is_active
                FROM users
                WHERE id = :id AND is_deleted = 0
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
        $role = (string)($_POST['role'] ?? 'guidance');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderEditWithError($id, 'Please enter a valid name and email.');
            return;
        }
        if (!in_array($role, ['admin','guidance'], true)) {
            $role = 'guidance';
        }

        // prevent self demotion/disable accidents (optional)
        $selfId = (int)($_SESSION['user_id'] ?? 0);
        if ($id === $selfId && $isActive !== 1) {
            self::renderEditWithError($id, 'You cannot disable your own account.');
            return;
        }

        $sql = "UPDATE users
                SET name = :name,
                    email = :email,
                    role = :role,
                    is_active = :is_active,
                    updated_by = :updated_by
                WHERE id = :id AND is_deleted = 0";
        try {
            Database::pdo()->prepare($sql)->execute([
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':is_active' => $isActive ? 1 : 0,
                ':updated_by' => $selfId,
                ':id' => $id,
            ]);
            flash('success', 'Account updated.');
            redirect('/admin/accounts');
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
            redirect('/admin/accounts');
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
        redirect('/admin/accounts');
    }

    public static function resetPassword(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);

        $tempPassword = 'Temp@1234';
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

        flash('success', "Password reset. Temporary password: {$tempPassword}");
        redirect('/admin/accounts');
    }

    private static function renderEditWithError(int $id, string $error): void
    {
        $sql = "SELECT id, name, email, role, is_active
                FROM users
                WHERE id = :id AND is_deleted = 0
                LIMIT 1";
        $st = Database::pdo()->prepare($sql);
        $st->execute([':id' => $id]);
        $user = $st->fetch();

        View::render('admin/accounts/edit', [
            'title' => 'Edit Account',
            'error' => $error,
            'user' => $user ?: ['id'=>$id,'name'=>'','email'=>'','role'=>'guidance','is_active'=>1],
        ]);
    }
}
