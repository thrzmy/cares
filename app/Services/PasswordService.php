<?php

declare(strict_types=1);

final class PasswordService
{
    public static function generateTempPassword(int $length = 12): string
    {
        if ($length < 8) {
            $length = 8;
        }

        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $symbols = '!@#$%&*?';
        $all = $lower . $upper . $digits . $symbols;

        $chars = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($chars);
        return implode('', $chars);
    }
}
