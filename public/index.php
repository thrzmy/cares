<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

$router = new Router();

// Get path without query string
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Remove base path (/cares) so routes are clean
$path = str_starts_with($uri, BASE_PATH) ? substr($uri, strlen(BASE_PATH)) : $uri;
$path = $path === '' ? '/' : $path;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Routes
$router->get('/', fn() => redirect('/login'));

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/verify-email', [AuthController::class, 'showVerifyEmail']);
$router->post('/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/verify-email/resend', [AuthController::class, 'resendVerifyEmail']);

$router->get('/force-password-change', [AuthController::class, 'showForcePasswordChange']);
$router->post('/force-password-change', [AuthController::class, 'forcePasswordChange']);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);

$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

$router->get('/admin', fn() => redirect('/administrator'));
$router->get('/guidance', fn() => redirect('/admission'));

$router->get('/administrator', [AdminController::class, 'dashboard']);

$router->get('/administrator/accounts', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::index();
});

$router->get('/administrator/accounts/create', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::create();
});

$router->post('/administrator/accounts/create', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::store();
});

$router->get('/administrator/accounts/edit', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::edit();
});

$router->post('/administrator/accounts/edit', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::update();
});

$router->post('/administrator/accounts/toggle', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::toggleActive();
});

$router->post('/administrator/accounts/reset-password', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::resetPassword();
});
$router->post('/administrator/accounts/verify', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::verify();
});
$router->post('/administrator/accounts/reject', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::reject();
});

$router->get('/administrator/matrix', [AdminController::class, 'matrix']);
$router->post('/administrator/matrix', [AdminController::class, 'saveMatrix']);
$router->get('/administrator/scores', [AdminController::class, 'scores']);
$router->get('/administrator/scores/view', [AdminController::class, 'viewScores']);
$router->get('/administrator/results', [AdminController::class, 'results']);
$router->get('/administrator/logs', [AdminController::class, 'logs']);
$router->get('/administrator/reports', [AdminController::class, 'reports']);
$router->get('/administrator/profile', [AdminController::class, 'profile']);
$router->post('/administrator/profile', [AdminController::class, 'updateProfile']);
$router->post('/administrator/profile/password', [AdminController::class, 'updatePassword']);
$router->get('/administrator/students', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::students();
});
$router->get('/administrator/students/create', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::createStudent();
});
$router->post('/administrator/students/create', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::storeStudent();
});
$router->get('/administrator/students/edit', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::editStudent();
});
$router->post('/administrator/students/edit', function () {
    RoleMiddleware::requireRole('administrator');
    AccountsController::updateStudent();
});

$router->get('/admission', [AdmissionController::class, 'dashboard']);
$router->get('/admission/encode', [AdmissionController::class, 'encode']);
$router->get('/admission/encode/edit', [AdmissionController::class, 'editScores']);
$router->post('/admission/encode/edit', [AdmissionController::class, 'saveScores']);
$router->get('/admission/results', [AdmissionController::class, 'results']);
$router->get('/admission/results/view', [AdmissionController::class, 'viewScores']);
$router->get('/admission/reports', [AdmissionController::class, 'reports']);
$router->get('/admission/profile', [AdmissionController::class, 'profile']);
$router->post('/admission/profile', [AdmissionController::class, 'updateProfile']);
$router->post('/admission/profile/password', [AdmissionController::class, 'updatePassword']);
$router->get('/admission/logs', [AdmissionController::class, 'logs']);
$router->get('/admission/storage', [AdmissionController::class, 'storage']);
$router->get('/admission/storage/edit', [AdmissionController::class, 'editStoredScores']);
$router->get('/admission/students', [AdmissionController::class, 'students']);
$router->get('/admission/students/create', [AdmissionController::class, 'createStudent']);
$router->post('/admission/students/create', [AdmissionController::class, 'storeStudent']);
$router->get('/admission/students/edit', [AdmissionController::class, 'editStudent']);
$router->post('/admission/students/edit', [AdmissionController::class, 'updateStudent']);

$router->dispatch($method, $path);
