<?php
declare(strict_types=1);

namespace App\Core;

/** AES-256-GCM for small secrets at rest (staff TOTP secrets), keyed by app.key (CTL-CRY-001). */
final class Crypto
{
    private static function key(): string
    {
        $key = base64_decode(Config::str('app.key'), true);
        if ($key === false || strlen($key) !== 32) {
            throw new \RuntimeException('app.key must be 32 random bytes, base64 encoded');
        }
        return $key;
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new \RuntimeException('Encryption failed');
        }
        return base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(string $encoded): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 29) {
            throw new \RuntimeException('Bad ciphertext');
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($plain === false) {
            throw new \RuntimeException('Decryption failed');
        }
        return $plain;
    }
}
