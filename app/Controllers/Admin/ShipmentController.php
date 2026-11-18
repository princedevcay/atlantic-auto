<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\Session;
use App\Core\Tokens;
use App\Core\Uploads;
use App\Core\Validator;
use App\Core\View;
use App\Models\Stages;

/** Shipments, stages, photos and private tracking links (PG-021, PG-023, CTL-AUTHZ-001/002, CTL-FILE-001). */
final class ShipmentController
{
    public static function index(): void
    {
        Auth::requireStaff();
        $stage = Http::query('stage');
        $q = mb_substr(Http::query('q'), 0, 60);
        $where = [];
        $params = [];
        if (Stages::valid($stage)) {
            $where[] = 'stage = ?';
            $params[] = $stage;
        } elseif ($stage === 'active') {
            $where[] = "stage <> 'delivered'";
        }
        if ($q !== '') {
            $where[] = '(reference = ? OR vin LIKE ? OR customer_name LIKE ?)';
            $params[] = Tokens::normaliseReference($q);
            $params[] = '%' . strtoupper($q) . '%';
            $params[] = '%' . $q . '%';
        }
        $rows = Db::all(
            'SELECT id, reference, customer_name, vehicle_year, vehicle_make, vehicle_model, vin, stage, updated_at FROM shipments'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY updated_at DESC LIMIT 200',
            $params
        );
        $counts = [];
        foreach (Db::all('SELECT stage, COUNT(*) AS n FROM shipments GROUP BY stage') as $c) {
            $counts[$c['stage']] = (int) $c['n'];
        }
        View::render('admin/shipments', ['title' => 'Shipments', 'section' => 'shipments', 'rows' => $rows, 'stage' => $stage, 'q' => $q, 'counts' => $counts]);
    }

    public static function createForm(): void
    {
        Auth::requireStaff();
        $prefill = Session::get('prefill_shipment');
        View::render('admin/shipment-form', ['title' => 'New shipment', 'section' => 'shipments', 'old' => is_array($prefill) ? $prefill : [], 'errors' => [], 'shipment' => null]);
    }

    public static function create(): void
    {
        Auth::requireStaff();
        Csrf::check();
        [$data, $errors, $old] = self::validated();
        if ($errors !== []) {
            http_response_code(422);
            View::render('admin/shipment-form', ['title' => 'New shipment', 'section' => 'shipments', 'old' => $old, 'errors' => $errors, 'shipment' => null]);
            return;
        }
        $prefill = Session::get('prefill_shipment');
        $enquiryId = is_array($prefill) && (int) ($prefill['enquiry_id'] ?? 0) === (int) Http::input('enquiry_id') ? (int) $prefill['enquiry_id'] : null;
        $token = Tokens::trackingToken();
        $id = Db::transaction(function () use ($data, $enquiryId, $token): int {
            $id = 0;
            for ($i = 0; $i < 5 && $id === 0; $i++) {
                try {
                    $id = Db::insert('shipments', $data + [
                        'reference' => Tokens::reference(), 'token_hash' => Tokens::hash($token), 'token_created_at' => Db::now(),
                        'stage' => 'sourcing', 'enquiry_id' => $enquiryId, 'created_by' => Auth::id(),
                        'created_at' => Db::now(), 'updated_at' => Db::now(),
                    ]);
                } catch (\PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                }
            }
            if ($id === 0) {
                throw new \RuntimeException('Could not allocate a reference');
            }
            Db::insert('shipment_updates', ['shipment_id' => $id, 'stage' => 'sourcing', 'note' => null, 'is_public' => 1, 'created_by' => Auth::id(), 'created_at' => Db::now()]);
            if ($enquiryId !== null) {
                Db::run("UPDATE enquiries SET shipment_id = ?, status = 'in_progress', last_activity_at = ? WHERE id = ?", [$id, Db::now(), $enquiryId]);
            }
            return $id;
        });
        Session::forget('prefill_shipment');
        Audit::log('shipment_created', 'shipment', $id);
        if (customer_tracking()) {
            Session::flash('new_link', $token);
            Session::flash('toast', 'Shipment created. Copy the private tracking link below now; it is shown only once.');
        } else {
            Session::flash('toast', 'Shipment created.');
        }
        Http::redirect('/admin/shipments/' . $id);
    }

