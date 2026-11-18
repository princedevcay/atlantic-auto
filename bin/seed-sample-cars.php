<?php
declare(strict_types=1);

// Sample car listings for demonstration (owner's request, 18 Nov 2026). Uses the vehicle photos in
// public/assets/img/site/ and reference prices from US dealer asking prices in November 2026.
// Samples show a "Sample" badge and take no enquiries. Remove them before launch:
//   php bin/seed-sample-cars.php          add the samples (skips if they exist)
//   php bin/seed-sample-cars.php --remove delete every sample listing and its photos

use App\Core\Db;
use App\Core\Tokens;
use App\Core\Uploads;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

if (in_array('--remove', $argv, true)) {
    $files = array_column(Db::all('SELECT p.file_name FROM car_photos p JOIN cars c ON c.id = p.car_id WHERE c.sample = 1'), 'file_name');
    $n = Db::run('DELETE FROM cars WHERE sample = 1')->rowCount();
    foreach ($files as $f) {
        Uploads::delete((string) $f);
    }
    echo "removed {$n} sample listing(s)\n";
    exit;
}
if ((int) Db::value('SELECT COUNT(*) FROM cars WHERE sample = 1') > 0) {
    echo "samples already present; run with --remove first to recreate\n";
    exit;
}

$samples = [
    [
        'photo' => 'type-saloon.webp', 'year' => 2020, 'make' => 'Toyota', 'model' => 'Camry', 'trim_level' => 'LE', 'body_type' => 'saloon',
        'mileage' => 62000, 'transmission' => 'automatic', 'fuel' => 'petrol', 'engine' => '2.5L 4-cylinder', 'colour' => 'Silver',
        'location' => 'ghana', 'price_amount' => '19900.00', 'status' => 'available', 'featured' => 1,
        'description' => "Comfortable, economical family saloon with Toyota's reputation for reliability. Smooth 8-speed automatic, Toyota Safety Sense with lane departure alert and adaptive cruise control, 7-inch touchscreen with Apple CarPlay and Android Auto, and a roomy back seat and boot. Already cleared and in Ghana.",
    ],
    [
        'photo' => 'type-suv.webp', 'year' => 2021, 'make' => 'Honda', 'model' => 'CR-V', 'trim_level' => 'EX', 'body_type' => 'suv',
        'mileage' => 58000, 'transmission' => 'automatic', 'fuel' => 'petrol', 'engine' => '1.5L turbo 4-cylinder', 'colour' => 'Charcoal grey',
        'location' => 'transit', 'price_amount' => '24500.00', 'status' => 'available', 'featured' => 1,
        'description' => "Practical compact SUV with a turbocharged engine, continuously variable transmission and Honda Sensing safety features. The EX adds a power moonroof, heated front seats, dual-zone climate control, blind spot monitoring and a 7-inch touchscreen with Apple CarPlay and Android Auto. Generous cargo space with a flat-folding rear seat. On the ship to Tema.",
    ],
    [
        'photo' => 'type-pickup.webp', 'year' => 2024, 'make' => 'Ford', 'model' => 'Ranger', 'trim_level' => 'XLT SuperCrew', 'body_type' => 'pickup',
        'mileage' => 21000, 'transmission' => 'automatic', 'fuel' => 'petrol', 'engine' => '2.3L EcoBoost turbo 4-cylinder', 'colour' => 'Blue',
        'location' => 'usa', 'price_amount' => '36900.00', 'status' => 'available', 'featured' => 1,
        'description' => "The new-generation Ranger double cab: strong 2.3L EcoBoost with a 10-speed automatic, a 5-foot bed and up to 7,500 lb towing when properly equipped. The XLT brings LED lighting, a large touchscreen with SYNC 4, smartphone integration and Ford Co-Pilot360 driver assistance. Low mileage. Currently in the USA, ready to ship.",
    ],
    [
        'photo' => 'type-van.webp', 'year' => 2021, 'make' => 'Mercedes-Benz', 'model' => 'Sprinter', 'trim_level' => '2500 Cargo 144" WB', 'body_type' => 'van',
        'mileage' => 48000, 'transmission' => 'automatic', 'fuel' => 'diesel', 'engine' => '3.0L V6 turbodiesel', 'colour' => 'White',
        'location' => 'usa', 'price_amount' => '37400.00', 'status' => 'reserved', 'featured' => 0,
        'description' => "Standard-roof cargo van on the 144-inch wheelbase, a favourite for deliveries and trade work. Efficient turbodiesel with a 9-speed automatic, a wide sliding side door and rear doors that open wide for easy loading, and an empty cargo area ready for shelving or racks. Reserved for a customer.",
    ],
    [
        'photo' => 'type-truck.webp', 'year' => 2020, 'make' => 'Isuzu', 'model' => 'NPR-HD', 'trim_level' => '16 ft box truck', 'body_type' => 'truck',
        'mileage' => 80000, 'transmission' => 'automatic', 'fuel' => 'petrol', 'engine' => '6.0L V8', 'colour' => 'White',
        'location' => 'ghana', 'price_amount' => '39900.00', 'status' => 'sold', 'featured' => 0,
        'description' => "Low-cab-forward box truck with a 16-foot dry van body, ideal for city distribution: excellent visibility, a tight turning circle and easy loading. Gas V8 with a 6-speed automatic. Sold.",
    ],
];

$photoDir = dirname(__DIR__) . '/public/assets/img/site/';
foreach ($samples as $i => $c) {
    $photo = $c['photo'];
    unset($c['photo']);
    $id = Db::insert('cars', $c + [
        'reference' => Tokens::reference(), 'mileage_unit' => 'mi', 'currency' => 'USD', 'published' => 1, 'sample' => 1,
        'created_at' => Db::now(), 'updated_at' => Db::now(), 'published_at' => gmdate('Y-m-d H:i:s', time() - $i * 3600),
    ]);
    $img = Uploads::importFile($photoDir . $photo);
    if (is_array($img)) {
        Db::insert('car_photos', ['car_id' => $id, 'file_name' => $img['name'], 'width' => $img['width'], 'height' => $img['height'], 'is_main' => 1, 'sort' => 0, 'created_at' => Db::now()]);
    }
    echo "added sample: {$c['year']} {$c['make']} {$c['model']}\n";
}
echo "done. Remove before launch: php bin/seed-sample-cars.php --remove\n";
