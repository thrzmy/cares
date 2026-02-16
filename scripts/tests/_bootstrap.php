<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';

if (!function_exists('ok')) {
    function ok(string $msg): void
    {
        echo "[OK] {$msg}\n";
    }
}

if (!function_exists('warn')) {
    function warn(string $msg): void
    {
        echo "[WARN] {$msg}\n";
    }
}

if (!function_exists('fail')) {
    function fail(string $msg): void
    {
        echo "[FAIL] {$msg}\n";
        exit(1);
    }
}

if (!function_exists('expect')) {
    function expect(bool $condition, string $msg): void
    {
        if ($condition) {
            ok($msg);
            return;
        }
        fail($msg);
    }
}

if (!function_exists('expectThrows')) {
    function expectThrows(callable $fn, string $msg): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            ok($msg);
            return;
        }
        fail($msg);
    }
}

if (!function_exists('getPdo')) {
    function getPdo(): PDO
    {
        try {
            return Database::pdo();
        } catch (Throwable $e) {
            fail('Database connection failed: ' . $e->getMessage());
        }
    }
}
