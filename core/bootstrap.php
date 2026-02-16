<?php
declare(strict_types=1);

require_once __DIR__ . '/Env.php';

Env::load(__DIR__ . '/../.env');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helpers.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/View.php';

date_default_timezone_set(APP_TIMEZONE);

// Basic manual autoload for our app classes
require_once __DIR__ . '/../app/Controllers/AccountsController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/AdmissionController.php';

require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Middleware/RoleMiddleware.php';

foreach ([
  'Logger',
  'Mailer',
  'EmailVerificationService',
  'PasswordService',
  'TokenService',
  'WeightsService',
  'ScoresService',
] as $svc) {
  require_once __DIR__ . "/../app/Services/{$svc}.php";
}

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_name('CARESSESSID');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
