<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

/** Admin only: staff accounts and the audit log (ID-001). */
final class StaffController
{
    public static function index(): void
    {
        Auth::requireAdmin();
        $staff = Db::all('SELECT id, email, name, role, active, must_change_password, totp_secret_enc IS NOT NULL AS has_mfa, locked_until, last_login_at FROM staff ORDER BY active DESC, name');
        $temp = Session::takeFlash('temp_password');
        View::render('admin/staff', ['title' => 'Staff', 'section' => 'staff', 'staff' => $staff, 'errors' => [], 'old' => [], 'temp' => is_array($temp) ? $temp : null]);
    }

    public static function create(): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $old = ['name' => Http::input('name'), 'email' => strtolower(Http::input('email')), 'role' => Http::input('role')];
        $v = new Validator();
        $v->required('name', $old['name'], 'name', 120);
        $v->email('email', $old['email'], true);
        $v->in('role', $old['role'], ['staff', 'admin'], 'Choose a role.');
        if ($v->ok() && Db::value('SELECT id FROM staff WHERE email = ?', [$old['email']]) !== null) {
            $v->errors['email'] = 'A staff account with this email already exists.';
        }
        if (!$v->ok()) {
            http_response_code(422);
            $staff = Db::all('SELECT id, email, name, role, active, must_change_password, totp_secret_enc IS NOT NULL AS has_mfa, locked_until, last_login_at FROM staff ORDER BY active DESC, name');
            View::render('admin/staff', ['title' => 'Staff', 'section' => 'staff', 'staff' => $staff, 'errors' => $v->errors, 'old' => $old, 'temp' => null]);
            return;
        }
        $temp = self::tempPassword();
        $id = Db::insert('staff', [
            'email' => $old['email'], 'name' => $old['name'], 'role' => $old['role'], 'password_hash' => Auth::hashPassword($temp),
            'must_change_password' => 1, 'active' => 1, 'failed_logins' => 0, 'created_at' => Db::now(), 'updated_at' => Db::now(),
        ]);
        Audit::log('staff_created', 'staff', $id, 'role ' . $old['role']);
        Auth::alert('Staff account created', "An Admin created a {$old['role']} account (staff id {$id}).");
        Session::flash('temp_password', ['name' => $old['name'], 'password' => $temp]);
        Http::redirect('/admin/staff');
    }

    public static function toggle(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $s = Db::one('SELECT id, active FROM staff WHERE id = ?', [(int) $id]);
        if ($s === null) {
            Http::abort(404);
        }
        if ((int) $s['id'] === Auth::id()) {
            Session::flash('toast', 'You cannot disable your own account.');
            Http::redirect('/admin/staff');
        }
        $active = (int) $s['active'] === 1 ? 0 : 1;
        if ($active === 0 && strtoupper(Http::input('confirm')) !== 'DISABLE') {
            Session::flash('toast', 'Type DISABLE to confirm.');
            Http::redirect('/admin/staff');
        }
        Db::run('UPDATE staff SET active = ?, updated_at = ? WHERE id = ?', [$active, Db::now(), $s['id']]);
        Audit::log($active ? 'staff_enabled' : 'staff_disabled', 'staff', (int) $s['id']);
        Auth::alert($active ? 'Staff account enabled' : 'Staff account disabled', 'Staff id ' . $s['id'] . ' was ' . ($active ? 'enabled' : 'disabled') . ' by an Admin.');
        Session::flash('toast', $active ? 'Account enabled.' : 'Account disabled. Their sessions end on their next request.');
        Http::redirect('/admin/staff');
    }

    /** Reset: new temporary password, forced change, and MFA re-enrolment (CTL-AUTH-001). */
    public static function reset(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $s = Db::one('SELECT id, name FROM staff WHERE id = ?', [(int) $id]);
        if ($s === null) {
            Http::abort(404);
        }
        $temp = self::tempPassword();
        Db::run(
            'UPDATE staff SET password_hash = ?, must_change_password = 1, totp_secret_enc = NULL, totp_last_step = NULL, recovery_codes = NULL, failed_logins = 0, locked_until = NULL, updated_at = ? WHERE id = ?',
            [Auth::hashPassword($temp), Db::now(), $s['id']]
        );
        Audit::log('staff_reset', 'staff', (int) $s['id']);
        Auth::alert('Staff account reset', 'Staff id ' . $s['id'] . ' had their password and authenticator reset by an Admin.');
        Session::flash('temp_password', ['name' => $s['name'], 'password' => $temp]);
        Http::redirect('/admin/staff');
    }

    public static function audit(): void
    {
        Auth::requireAdmin();
        $rows = Db::all('SELECT a.*, s.name AS staff_name FROM audit_log a LEFT JOIN staff s ON s.id = a.staff_id ORDER BY a.id DESC LIMIT 300');
        View::render('admin/audit', ['title' => 'Audit log', 'section' => 'audit', 'rows' => $rows]);
    }

    private static function tempPassword(): string
    {
        $words = [];
        for ($i = 0; $i < 4; $i++) {
            $words[] = strtolower(\App\Core\Tokens::reference());
        }
        return implode('-', $words);
    }
}
