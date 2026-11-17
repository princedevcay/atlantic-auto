<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/helpers.php';

App\Core\Config::set([
    'app' => ['env' => 'local', 'key' => base64_encode(str_repeat("\x01", 32)), 'url' => 'http://localhost'],
    'paths' => ['storage' => sys_get_temp_dir() . '/atlantic-test'],
]);
