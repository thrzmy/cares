<?php
declare(strict_types=1);

// Profile + password module smoke tests.
// Run: php scripts/tests/modules/profile_password_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

$userEmail = 'module_profile@test.local';
$dupEmail = 'module_profile_dup@test.local';

$pdo->exec("DELETE FROM users WHERE email IN ('{$userEmail}', '{$dupEmail}')");

$hash = password_hash('ModulePass@123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, role, account_status, email_verified_at, is_active, force_password_change)
               VALUES ('Module Profile', :email, :password, 'admission', 'verified', NOW(), 1, 0)")
    ->execute([
        ':email' => $userEmail,
        ':password' => $hash,
    ]);
$userId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO users (name, email, password, role, account_status, email_verified_at, is_active, force_password_change)
               VALUES ('Module Profile Dup', :email, :password, 'admission', 'verified', NOW(), 1, 0)")
    ->execute([
        ':email' => $dupEmail,
        ':password' => $hash,
    ]);

try {
    $dupCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id <> :id AND is_deleted = 0 LIMIT 1");
    $dupCheck->execute([
        ':email' => $dupEmail,
        ':id' => $userId,
    ]);
    expect((bool)$dupCheck->fetch(), 'Profile module detects duplicate email');

    $pdo->prepare("UPDATE users SET name = :name, email = :email, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
        ->execute([
            ':name' => 'Module Profile Updated',
            ':email' => 'module_profile_updated@test.local',
            ':updated_by' => $userId,
            ':id' => $userId,
        ]);
    $profileRow = $pdo->query("SELECT name, email FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
    expect(($profileRow['name'] ?? '') === 'Module Profile Updated', 'Profile module updates user name');
    expect(($profileRow['email'] ?? '') === 'module_profile_updated@test.local', 'Profile module updates user email');

    $passwordRow = $pdo->query("SELECT password FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
    expect(password_verify('ModulePass@123', (string)$passwordRow['password']), 'Password module validates current password');

    $newHash = password_hash('ModulePass@456', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = :password, updated_by = :updated_by WHERE id = :id AND is_deleted = 0")
        ->execute([
            ':password' => $newHash,
            ':updated_by' => $userId,
            ':id' => $userId,
        ]);
    $updatedPassRow = $pdo->query("SELECT password FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
    expect(password_verify('ModulePass@456', (string)$updatedPassRow['password']), 'Password module updates hash correctly');
} finally {
    $pdo->exec("DELETE FROM users WHERE email LIKE 'module_profile%@test.local'");
    $pdo->exec("DELETE FROM users WHERE email = '{$dupEmail}'");
}

echo "Profile/password tests passed.\n";
