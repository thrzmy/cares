<?php

declare(strict_types=1);

final class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('/login');
        }
    }
}
