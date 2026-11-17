<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Crypto;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\Session;
use App\Core\Totp;
use App\Core\View;

/** Staff sign-in: password, then TOTP; first sign-in enrols MFA and forces a new password. */
final class AuthController
{
    public static function loginForm(): void
    {
        if (Auth::resume()) {
            Http::redirect('/admin/inbox');
        }
        $expired = (bool) Session::get('expired', false);
        Session::forget('expired');
        View::render('admin/login', ['title' => 'Staff sign in', 'error' => null, 'email' => '', 'expired' => $expired], 'layouts/admin-bare');
    }

    public static function login(): void
    {
        Csrf::check();
        $email = strtolower(Http::input('email'));
        $result = Auth::attemptPassword($email, (string) ($_POST['password'] ?? ''));
        if ($result === 'ok') {
            $user = Auth::pending();
            Http::redirect($user !== null && $user['totp_secret_enc'] === null ? '/admin/mfa/setup' : '/admin/mfa');
        }
        $messages = [
            'invalid' => 'That email and password do not match a staff account.',
            'locked' => 'This account is locked for ' . Auth::LOCK_MINUTES . ' minutes after too many failed attempts. Ask an Admin if you need help.',
            'throttled' => 'Too many sign-in attempts from this connection. Please wait an hour.',
        ];
        http_response_code($result === 'throttled' ? 429 : 401);
        View::render('admin/login', ['title' => 'Staff sign in', 'error' => $messages[$result] ?? $messages['invalid'], 'email' => $email, 'expired' => false], 'layouts/admin-bare');
    }

    public static function mfaForm(): void
    {
        $user = Auth::pending();
        if ($user === null || $user['totp_secret_enc'] === null) {
            Http::redirect('/admin/login');
        }
        View::render('admin/mfa', ['title' => 'Enter your code', 'error' => null], 'layouts/admin-bare');
    }

    public static function mfa(): void
    {
        Csrf::check();
        $user = Auth::pending();
        if ($user === null || $user['totp_secret_enc'] === null) {
            Http::redirect('/admin/login');
        }
        if (!Auth::attemptMfa($user, Http::input('code'))) {
            http_response_code(401);
            View::render('admin/mfa', ['title' => 'Enter your code', 'error' => 'That code did not work. Enter the current 6-digit code from your authenticator app, or a recovery code.'], 'layouts/admin-bare');
            return;
        }
        Http::redirect('/admin/inbox');
    }

    public static function setupForm(): void
    {
        $user = Auth::pending();
        if ($user === null || $user['totp_secret_enc'] !== null) {
            Http::redirect('/admin/login');
        }
        $secret = Session::get('mfa_setup_secret');
        if (!is_string($secret)) {
            $secret = Totp::newSecret();
            Session::set('mfa_setup_secret', $secret);
        }
        View::render('admin/mfa-setup', [
            'title' => 'Set up your authenticator',
            'secret' => $secret,
            'uri' => Totp::uri($secret, (string) $user['email'], 'Atlantic Auto'),
            'error' => null,
            'codes' => null,
        ], 'layouts/admin-bare');
    }

    public static function setup(): void
    {
        Csrf::check();
        $user = Auth::pending();
        $secret = Session::get('mfa_setup_secret');
        if ($user === null || $user['totp_secret_enc'] !== null || !is_string($secret)) {
            Http::redirect('/admin/login');
        }
        $step = Totp::verify($secret, Http::input('code'), null);
        if ($step === null) {
            http_response_code(422);
            View::render('admin/mfa-setup', [
                'title' => 'Set up your authenticator', 'secret' => $secret,
                'uri' => Totp::uri($secret, (string) $user['email'], 'Atlantic Auto'),
                'error' => 'That code did not match. Check the time on your phone is set automatically, then try the current code.', 'codes' => null,
            ], 'layouts/admin-bare');
            return;
        }
        Db::run('UPDATE staff SET totp_secret_enc = ?, totp_last_step = ?, updated_at = ? WHERE id = ?', [Crypto::encrypt($secret), $step, Db::now(), $user['id']]);
        $codes = Auth::newRecoveryCodes((int) $user['id']);
        Session::forget('mfa_setup_secret');
        Audit::log('mfa_enrolled', 'staff', (int) $user['id'], null, (int) $user['id']);
        Auth::completeLogin((int) $user['id']);
        View::render('admin/mfa-setup', ['title' => 'Save your recovery codes', 'secret' => null, 'uri' => null, 'error' => null, 'codes' => $codes], 'layouts/admin-bare');
    }

    public static function logout(): void
    {
        Csrf::check();
        Auth::resume();
        Auth::logout();
        Http::redirect('/admin/login');
    }

    public static function passwordForm(): void
    {
        Auth::requireStaff();
        View::render('admin/password', ['title' => 'Change your password', 'error' => null, 'forced' => (int) Auth::user()['must_change_password'] === 1]);
    }

    public static function password(): void
    {
        Auth::requireStaff();
        Csrf::check();
        $user = Db::one('SELECT id, email, password_hash, must_change_password FROM staff WHERE id = ?', [Auth::id()]);
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $error = null;
        if ($user === null || !password_verify($current, (string) $user['password_hash'])) {
            $error = 'Your current password is not correct.';
        } elseif ($new !== $confirm) {
            $error = 'The new passwords do not match.';
        } elseif (password_verify($new, (string) $user['password_hash'])) {
            $error = 'Choose a password you have not used here before.';
        } else {
            $error = Auth::passwordProblem($new, (string) $user['email']);
        }
        if ($error !== null) {
            http_response_code(422);
            View::render('admin/password', ['title' => 'Change your password', 'error' => $error, 'forced' => (int) $user['must_change_password'] === 1]);
            return;
        }
        Db::run('UPDATE staff SET password_hash = ?, must_change_password = 0, updated_at = ? WHERE id = ?', [Auth::hashPassword($new), Db::now(), Auth::id()]);
        Audit::log('password_changed', 'staff', Auth::id());
        Session::regenerate();
        Session::flash('toast', 'Your password has been changed.');
        Http::redirect('/admin/inbox');
    }
}
