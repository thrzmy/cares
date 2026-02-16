<?php
declare(strict_types=1);

// Logger smoke tests.
// Run: php scripts/tests/services/logger_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

$userEmail = 'module_logger@test.local';
$studentEmail = 'module_logger@student.local';

$pdo->exec("DELETE FROM users WHERE email = '{$userEmail}'");
$pdo->exec("DELETE FROM students WHERE email = '{$studentEmail}'");
$pdo->exec("DELETE FROM logs WHERE action = 'MODULE_LOGGER_TEST'");

$hash = password_hash('LoggerPass@123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, role, account_status, email_verified_at, is_active, force_password_change)
               VALUES ('Module Logger User', :email, :password, 'admission', 'verified', NOW(), 1, 0)")
    ->execute([
        ':email' => $userEmail,
        ':password' => $hash,
    ]);
$userId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO students (id_number, name, email, status, created_by)
               VALUES (NULL, 'Module Logger Student', :email, 'pending', :created_by)")
    ->execute([
        ':email' => $studentEmail,
        ':created_by' => $userId,
    ]);
$studentId = (int)$pdo->lastInsertId();

try {
    Logger::log($userId, 'MODULE_LOGGER_TEST', 'students', $studentId, 'Logger smoke test entry');

    $countSt = $pdo->prepare("SELECT COUNT(*) FROM logs WHERE action = 'MODULE_LOGGER_TEST' AND user_id = :user_id");
    $countSt->execute([':user_id' => $userId]);
    expect((int)$countSt->fetchColumn() >= 1, 'Logger writes a log row');

    $detailSql = "SELECT
                    l.action,
                    l.details,
                    COALESCE(s.name, u.name) AS entity_name
                 FROM logs l
                 LEFT JOIN students s ON l.entity = 'students' AND s.id = l.entity_id
                 LEFT JOIN users u ON l.entity = 'users' AND u.id = l.entity_id
                 WHERE l.action = 'MODULE_LOGGER_TEST'
                 ORDER BY l.id DESC
                 LIMIT 1";
    $row = $pdo->query($detailSql)->fetch(PDO::FETCH_ASSOC);
    expect(($row['entity_name'] ?? '') === 'Module Logger Student', 'Logs query resolves student entity name');
    expect(($row['details'] ?? '') === 'Logger smoke test entry', 'Logs query keeps details text');
} finally {
    $pdo->exec("DELETE FROM logs WHERE action = 'MODULE_LOGGER_TEST'");
    $pdo->prepare("DELETE FROM students WHERE id = :id")->execute([':id' => $studentId]);
    $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $userId]);
}

echo "Logger tests passed.\n";
