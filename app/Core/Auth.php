<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Staff authentication: password + TOTP (CTL-AUTH-001), throttling and lockout (CTL-AUTH-002),
 * session timeouts (CTL-SES-001) and role checks (CTL-AUTHZ-001).
 */
final class Auth
{
    public const MAX_FAILS = 5;
    public const LOCK_MINUTES = 15;
    public const MIN_PASSWORD = 12;

    /** @var array<string, mixed>|null */
    private static ?array $user = null;

    public static function id(): ?int
    {
        return self::$user !== null ? (int) self::$user['id'] : null;
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return self::$user;
    }

    public static function isAdmin(): bool
    {
        return self::$user !== null && self::$user['role'] === 'admin';
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT);
    }

    /** Returns an error message or null when the password is acceptable. */
    public static function passwordProblem(string $password, string $email = ''): ?string
    {
        if (mb_strlen($password) < self::MIN_PASSWORD) {
            return 'Use at least ' . self::MIN_PASSWORD . ' characters.';
        }
        if (mb_strlen($password) > 200) {
            return 'Use 200 characters or fewer.';
        }
        $lower = strtolower($password);
        $common = ['password', '123456', 'qwerty', 'atlantic', 'welcome', 'letmein', 'admin', 'iloveyou', 'ghana', 'accra', 'tema', 'abc123', '111111', 'monkey', 'football'];
        foreach ($common as $w) {
            if (str_contains($lower, $w) && mb_strlen($password) < 20) {
                return 'That password is too easy to guess. Avoid common words, or use a longer passphrase.';
            }
        }
        if ($email !== '' && str_contains($lower, strtolower(strstr($email, '@', true) ?: $email))) {
            return 'Do not include your email name in the password.';
        }
        if (count(array_unique(str_split($password))) < 5) {
            return 'That password repeats too few characters.';
        }
        return null;
    }

    /**
     * Step 1: password. Returns 'ok', 'locked', 'invalid' or 'throttled'.
     * The same message is shown for unknown email and wrong password.
     */
    public static function attemptPassword(string $email, string $password): string
    {
        if (!RateLimit::hit('login-ip:' . Http::ipHash(), 20, 3600)) {
            Audit::log('login_throttled', null, null, 'ip limit');
            return 'throttled';
        }
        $user = Db::one('SELECT * FROM staff WHERE email = ? AND active = 1', [strtolower($email)]);
        if ($user === null) {
            password_verify($password, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
            Audit::log('login_failed', null, null, 'unknown account');
            return 'invalid';
        }
        if ($user['locked_until'] !== null && strtotime((string) $user['locked_until'] . ' UTC') > time()) {
            Audit::log('login_locked', 'staff', (int) $user['id'], null, (int) $user['id']);
            return 'locked';
        }
        if (!password_verify($password, (string) $user['password_hash'])) {
            $fails = (int) $user['failed_logins'] + 1;
            if ($fails >= self::MAX_FAILS) {
                Db::run('UPDATE staff SET failed_logins = 0, locked_until = ? WHERE id = ?', [gmdate('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60), $user['id']]);
                Audit::log('account_locked', 'staff', (int) $user['id'], 'too many failed logins', (int) $user['id']);
                self::alert('Staff account locked', "A staff account (id {$user['id']}) was locked after " . self::MAX_FAILS . " failed sign-in attempts.\nCheck the audit log in the admin if this was not the staff member.");
                return 'locked';
            }
            Db::run('UPDATE staff SET failed_logins = ? WHERE id = ?', [$fails, $user['id']]);
            Audit::log('login_failed', 'staff', (int) $user['id'], 'wrong password', (int) $user['id']);
            return 'invalid';
        }
        if (password_needs_rehash((string) $user['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT)) {
            Db::run('UPDATE staff SET password_hash = ? WHERE id = ?', [self::hashPassword($password), $user['id']]);
        }
        Db::run('UPDATE staff SET failed_logins = 0, locked_until = NULL WHERE id = ?', [$user['id']]);
        Session::regenerate();
        Session::set('pending_staff_id', (int) $user['id']);
        Session::set('pending_at', time());
        return 'ok';
    }

    /** @return array<string, mixed>|null the staff row waiting for MFA, if still valid */
    public static function pending(): ?array
    {
        $id = Session::get('pending_staff_id');
        $at = (int) Session::get('pending_at', 0);
        if (!is_int($id) || time() - $at > 600) {
            return null;
        }
        return Db::one('SELECT * FROM staff WHERE id = ? AND active = 1', [$id]);
    }

    /** Step 2: TOTP or a recovery code. */
    public static function attemptMfa(array $user, string $code): bool
    {
        if (!RateLimit::hit('mfa:' . $user['id'], 6, 900)) {
            Audit::log('mfa_throttled', 'staff', (int) $user['id'], null, (int) $user['id']);
            return false;
        }
        $ok = false;
        $secret = Crypto::decrypt((string) $user['totp_secret_enc']);
        $step = Totp::verify($secret, $code, $user['totp_last_step'] !== null ? (int) $user['totp_last_step'] : null);
        if ($step !== null) {
            Db::run('UPDATE staff SET totp_last_step = ? WHERE id = ?', [$step, $user['id']]);
            $ok = true;
        } else {
            $ok = self::useRecoveryCode($user, $code);
        }
        if (!$ok) {
            Audit::log('mfa_failed', 'staff', (int) $user['id'], null, (int) $user['id']);
            return false;
        }
        self::completeLogin((int) $user['id']);
        return true;
    }

    public static function completeLogin(int $id): void
    {
        Session::regenerate();
        Session::forget('pending_staff_id');
        Session::forget('pending_at');
        Session::set('staff_id', $id);
        Session::set('auth_at', time());
        Session::set('seen_at', time());
        Db::run('UPDATE staff SET last_login_at = ? WHERE id = ?', [Db::now(), $id]);
        Audit::log('login', 'staff', $id, null, $id);
    }

    /** @return list<string> plain codes to show once; stores hashes */
    public static function newRecoveryCodes(int $staffId): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtolower(Tokens::reference()) . '-' . strtolower(Tokens::reference());
        }
        $hashes = array_map(fn ($c) => password_hash($c, PASSWORD_BCRYPT), $codes);
        Db::run('UPDATE staff SET recovery_codes = ? WHERE id = ?', [json_encode($hashes), $staffId]);
        return $codes;
    }

    private static function useRecoveryCode(array $user, string $code): bool
    {
        $code = strtolower(trim($code));
        if (!preg_match('/^[a-z0-9]{8}-[a-z0-9]{8}$/', $code)) {
            return false;
        }
        $hashes = json_decode((string) ($user['recovery_codes'] ?? '[]'), true);
        if (!is_array($hashes)) {
            return false;
        }
        foreach ($hashes as $i => $h) {
            if (is_string($h) && password_verify($code, $h)) {
                unset($hashes[$i]);
                Db::run('UPDATE staff SET recovery_codes = ? WHERE id = ?', [json_encode(array_values($hashes)), $user['id']]);
                Audit::log('recovery_code_used', 'staff', (int) $user['id'], count($hashes) . ' left', (int) $user['id']);
                self::alert('Recovery code used', "A staff recovery code was used to sign in (staff id {$user['id']}).");
                return true;
            }
        }
        return false;
    }

    /**
     * Loads the signed-in staff member for an /admin request, enforcing timeouts.
     * Returns false when there is no valid session.
     */
    public static function resume(): bool
    {
        $id = Session::get('staff_id');
        if (!is_int($id)) {
            return false;
        }
        $now = time();
        if ($now - (int) Session::get('seen_at', 0) > Session::IDLE_SECONDS || $now - (int) Session::get('auth_at', 0) > Session::ABSOLUTE_SECONDS) {
            Session::destroy();
            return false;
        }
        $user = Db::one('SELECT id, email, name, role, must_change_password, totp_secret_enc, active FROM staff WHERE id = ?', [$id]);
        if ($user === null || (int) $user['active'] !== 1) {
            Session::destroy();
            return false;
        }
        Session::set('seen_at', $now);
        self::$user = $user;
        return true;
    }

    /** Default deny for every /admin route (CTL-AUTHZ-001). */
    public static function requireStaff(): void
    {
        if (!self::resume()) {
            Session::set('expired', true);
            Http::redirect('/admin/login');
        }
        if ((int) self::$user['must_change_password'] === 1 && Http::path() !== '/admin/account/password') {
            Http::redirect('/admin/account/password');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireStaff();
        if (!self::isAdmin()) {
            Audit::log('forbidden', null, null, Http::method() . ' ' . Http::path());
            Http::abort(403, 'Not allowed', 'Only an Admin can do this.');
        }
    }

    public static function logout(): void
    {
        if (self::id() !== null) {
            Audit::log('logout', 'staff', self::id());
        }
        Session::destroy();
    }

    public static function alert(string $subject, string $body): void
    {
        $to = Config::str('mail.alerts');
        if ($to === '') {
            Log::info('security alert (no alerts address set)', ['subject' => $subject]);
            return;
        }
        try {
            $id = Mailer::queue($to, '[' . Mailer::COMPANY . '] Security: ' . $subject, $body);
            Mailer::sendQueued($id);
        } catch (\Throwable $e) {
            Log::error('alert failed', ['subject' => $subject]);
        }
    }

    /** For tests. */
    public static function setUser(?array $user): void
    {
        self::$user = $user;
    }
}
