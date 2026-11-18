<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Http;
use App\Core\RateLimit;
use App\Core\Session;
use App\Core\Tokens;
use App\Core\Uploads;
use App\Core\Validator;
use App\Core\View;
use App\Models\Cars;

/** Staff car management (PG-033, CTL-AUTHZ-003). Delete is Admin only. */
final class CarController
{
    private const FIELDS = ['year', 'make', 'model', 'trim_level', 'body_type', 'mileage', 'mileage_unit', 'transmission', 'fuel', 'engine', 'colour', 'location', 'price_amount', 'currency', 'price_on_request', 'status', 'description', 'vin'];

    public static function index(): void
    {
        Auth::requireStaff();
        $status = Http::query('status');
        $vis = Http::query('vis');
        $where = [];
        $params = [];
        if (isset(Cars::STATUSES[$status])) {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        if ($vis === 'published' || $vis === 'hidden') {
            $where[] = 'c.published = ?';
            $params[] = $vis === 'published' ? 1 : 0;
        }
        $rows = Db::all(
            'SELECT c.*, (SELECT p.id FROM car_photos p WHERE p.car_id = c.id ORDER BY p.is_main DESC, p.sort, p.id LIMIT 1) AS photo_id,
                    (SELECT COUNT(*) FROM enquiries e WHERE e.car_id = c.id AND e.status = \'new\') AS new_enquiries
             FROM cars c' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY c.updated_at DESC LIMIT 300',
            $params
        );
        View::render('admin/cars', ['title' => 'Cars', 'section' => 'cars', 'rows' => $rows, 'status' => $status, 'vis' => $vis]);
    }

    public static function createForm(): void
    {
        Auth::requireStaff();
        View::render('admin/car-form', ['title' => 'Add a car', 'section' => 'cars', 'car' => null, 'old' => ['currency' => 'GHS', 'mileage_unit' => 'mi', 'location' => 'usa', 'status' => 'available'], 'errors' => [], 'photos' => []]);
    }

    public static function create(): void
    {
        Auth::requireStaff();
        Csrf::check();
        [$data, $errors, $old] = self::validated();
        if ($errors !== []) {
            http_response_code(422);
            View::render('admin/car-form', ['title' => 'Add a car', 'section' => 'cars', 'car' => null, 'old' => $old, 'errors' => $errors, 'photos' => []]);
            return;
        }
        $id = 0;
        for ($i = 0; $i < 5 && $id === 0; $i++) {
            try {
                $id = Db::insert('cars', $data + ['reference' => Tokens::reference(), 'featured' => 0, 'published' => 0, 'created_by' => Auth::id(), 'created_at' => Db::now(), 'updated_at' => Db::now()]);
            } catch (\PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }
        Audit::log('car_created', 'car', $id);
        Session::flash('toast', 'Car saved as hidden. Add photos, then publish it when ready.');
        Http::redirect('/admin/cars/' . $id);
    }

    public static function edit(string $id): void
    {
        Auth::requireStaff();
        $car = self::find($id);
        View::render('admin/car-form', ['title' => Cars::title($car), 'section' => 'cars', 'car' => $car, 'old' => $car + ['price_on_request' => $car['price_amount'] === null ? '1' : ''], 'errors' => [], 'photos' => self::adminPhotos((int) $car['id'])]);
    }

    public static function update(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $car = self::find($id);
        [$data, $errors, $old] = self::validated();
        if ($errors !== []) {
            http_response_code(422);
            View::render('admin/car-form', ['title' => Cars::title($car), 'section' => 'cars', 'car' => $car, 'old' => $old, 'errors' => $errors, 'photos' => self::adminPhotos((int) $car['id'])]);
            return;
        }
        $sets = implode(', ', array_map(fn ($k) => $k . ' = ?', array_keys($data)));
        Db::run('UPDATE cars SET ' . $sets . ', updated_at = ? WHERE id = ?', array_merge(array_values($data), [Db::now(), $car['id']]));
        Audit::log('car_updated', 'car', (int) $car['id'], $car['price_amount'] !== $data['price_amount'] ? 'price changed' : null);
        Session::flash('toast', 'Car details saved.');
        Http::redirect('/admin/cars/' . $car['id']);
    }

    public static function status(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $car = self::find($id);
        $status = Http::input('status');
        if (!isset(Cars::STATUSES[$status])) {
            Http::abort(422);
        }
        Db::run('UPDATE cars SET status = ?, featured = IF(? = \'available\', featured, 0), updated_at = ? WHERE id = ?', [$status, $status, Db::now(), $car['id']]);
        Audit::log('car_status', 'car', (int) $car['id'], $car['status'] . ' -> ' . $status);
        Session::flash('toast', 'Marked as ' . Cars::STATUSES[$status] . '.');
        Http::redirect(self::back($car));
    }

    public static function featured(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $car = self::find($id);
        $on = (int) $car['featured'] === 1 ? 0 : 1;
        Db::run('UPDATE cars SET featured = ?, updated_at = ? WHERE id = ?', [$on, Db::now(), $car['id']]);
        Audit::log($on ? 'car_featured' : 'car_unfeatured', 'car', (int) $car['id']);
        Session::flash('toast', $on ? 'Featured on the home page (while available and published).' : 'No longer featured.');
        Http::redirect(self::back($car));
    }

    /** Publish or hide. Hidden cars vanish from the site at once (CTL-AUTHZ-003). */
    public static function publish(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $car = self::find($id);
        $on = (int) $car['published'] === 1 ? 0 : 1;
        Db::run('UPDATE cars SET published = ?, published_at = IF(? = 1 AND published_at IS NULL, ?, published_at), updated_at = ? WHERE id = ?', [$on, $on, Db::now(), Db::now(), $car['id']]);
        Audit::log($on ? 'car_published' : 'car_hidden', 'car', (int) $car['id']);
        Session::flash('toast', $on ? 'Published. The car is now on the website.' : 'Hidden. The car is no longer on the website.');
        Http::redirect(self::back($car));
    }

    public static function upload(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $car = self::find($id);
        if (!RateLimit::hit('upload:' . Auth::id(), 60, 600)) {
            Session::flash('toast', 'Too many uploads in a short time. Please wait a few minutes.');
            Http::redirect('/admin/cars/' . $car['id']);
        }
        $count = (int) Db::value('SELECT COUNT(*) FROM car_photos WHERE car_id = ?', [$car['id']]);
        if ($count >= Cars::MAX_PHOTOS) {
            Session::flash('toast', 'A car can have up to ' . Cars::MAX_PHOTOS . ' photos. Delete one first.');
            Http::redirect('/admin/cars/' . $car['id']);
        }
        $result = Uploads::storeImage(is_array($_FILES['photo'] ?? null) ? $_FILES['photo'] : []);
        if (is_string($result)) {
            Session::flash('toast', $result);
            Http::redirect('/admin/cars/' . $car['id']);
        }
        $pid = Db::insert('car_photos', ['car_id' => $car['id'], 'file_name' => $result['name'], 'width' => $result['width'], 'height' => $result['height'], 'is_main' => $count === 0 ? 1 : 0, 'sort' => $count, 'uploaded_by' => Auth::id(), 'created_at' => Db::now()]);
        Db::run('UPDATE cars SET updated_at = ? WHERE id = ?', [Db::now(), $car['id']]);
        Audit::log('car_photo_uploaded', 'car', (int) $car['id'], 'photo ' . $pid);
        Session::flash('toast', 'Photo added.');
        Http::redirect('/admin/cars/' . $car['id']);
    }

    public static function photo(string $id): void
    {
        Auth::requireStaff();
        $p = Db::one('SELECT file_name FROM car_photos WHERE id = ?', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Uploads::stream((string) $p['file_name']);
    }

    public static function mainPhoto(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $p = Db::one('SELECT id, car_id FROM car_photos WHERE id = ?', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Db::transaction(function () use ($p): void {
            Db::run('UPDATE car_photos SET is_main = 0 WHERE car_id = ?', [$p['car_id']]);
            Db::run('UPDATE car_photos SET is_main = 1 WHERE id = ?', [$p['id']]);
        });
        Audit::log('car_main_photo', 'car', (int) $p['car_id'], 'photo ' . $p['id']);
        Session::flash('toast', 'Main photo changed.');
        Http::redirect('/admin/cars/' . $p['car_id']);
    }

    public static function deletePhoto(string $id): void
    {
        Auth::requireStaff();
        Csrf::check();
        $p = Db::one('SELECT id, car_id, file_name, is_main FROM car_photos WHERE id = ?', [(int) $id]);
        if ($p === null) {
            Http::abort(404);
        }
        Db::run('DELETE FROM car_photos WHERE id = ?', [$p['id']]);
        Uploads::delete((string) $p['file_name']);
        if ((int) $p['is_main'] === 1) {
            $next = Db::value('SELECT id FROM car_photos WHERE car_id = ? ORDER BY sort, id LIMIT 1', [$p['car_id']]);
            if ($next !== null) {
                Db::run('UPDATE car_photos SET is_main = 1 WHERE id = ?', [$next]);
            }
        }
        Audit::log('car_photo_deleted', 'car', (int) $p['car_id'], 'photo ' . $p['id']);
        Session::flash('toast', 'Photo deleted.');
        Http::redirect('/admin/cars/' . $p['car_id']);
    }

    public static function delete(string $id): void
    {
        Auth::requireAdmin();
        Csrf::check();
        $car = self::find($id);
        if (strtoupper(Http::input('confirm')) !== $car['reference']) {
            Session::flash('toast', 'Type the reference ' . $car['reference'] . ' to confirm.');
            Http::redirect('/admin/cars/' . $car['id']);
        }
        $files = array_column(Db::all('SELECT file_name FROM car_photos WHERE car_id = ?', [$car['id']]), 'file_name');
        Db::run('UPDATE enquiries SET car_id = NULL WHERE car_id = ?', [$car['id']]);
        Db::run('DELETE FROM cars WHERE id = ?', [$car['id']]);
        foreach ($files as $f) {
            Uploads::delete((string) $f);
        }
        Audit::log('car_deleted', 'car', (int) $car['id'], $car['reference']);
        Session::flash('toast', 'Car ' . $car['reference'] . ' deleted.');
        Http::redirect('/admin/cars');
    }

    /** @return array<string, mixed> */
    private static function find(string $id): array
    {
        $car = Db::one('SELECT * FROM cars WHERE id = ?', [(int) $id]);
        if ($car === null) {
            Http::abort(404);
        }
        return $car;
    }

    /** @return list<array<string, mixed>> */
    private static function adminPhotos(int $carId): array
    {
        return Db::all('SELECT id, width, height, is_main FROM car_photos WHERE car_id = ? ORDER BY is_main DESC, sort, id', [$carId]);
    }

    private static function back(array $car): string
    {
        return Http::input('back') === 'list' ? '/admin/cars' : '/admin/cars/' . $car['id'];
    }

    /** @return array{0: array<string, mixed>, 1: array<string, string>, 2: array<string, string>} */
    private static function validated(): array
    {
        $old = [];
        foreach (self::FIELDS as $f) {
            $old[$f] = Http::input($f);
        }
        $v = new Validator();
        $year = $v->year('year', $old['year']);
        $v->required('make', $old['make'], 'make', 60);
        $v->required('model', $old['model'], 'model', 80);
        $v->optional('trim_level', $old['trim_level'], 'trim', 60);
        $v->in('body_type', $old['body_type'], array_keys(Cars::BODY_TYPES), 'Choose a body type.');
        $mileage = null;
        if ($old['mileage'] !== '') {
            $m = str_replace([',', ' '], '', $old['mileage']);
            if (!ctype_digit($m) || (int) $m > 2_000_000) {
                $v->errors['mileage'] = 'Enter the mileage as a whole number, for example 45000.';
            } else {
                $mileage = (int) $m;
            }
        }
        $v->in('mileage_unit', $old['mileage_unit'], ['mi', 'km'], 'Choose miles or km.');
        if ($old['transmission'] !== '') {
            $v->in('transmission', $old['transmission'], array_keys(Cars::TRANSMISSIONS), 'Choose a transmission.');
        }
        if ($old['fuel'] !== '') {
            $v->in('fuel', $old['fuel'], array_keys(Cars::FUELS), 'Choose a fuel type.');
        }
        $v->optional('engine', $old['engine'], 'engine', 40);
        $v->optional('colour', $old['colour'], 'colour', 40);
        $v->in('location', $old['location'], array_keys(Cars::LOCATIONS), 'Choose where the car is now.');
        $v->in('currency', $old['currency'], Cars::CURRENCIES, 'Choose GHS or USD.');
        $v->in('status', $old['status'], array_keys(Cars::STATUSES), 'Choose a status.');
        $price = null;
        if ($old['price_on_request'] !== '1') {
            $p = str_replace([',', ' '], '', $old['price_amount']);
            if ($p === '' || !preg_match('/^\d{1,10}(\.\d{1,2})?$/', $p) || (float) $p <= 0) {
                $v->errors['price_amount'] = 'Enter the price, for example 185000, or tick Price on request.';
            } else {
                $price = number_format((float) $p, 2, '.', '');
            }
        }
        $v->optional('description', $old['description'], 'description', 5000);
        $vin = Validator::normaliseVin($old['vin']);
        if ($vin !== '' && !Validator::isVin($vin)) {
            $v->errors['vin'] = 'A VIN has 17 letters and numbers (no I, O or Q).';
        }
        $data = [
            'year' => $year, 'make' => $old['make'], 'model' => $old['model'], 'trim_level' => $old['trim_level'] !== '' ? $old['trim_level'] : null,
            'body_type' => $old['body_type'], 'mileage' => $mileage, 'mileage_unit' => $old['mileage_unit'] === 'km' ? 'km' : 'mi',
            'transmission' => $old['transmission'] !== '' ? $old['transmission'] : null, 'fuel' => $old['fuel'] !== '' ? $old['fuel'] : null,
            'engine' => $old['engine'] !== '' ? $old['engine'] : null, 'colour' => $old['colour'] !== '' ? $old['colour'] : null,
            'location' => $old['location'], 'price_amount' => $price, 'currency' => $old['currency'], 'status' => $old['status'],
            'description' => $old['description'] !== '' ? $old['description'] : null, 'vin' => $vin !== '' ? $vin : null,
        ];
        return [$data, $v->errors, $old];
    }
}
