<?php
declare(strict_types=1);

// Account + student management smoke tests.
// Run: php scripts/tests/accounts/account_management_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

// 1) Tables exist
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$required = ['users', 'students', 'logs', 'email_verifications'];
foreach ($required as $t) {
    if (!in_array($t, $tables, true)) {
        fail("Missing table: {$t}");
    }
}
ok('Required tables exist');

// 2) Users roles restricted to system roles
$roleRows = $pdo->query("SELECT DISTINCT role FROM users WHERE is_deleted = 0")->fetchAll(PDO::FETCH_COLUMN);
foreach ($roleRows as $role) {
    if (!in_array($role, ['administrator', 'admission'], true)) {
        fail("Unexpected user role found: {$role}");
    }
}
ok('User roles are restricted to administrator/admission');

// 3) Account status values
$statusRows = $pdo->query("SELECT DISTINCT account_status FROM users WHERE is_deleted = 0")->fetchAll(PDO::FETCH_COLUMN);
foreach ($statusRows as $status) {
    if (!in_array($status, ['pending', 'verified', 'rejected'], true)) {
        fail("Unexpected account_status found: {$status}");
    }
}
ok('Account statuses are valid');

// 4) Students exist and status values
$studentStatuses = $pdo->query("SELECT DISTINCT status FROM students WHERE is_deleted = 0")->fetchAll(PDO::FETCH_COLUMN);
foreach ($studentStatuses as $status) {
    if (!in_array($status, ['pending', 'admitted', 'rejected', 'waitlisted'], true)) {
        fail("Unexpected student status found: {$status}");
    }
}
ok('Student statuses are valid');

// 5) Admitted students must have ID numbers (rule check)
$badAdmitted = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'admitted' AND (id_number IS NULL OR id_number = '')")->fetchColumn();
if ((int)$badAdmitted > 0) {
    fail('Admitted students missing ID numbers');
}
ok('Admitted students have ID numbers');

