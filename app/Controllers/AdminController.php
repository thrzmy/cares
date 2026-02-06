<?php
declare(strict_types=1);

final class AdminController
{
    public static function dashboard(): void
    {
        RoleMiddleware::requireRole('admin');
        View::render('admin/dashboard', ['title' => 'Admin Dashboard']);
    }

    public static function scores(): void
    {
        RoleMiddleware::requireRole('admin');
        View::render('admin/scores', ['title' => 'Encode Scores']);
    }

    public static function results(): void
    {
        RoleMiddleware::requireRole('admin');
        View::render('admin/results', ['title' => 'Results']);
    }
}
