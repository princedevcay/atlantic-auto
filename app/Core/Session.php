<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Two cookie scopes (CTL-SES-001): a public session (SameSite=Lax) used by forms and tracking,
 * and an admin session (SameSite=Strict) with idle and absolute timeouts.
 */
final class Session
{
    public const IDLE_SECONDS = 1800;
    public const ABSOLUTE_SECONDS = 28800;

    private static ?string $scope = null;

    public static function start(string $scope = 'public'): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $secure = Http::isHttps() || Config::isLocal();
        $prefix = Http::isHttps() ? '__Host-' : '';
        $dir = rtrim(Config::str('paths.storage'), '/') . '/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        ini_set('session.save_path', $dir);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) self::ABSOLUTE_SECONDS);
        session_name($prefix . ($scope === 'admin' ? 'aa_admin' : 'aa_site'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure && Http::isHttps(),
            'httponly' => true,
            'samesite' => $scope === 'admin' ? 'Strict' : 'Lax',
        ]);
        session_start();
        self::$scope = $scope;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION = [];
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'],
            'httponly' => true, 'samesite' => $p['samesite'],
        ]);
        session_destroy();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function takeFlash(string $key, mixed $default = null): mixed
    {
        $v = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }

    public static function scope(): ?string
    {
        return self::$scope;
    }
}
