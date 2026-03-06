<?php

declare(strict_types=1);

final class EmailVerificationService
{
    public static function generateCode(): string
    {
        return (string)random_int(100000, 999999);
    }

    public static function hash(string $code): string
    {
        return hash('sha256', $code);
    }
}
