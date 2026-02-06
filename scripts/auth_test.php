<?php
declare(strict_types=1);

// Authentication-focused smoke tests.
// Run: php scripts/auth_test.php

require_once __DIR__ . '/../core/bootstrap.php';

function ok(string $msg): void
{
    echo "[OK] {$msg}\n";
}

function fail(string $msg): void
{
    echo "[FAIL] {$msg}\n";
    exit(1);
}

function expect(bool $condition, string $msg): void
{
    if ($condition) {
        ok($msg);
        return;
    }
    fail($msg);
}

function simulateLogin(PDO $pdo, string $email, string $password): string
{
    $sql = "SELECT id, password, role, account_status, is_active, failed_login_attempts, locked_until, email_verified_at
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
        $lockedUntil = null;
        if ($attempts >= 5) {
            $lockedUntil = appNow()->modify('+10 minutes')->format('Y-m-d H:i:s');
        }

        $upd = "UPDATE users
                SET failed_login_attempts = :attempts,
                    locked_until = :locked_until,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        $pdo->prepare($upd)->execute([
            ':attempts' => $attempts,
            ':locked_until' => $lockedUntil,
            ':id' => (int)$user['id'],
        ]);

        return $lockedUntil ? 'locked' : 'invalid';
    }

    $reset = "UPDATE users
              SET failed_login_attempts = 0,
                  locked_until = NULL,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    $pdo->prepare($reset)->execute([':id' => (int)$user['id']]);

    return 'ok';
}

try {
    $pdo = Database::pdo();
} catch (Throwable $e) {
    fail('Database connection failed: ' . $e->getMessage());
}

$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM users WHERE email LIKE 'auth_%@test.local'");

    $password = 'AuthTest@1234';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, account_status, is_active, force_password_change, failed_login_attempts, locked_until, email_verified_at)
                             VALUES (:name, :email, :password, :role, :status, :active, 1, 0, NULL, :email_verified_at)");

    $cases = [
        ['name' => 'Auth Unverified', 'email' => 'auth_unverified@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => null],
        ['name' => 'Auth Pending', 'email' => 'auth_pending@test.local', 'status' => 'pending', 'active' => 0, 'email_verified_at' => appNow()->format('Y-m-d H:i:s')],
        ['name' => 'Auth Inactive', 'email' => 'auth_inactive@test.local', 'status' => 'verified', 'active' => 0, 'email_verified_at' => appNow()->format('Y-m-d H:i:s')],
        ['name' => 'Auth Locked', 'email' => 'auth_locked@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s')],
        ['name' => 'Auth Wrong', 'email' => 'auth_wrong@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s')],
        ['name' => 'Auth Ok', 'email' => 'auth_ok@test.local', 'status' => 'verified', 'active' => 1, 'email_verified_at' => appNow()->format('Y-m-d H:i:s')],
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

    $pdo->rollBack();
} catch (Throwable $e) {
    $pdo->rollBack();
    fail('Auth tests failed: ' . $e->getMessage());
}

echo "Auth tests passed.\n";
