<?php

declare(strict_types=1);

final class SessionController
{
    /**
     * Heartbeat endpoint to reset session activity from frontend.
     * AuthMiddleware will automatically update last_activity on this request.
     */
    public static function ping(): void
    {
        AuthMiddleware::requireLogin();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'timestamp' => time()]);
        exit;
    }
}
