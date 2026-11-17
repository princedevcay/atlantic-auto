<?php
declare(strict_types=1);

// Applies database/NNN_*.sql files in order. Run on the server: php bin/migrate.php
// Take a backup first (php bin/cron.php backup). CLI only.

use App\Core\Db;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = Db::pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (name VARCHAR(100) PRIMARY KEY, applied_at DATETIME NOT NULL) ENGINE=InnoDB');
$done = array_column(Db::all('SELECT name FROM schema_migrations'), 'name');
$files = glob(dirname(__DIR__) . '/database/[0-9][0-9][0-9]_*.sql') ?: [];
sort($files);
$applied = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $done, true)) {
        continue;
    }
    $sql = (string) file_get_contents($file);
    $sql = (string) preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
    Db::run('INSERT INTO schema_migrations (name, applied_at) VALUES (?, ?)', [$name, Db::now()]);
    echo "applied {$name}\n";
    $applied++;
}
echo $applied === 0 ? "database is up to date\n" : "done\n";
