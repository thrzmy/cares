<?php
declare(strict_types=1);

// EmailVerificationService smoke tests.
// Run: php scripts/tests/services/email_verification_service_test.php

require_once __DIR__ . '/../_bootstrap.php';

$code = EmailVerificationService::generateCode();
expect((bool)preg_match('/^\d{6}$/', $code), 'EmailVerificationService generates 6-digit code');

$hash = EmailVerificationService::hash($code);
expect(strlen($hash) === 64, 'EmailVerificationService hash is SHA-256 length');
expect($hash === hash('sha256', $code), 'EmailVerificationService hash matches SHA-256');

echo "EmailVerificationService tests passed.\n";
