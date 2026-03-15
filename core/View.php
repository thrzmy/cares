<?php
declare(strict_types=1);

final class View
{
    public static function render(string $viewPath, array $data = []): void
    {
        if (str_starts_with($viewPath, 'errors/')) {
            self::renderStandalone($viewPath, $data);
            return;
        }

        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../app/Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("View not found: " . e($viewPath));
        }

        require __DIR__ . '/../app/Views/layouts/main.php';
    }

    public static function renderStandalone(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../app/Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("View not found: " . e($viewPath));
        }

        require $viewFile;
    }
}
