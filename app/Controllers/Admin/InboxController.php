<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\Session;
use App\Core\View;

/** One shared inbox with tabs All, Import Quotes, Vehicle Updates (PG-025). */
final class InboxController
{
    public const STATUSES = ['new' => 'New', 'in_progress' => 'In progress', 'done' => 'Done'];

    public static function index(): void
    {
        Auth::requireStaff();
        $tab = Http::query('tab', 'all');
        $tab = in_array($tab, ['all', 'quotes', 'updates', 'cars'], true) ? $tab : 'all';
        $status = Http::query('status', 'open');
        $status = in_array($status, ['open', 'new', 'in_progress', 'done', 'any'], true) ? $status : 'open';

        $where = [];
        $params = [];
        if ($tab !== 'all') {
            $where[] = 'type = ?';
            $params[] = ['quotes' => 'quote', 'updates' => 'update', 'cars' => 'car'][$tab];
        }
        if ($status === 'open') {
            $where[] = "status <> 'done'";
        } elseif ($status !== 'any') {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $sql = 'SELECT id, type, reference, status, name, phone, vehicle_year, vehicle_make, vehicle_model, vin_or_reference, created_at FROM enquiries'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC LIMIT 200';
        $rows = Db::all($sql, $params);

        $counts = ['all' => 0, 'quotes' => 0, 'updates' => 0, 'cars' => 0];
        foreach (Db::all("SELECT type, COUNT(*) AS n FROM enquiries WHERE status = 'new' GROUP BY type") as $c) {
            $counts[['quote' => 'quotes', 'update' => 'updates', 'car' => 'cars'][$c['type']] ?? 'updates'] = (int) $c['n'];
        }
        $counts['all'] = $counts['quotes'] + $counts['updates'] + $counts['cars'];

        $open = null;
        $openId = (int) Http::query('open', '0');
        if ($openId > 0) {
            $open = Db::one('SELECT e.*, s.reference AS shipment_reference, c.reference AS car_reference, c.price_amount AS car_price, c.currency AS car_currency, c.status AS car_status FROM enquiries e LEFT JOIN shipments s ON s.id = e.shipment_id LEFT JOIN cars c ON c.id = e.car_id WHERE e.id = ?', [$openId]);
            if ($open !== null) {
                Audit::log('enquiry_viewed', 'enquiry', $openId);
            }
        }
        View::render('admin/inbox', [
            'title' => 'Inbox', 'section' => 'inbox', 'tab' => $tab, 'status' => $status,
            'rows' => $rows, 'counts' => $counts, 'open' => $open,
        ]);
    }

    public static function status(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $status = Http::input('status');
        if (!isset(self::STATUSES[$status])) {
            Http::abort(422);
        }
        $n = Db::run('UPDATE enquiries SET status = ?, last_activity_at = ? WHERE id = ?', [$status, Db::now(), (int) $id])->rowCount();
        if ($n === 0) {
            Http::abort(404);
        }
        Audit::log('enquiry_status', 'enquiry', (int) $id, $status);
        Session::flash('toast', 'Marked as ' . strtolower(self::STATUSES[$status]) . '.');
        Http::redirect(self::back((int) $id));
    }

    /** Create a shipment from a quote request. */
    public static function convert(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $e = Db::one('SELECT * FROM enquiries WHERE id = ?', [(int) $id]);
        if ($e === null) {
            Http::abort(404);
        }
        if ($e['type'] !== 'quote' || $e['shipment_id'] !== null) {
            Session::flash('toast', 'This request already has a shipment or is not a quote.');
            Http::redirect(self::back((int) $id));
        }
        Session::set('prefill_shipment', [
            'enquiry_id' => (int) $e['id'], 'customer_name' => $e['name'], 'phone' => $e['phone'], 'email' => $e['email'] ?? '',
            'vehicle_year' => (string) ($e['vehicle_year'] ?? ''), 'vehicle_make' => $e['vehicle_make'] ?? '', 'vehicle_model' => $e['vehicle_model'] ?? '',
        ]);
        Http::redirect('/admin/shipments/new');
    }

    public static function delete(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        if (strtoupper(Http::input('confirm')) !== 'DELETE') {
            Session::flash('toast', 'Type DELETE to confirm.');
            Http::redirect(self::back((int) $id));
        }
        $n = Db::run('DELETE FROM enquiries WHERE id = ?', [(int) $id])->rowCount();
        if ($n > 0) {
            Audit::log('enquiry_deleted', 'enquiry', (int) $id);
        }
        Session::flash('toast', 'Request deleted.');
        Http::redirect('/admin/inbox');
    }

    private static function back(int $id): string
    {
        $tab = Http::input('tab');
        return '/admin/inbox?' . http_build_query(['tab' => in_array($tab, ['all', 'quotes', 'updates', 'cars'], true) ? $tab : 'all', 'open' => $id]);
    }
}
