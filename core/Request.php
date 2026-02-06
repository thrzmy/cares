<?php
declare(strict_types=1);

final class Request
{
    public static function postString(string $key): string
    {
        return trim((string)($_POST[$key] ?? ''));
    }

    public static function postArray(string $key): array
    {
        $v = $_POST[$key] ?? [];
        return is_array($v) ? $v : [];
    }
}
