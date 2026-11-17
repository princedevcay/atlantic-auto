<?php
declare(strict_types=1);

namespace App\Core;

/** Random references and tracking tokens (CTL-AUTHZ-002). */
final class Tokens
{
    /** No 0/O/1/I/L so references read clearly over the phone. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public static function reference(): string
    {
        $out = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < 8; $i++) {
            $out .= self::ALPHABET[random_int(0, $max)];
        }
        return $out;
    }

    public static function normaliseReference(string $ref): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $ref) ?? '');
    }

    public static function isReference(string $ref): bool
    {
        return (bool) preg_match('/^[' . self::ALPHABET . ']{8}$/', $ref);
    }

    /** 128-bit token for private tracking links; only its SHA-256 is stored. */
    public static function trackingToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
