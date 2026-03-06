<?php
declare(strict_types=1);

final class RoleMiddleware
{
    public static function requireRole(string ...$roles): void
    {
        AuthMiddleware::requireLogin();

        $role = (string)($_SESSION['role'] ?? '');
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            exit;
        }
    }
}
