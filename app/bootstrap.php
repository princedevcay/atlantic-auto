<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Http;
use App\Core\Log;

$appRoot = dirname(__DIR__);
require $appRoot . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';

Config::load($appRoot);

date_default_timezone_set('UTC');
ini_set('display_errors', Config::isLocal() ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

// Fail closed with a plain page; details only go to the log (CTL-ERR-001).
set_exception_handler(function (\Throwable $e): void {
    Log::error('unhandled exception', [
        'type' => $e::class,
        'msg' => mb_substr($e->getMessage(), 0, 300),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'path' => PHP_SAPI === 'cli' ? 'cli' : Http::path(),
    ]);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
        Http::securityHeaders(true);
    }
    try {
        App\Core\View::render('pages/error', ['title' => 'Something went wrong', 'message' => 'Please try again in a moment. If it keeps happening, contact us by phone or WhatsApp.', 'code' => 500]);
    } catch (\Throwable) {
        echo 'Something went wrong. Please try again in a moment.';
    }
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
