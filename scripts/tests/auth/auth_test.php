<?php
declare(strict_types=1);

// Authentication-focused smoke tests.
// Run: php scripts/tests/auth/auth_test.php

require_once __DIR__ . '/../_bootstrap.php';

function simulateLogin(PDO $pdo, string $email, string $password): string
{
    $sql = "SELECT id, password, role, account_status, is_active, failed_login_attempts, locked_until, email_verified_at, force_password_change
            FROM users
            WHERE email = :email AND is_deleted = 0
            LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->execute([':email' => $email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return 'invalid';
    }

    $lockedUntil = appFromDb($user['locked_until'] ?? null);
    if ($lockedUntil && appNow() < $lockedUntil) {
        return 'locked';
    }

    if (empty($user['email_verified_at'])) {
        return 'email';
    }

    if (($user['account_status'] ?? 'verified') !== 'verified') {
        return 'status';
    }

    if ((int)$user['is_active'] !== 1) {
        return 'inactive';
    }

    if (!password_verify($password, (string)$user['password'])) {
        $attempts = ((int)$user['failed_login_attempts']) + 1;
        $nextLockedUntil = null;
        if ($attempts >= 5) {
            $nextLockedUntil = appNow()->modify('+10 minutes')->format('Y-m-d H:i:s');
        }

        $upd = "UPDATE users
                SET failed_login_attempts = :attempts,
                    locked_until = :locked_until,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        $pdo->prepare($upd)->execute([
            ':attempts' => $attempts,
            ':locked_until' => $nextLockedUntil,
            ':id' => (int)$user['id'],
        ]);

        return $nextLockedUntil ? 'locked' : 'invalid';
    }

    $reset = "UPDATE users
              SET failed_login_attempts = 0,
                  locked_until = NULL,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    $pdo->prepare($reset)->execute([':id' => (int)$user['id']]);

    if ((int)$user['force_password_change'] === 1) {
        return 'force';
    }

    return 'ok';
}

function simulateForcePasswordChange(PDO $pdo, string $email, string $newPassword): string
{
    $st = $pdo->prepare("SELECT id FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
    $st->execute([':email' => $email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return 'invalid';
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = :password, force_password_change = 0 WHERE id = :id AND is_deleted = 0")
        ->execute([
            ':password' => $hash,
            ':id' => (int)$user['id'],
        ]);

    return 'ok';
}

function simulateVerifyEmail(PDO $pdo, string $email, string $code): string
{
    $st = $pdo->prepare("SELECT id, email_verified_at FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
    $st->execute([':email' => $email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return 'invalid';
    }

    if (!empty($user['email_verified_at'])) {
        return 'already';
    }

    $hash = EmailVerificationService::hash($code);
    $sql = "SELECT id, expires_at, used_at
            FROM email_verifications
            WHERE user_id = :user_id AND code_hash = :code_hash
            ORDER BY id DESC
            LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->execute([
        ':user_id' => (int)$user['id'],
        ':code_hash' => $hash,
    ]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    $expiresAt = appFromDb($row['expires_at'] ?? null);
    if (!$row || !empty($row['used_at']) || ($expiresAt && appNow() > $expiresAt)) {
        return 'expired';
    }

    $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = :id")
        ->execute([':id' => (int)$user['id']]);
    $pdo->prepare("UPDATE email_verifications SET used_at = NOW() WHERE id = :id")
        ->execute([':id' => (int)$row['id']]);

    return 'ok';
}

function simulateResendAllowed(PDO $pdo, string $email): array
{
    $st = $pdo->prepare("SELECT id, email_verified_at FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1");
    $st->execute([':email' => $email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return ['status' => 'invalid', 'remaining' => null];
    }
    if (!empty($user['email_verified_at'])) {
        return ['status' => 'already', 'remaining' => null];
    }

    $recent = $pdo->prepare("SELECT created_at FROM email_verifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
    $recent->execute([':user_id' => (int)$user['id']]);
    $last = $recent->fetch(PDO::FETCH_ASSOC);
    if ($last && !empty($last['created_at'])) {
        $lastAt = appFromDb((string)$last['created_at']);
        if ($lastAt) {
            $nextAllowed = $lastAt->modify('+' . EMAIL_VERIFICATION_RESEND_SECONDS . ' seconds');
            $now = appNow();
            if ($now < $nextAllowed) {
                $remaining = $nextAllowed->getTimestamp() - $now->getTimestamp();
                if ($remaining < 0) {
                    $remaining = 0;
                }
                if ($remaining > EMAIL_VERIFICATION_RESEND_SECONDS) {
                    $remaining = EMAIL_VERIFICATION_RESEND_SECONDS;
                }
                return ['status' => 'wait', 'remaining' => $remaining];
            }
        }
    }

    return ['status' => 'ok', 'remaining' => null];
}

$pdo = getPdo();

$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM users WHERE email LIKE 'auth_%@test.local'");

    $password = 'AuthTest@1234';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, account_status, is_active, force_password_change, failed_login_attempts, locked_until, email_verified_at)
                             VALUES (:name, :email, :password, :role, :status, :active, :force_password_change, 0, NULL, :email_verified_at)");

    $cases = [
        ['name' => 'Auth Unverified', 'email' => 'auth_unverified@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => null, 'force' => 0],
        ['name' => 'Auth Pending', 'email' => 'auth_pending@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 0],
        ['name' => 'Auth Inactive', 'email' => 'auth_inactive@test.local', 'status' => 'verified', 'active' => 0, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 0],
        ['name' => 'Auth Locked', 'email' => 'auth_locked@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 0],
        ['name' => 'Auth Wrong', 'email' => 'auth_wrong@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 0],
        ['name' => 'Auth Ok', 'email' => 'auth_ok@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 0],
        ['name' => 'Auth Force', 'email' => 'auth_force@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s'), 'force' => 1],
        ['name' => 'Auth Verify', 'email' => 'auth_verify@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => null, 'force' => 0],
        ['name' => 'Auth Resend', 'email' => 'auth_resend@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => null, 'force' => 0],
    ];

    foreach ($cases as $c) {
        $insert->execute([
            ':name' => $c['name'],
            ':email' => $c['email'],
            ':password' => $hash,
            ':role' => 'admission',
            ':status' => $c['status'],
            ':active' => $c['active'],
            ':email_verified_at' => $c['email_verified_at'],
            ':force_password_change' => $c['force'],
        ]);
    }

    $pdo->prepare("UPDATE users SET locked_until = :lock WHERE email = :email")
        ->execute([
            ':lock' => appNow()->modify('+10 minutes')->format('Y-m-d H:i:s'),
            ':email' => 'auth_locked@test.local',
        ]);

    expect(simulateLogin($pdo, 'auth_unverified@test.local', $password) === 'email', 'Unverified email is blocked');
    expect(simulateLogin($pdo, 'auth_pending@test.local', $password) === 'status', 'Pending account is blocked');
    expect(simulateLogin($pdo, 'auth_inactive@test.local', $password) === 'inactive', 'Inactive account is blocked');
    expect(simulateLogin($pdo, 'auth_locked@test.local', $password) === 'locked', 'Locked account is blocked');
    expect(simulateLogin($pdo, 'auth_force@test.local', $password) === 'force', 'Force password change is required');
    expect(simulateForcePasswordChange($pdo, 'auth_force@test.local', 'NewPass@1234') === 'ok', 'Force password change updates password');
    expect(simulateLogin($pdo, 'auth_force@test.local', 'NewPass@1234') === 'ok', 'Login succeeds after forced password change');

    for ($i = 0; $i < 5; $i++) {
        simulateLogin($pdo, 'auth_wrong@test.local', 'WrongPass@1234');
    }
    $row = $pdo->query("SELECT failed_login_attempts, locked_until FROM users WHERE email = 'auth_wrong@test.local' LIMIT 1")
        ->fetch(PDO::FETCH_ASSOC);
    expect((int)$row['failed_login_attempts'] >= 5, 'Failed attempts increment');
    expect(!empty($row['locked_until']), 'Account locks after 5 failed attempts');

    expect(simulateLogin($pdo, 'auth_ok@test.local', $password) === 'ok', 'Verified active account can login');
    $rowOk = $pdo->query("SELECT failed_login_attempts, locked_until FROM users WHERE email = 'auth_ok@test.local' LIMIT 1")
        ->fetch(PDO::FETCH_ASSOC);
    expect((int)$rowOk['failed_login_attempts'] === 0, 'Successful login resets failed attempts');
    expect(empty($rowOk['locked_until']), 'Successful login clears lock');

    // Email verification flow
    $verifyCode = '123456';
    $pdo->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at, created_at)
                   SELECT id, :hash, :expires_at, :created_at FROM users WHERE email = :email LIMIT 1")
        ->execute([
            ':hash' => EmailVerificationService::hash($verifyCode),
            ':expires_at' => appNow()->modify('+15 minutes')->format('Y-m-d H:i:s'),
            ':created_at' => appNow()->format('Y-m-d H:i:s'),
            ':email' => 'auth_verify@test.local',
        ]);

    expect(simulateVerifyEmail($pdo, 'auth_verify@test.local', $verifyCode) === 'ok', 'Email verification succeeds with valid code');
    $verifiedAt = $pdo->query("SELECT email_verified_at FROM users WHERE email = 'auth_verify@test.local' LIMIT 1")
        ->fetchColumn();
    expect(!empty($verifiedAt), 'Email verification sets email_verified_at');
    expect(simulateVerifyEmail($pdo, 'auth_verify@test.local', $verifyCode) === 'already', 'Already verified email is handled');

    $expiredCode = '654321';
    $pdo->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at, created_at)
                   SELECT id, :hash, :expires_at, :created_at FROM users WHERE email = :email LIMIT 1")
        ->execute([
            ':hash' => EmailVerificationService::hash($expiredCode),
            ':expires_at' => appNow()->modify('-5 minutes')->format('Y-m-d H:i:s'),
            ':created_at' => appNow()->modify('-6 minutes')->format('Y-m-d H:i:s'),
            ':email' => 'auth_resend@test.local',
        ]);
    expect(simulateVerifyEmail($pdo, 'auth_resend@test.local', $expiredCode) === 'expired', 'Expired verification code is rejected');

    // Resend rate limit
    $pdo->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at, created_at)
                   SELECT id, :hash, :expires_at, :created_at FROM users WHERE email = :email LIMIT 1")
        ->execute([
            ':hash' => EmailVerificationService::hash('111111'),
            ':expires_at' => appNow()->modify('+15 minutes')->format('Y-m-d H:i:s'),
            ':created_at' => appNow()->format('Y-m-d H:i:s'),
            ':email' => 'auth_resend@test.local',
        ]);
    $resend = simulateResendAllowed($pdo, 'auth_resend@test.local');
    expect($resend['status'] === 'wait', 'Resend is rate-limited when recently sent');
    $pdo->prepare("INSERT INTO email_verifications (user_id, code_hash, expires_at, created_at)
                   SELECT id, :hash, :expires_at, :created_at FROM users WHERE email = :email LIMIT 1")
        ->execute([
            ':hash' => EmailVerificationService::hash('222222'),
            ':expires_at' => appNow()->modify('+15 minutes')->format('Y-m-d H:i:s'),
            ':created_at' => appNow()->modify('-2 minutes')->format('Y-m-d H:i:s'),
            ':email' => 'auth_resend@test.local',
        ]);
    $resend = simulateResendAllowed($pdo, 'auth_resend@test.local');
    expect($resend['status'] === 'ok', 'Resend is allowed after cooldown');

    $pdo->rollBack();
} catch (Throwable $e) {
    $pdo->rollBack();
    fail('Auth tests failed: ' . $e->getMessage());
}

echo "Auth tests passed.\n";
