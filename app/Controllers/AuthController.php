<?php

declare(strict_types=1);

final class AuthController
{
    public static function showLogin(): void
    {
        View::render('auth/login', [
            'title' => 'Login',
            'error' => null,
        ]);
    }

    public static function login(): void
    {
        verifyCsrfOrFail();
        
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Please enter your email and password.',
            ]);
            return;
        }

        $sql = "SELECT id, name, password, role, is_active, failed_login_attempts, locked_until
                FROM users
                WHERE email = :email AND is_deleted = 0
                LIMIT 1
                ";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Invalid email or password.',
            ]);
            return;
        }

        if (!empty($user['locked_until']) && new DateTime() < new DateTime($user['locked_until'])) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Account is temporarily locked. Please try again later.',
            ]);
            return;
        }

        if ((int)$user['is_active'] !== 1) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Account is disabled. Please contact the admin.',
            ]);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = ((int)$user['failed_login_attempts']) + 1;

            $lockedUntil = null;
            if ($attempts >= 5) {
                $lockedUntil = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
            }

            $upd = "UPDATE users
                SET failed_login_attempts = :attempts,
                    locked_until = :locked_until,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
            Database::pdo()->prepare($upd)->execute([
                ':attempts' => $attempts,
                ':locked_until' => $lockedUntil,
                ':id' => (int)$user['id'],
            ]);

            $errorMsg = $lockedUntil
                ? 'Too many failed attempts. Account locked for 10 minutes.'
                : 'Invalid email or password.';

            View::render('auth/login', [
                'title' => 'Login',
                'error' => $errorMsg,
            ]);
            return;
        }

        
        $reset = "UPDATE users
                SET failed_login_attempts = 0,
                    locked_until = NULL,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        Database::pdo()->prepare($reset)->execute([':id' => (int)$user['id']]);

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = (string)$user['name'];
        $_SESSION['role'] = (string)$user['role'];

        Logger::log((int)$user['id'], 'LOGIN', 'users', (int)$user['id'], 'User logged in');

        // Role redirects (your current rule)
        if ($user['role'] === 'admin') {
            redirect('/admin');
        }
        redirect('/guidance');
    }

    public static function logout(): void
    {
        $uid = currentUserId();
        if ($uid) {
            Logger::log($uid, 'LOGOUT', 'users', $uid, 'User logged out');
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        redirect('/login');
    }

    public static function showForgotPassword(): void
    {
        View::render('auth/forgot_password', [
            'title' => 'Forgot Password',
            'error' => null,
        ]);
    }

    public static function forgotPassword(): void
    {
        verifyCsrfOrFail();

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email === '') {
            View::render('auth/forgot_password', [
                'title' => 'Forgot Password',
                'error' => 'Please enter your email.',
            ]);
            return;
        }

        // Don't reveal if email exists (good practice)
        $genericMsg = 'If that email exists, we sent a password reset link.';

        $sql = "SELECT id, name, email
            FROM users
            WHERE email = :email AND is_deleted = 0
            LIMIT 1";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            flash('success', $genericMsg);
            redirect('/forgot-password');
        }

        [$raw, $hash] = TokenService::generate();
        $expiresAt = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

        $ins = "INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)";
        $st = Database::pdo()->prepare($ins);
        $st->execute([
            ':user_id' => (int)$user['id'],
            ':token_hash' => $hash,
            ':expires_at' => $expiresAt,
        ]);

        $link = APP_URL . '/reset-password?token=' . urlencode($raw);

        $subject = APP_NAME . ' - Password Reset';
        $html = "
            <p>Hello " . e((string)$user['name']) . ",</p>
            <p>Click the link to reset your password (expires in 30 minutes):</p>
            <p><a href=\"" . e($link) . "\">Reset Password</a></p>
            <p>If you didn’t request this, ignore this email.</p>
            ";

        $sent = Mailer::send((string)$user['email'], (string)$user['name'], $subject, $html);

        Logger::log((int)$user['id'], 'REQUEST_PASSWORD_RESET', 'users', (int)$user['id'], $sent ? 'Email sent' : 'Email NOT sent (dev fallback)');

        // Dev fallback: if no API key, show the link in flash so you can test flow
        if (!$sent) {
            flash('success', $genericMsg . ' (DEV LINK: ' . $link . ')');
        } else {
            flash('success', $genericMsg);
        }

        redirect('/forgot-password');
    }

    public static function showResetPassword(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token === '') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        View::render('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
            'error' => null,
        ]);
    }

    public static function resetPassword(): void
    {
        verifyCsrfOrFail();

        $token = trim((string)($_POST['token'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        if ($token === '') {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        if ($password === '' || strlen($password) < 8) {
            View::render('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Password must be at least 8 characters.',
            ]);
            return;
        }

        if ($password !== $confirm) {
            View::render('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Passwords do not match.',
            ]);
            return;
        }

        $hash = TokenService::hash($token);

        $sql = "SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at
            FROM password_resets pr
            WHERE pr.token_hash = :token_hash
            LIMIT 1";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([':token_hash' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            View::render('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Invalid or expired reset link.',
            ]);
            return;
        }

        if (!empty($row['used_at']) || (new DateTime() > new DateTime($row['expires_at']))) {
            View::render('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => 'Invalid or expired reset link.',
            ]);
            return;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);

            $u = "UPDATE users
              SET password = :password,
                  failed_login_attempts = 0,
                  locked_until = NULL,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id AND is_deleted = 0";
            $st1 = $pdo->prepare($u);
            $st1->execute([
                ':password' => $newHash,
                ':id' => (int)$row['user_id'],
            ]);

            $m = "UPDATE password_resets
              SET used_at = NOW()
              WHERE id = :id";
            $st2 = $pdo->prepare($m);
            $st2->execute([':id' => (int)$row['id']]);

            $pdo->commit();

            Logger::log((int)$row['user_id'], 'RESET_PASSWORD', 'users', (int)$row['user_id'], 'Password reset successful');

            flash('success', 'Password updated. You can now log in.');
            redirect('/login');
        } catch (Throwable $e) {
            $pdo->rollBack();
            View::render('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to reset password.',
            ]);
        }
    }
}
