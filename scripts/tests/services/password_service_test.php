<?php
declare(strict_types=1);

// PasswordService smoke tests.
// Run: php scripts/tests/services/password_service_test.php

require_once __DIR__ . '/../_bootstrap.php';

$password = PasswordService::generateTempPassword(12);
expect(strlen($password) >= 12, 'PasswordService returns requested password length');
expect((bool)preg_match('/[a-z]/', $password), 'PasswordService includes lowercase chars');
expect((bool)preg_match('/[A-Z]/', $password), 'PasswordService includes uppercase chars');
expect((bool)preg_match('/[0-9]/', $password), 'PasswordService includes digits');
expect((bool)preg_match('/[!@#$%&*?]/', $password), 'PasswordService includes symbols');

$minPassword = PasswordService::generateTempPassword(4);
expect(strlen($minPassword) >= 8, 'PasswordService enforces minimum length');

echo "PasswordService tests passed.\n";
