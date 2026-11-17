<?php
declare(strict_types=1);

namespace App\Core;

/** Synchroniser token on every state-changing form, public forms included (CTL-SES-001). */
final class Csrf
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Session::start();
        }
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function check(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        $expected = $_SESSION['_csrf'] ?? '';
        if (!is_string($sent) || !is_string($expected) || $expected === '' || !hash_equals($expected, $sent)) {
            Http::abort(419, 'Page expired', 'Your session expired or the form was already sent. Please go back, refresh the page and try again.');
        }
    }
}
