<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/helpers.php';

/** Base config for unit tests; tests that change settings layer theirs on top of this. */
function test_config(array $extra = []): array
{
    return array_replace_recursive([
        'app' => ['env' => 'local', 'key' => base64_encode(str_repeat("\x01", 32)), 'url' => 'http://localhost'],
        'paths' => ['storage' => sys_get_temp_dir() . '/atlantic-test'],
    ], $extra);
}

App\Core\Config::set(test_config());
