<?php
declare(strict_types=1);

final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    /**
     * Normalize a handler: if it's an array like [Class, 'method'], wrap it.
     */
    private function normalizeHandler($handler): callable
    {
        if (is_array($handler) && count($handler) === 2) {
            return static function () use ($handler): void {
                [$class, $method] = $handler;
                $class::$method();
            };
        }
        return $handler;
    }

    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$path] = $this->normalizeHandler($handler);
    }

    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$path] = $this->normalizeHandler($handler);
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Not Found']);
            return;
        }

        $handler();
    }
}
