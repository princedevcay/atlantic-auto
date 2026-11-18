<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Session;
use App\Core\Tokens;
use App\Core\Validator;
use App\Core\View;

/** Request an Import Quote (PG-006) and Request a Vehicle Update (PG-020). */
final class FormController
{
    public const SERVICES = [
        'sourcing' => 'Sourcing: find a vehicle for me',
        'purchase' => 'Purchase: buy it on my behalf',
        'pickup' => 'Pickup in the USA',
        'shipping' => 'Shipping to Tema',
        'clearance' => 'Clearance at Tema port',
        'delivery' => 'Delivery to me in Ghana',
    ];

    public const SOURCES = [
        'auction' => 'An auction (for example Copart or IAAI)',
        'dealer' => 'A dealer',
        'private' => 'A private seller',
        'not_yet' => 'I have not found one yet',
    ];

    private const MIN_FILL_SECONDS = 4;

    public static function quoteForm(): void
    {
        Session::start();
        View::render('pages/quote', ['title' => 'Request an Import Quote', 'nav' => 'quote', 'old' => [], 'errors' => [], 'started' => self::stamp()]);
    }

    public static function updateForm(): void
    {
        Session::start();
        View::render('pages/update', ['title' => 'Request a Vehicle Update', 'nav' => 'update', 'old' => [], 'errors' => [], 'started' => self::stamp()]);
    }

    public static function quote(): void
    {
        self::guarded('pages/quote', 'Request an Import Quote', 'quote', fn () => self::handleQuote());
    }

    private static function handleQuote(): void
    {
        Session::start();
        Csrf::check();
        if (self::looksLikeBot()) {
            Http::redirect('/thanks');
        }
        if (!RateLimit::hit('form-quote:' . Http::ipHash(), 5, 3600)) {
            self::tooMany('pages/quote', 'Request an Import Quote', 'quote');
        }
        $old = [
            'name' => Http::input('name'), 'phone' => Http::input('phone'), 'email' => Http::input('email'),
            'year' => Http::input('year'), 'make' => Http::input('make'), 'model' => Http::input('model'),
            'source' => Http::input('source'), 'listing_url' => Http::input('listing_url'), 'notes' => Http::input('notes'),
            'services' => array_values(array_intersect(Http::inputList('services'), array_keys(self::SERVICES))),
        ];
        $v = new Validator();
        $v->required('name', $old['name'], 'name', 120);
        $phone = $v->phone('phone', $old['phone']);
        $v->email('email', $old['email']);
        $year = $v->year('year', $old['year']);
        $v->required('make', $old['make'], 'vehicle make', 60);
        $v->required('model', $old['model'], 'vehicle model', 80);
        $v->in('source', $old['source'], array_keys(self::SOURCES), 'Choose where the vehicle is.');
        $v->url('listing_url', $old['listing_url']);
        $v->optional('notes', $old['notes'], 'notes', 2000);
        if ($old['services'] === []) {
            $v->errors['services'] = 'Choose at least one thing you need help with.';
        }
        if (!$v->ok()) {
            http_response_code(422);
            View::render('pages/quote', ['title' => 'Request an Import Quote', 'nav' => 'quote', 'old' => $old, 'errors' => $v->errors, 'started' => self::stamp()]);
            return;
        }
        $ref = self::saveEnquiry([
            'type' => 'quote', 'name' => $old['name'], 'phone' => $phone, 'email' => $old['email'] ?: null,
            'services' => implode(',', $old['services']), 'vehicle_year' => $year,
            'vehicle_make' => $old['make'], 'vehicle_model' => $old['model'], 'source' => $old['source'],
            'listing_url' => $old['listing_url'] ?: null, 'message' => $old['notes'] ?: null,
        ]);
        $vehicle = trim(($year ?? '') . ' ' . $old['make'] . ' ' . $old['model']);
        $services = implode(', ', array_map(fn ($s) => self::SERVICES[$s], $old['services']));
        self::notify(
            '[Import Quote] ' . $vehicle . ' (' . $ref . ')',
            "New import quote request\n\nReference: {$ref}\nName: {$old['name']}\nPhone/WhatsApp: {$phone}\nEmail: " . ($old['email'] ?: '-')
            . "\nVehicle: {$vehicle}\nWhere it is: " . self::SOURCES[$old['source']] . "\nListing: " . ($old['listing_url'] ?: '-')
            . "\nServices: {$services}\n\nNotes:\n" . ($old['notes'] ?: '-') . "\n\nOpen the admin inbox to manage it.",
            $old['email'] ?: null
        );
        Session::flash('thanks', ['type' => 'quote', 'ref' => $ref, 'vehicle' => $vehicle]);
        Http::redirect('/thanks');
    }

    public static function update(): void
    {
        self::guarded('pages/update', 'Request a Vehicle Update', 'update', fn () => self::handleUpdate());
    }