    public static function show(string $id): void
    {
        Auth::requireStaff();
        $s = self::find($id);
        $updates = Db::all('SELECT u.*, st.name AS staff_name FROM shipment_updates u LEFT JOIN staff st ON st.id = u.created_by WHERE u.shipment_id = ? ORDER BY u.created_at DESC, u.id DESC', [$s['id']]);
        $photos = Db::all('SELECT id, width, height, is_public, created_at FROM shipment_photos WHERE shipment_id = ? ORDER BY created_at DESC', [$s['id']]);
        $events = Db::all('SELECT event_time, description, location FROM carrier_events WHERE shipment_id = ? ORDER BY event_time DESC LIMIT 20', [$s['id']]);
        $requests = Db::all('SELECT id, type, reference, status, created_at FROM enquiries WHERE shipment_id = ? ORDER BY created_at DESC', [$s['id']]);
        $link = Session::takeFlash('new_link');
        View::render('admin/shipment', [
            'title' => 'Shipment ' . $s['reference'], 'section' => 'shipments', 's' => $s, 'updates' => $updates,
            'photos' => $photos, 'events' => $events, 'requests' => $requests,
            'newLink' => is_string($link) ? rtrim(Config::str('app.url'), '/') . '/t/' . $link : null,
        ]);
    }

    public static function update(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $s = self::find($id);
        [$data, $errors, $old] = self::validated();
        if ($errors !== []) {
            http_response_code(422);
            View::render('admin/shipment-form', ['title' => 'Edit shipment ' . $s['reference'], 'section' => 'shipments', 'old' => $old, 'errors' => $errors, 'shipment' => $s]);
            return;
        }
        Db::run(
            'UPDATE shipments SET customer_name = ?, phone = ?, phone_last4 = ?, email = ?, vehicle_year = ?, vehicle_make = ?, vehicle_model = ?, vin = ?, carrier_ref_type = ?, carrier_ref = ?, vessel = ?, eta = ?, updated_at = ? WHERE id = ?',
            [$data['customer_name'], $data['phone'], $data['phone_last4'], $data['email'], $data['vehicle_year'], $data['vehicle_make'], $data['vehicle_model'], $data['vin'],
             $data['carrier_ref_type'], $data['carrier_ref'], $data['vessel'], $data['eta'], Db::now(), $s['id']]
        );
        Audit::log('shipment_updated', 'shipment', (int) $s['id']);
        Session::flash('toast', 'Shipment details saved.');
        Http::redirect('/admin/shipments/' . $s['id']);
    }

    public static function edit(string $id): void
    {
        Auth::requireStaff();
        $s = self::find($id);
        View::render('admin/shipment-form', ['title' => 'Edit shipment ' . $s['reference'], 'section' => 'shipments', 'old' => $s, 'errors' => [], 'shipment' => $s]);
    }

