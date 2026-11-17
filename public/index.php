<?php
declare(strict_types=1);

// Only this folder is web-reachable. The app, vendor and config live outside it (CTL-CFG-001).
// APP_ROOT points at the deployed app folder; on cPanel set it in public/.app-root if public/
// is synced into public_html separately (see ARCHITECTURE.md, DEC-006).
$rootFile = __DIR__ . '/.app-root';
$appRoot = is_file($rootFile) ? trim((string) file_get_contents($rootFile)) : dirname(__DIR__);

require $appRoot . '/app/bootstrap.php';
require $appRoot . '/app/routes.php';
