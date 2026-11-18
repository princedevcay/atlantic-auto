<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

/**
 * Cars for sale (PG-031 to PG-034). Public reads go through the published* methods only,
 * which never return hidden cars (CTL-AUTHZ-003).
 */
final class Cars
{
    public const BODY_TYPES = ['saloon' => 'Saloon', 'suv' => 'SUV', 'pickup' => 'Pickup', 'van' => 'Van', 'truck' => 'Truck', 'hatchback' => 'Hatchback', 'coupe' => 'Coupe', 'other' => 'Other'];
    public const TRANSMISSIONS = ['automatic' => 'Automatic', 'manual' => 'Manual'];
    public const FUELS = ['petrol' => 'Petrol', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electric' => 'Electric'];
    public const LOCATIONS = ['usa' => 'In the USA', 'transit' => 'In transit', 'ghana' => 'In Ghana'];
    public const STATUSES = ['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold'];
    public const CURRENCIES = ['GHS', 'USD'];
    public const MAX_PHOTOS = 12;

    private const CARD_COLUMNS = 'c.id, c.reference, c.year, c.make, c.model, c.trim_level, c.body_type, c.mileage, c.mileage_unit, c.transmission, c.price_amount, c.currency, c.status, c.location, c.sample,
        (SELECT p.id FROM car_photos p WHERE p.car_id = c.id ORDER BY p.is_main DESC, p.sort, p.id LIMIT 1) AS photo_id,
        (SELECT p.width FROM car_photos p WHERE p.car_id = c.id ORDER BY p.is_main DESC, p.sort, p.id LIMIT 1) AS photo_w,
        (SELECT p.height FROM car_photos p WHERE p.car_id = c.id ORDER BY p.is_main DESC, p.sort, p.id LIMIT 1) AS photo_h';

    /** @return list<array<string, mixed>> */
    public static function publishedList(string $status = '', string $body = ''): array
    {
        $where = ['c.published = 1'];
        $params = [];
        if (isset(self::STATUSES[$status])) {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        if (isset(self::BODY_TYPES[$body])) {
            $where[] = 'c.body_type = ?';
            $params[] = $body;
        }
        return Db::all(
            'SELECT ' . self::CARD_COLUMNS . ' FROM cars c WHERE ' . implode(' AND ', $where)
            . " ORDER BY FIELD(c.status, 'available', 'reserved', 'sold'), c.published_at DESC, c.id DESC LIMIT 200",
            $params
        );
    }

    /** Featured available cars for the home page; newest available when none are featured. @return list<array<string, mixed>> */
    public static function featured(int $limit = 6): array
    {
        $rows = Db::all('SELECT ' . self::CARD_COLUMNS . " FROM cars c WHERE c.published = 1 AND c.status = 'available' AND c.featured = 1 ORDER BY c.published_at DESC LIMIT " . $limit);
        if ($rows === []) {
            $rows = Db::all('SELECT ' . self::CARD_COLUMNS . " FROM cars c WHERE c.published = 1 AND c.status = 'available' ORDER BY c.published_at DESC LIMIT " . $limit);
        }
        return $rows;
    }

    public static function anyPublished(): bool
    {
        return Db::value('SELECT 1 FROM cars WHERE published = 1 LIMIT 1') !== null;
    }

    /** @return array<string, mixed>|null */
    public static function findPublished(string $reference): ?array
    {
        return Db::one('SELECT * FROM cars WHERE reference = ? AND published = 1', [$reference]);
    }

    /** @return list<array<string, mixed>> */
    public static function photos(int $carId): array
    {
        return Db::all('SELECT id, width, height, is_main FROM car_photos WHERE car_id = ? ORDER BY is_main DESC, sort, id', [$carId]);
    }

    public static function title(array $car): string
    {
        return trim(($car['year'] ?? '') . ' ' . $car['make'] . ' ' . $car['model'] . (empty($car['trim_level']) ? '' : ' ' . $car['trim_level']));
    }

    /** Price exactly as staff entered it; never calculated or converted (DESIGN rule 15). */
    public static function price(array $car): string
    {
        if ($car['price_amount'] === null || $car['price_amount'] === '') {
            return 'Price on request';
        }
        $amount = (float) $car['price_amount'];
        $decimals = floor($amount) === $amount ? 0 : 2;
        return $car['currency'] . ' ' . number_format($amount, $decimals);
    }

    public static function mileage(array $car): string
    {
        if ($car['mileage'] === null || $car['mileage'] === '') {
            return '';
        }
        return number_format((int) $car['mileage']) . ' ' . ($car['mileage_unit'] === 'km' ? 'km' : 'miles');
    }
}
