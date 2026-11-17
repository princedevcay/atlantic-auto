<?php
declare(strict_types=1);

namespace App\Core;

/** Loads config.php from outside the web root (CTL-SEC-001, CTL-CFG-001). */
final class Config
{
    /** @var array<string, mixed> */
    private static array $data = [];

    public static function load(string $appRoot): void
    {
        $candidates = array_filter([
            getenv('ATLANTIC_CONFIG') ?: null,
            dirname($appRoot) . '/atlantic-auto-private/config.php',
            $appRoot . '/private/config.php',
        ]);
        foreach ($candidates as $path) {
            if (is_file($path)) {
                $data = require $path;
                if (!is_array($data)) {
                    throw new \RuntimeException('Config file must return an array');
                }
                self::$data = $data;
                return;
            }
        }
        throw new \RuntimeException('No config.php found outside the web root');
    }

    /** @param array<string, mixed> $data */
    public static function set(array $data): void
    {
        self::$data = $data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$data;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    public static function str(string $key, string $default = ''): string
    {
        $v = self::get($key, $default);
        return is_scalar($v) ? (string) $v : $default;
    }

    public static function isLocal(): bool
    {
        return self::str('app.env') === 'local';
    }
}
