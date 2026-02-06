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

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);

$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

$router->get('/admin', function () {
    RoleMiddleware::requireRole('admin');
    AdminController::dashboard();
});

$router->get('/admin/accounts', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::index();
});

$router->get('/admin/accounts/create', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::create();
});

$router->post('/admin/accounts/create', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::store();
});

$router->get('/admin/accounts/edit', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::edit();
});

$router->post('/admin/accounts/edit', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::update();
});

$router->post('/admin/accounts/toggle', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::toggleActive();
});

$router->post('/admin/accounts/reset-password', function () {
    RoleMiddleware::requireRole('admin');
    AccountsController::resetPassword();
});


$router->get('/admin/scores', [AdminController::class, 'scores']);
$router->get('/admin/results', [AdminController::class, 'results']);

$router->get('/guidance', function () {
    RoleMiddleware::requireRole('guidance', 'admin');
    GuidanceController::dashboard();
});

$router->get('/guidance/weights', [GuidanceController::class, 'weights']);
$router->post('/guidance/weights', [GuidanceController::class, 'saveWeights']);

$router->dispatch($method, $path);
