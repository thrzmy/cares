<?php

declare(strict_types=1);

function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }

    if (is_bool($value)) {
        $value = $value ? '1' : '0';
    } elseif (!is_scalar($value)) {
        $value = (string)json_encode($value);
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
        'passed' => 'text-bg-success',
        'failed' => 'text-bg-danger',
        'pending' => 'text-bg-warning',
        default => 'text-bg-secondary',
    };
}

function studentStatusLabel(string $status): string
{
    return match (strtolower($status)) {
        'passed' => 'Passed',
        'failed' => 'Failed',
        'pending' => 'Pending',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

function studentScreeningStatusLabel(string $status): string
{
    return match (strtolower($status)) {
        'qualified' => 'Qualified',
        'not_qualified' => 'Not Qualified',
        'pending' => 'Pending',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

function studentApplicationStatusLabel(string $status): string
{
    return match (strtolower($status)) {
        'new_student' => 'New Student',
        'transferee' => 'Transferee',
        'returning_student' => 'Returning Student',
        'adult_learner' => 'Adult Learner',
        'old_curriculum' => 'Old Curriculum',
        'als_passer' => 'ALS Passer',
        default => ucfirst(str_replace('_', ' ', $status)),
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

function screeningBonusMaxPoints(string $field): ?int
{
    return match ($field) {
        'honors_awards_points', 'residence_points', 'other_screening_points' => 5,
        default => null,
    };
}

function screeningHonorsPointPresets(): array
{
    return [
        ['label' => 'Valedictorian', 'points' => 5],
        ['label' => 'Salutatorian', 'points' => 3],
        ['label' => 'Academic Distinction', 'points' => 1],
        ['label' => 'Non-Academic Distinction', 'points' => 1],
    ];
}

function screeningFieldHelperText(string $field): ?string
{
    return match ($field) {
        'physical_requirement_status' => 'Use for programs with physical screening such as BSTM. Leave as pending if not yet checked.',
        'honors_awards_points' => 'Workbook guide: Valedictorian = 5, Salutatorian = 3, Academic or Non-Academic Distinction = 1.',
        'residence_points' => 'Workbook guide: residence uses plus points up to a maximum of 5.',
        'other_screening_points' => 'Workbook guide: other approved screening factors use plus points up to a maximum of 5.',
        default => null,
    };
}
