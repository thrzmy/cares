<?php
declare(strict_types=1);

// Simple test runner for local scripts.
// Run: php scripts/run_tests.php

$tests = [
    __DIR__ . '/acct_mang_test.php',
    __DIR__ . '/auth_test.php',
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
