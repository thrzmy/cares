<?php
declare(strict_types=1);

final class TokenService
{
    /** Generate raw token (send to user) + hash (store in DB) */
    public static function generate(): array
    {
        $raw = bin2hex(random_bytes(32)); // 64 chars
        $hash = hash('sha256', $raw);
        return [$raw, $hash];
    }

    public static function hash(string $raw): string
    {
        return hash('sha256', $raw);
    }
}