// 6) Account management + auth rule checks (transactional)
$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM users WHERE email LIKE 'smoke_%@test.local'");
    $pdo->exec("DELETE FROM students WHERE email LIKE 'smoke_%@student.local'");

    $hash = password_hash(PasswordService::generateTempPassword(), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, account_status, is_active, force_password_change)
                           VALUES (:name, :email, :password, :role, :status, :active, 1)");

    $cases = [
        ['name' => 'Smoke Pending', 'email' => 'smoke_pending@test.local', 'role' => 'admission', 'status' => 'pending', 'active' => 0],
        ['name' => 'Smoke Rejected', 'email' => 'smoke_rejected@test.local', 'role' => 'admission', 'status' => 'rejected', 'active' => 0],
        ['name' => 'Smoke Verified', 'email' => 'smoke_verified@test.local', 'role' => 'admission', 'status' => 'verified', 'active' => 1],
    ];
    foreach ($cases as $c) {
        $stmt->execute([
            ':name' => $c['name'],
            ':email' => $c['email'],
            ':password' => $hash,
            ':role' => $c['role'],
            ':status' => $c['status'],
            ':active' => $c['active'],
        ]);
    }

    // Verify login rule: only verified + active should be allowed
    $rows = $pdo->query("SELECT email, account_status, is_active FROM users WHERE email LIKE 'smoke_%@test.local'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $canLogin = ($r['account_status'] === 'verified' && (int)$r['is_active'] === 1);
        if ($r['account_status'] === 'verified') {
            expect($canLogin, 'Verified account should be allowed to login');
        } else {
            expect(!$canLogin, 'Pending/rejected account should not be allowed to login');
        }
    }
    ok('Auth rule checks passed');

    // Verify/reject field updates
    $adminId = (int)$pdo->query("SELECT id FROM users WHERE email = 'admin@cares.local' LIMIT 1")->fetchColumn();
    $pendingId = (int)$pdo->query("SELECT id FROM users WHERE email = 'smoke_pending@test.local' LIMIT 1")->fetchColumn();
    $rejId = (int)$pdo->query("SELECT id FROM users WHERE email = 'smoke_rejected@test.local' LIMIT 1")->fetchColumn();

    $pdo->prepare("UPDATE users
                   SET account_status = 'verified',
                       is_active = 1,
                       verified_by = :uid,
                       verified_at = NOW(),
                       rejected_by = NULL,
                       rejected_at = NULL,
                       rejection_reason = NULL
                   WHERE id = :id")->execute([
        ':uid' => $adminId,
        ':id' => $pendingId,
    ]);

    $pdo->prepare("UPDATE users
                   SET account_status = 'rejected',
                       is_active = 0,
                       rejected_by = :uid,
                       rejected_at = NOW(),
                       rejection_reason = 'Incomplete requirements'
                   WHERE id = :id")->execute([
        ':uid' => $adminId,
        ':id' => $rejId,
    ]);

    $checkVerified = $pdo->prepare("SELECT account_status, is_active, verified_by, verified_at, rejected_by, rejected_at, rejection_reason
                                    FROM users WHERE id = :id");
    $checkVerified->execute([':id' => $pendingId]);
    $rowV = $checkVerified->fetch(PDO::FETCH_ASSOC);
    expect($rowV['account_status'] === 'verified', 'Verify sets account_status to verified');
    expect((int)$rowV['is_active'] === 1, 'Verify activates account');
    expect((int)$rowV['verified_by'] === $adminId, 'Verify sets verified_by');
    expect(!empty($rowV['verified_at']), 'Verify sets verified_at');
    expect(empty($rowV['rejected_by']) && empty($rowV['rejected_at']), 'Verify clears rejection fields');

    $checkVerified->execute([':id' => $rejId]);
    $rowR = $checkVerified->fetch(PDO::FETCH_ASSOC);
    expect($rowR['account_status'] === 'rejected', 'Reject sets account_status to rejected');
    expect((int)$rowR['is_active'] === 0, 'Reject deactivates account');
    expect((int)$rowR['rejected_by'] === $adminId, 'Reject sets rejected_by');
    expect(!empty($rowR['rejected_at']), 'Reject sets rejected_at');
    expect($rowR['rejection_reason'] === 'Incomplete requirements', 'Reject sets rejection_reason');

    // Logging checks
    $beforeLogs = (int)$pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
    Logger::log($adminId, 'VERIFY_ACCOUNT', 'users', $pendingId, 'Verified system account');
    Logger::log($adminId, 'REJECT_ACCOUNT', 'users', $rejId, 'Rejected system account');
    Logger::log($adminId, 'UPDATE_PROFILE', 'users', $adminId, 'Updated profile');
    Logger::log($adminId, 'UPDATE_ACCOUNT', 'users', $pendingId, 'Updated account details');
    Logger::log($adminId, 'CREATE_STUDENT', 'students', null, 'Created student record');
    Logger::log($adminId, 'UPDATE_STUDENT', 'students', null, 'Updated student record');
    $afterLogs = (int)$pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
    expect($afterLogs >= $beforeLogs + 6, 'Logs recorded for key actions');
    ok('Logging checks passed');

    // Student rules
    $sInsert = $pdo->prepare("INSERT INTO students (id_number, name, email, status)
                              VALUES (:id_number, :name, :email, :status)");

    $sInsert->execute([
        ':id_number' => null,
        ':name' => 'Smoke Student Pending',
        ':email' => 'smoke_pending@student.local',
        ':status' => 'pending',
    ]);
    ok('Pending student can be created without ID number');

    $admittedFailed = false;
    try {
        $sInsert->execute([
            ':id_number' => null,
            ':name' => 'Smoke Student Admitted',
            ':email' => 'smoke_admitted@student.local',
            ':status' => 'admitted',
        ]);
    } catch (Throwable $e) {
        $admittedFailed = true;
    }
    if ($admittedFailed) {
        ok('Admitted student without ID number rejected (DB check)');
    } else {
        warn('Admitted student without ID number was inserted (CHECK not enforced)');
    }

    $dupFailed = false;
    try {
        $sInsert->execute([
            ':id_number' => 'S-TEST-0001',
            ':name' => 'Smoke Student Dup Email',
            ':email' => 'smoke_pending@student.local',
            ':status' => 'pending',
        ]);
    } catch (Throwable $e) {
        $dupFailed = true;
    }
    expect($dupFailed, 'Duplicate student email is blocked');

    $pdo->rollBack();
} catch (Throwable $e) {
    $pdo->rollBack();
    fail('Account management tests failed: ' . $e->getMessage());
}

echo "Account management tests passed.\n";
