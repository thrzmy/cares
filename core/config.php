<?php
declare(strict_types=1);

define('APP_URL', env('APP_URL', 'http://localhost:8000'));

define('APP_NAME', env('APP_NAME', 'CARES'));
define('BASE_PATH', env('BASE_PATH', ''));

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NAME', 'cares'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

define('APP_DEBUG', filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL));

// Brevo (Sendinblue) API
define('BREVO_API_KEY', env('BREVO_API_KEY', '')); // put your API key here later
define('MAIL_FROM_EMAIL', env('MAIL_FROM_EMAIL', 'no-reply@cares.local')); // for demo; replace with your verified sender in Brevo
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'CARES'));
