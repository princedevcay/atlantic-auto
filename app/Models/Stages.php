<?php
declare(strict_types=1);

namespace App\Models;

/** The six tracking stages (PG-021). */
final class Stages
{
    public const LABELS = [
        'sourcing' => 'Sourcing',
        'purchased' => 'Purchased',
        'picked_up' => 'Picked up',
        'shipped' => 'Shipped',
        'clearing' => 'Clearing',
        'delivered' => 'Delivered',
    ];

    public const DESCRIPTIONS = [
        'sourcing' => 'We are finding the right vehicle for you in the USA.',
        'purchased' => 'The vehicle has been bought and paperwork is in progress.',
        'picked_up' => 'The vehicle has been collected and is going to the US port.',
        'shipped' => 'The vehicle is on the ship to Tema.',
        'clearing' => 'The vehicle has arrived and is being cleared at Tema port.',
        'delivered' => 'The vehicle has been handed over.',
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }

    public static function index(string $stage): int
    {
        $i = array_search($stage, self::keys(), true);
        return $i === false ? 0 : (int) $i;
    }

    public static function valid(string $stage): bool
    {
        return isset(self::LABELS[$stage]);
    }
}
