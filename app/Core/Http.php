<?php
declare(strict_types=1);

namespace App\Core;

/** Request helpers and responses. */
final class Http
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';
        $path = '/' . trim($path, '/');
        return $path;
    }

    public static function input(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    /** @return list<string> */
    public static function inputList(string $key): array
    {
        $v = $_POST[$key] ?? [];
        if (!is_array($v)) {
            return [];
        }
        return array_values(array_filter(array_map(fn ($x) => is_string($x) ? trim($x) : '', $v), fn ($x) => $x !== ''));
    }

    public static function query(string $key, string $default = ''): string
    {
        $v = $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    /** Client IP. Shared hosting: REMOTE_ADDR only; proxy headers are not trusted. */
    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** Short keyed hash of the IP for logs and limits, so raw IPs are not stored. */
    public static function ipHash(): string
    {
        return substr(hash_hmac('sha256', self::ip(), Config::str('app.key', 'local')), 0, 16);
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    public static function redirect(string $to, int $code = 303): never
    {
        if (!str_starts_with($to, '/') || str_starts_with($to, '//')) {
            $to = '/';
        }
        header('Location: ' . $to, true, $code);
        exit;
    }

    public static function securityHeaders(bool $noStore = false): void
    {
        // Shared hosting sends X-Powered-By with the exact PHP version; expose_php cannot be changed there.
        header_remove('X-Powered-By');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'; object-src 'none'");
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('X-Frame-Options: DENY');
        header('Cross-Origin-Opener-Policy: same-origin');
        if (Config::get('security.hsts') === true && self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000');
        }
        if ($noStore) {
            header('Cache-Control: no-store, private');
            header('X-Robots-Tag: noindex, nofollow');
        }
    }

    public static function abort(int $code, string $title = '', string $message = ''): never
    {
        http_response_code($code);
        $titles = [403 => 'Not allowed', 404 => 'Page not found', 405 => 'Not allowed', 419 => 'Page expired', 429 => 'Too many requests', 500 => 'Something went wrong'];
        View::render('pages/error', [
            'title' => $title !== '' ? $title : ($titles[$code] ?? 'Error'),
            'message' => $message,
            'code' => $code,
        ]);
        exit;
    }
}
