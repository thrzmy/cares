<?php

declare(strict_types=1);

final class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('/login');
        }

        if (!empty($_SESSION['force_password_change'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            $current = str_starts_with($uri, BASE_PATH) ? substr($uri, strlen(BASE_PATH)) : $uri;
            $current = $current === '' ? '/' : $current;

            $allowed = ['/force-password-change', '/logout'];
            if (!in_array($current, $allowed, true)) {
                redirect('/force-password-change');
            }
        }
    }
}
