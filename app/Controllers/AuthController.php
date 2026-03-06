<?php

declare(strict_types=1);

final class AuthController
{
    public static function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $role = (string)($_SESSION['role'] ?? '');
            if ($role === 'administrator') {
                redirect('/administrator');
            }
            if ($role === 'admission') {
                redirect('/admission');
            }
            redirect('/');
        }

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

        $sql = "SELECT id, name, password, role, account_status, is_active, failed_login_attempts, locked_until, force_password_change, email_verified_at
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

        $lockedUntil = appFromDb($user['locked_until'] ?? null);
        if ($lockedUntil && appNow() < $lockedUntil) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Account is temporarily locked. Please try again later.',
            ]);
            return;
        }

        if (empty($user['email_verified_at'])) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Please verify your email before logging in.',
            ]);
            return;
        }

        if (($user['account_status'] ?? 'verified') !== 'verified') {
            $status = (string)($user['account_status'] ?? 'pending');
            $msg = $status === 'rejected'
                ? 'Your account was rejected. Please contact the administrator.'
                : 'Your account is pending approval. Please contact the administrator.';
            View::render('auth/login', [
                'title' => 'Login',
                'error' => $msg,
            ]);
            return;
        }

        if ((int)$user['is_active'] !== 1) {
            View::render('auth/login', [
                'title' => 'Login',
                'error' => 'Account is disabled. Please contact the administrator.',
            ]);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = ((int)$user['failed_login_attempts']) + 1;

            $lockedUntil = null;
            if ($attempts >= 5) {
                $lockedUntil = appNow()->modify('+10 minutes')->format('Y-m-d H:i:s');
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

        if ((int)$user['force_password_change'] === 1) {
            $_SESSION['force_password_change'] = 1;
            redirect('/force-password-change');
        }

        // Role redirects
        if ($user['role'] === 'administrator') {
            redirect('/administrator');
        }
        if ($user['role'] === 'admission') {
            redirect('/admission');
        }

        $_SESSION = [];
        session_destroy();
        View::render('auth/login', [
            'title' => 'Login',
            'error' => 'Your account role is not allowed to access this system.',
        ]);
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

    public static function showRegister(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $role = (string)($_SESSION['role'] ?? '');
            if ($role === 'administrator') {
                redirect('/administrator');
            }
            if ($role === 'admission') {
                redirect('/admission');
            }
            redirect('/');
        }

        View::render('auth/register', [
            'title' => 'Register',
            'error' => null,
            'old' => ['name' => '', 'email' => ''],
        ]);
    }

    public static function register(): void
    {
        verifyCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::render('auth/register', [
                'title' => 'Register',
                'error' => 'Please enter a valid name and email.',
                'old' => compact('name', 'email'),
            ]);
            return;
        }

        if ($password === '' || strlen($password) < 8) {
            View::render('auth/register', [
                'title' => 'Register',
                'error' => 'Password must be at least 8 characters.',
                'old' => compact('name', 'email'),
            ]);
            return;
        }

        if ($password !== $confirm) {
            View::render('auth/register', [
                'title' => 'Register',
                'error' => 'Passwords do not match.',
                'old' => compact('name', 'email'),
            ]);
            return;
        }

        $pdo = Database::pdo();
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            View::render('auth/register', [
                'title' => 'Register',
                'error' => 'Email is already in use.',
                'old' => compact('name', 'email'),
            ]);
            return;
        }

        $pdo->beginTransaction();
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $ins = "INSERT INTO users (name, email, password, role, account_status, is_active, force_password_change)
                    VALUES (:name, :email, :password, 'admission', 'pending', 0, 0)";
            $pdo->prepare($ins)->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hash,
            ]);

            $userId = (int)$pdo->lastInsertId();
            $code = EmailVerificationService::generateCode();
            $codeHash = EmailVerificationService::hash($code);
            $expiresAt = appNow()->modify('+' . EMAIL_VERIFICATION_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s');

            $pdo->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at)
                           VALUES (:user_id, :code_hash, :expires_at)")
                ->execute([
                    ':user_id' => $userId,
                    ':code_hash' => $codeHash,
                    ':expires_at' => $expiresAt,
                ]);

            $pdo->commit();

            $subject = APP_NAME . ' - Verify Your Email';
            $html = "
                <p>Hello " . e($name) . ",</p>
                <p>Your verification code is:</p>
                <p><strong>" . e($code) . "</strong></p>
                <p>This code expires in " . (int)EMAIL_VERIFICATION_TTL_MINUTES . " minutes.</p>
                ";

            $sent = Mailer::send($email, $name, $subject, $html);
            Logger::log($userId, 'REGISTER', 'users', $userId, $sent ? 'Verification code emailed' : 'Verification email NOT sent (dev fallback)');

            if ($sent) {
                flash('success', 'Registration successful. We sent a verification code to your email.');
            } else {
                flash('success', "Registration successful. Verification code: {$code} (DEV fallback: email not sent).");
            }

            redirect('/verify-email?email=' . urlencode($email));
        } catch (Throwable $e) {
            $pdo->rollBack();
            View::render('auth/register', [
                'title' => 'Register',
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to register.',
                'old' => compact('name', 'email'),
            ]);
        }
    }

    public static function showVerifyEmail(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $role = (string)($_SESSION['role'] ?? '');
            if ($role === 'administrator') {
                redirect('/administrator');
            }
            if ($role === 'admission') {
                redirect('/admission');
            }
            redirect('/');
        }

        $email = trim((string)($_GET['email'] ?? ''));
        View::render('auth/verify_email', [
            'title' => 'Verify Email',
            'email' => $email,
            'error' => null,
        ]);
    }

    public static function verifyEmail(): void
    {
        verifyCsrfOrFail();

        $email = trim((string)($_POST['email'] ?? ''));
        $code = trim((string)($_POST['code'] ?? ''));

        if ($email === '' || $code === '') {
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => 'Please enter your email and code.',
            ]);
            return;
        }

        $st = Database::pdo()->prepare("SELECT id, email_verified_at FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
        $st->execute([':email' => $email]);
        $user = $st->fetch();
        if (!$user) {
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => 'Invalid email or code.',
            ]);
            return;
        }

        if (!empty($user['email_verified_at'])) {
            flash('success', 'Email already verified. You can log in after admin approval.');
            redirect('/login');
        }

        $hash = EmailVerificationService::hash($code);
        $sql = "SELECT id, expires_at, used_at
                FROM email_verifications
                WHERE user_id = :user_id AND code_hash = :code_hash
                ORDER BY id DESC
                LIMIT 1";
        $st = Database::pdo()->prepare($sql);
        $st->execute([
            ':user_id' => (int)$user['id'],
            ':code_hash' => $hash,
        ]);
        $row = $st->fetch();

        $expiresAt = appFromDb($row['expires_at'] ?? null);
        if (!$row || !empty($row['used_at']) || ($expiresAt && appNow() > $expiresAt)) {
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => 'Invalid or expired verification code.',
            ]);
            return;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = :id")
                ->execute([':id' => (int)$user['id']]);

            $pdo->prepare("UPDATE email_verifications SET used_at = NOW() WHERE id = :id")
                ->execute([':id' => (int)$row['id']]);

            $pdo->commit();

            Logger::log((int)$user['id'], 'VERIFY_EMAIL', 'users', (int)$user['id'], 'Email verified');

            flash('success', 'Email verified. Your account is pending for admin approval.');
            redirect('/login');
        } catch (Throwable $e) {
            $pdo->rollBack();
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to verify email.',
            ]);
        }
    }

    public static function resendVerifyEmail(): void
    {
        verifyCsrfOrFail();

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => 'Please enter a valid email.',
            ]);
            return;
        }

        $st = Database::pdo()->prepare("SELECT id, name, email_verified_at FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
        $st->execute([':email' => $email]);
        $user = $st->fetch();
        if (!$user) {
            View::render('auth/verify_email', [
                'title' => 'Verify Email',
                'email' => $email,
                'error' => 'Email not found.',
            ]);
            return;
        }

        if (!empty($user['email_verified_at'])) {
            flash('success', 'Email already verified. You can log in after admin approval.');
            redirect('/login');
        }

        $recent = Database::pdo()->prepare("SELECT created_at
                                            FROM email_verifications
                                            WHERE user_id = :user_id
                                            ORDER BY id DESC
                                            LIMIT 1");
        $recent->execute([':user_id' => (int)$user['id']]);
        $last = $recent->fetch();
        if ($last && !empty($last['created_at'])) {
            $lastAt = appFromDb((string)$last['created_at']);
            if ($lastAt) {
                $nextAllowed = $lastAt->modify('+' . EMAIL_VERIFICATION_RESEND_SECONDS . ' seconds');
                $now = appNow();
                if ($now < $nextAllowed) {
                    $remaining = $nextAllowed->getTimestamp() - $now->getTimestamp();
                $maxWait = EMAIL_VERIFICATION_RESEND_SECONDS;
                if ($remaining > $maxWait) {
                    $remaining = $maxWait; // guard against clock skew or bad timestamps
                }
                if ($remaining < 0) {
                    $remaining = 0;
                }
                $minutes = intdiv($remaining, 60);
                $seconds = $remaining % 60;
                $minLabel = $minutes === 1 ? 'minute' : 'minutes';
                $secLabel = $seconds === 1 ? 'second' : 'seconds';
                $waitText = $minutes > 0
                    ? "{$minutes} {$minLabel} {$seconds} {$secLabel}"
                    : "{$seconds} {$secLabel}";
                View::render('auth/verify_email', [
                    'title' => 'Verify Email',
                    'email' => $email,
                    'error' => 'Please wait ' . $waitText . ' before requesting another code.',
                ]);
                return;
                }
            }
        }

        $code = EmailVerificationService::generateCode();
        $codeHash = EmailVerificationService::hash($code);
        $expiresAt = appNow()->modify('+' . EMAIL_VERIFICATION_TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s');

        Database::pdo()->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at)
                                  VALUES (:user_id, :code_hash, :expires_at)")
            ->execute([
                ':user_id' => (int)$user['id'],
                ':code_hash' => $codeHash,
                ':expires_at' => $expiresAt,
            ]);

        $subject = APP_NAME . ' - Verify Your Email';
        $html = "
            <p>Hello " . e((string)$user['name']) . ",</p>
            <p>Your verification code is:</p>
            <p><strong>" . e($code) . "</strong></p>
            <p>This code expires in " . (int)EMAIL_VERIFICATION_TTL_MINUTES . " minutes.</p>
            ";

        $sent = Mailer::send((string)$email, (string)$user['name'], $subject, $html);
        Logger::log((int)$user['id'], 'RESEND_VERIFY_EMAIL', 'users', (int)$user['id'], $sent ? 'Verification code re-sent' : 'Verification email NOT sent (dev fallback)');

        if ($sent) {
            flash('success', 'A new verification code was sent to your email.');
        } else {
            flash('success', "New verification code: {$code} (DEV fallback: email not sent).");
        }

        redirect('/verify-email?email=' . urlencode($email));
    }

    public static function showForcePasswordChange(): void
    {
        AuthMiddleware::requireLogin();
        View::render('auth/force_password_change', [
            'title' => 'Change Password',
            'error' => null,
        ]);
    }

    public static function forcePasswordChange(): void
    {
        AuthMiddleware::requireLogin();
        verifyCsrfOrFail();

        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        if ($password === '' || strlen($password) < 8) {
            View::render('auth/force_password_change', [
                'title' => 'Change Password',
                'error' => 'Password must be at least 8 characters.',
            ]);
            return;
        }

        if ($password !== $confirm) {
            View::render('auth/force_password_change', [
                'title' => 'Change Password',
                'error' => 'Passwords do not match.',
            ]);
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::pdo()->prepare("UPDATE users SET password = :password, force_password_change = 0 WHERE id = :id AND is_deleted = 0")
            ->execute([
                ':password' => $hash,
                ':id' => $userId,
            ]);

        unset($_SESSION['force_password_change']);
        Logger::log($userId, 'FORCE_PASSWORD_CHANGE', 'users', $userId, 'User changed temporary password');

        $role = (string)($_SESSION['role'] ?? '');
        if ($role === 'administrator') {
            redirect('/administrator');
        }
        if ($role === 'admission') {
            redirect('/admission');
        }

        redirect('/login');
    }

    public static function showForgotPassword(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $role = (string)($_SESSION['role'] ?? '');
            if ($role === 'administrator') {
                redirect('/administrator');
            }
            if ($role === 'admission') {
                redirect('/admission');
            }
            redirect('/');
        }

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
        $expiresAt = appNow()->modify('+30 minutes')->format('Y-m-d H:i:s');

        $ins = "INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)";
        $st = Database::pdo()->prepare($ins);
        $st->execute([
            ':user_id' => (int)$user['id'],
            ':token_hash' => $hash,
            ':expires_at' => $expiresAt,
        ]);

        $link = rtrim(APP_URL, '/') . BASE_PATH . '/reset-password?token=' . urlencode($raw);

        $subject = APP_NAME . ' - Password Reset';
        $html = "
            <p>Hello " . e((string)$user['name']) . ",</p>
            <p>Click the link to reset your password (expires in 30 minutes):</p>
            <p><a href=\"" . e($link) . "\">Reset Password</a></p>
            <p>If you didn't request this, ignore this email.</p>
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
        if (!empty($_SESSION['user_id'])) {
            $role = (string)($_SESSION['role'] ?? '');
            if ($role === 'administrator') {
                redirect('/administrator');
            }
            if ($role === 'admission') {
                redirect('/admission');
            }
            redirect('/');
        }

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

        $expiresAt = appFromDb($row['expires_at'] ?? null);
        if (!empty($row['used_at']) || ($expiresAt && appNow() > $expiresAt)) {
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