    private static function handleUpdate(): void
    {
        Session::start();
        Csrf::check();
        if (self::looksLikeBot()) {
            Http::redirect('/thanks');
        }
        if (!RateLimit::hit('form-update:' . Http::ipHash(), 5, 3600)) {
            self::tooMany('pages/update', 'Request a Vehicle Update', 'update');
        }
        $old = [
            'name' => Http::input('name'), 'phone' => Http::input('phone'),
            'vin_or_reference' => Http::input('vin_or_reference'), 'question' => Http::input('question'),
        ];
        $v = new Validator();
        $v->required('name', $old['name'], 'name', 120);
        $phone = $v->phone('phone', $old['phone']);
        $v->required('vin_or_reference', $old['vin_or_reference'], 'VIN or reference', 40);
        $v->required('question', $old['question'], 'question', 2000);
        $idValue = '';
        if (!isset($v->errors['vin_or_reference'])) {
            $vin = Validator::normaliseVin($old['vin_or_reference']);
            $ref = Tokens::normaliseReference($old['vin_or_reference']);
            if (Validator::isVin($vin)) {
                $idValue = $vin;
            } elseif (Tokens::isReference($ref)) {
                $idValue = $ref;
            } else {
                $v->errors['vin_or_reference'] = 'Enter the 17-character VIN or the 8-character reference our team gave you.';
            }
        }
        if (!$v->ok()) {
            http_response_code(422);
            View::render('pages/update', ['title' => 'Request a Vehicle Update', 'nav' => 'update', 'old' => $old, 'errors' => $v->errors, 'started' => self::stamp()]);
            return;
        }
        $ref = self::saveEnquiry([
            'type' => 'update', 'name' => $old['name'], 'phone' => $phone,
            'vin_or_reference' => $idValue, 'message' => $old['question'],
        ]);
        // Link the request to a shipment for staff, without telling the visitor whether it exists.
        $shipment = Db::one('SELECT id, reference FROM shipments WHERE vin = ? OR reference = ? LIMIT 1', [$idValue, $idValue]);
        if ($shipment !== null) {
            Db::run('UPDATE enquiries SET shipment_id = ? WHERE reference = ?', [$shipment['id'], $ref]);
        }
        self::notify(
            '[Vehicle Update] ' . $idValue . ' (' . $ref . ')',
            "New vehicle update request\n\nRequest reference: {$ref}\nName: {$old['name']}\nPhone/WhatsApp: {$phone}\nVIN or tracking reference: {$idValue}"
            . "\nMatched shipment: " . ($shipment !== null ? (string) $shipment['reference'] : 'none found') . "\n\nQuestion:\n{$old['question']}\n\nOpen the admin inbox to reply.",
            null
        );
        Session::flash('thanks', ['type' => 'update', 'ref' => $ref, 'vehicle' => '']);
        Http::redirect('/thanks');
    }

    /**
     * If the database is unavailable, show the form again with what the visitor typed and a
     * retry message, instead of an error page that loses their input (CTL-ERR-001).
     */
    private static function guarded(string $view, string $title, string $nav, callable $handler): void
    {
        try {
            $handler();
        } catch (\PDOException $e) {
            Log::error('form could not be saved', ['form' => $nav, 'code' => (string) $e->getCode()]);
            $old = [];
            foreach ($_POST as $k => $v) {
                if (is_string($k) && $k !== '_csrf' && $k !== 'started' && $k !== 'website') {
                    $old[$k] = is_array($v) ? array_values(array_filter($v, 'is_string')) : (is_string($v) ? trim($v) : '');
                }
            }
            http_response_code(503);
            View::render($view, ['title' => $title, 'nav' => $nav, 'old' => $old, 'errors' => [], 'started' => self::stamp(),
                'notice' => 'We could not save your request just now. Your details are still here: please try again in a minute, or message us on WhatsApp.']);
        }
    }

    public static function thanks(): void
    {
        Session::start();
        $t = Session::takeFlash('thanks');
        if (!is_array($t)) {
            Http::redirect('/');
        }
        View::render('pages/thanks', ['title' => 'Thank you', 'nav' => '', 'thanks' => $t]);
    }

    /** @param array<string, mixed> $row */
    public static function saveEnquiry(array $row): string
    {
        for ($i = 0; $i < 5; $i++) {
            $ref = Tokens::reference();
            try {
                Db::insert('enquiries', $row + [
                    'reference' => $ref, 'status' => 'new',
                    'created_at' => Db::now(), 'last_activity_at' => Db::now(),
                ]);
                return $ref;
            } catch (\PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }
        throw new \RuntimeException('Could not allocate a reference');
    }

    public static function notify(string $subject, string $body, ?string $replyTo): void
    {
        $inbox = Config::str('mail.inbox');
        if ($inbox === '') {
            Log::info('no inbox configured; enquiry saved without email');
            return;
        }
        try {
            $id = Mailer::queue($inbox, $subject, $body, $replyTo);
            Mailer::sendQueued($id); // failures stay queued for cron retry
        } catch (\Throwable $e) {
            Log::error('could not queue notification', ['type' => $e::class]);
        }
    }

    public static function stamp(): string
    {
        $t = (string) time();
        return $t . '.' . substr(hash_hmac('sha256', $t, Config::str('app.key', 'local')), 0, 16);
    }

    /** Honeypot and minimum fill time (CTL-API-001). Bots get a normal-looking redirect. */
    public static function looksLikeBot(): bool
    {
        if (Http::input('website') !== '') {
            return true;
        }
        $parts = explode('.', Http::input('started'));
        if (count($parts) !== 2 || !hash_equals(substr(hash_hmac('sha256', $parts[0], Config::str('app.key', 'local')), 0, 16), $parts[1])) {
            return true;
        }
        $elapsed = time() - (int) $parts[0];
        return $elapsed < self::MIN_FILL_SECONDS || $elapsed > 86400;
    }

    private static function tooMany(string $view, string $title, string $nav): never
    {
        http_response_code(429);
        View::render($view, ['title' => $title, 'nav' => $nav, 'old' => [], 'errors' => [], 'started' => self::stamp(),
            'notice' => 'We have received several requests from your connection in the last hour. Please wait a while, or message us on WhatsApp.']);
        exit;
    }
}
