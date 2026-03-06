<?php
declare(strict_types=1);

// Mailer smoke tests.
// Run: php scripts/tests/services/mailer_test.php

require_once __DIR__ . '/../_bootstrap.php';

if (BREVO_API_KEY === '') {
    $result = Mailer::send('noreply@test.local', 'No Reply', 'Mailer Smoke', '<p>Smoke test</p>');
    expect($result === false, 'Mailer returns false when BREVO_API_KEY is empty');
    echo "Mailer tests passed.\n";
    exit(0);
}

warn('BREVO_API_KEY is set. Skipping outbound Mailer::send() smoke test to avoid external side effects.');
echo "Mailer tests skipped.\n";
