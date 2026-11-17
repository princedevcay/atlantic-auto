<?php
declare(strict_types=1);

namespace App\Core;

/** Fixed-window counters in MySQL (CTL-API-001, CTL-AUTH-002). */
final class RateLimit
{
    /** Records a hit and returns true while the key is within its limit. */
    public static function hit(string $key, int $max, int $windowSeconds): bool
    {
        $window = intdiv(time(), $windowSeconds) * $windowSeconds;
        $key = substr($key, 0, 120);
        Db::run(
            'INSERT INTO rate_limits (rkey, window_start, hits) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE hits = hits + 1',
            [$key, $window]
        );
        $hits = (int) Db::value('SELECT hits FROM rate_limits WHERE rkey = ? AND window_start = ?', [$key, $window]);
        return $hits <= $max;
    }

    /** Checks without recording. */
    public static function exceeded(string $key, int $max, int $windowSeconds): bool
    {
        $window = intdiv(time(), $windowSeconds) * $windowSeconds;
        $hits = (int) Db::value('SELECT hits FROM rate_limits WHERE rkey = ? AND window_start = ?', [substr($key, 0, 120), $window]);
        return $hits >= $max;
    }

    public static function prune(): int
    {
        return Db::run('DELETE FROM rate_limits WHERE window_start < ?', [time() - 2 * 86400])->rowCount();
    }
}
