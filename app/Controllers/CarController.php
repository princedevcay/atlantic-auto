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
use App\Core\Uploads;
use App\Core\Validator;
use App\Core\View;
use App\Models\Cars;

/** Public cars for sale and car enquiries (PG-031, PG-034, CTL-AUTHZ-003). */
final class CarController
{
    public static function index(): void
    {
        $status = Http::query('status');
        $body = Http::query('body');
        View::render('pages/cars', [
            'title' => 'Cars for Sale', 'nav' => 'cars',
            'cars' => Cars::publishedList($status, $body),
            'status' => isset(Cars::STATUSES[$status]) ? $status : '',
            'body' => isset(Cars::BODY_TYPES[$body]) ? $body : '',
        ]);
    }

    public static function show(string $reference): void
    {
        Session::start();
        $car = self::car($reference);
        View::render('pages/car', [
            'title' => Cars::title($car), 'nav' => 'cars', 'car' => $car, 'photos' => Cars::photos((int) $car['id']),
            'old' => [], 'errors' => [], 'started' => FormController::stamp(),
        ]);
    }

    /** Car photos are public only while the car is published. */
    public static function photo(string $id): void
    {
        $p = Db::one('SELECT p.file_name FROM car_photos p JOIN cars c ON c.id = p.car_id WHERE p.id = ? AND c.published = 1', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Uploads::stream((string) $p['file_name'], true);
    }

    public static function enquire(string $reference): void
    {
        Session::start();
        Csrf::check();
        $car = self::car($reference);
        if ((int) ($car['sample'] ?? 0) === 1) {
            Session::flash('car_notice', 'This is a sample listing, so it cannot take enquiries. Tell us what you are looking for instead.');
            Http::redirect('/cars/' . $car['reference']);
        }
        if ($car['status'] !== 'available') {
            Session::flash('car_notice', 'This car is no longer available. Ask us about similar cars.');
            Http::redirect('/cars/' . $car['reference']);
        }
        if (FormController::looksLikeBot()) {
            Http::redirect('/thanks');
        }
        $old = ['name' => Http::input('name'), 'phone' => Http::input('phone'), 'message' => Http::input('message')];
        $render = function (array $errors, ?string $notice = null) use ($car, $old): never {
            View::render('pages/car', [
                'title' => Cars::title($car), 'nav' => 'cars', 'car' => $car, 'photos' => Cars::photos((int) $car['id']),
                'old' => $old, 'errors' => $errors, 'started' => FormController::stamp(), 'notice' => $notice,
            ]);
            exit;
        };
        if (!RateLimit::hit('form-car:' . Http::ipHash(), 5, 3600)) {
            http_response_code(429);
            $render([], 'We have received several requests from your connection in the last hour. Please wait a while, or message us on WhatsApp.');
        }
        $v = new Validator();
        $v->required('name', $old['name'], 'name', 120);
        $phone = $v->phone('phone', $old['phone']);
        $v->optional('message', $old['message'], 'message', 2000);
        if (!$v->ok()) {
            http_response_code(422);
            $render($v->errors);
        }
        $title = Cars::title($car);
        $ref = FormController::saveEnquiry([
            'type' => 'car', 'name' => $old['name'], 'phone' => $phone, 'car_id' => (int) $car['id'],
            'vehicle_year' => $car['year'], 'vehicle_make' => $car['make'], 'vehicle_model' => $car['model'],
            'vin_or_reference' => $car['reference'], 'message' => $old['message'] !== '' ? $old['message'] : null,
        ]);
        FormController::notify(
            '[Car Enquiry] ' . $title . ' (' . $car['reference'] . ')',
            "New car enquiry\n\nRequest reference: {$ref}\nCar: {$title}\nCar reference: {$car['reference']}\nListed price: " . Cars::price($car)
            . "\nName: {$old['name']}\nPhone/WhatsApp: {$phone}\n\nMessage:\n" . ($old['message'] !== '' ? $old['message'] : '-')
            . "\n\nOpen the admin inbox (Car Enquiries) to reply.",
            null
        );
        Session::flash('thanks', ['type' => 'car', 'ref' => $ref, 'vehicle' => $title, 'car_ref' => $car['reference']]);
        Http::redirect('/thanks');
    }

    /** @return array<string, mixed> */
    private static function car(string $reference): array
    {
        $ref = Tokens::normaliseReference($reference);
        $car = Tokens::isReference($ref) ? Cars::findPublished($ref) : null;
        if ($car === null) {
            Http::abort(404, 'Car not found', 'This car is no longer listed. See the cars we have for sale now.');
        }
        return $car;
    }
}