    /** Change stage and/or add a note. Carrier data never changes the stage (CTL-API-002). */
    public static function stage(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $s = self::find($id);
        $stage = Http::input('stage');
        $note = Http::input('note');
        $public = Http::input('is_public') === '1' ? 1 : 0;
        if (!Stages::valid($stage) || mb_strlen($note) > 2000) {
            Session::flash('toast', 'Choose a stage and keep the note under 2000 characters.');
            Http::redirect('/admin/shipments/' . $s['id']);
        }
        if ($stage === $s['stage'] && $note === '') {
            Session::flash('toast', 'Nothing to save: choose a new stage or write a note.');
            Http::redirect('/admin/shipments/' . $s['id']);
        }
        Db::transaction(function () use ($s, $stage, $note, $public): void {
            Db::insert('shipment_updates', ['shipment_id' => $s['id'], 'stage' => $stage, 'note' => $note !== '' ? $note : null, 'is_public' => $public, 'created_by' => Auth::id(), 'created_at' => Db::now()]);
            Db::run('UPDATE shipments SET stage = ?, delivered_at = ?, updated_at = ? WHERE id = ?', [
                $stage, $stage === 'delivered' ? ($s['delivered_at'] ?? Db::now()) : null, Db::now(), $s['id'],
            ]);
        });
        if ($stage !== $s['stage']) {
            Audit::log('stage_changed', 'shipment', (int) $s['id'], $s['stage'] . ' -> ' . $stage);
        } else {
            Audit::log('note_added', 'shipment', (int) $s['id'], $public ? 'public' : 'internal');
        }
        Session::flash('toast', $stage !== $s['stage'] ? 'Stage changed to ' . stage_label($stage) . '.' : 'Note added.');
        Http::redirect('/admin/shipments/' . $s['id']);
    }

    public static function upload(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $s = self::find($id);
        if (!\App\Core\RateLimit::hit('upload:' . Auth::id(), 30, 600)) {
            Session::flash('toast', 'Too many uploads in a short time. Please wait a few minutes.');
            Http::redirect('/admin/shipments/' . $s['id']);
        }
        $result = Uploads::storeImage(is_array($_FILES['photo'] ?? null) ? $_FILES['photo'] : []);
        if (is_string($result)) {
            Session::flash('toast', $result);
            Http::redirect('/admin/shipments/' . $s['id']);
        }
        $pid = Db::insert('shipment_photos', [
            'shipment_id' => $s['id'], 'file_name' => $result['name'], 'width' => $result['width'], 'height' => $result['height'],
            'is_public' => Http::input('is_public') === '1' ? 1 : 0, 'uploaded_by' => Auth::id(), 'created_at' => Db::now(),
        ]);
        Db::run('UPDATE shipments SET updated_at = ? WHERE id = ?', [Db::now(), $s['id']]);
        Audit::log('photo_uploaded', 'shipment', (int) $s['id'], 'photo ' . $pid);
        Session::flash('toast', 'Photo added.');
        Http::redirect('/admin/shipments/' . $s['id']);
    }

