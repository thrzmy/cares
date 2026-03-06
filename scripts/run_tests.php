<?php
declare(strict_types=1);

// Simple test runner for local scripts.
// Run: php scripts/run_tests.php

$tests = [
    __DIR__ . '/tests/accounts/account_management_test.php',
    __DIR__ . '/tests/auth/auth_test.php',
    __DIR__ . '/tests/services/password_service_test.php',
    __DIR__ . '/tests/services/token_service_test.php',
    __DIR__ . '/tests/services/email_verification_service_test.php',
    __DIR__ . '/tests/services/mailer_test.php',
    __DIR__ . '/tests/services/weights_service_test.php',
    __DIR__ . '/tests/services/scores_recommendation_test.php',
    __DIR__ . '/tests/services/logger_test.php',
    __DIR__ . '/tests/modules/reports_test.php',
    __DIR__ . '/tests/modules/profile_password_test.php',
];

foreach ($tests as $test) {
    echo "Running " . basename($test) . "...\n";
    $cmd = PHP_BINARY . ' ' . escapeshellarg($test);
    $code = 0;
    passthru($cmd, $code);
    if ($code !== 0) {
        echo "[FAIL] " . basename($test) . " failed with code {$code}\n";
        exit($code);
    }
    echo "[OK] " . basename($test) . " passed\n\n";
}

echo "All tests passed.\n";
