<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\RateLimit;
use App\Core\Session;
use App\Core\Tokens;
use App\Core\Uploads;
use App\Core\View;

/**
 * Tracking pages (PG-021, PG-022, CTL-AUTHZ-002, CTL-DATA-002).
 * Access: private link token, or reference + last 4 phone digits. Never VIN alone.
 * Every failure shows the same message.
 */
final class TrackController
{
    private const NOT_FOUND = 'We could not find a vehicle with those details. Check the reference and the last 4 digits of the phone number you gave us, or message us on WhatsApp.';
    private const ACCESS_SECONDS = 1800;

    public static function form(): void
    {
        Session::start();
        View::render('pages/track', ['title' => 'Track Your Vehicle', 'nav' => 'track', 'error' => null, 'old' => []]);
    }

    public static function lookup(): void
    {
        Session::start();
        Csrf::check();
        $ref = Tokens::normaliseReference(Http::input('reference'));
        $last4 = preg_replace('/\D/', '', Http::input('phone_last4')) ?? '';
        $old = ['reference' => Http::input('reference')];

        if (!RateLimit::hit('track-ip:' . Http::ipHash(), 10, 3600)) {
            http_response_code(429);
            View::render('pages/track', ['title' => 'Track Your Vehicle', 'nav' => 'track', 'old' => $old,
                'error' => 'Too many lookups from your connection. Please wait an hour, or message us on WhatsApp.']);
            return;
        }
        $refKey = 'track-ref:' . hash('sha256', $ref);
        if (RateLimit::exceeded($refKey, 5, 86400)) {
            self::notFound($old);
        }
        if (!Tokens::isReference($ref) || strlen($last4) !== 4) {
            self::notFound($old);
        }
        $s = Db::one('SELECT id, phone_last4, link_disabled_at FROM shipments WHERE reference = ?', [$ref]);
        if ($s === null || $s['link_disabled_at'] !== null || !hash_equals((string) $s['phone_last4'], $last4)) {
            RateLimit::hit($refKey, 5, 86400);
            self::notFound($old);
        }
        self::grant((int) $s['id']);
        Http::redirect('/track/view');
    }

    public static function byToken(string $token): void
    {
        Session::start();
        if (!RateLimit::hit('token-ip:' . Http::ipHash(), 60, 3600)) {
            Http::abort(429);
        }
        if (!preg_match('/^[A-Za-z0-9_-]{22}$/', $token)) {
            Http::abort(404, 'Link not found', self::NOT_FOUND);
        }
        $s = Db::one('SELECT id FROM shipments WHERE token_hash = ? AND link_disabled_at IS NULL', [Tokens::hash($token)]);
        if ($s === null) {
            Http::abort(404, 'Link not found', 'This tracking link is not valid or has been replaced. Ask the Atlantic team for a new link, or use Track Your Vehicle with your reference.');
        }
        self::grant((int) $s['id']);
        Http::redirect('/track/view');
    }

    public static function view(): void
    {
        Session::start();
        $id = self::grantedShipment();
        if ($id === null) {
            Http::redirect('/track');
        }
        $s = Db::one('SELECT id, reference, vehicle_year, vehicle_make, vehicle_model, vin, stage, vessel, eta, carrier_synced_at, updated_at, link_disabled_at FROM shipments WHERE id = ?', [$id]);
        if ($s === null || $s['link_disabled_at'] !== null) {
            Session::forget('track');
            Http::redirect('/track');
        }
        $updates = Db::all('SELECT stage, note, created_at FROM shipment_updates WHERE shipment_id = ? AND is_public = 1 ORDER BY created_at ASC, id ASC', [$id]);
        $photos = Db::all('SELECT id, width, height FROM shipment_photos WHERE shipment_id = ? AND is_public = 1 ORDER BY created_at DESC', [$id]);
        $events = Db::all('SELECT event_time, description, location FROM carrier_events WHERE shipment_id = ? ORDER BY event_time DESC LIMIT 20', [$id]);
        View::render('pages/tracking', ['title' => 'Your vehicle', 'nav' => 'track', 's' => $s, 'updates' => $updates, 'photos' => $photos, 'events' => $events]);
    }

    public static function photo(string $id): void
    {
        Session::start();
        $sid = self::grantedShipment();
        $p = $sid === null ? null : Db::one('SELECT file_name FROM shipment_photos WHERE id = ? AND shipment_id = ? AND is_public = 1', [(int) $id, $sid]);
        if ($p === null) {
            Http::abort(404);
        }
        Uploads::stream((string) $p['file_name']);
    }

    private static function grant(int $shipmentId): void
    {
        Session::regenerate();
        Session::set('track', ['id' => $shipmentId, 'at' => time()]);
    }

    private static function grantedShipment(): ?int
    {
        $t = Session::get('track');
        if (!is_array($t) || !isset($t['id'], $t['at']) || time() - (int) $t['at'] > self::ACCESS_SECONDS) {
            return null;
        }
        return (int) $t['id'];
    }

    /** @param array<string, string> $old */
    private static function notFound(array $old): never
    {
        http_response_code(404);
        View::render('pages/track', ['title' => 'Track Your Vehicle', 'nav' => 'track', 'old' => $old, 'error' => self::NOT_FOUND]);
        exit;
    }
}
