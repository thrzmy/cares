<?php

declare(strict_types=1);

final class Mailer
{
    /**
     * Sends email via Brevo API if key is set.
     * If no key, it will return false (you can fallback to displaying/logging link).
     */
    public static function send(string $toEmail, string $toName, string $subject, string $html): bool
    {
        if (BREVO_API_KEY === '') {
            return false; // dev fallback path
        }

        $payload = [
            'sender' => [
                'name'  => MAIL_FROM_NAME,
                'email' => MAIL_FROM_EMAIL,
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => $subject,
            'htmlContent' => $html,
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'api-key: ' . BREVO_API_KEY,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            if (APP_DEBUG) {
                throw new RuntimeException('Mailer cURL error: ' . $err);
            }
            return false;
        }

        // Brevo usually returns 201 on success
        return $code >= 200 && $code < 300;
    }
}