    public static function photo(string $id): void
    {
        Auth::requireStaff();
        $p = Db::one('SELECT file_name FROM shipment_photos WHERE id = ?', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Uploads::stream((string) $p['file_name']);
    }

    public static function deletePhoto(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $p = Db::one('SELECT id, shipment_id, file_name FROM shipment_photos WHERE id = ?', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Db::run('DELETE FROM shipment_photos WHERE id = ?', [$p['id']]);
        Uploads::delete((string) $p['file_name']);
        Audit::log('photo_deleted', 'shipment', (int) $p['shipment_id'], 'photo ' . $p['id']);
        Session::flash('toast', 'Photo deleted.');
        Http::redirect('/admin/shipments/' . $p['shipment_id']);
    }

    /** Issue a new private link. The old link stops working; the new one is shown once. */
    public static function newLink(string $id): void
    {
        Auth::requireStaff();
        if (!customer_tracking()) {
            Http::abort(404);
        }
        Csrf::check();
        $s = self::find($id);
        $token = Tokens::trackingToken();
        Db::run('UPDATE shipments SET token_hash = ?, token_created_at = ?, link_disabled_at = NULL WHERE id = ?', [Tokens::hash($token), Db::now(), $s['id']]);
        Audit::log('tracking_link_regenerated', 'shipment', (int) $s['id']);
        Session::flash('new_link', $token);
        Session::flash('toast', 'New tracking link created. The previous link no longer works.');
        Http::redirect('/admin/shipments/' . $s['id']);
    }

    public static function revokeLink(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $s = self::find($id);
        Db::run('UPDATE shipments SET token_hash = NULL, link_disabled_at = ? WHERE id = ?', [Db::now(), $s['id']]);
        Audit::log('tracking_disabled', 'shipment', (int) $s['id']);
        Session::flash('toast', 'Tracking is switched off for this shipment. The link and reference lookup no longer work.');
        Http::redirect('/admin/shipments/' . $s['id']);
    }

    public static function delete(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $s = self::find($id);
        if (strtoupper(Http::input('confirm')) !== $s['reference']) {
            Session::flash('toast', 'Type the reference ' . $s['reference'] . ' to confirm.');
            Http::redirect('/admin/shipments/' . $s['id']);
        }
        $files = array_column(Db::all('SELECT file_name FROM shipment_photos WHERE shipment_id = ?', [$s['id']]), 'file_name');
        Db::run('UPDATE enquiries SET shipment_id = NULL WHERE shipment_id = ?', [$s['id']]);
        Db::run('DELETE FROM shipments WHERE id = ?', [$s['id']]);
        foreach ($files as $f) {
            Uploads::delete((string) $f);
        }
        Audit::log('shipment_deleted', 'shipment', (int) $s['id'], $s['reference']);
        Session::flash('toast', 'Shipment ' . $s['reference'] . ' deleted.');
        Http::redirect('/admin/shipments');
    }

    /** @return array<string, mixed> */
    private static function find(string $id): array
    {
        $s = Db::one('SELECT * FROM shipments WHERE id = ?', [(int) $id]);
        if ($s === null) {
            Http::abort(404);
        }
        return $s;
    }

    /** @return array{0: array<string, mixed>, 1: array<string, string>, 2: array<string, string>} */
    private static function validated(): array
    {
        $old = [];
        foreach (['customer_name', 'phone', 'email', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'vin', 'carrier_ref_type', 'carrier_ref', 'vessel', 'eta', 'enquiry_id'] as $f) {
            $old[$f] = Http::input($f);
        }
        $v = new Validator();
        $v->required('customer_name', $old['customer_name'], 'customer name', 120);
        $phone = $v->phone('phone', $old['phone']);
        $v->email('email', $old['email']);
        $year = $v->year('vehicle_year', $old['vehicle_year']);
        $v->required('vehicle_make', $old['vehicle_make'], 'vehicle make', 60);
        $v->required('vehicle_model', $old['vehicle_model'], 'vehicle model', 80);
        $vin = Validator::normaliseVin($old['vin']);
        if ($vin !== '' && !Validator::isVin($vin)) {
            $v->errors['vin'] = 'A VIN has 17 letters and numbers (no I, O or Q).';
        }
        if ($old['carrier_ref_type'] !== '') {
            $v->in('carrier_ref_type', $old['carrier_ref_type'], ['booking', 'bl', 'container'], 'Choose a reference type.');
        }
        if ($old['carrier_ref'] !== '' && !preg_match('/^[A-Za-z0-9-]{4,40}$/', $old['carrier_ref'])) {
            $v->errors['carrier_ref'] = 'Use 4 to 40 letters, numbers or dashes.';
        }
        $v->optional('vessel', $old['vessel'], 'vessel', 80);
        if ($old['eta'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['eta'])) {
            $v->errors['eta'] = 'Enter the date as YYYY-MM-DD.';
        }
        $data = [
            'customer_name' => $old['customer_name'], 'phone' => $phone, 'phone_last4' => $phone !== null ? substr($phone, -4) : '',
            'email' => $old['email'] !== '' ? strtolower($old['email']) : null, 'vehicle_year' => $year,
            'vehicle_make' => $old['vehicle_make'], 'vehicle_model' => $old['vehicle_model'], 'vin' => $vin !== '' ? $vin : null,
            'carrier_ref_type' => $old['carrier_ref_type'] !== '' ? $old['carrier_ref_type'] : null,
            'carrier_ref' => $old['carrier_ref'] !== '' ? strtoupper($old['carrier_ref']) : null,
            'vessel' => $old['vessel'] !== '' ? $old['vessel'] : null, 'eta' => $old['eta'] !== '' ? $old['eta'] : null,
        ];
        return [$data, $v->errors, $old];
    }
}
