<?php
declare(strict_types=1);

final class AccountsController
{
    public static function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

        $where = "WHERE u.is_deleted = 0";
        $params = [];

        if ($q !== '') {
            $where .= " AND (name LIKE :q OR email LIKE :q OR id_number LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        if (in_array($role, ['administrator', 'admission'], true)) {
            $where .= " AND role = :role";
            $params[':role'] = $role;
        }

        $where .= " AND u.role IN ('administrator', 'admission')";

        if (in_array($status, ['pending', 'verified', 'rejected'], true)) {
            $where .= " AND account_status = :status";
            $params[':status'] = $status;
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
                       u.account_status,
                       u.rejection_reason,
                       u.is_active,
                       u.created_at,
                       u.verified_at,
                       u.rejected_at,
                       v.name AS verified_by_name,
                       r.name AS rejected_by_name
                FROM users u
                LEFT JOIN users v ON v.id = u.verified_by
                LEFT JOIN users r ON r.id = u.rejected_by
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

        View::render('admin/accounts/index', [
            'title' => 'Accounts',
            'users' => $users,
            'q' => $q,
            'roleFilter' => $role,
            'statusFilter' => $status,
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
                ],
            ],
        ]);
    }

    public static function students(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 5;

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

        View::render('admin/students', [
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
                'basePath' => '/administrator/students',
                'query' => [
                    'q' => $q,
                    'status' => $status,
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
            'student' => ['name' => '', 'email' => '', 'id_number' => '', 'status' => 'pending'],
            'error' => null,
        ]);
    }

    public static function storeStudent(): void
    {
        verifyCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idNumber = trim((string)($_POST['id_number'] ?? ''));
        $status = (string)($_POST['status'] ?? 'pending');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderStudentForm('create', 'Please enter a valid name and email.', [
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
            self::renderStudentForm('create', 'ID number is required for admitted students.', [
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
            self::renderStudentForm('create', 'Email or ID number is already in use.', [
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
        redirect('/administrator/students');
    }

    public static function editStudent(): void
    {
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
            'action' => '/administrator/students/edit',
            'student' => $student,
            'error' => null,
        ]);
    }

    public static function updateStudent(): void
    {
        verifyCsrfOrFail();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idNumber = trim((string)($_POST['id_number'] ?? ''));
        $status = (string)($_POST['status'] ?? 'pending');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::renderStudentForm('edit', 'Please enter a valid name and email.', [
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
            self::renderStudentForm('edit', 'ID number is required for admitted students.', [
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
            self::renderStudentForm('edit', 'Email or ID number is already in use.', [
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
            'error' => $error,
        ]);
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
}
