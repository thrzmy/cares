<?php
declare(strict_types=1);

// TokenService smoke tests.
// Run: php scripts/tests/services/token_service_test.php

require_once __DIR__ . '/../_bootstrap.php';

[$rawToken, $hashToken] = TokenService::generate();
expect(strlen($rawToken) === 64, 'TokenService generates 64-char raw token');
expect(strlen($hashToken) === 64, 'TokenService generates 64-char token hash');
expect(TokenService::hash($rawToken) === $hashToken, 'TokenService hash matches generated hash');

echo "TokenService tests passed.\n";
