<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Helpers.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/View.php';

// Basic “manual autoload” for our app classes
require_once __DIR__ . '/../app/Controllers/AccountsController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/GuidanceController.php';

require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Middleware/RoleMiddleware.php';

foreach ([
  'Logger',
  'Mailer',
  'TokenService',
  'WeightsService',
] as $svc) {
  require_once __DIR__ . "/../app/Services/{$svc}.php";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
