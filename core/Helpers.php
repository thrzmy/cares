<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

function verifyCsrfOrFail(): void
{
    $token = (string)($_POST['_csrf'] ?? '');
    if ($token === '' || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(419);
        View::render('errors/419', ['title' => 'Session Expired']);
        exit;
    }
}

function redirect(string $path): void
{
    header('Location: ' . BASE_PATH . $path);
    exit;
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function isActive(string $path): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $current = str_starts_with($uri, BASE_PATH) ? substr($uri, strlen(BASE_PATH)) : $uri;
    $current = $current === '' ? '/' : $current;

    if ($path === '/administrator' || $path === '/admission') {
        return $current === $path ? 'active' : '';
    }

    return ($current === $path || str_starts_with($current, $path . '/')) ? 'active' : '';
}

function studentStatusBadgeClass(string $status): string
{
    return match (strtolower($status)) {
        'admitted' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        'waitlisted' => 'text-bg-info',
        'pending' => 'text-bg-warning',
        default => 'text-bg-secondary',
    };
}

function appNow(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
}

function appFromDb(?string $value): ?DateTimeImmutable
{
    if ($value === null || $value === '') {
        return null;
    }
    return new DateTimeImmutable($value, new DateTimeZone(APP_TIMEZONE));
}
