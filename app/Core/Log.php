<?php
declare(strict_types=1);

namespace App\Core;

/** File log outside the web root. Never pass passwords, tokens or full contact details (CTL-LOG-001). */
final class Log
{
    /** @param array<string, scalar|null> $context */
    public static function write(string $level, string $message, array $context = []): void
    {
        $dir = rtrim(Config::str('paths.storage'), '/') . '/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        $line = json_encode([
            't' => gmdate('c'),
            'level' => $level,
            'msg' => $message,
            'ctx' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        @file_put_contents($dir . '/app-' . gmdate('Y-m') . '.log', $line . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }
}
