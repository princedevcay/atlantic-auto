<?php
declare(strict_types=1);

// Scheduled jobs. cPanel cron examples (times are server time):
//   */5 * * * *  php ~/atlantic-auto/bin/cron.php mail        retry queued emails
//   15 * * * *   php ~/atlantic-auto/bin/cron.php carrier     carrier sync (no-op until DEC-005)
//   30 2 * * *   php ~/atlantic-auto/bin/cron.php backup      nightly database + uploads backup (CTL-REC-001)
//   45 3 * * *   php ~/atlantic-auto/bin/cron.php retention   retention deletes and housekeeping (CTL-DATA-001)
//   0 8 * * *    php ~/atlantic-auto/bin/cron.php health      alert on stuck mail or stale carrier sync

use App\Core\Auth;
use App\Core\Config;
use App\Core\Db;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Uploads;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

$task = $argv[1] ?? '';
$started = microtime(true);

switch ($task) {
    case 'mail':
        $n = Mailer::retryDue();
        echo "sent {$n}\n";
        break;

    case 'carrier':
        // DEC-005: no provider chosen yet. When one is, add a client that calls only
        // carrier.allowed_hosts over HTTPS with timeouts (CTL-API-002) and record the change.
        if (Config::get('carrier.provider') === null) {
            echo "no carrier provider configured\n";
            break;
        }
        echo "carrier provider not implemented\n";
        break;

    case 'backup':
        $dir = rtrim(Config::str('paths.backups'), '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $stamp = gmdate('Ymd-His');
        $file = "{$dir}/db-{$stamp}.sql.gz";
        $gz = gzopen($file, 'wb6');
        // Tables are written alphabetically, so a child table can come before the table it references;
        // foreign key checks are off while restoring and back on at the end.
        gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
        $tables = array_map(fn ($r) => array_values($r)[0], Db::all('SHOW TABLES'));
        foreach ($tables as $t) {
            if (!preg_match('/^[a-z_]+$/', (string) $t)) {
                continue;
            }
            $create = Db::one("SHOW CREATE TABLE `{$t}`");
            gzwrite($gz, "DROP TABLE IF EXISTS `{$t}`;\n" . array_values($create)[1] . ";\n");
            $stmt = Db::run("SELECT * FROM `{$t}`");
            while ($row = $stmt->fetch()) {
                $vals = array_map(fn ($v) => $v === null ? 'NULL' : Db::pdo()->quote((string) $v), $row);
                gzwrite($gz, "INSERT INTO `{$t}` VALUES (" . implode(',', $vals) . ");\n");
            }
        }
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
        chmod($file, 0600);
        // Uploaded photos
        $uploads = Uploads::dir();
        $tar = "{$dir}/uploads-{$stamp}.tar";
        $phar = new PharData($tar);
        $phar->buildFromDirectory($uploads);
        $phar->compress(Phar::GZ);
        unset($phar);
        @unlink($tar);
        @chmod($tar . '.gz', 0600);
        // Keep 14 days on the host
        foreach (glob("{$dir}/{db,uploads}-*.gz", GLOB_BRACE) ?: [] as $old) {
            if (filemtime($old) < time() - 14 * 86400) {
                unlink($old);
            }
        }
        Log::info('backup done', ['file' => basename($file)]);
        echo 'backup ' . basename($file) . "\n";
        break;

    case 'retention':
        // DEC-004 proposed periods; confirm with the client and adjust in config.php.
        $enqMonths = (int) Config::get('retention.enquiry_months', 12);
        $linkDays = (int) Config::get('retention.link_days_after_delivery', 90);
        $auditMonths = (int) Config::get('retention.audit_months', 12);
        $a = Db::run('DELETE FROM enquiries WHERE shipment_id IS NULL AND last_activity_at < ?', [gmdate('Y-m-d H:i:s', strtotime("-{$enqMonths} months"))])->rowCount();
        $b = Db::run("UPDATE shipments SET token_hash = NULL, link_disabled_at = ? WHERE stage = 'delivered' AND link_disabled_at IS NULL AND delivered_at < ?", [Db::now(), gmdate('Y-m-d H:i:s', time() - $linkDays * 86400)])->rowCount();
        $c = Db::run('DELETE FROM audit_log WHERE created_at < ?', [gmdate('Y-m-d H:i:s', strtotime("-{$auditMonths} months"))])->rowCount();
        $d = Db::run('DELETE FROM mail_queue WHERE created_at < ? AND (sent_at IS NOT NULL OR attempts >= 12)', [gmdate('Y-m-d H:i:s', strtotime('-30 days'))])->rowCount();
        $e = RateLimit::prune();
        foreach (glob(rtrim(Config::str('paths.storage'), '/') . '/logs/app-*.log') ?: [] as $log) {
            if (filemtime($log) < strtotime('-12 months')) {
                unlink($log);
            }
        }
        Log::info('retention run', ['enquiries' => $a, 'links_disabled' => $b, 'audit' => $c, 'mail' => $d, 'limits' => $e]);
        echo "enquiries deleted {$a}, links disabled {$b}, audit rows {$c}, mail rows {$d}\n";
        break;

    case 'health':
        $stuck = (int) Db::value('SELECT COUNT(*) FROM mail_queue WHERE sent_at IS NULL AND created_at < ?', [gmdate('Y-m-d H:i:s', time() - 3600)]);
        if ($stuck > 0) {
            Log::error('emails stuck in queue', ['count' => $stuck]);
            Auth::alert('Emails not sending', "{$stuck} notification emails have not been sent for over an hour. Check the SMTP settings.");
        }
        $locks = (int) Db::value("SELECT COUNT(*) FROM audit_log WHERE action = 'account_locked' AND created_at > ?", [gmdate('Y-m-d H:i:s', time() - 86400)]);
        echo "stuck mail {$stuck}, lockouts in 24h {$locks}\n";
        break;

    default:
        fwrite(STDERR, "Usage: php bin/cron.php mail|carrier|backup|retention|health\n");
        exit(1);
}
Log::info('cron ' . $task, ['ms' => (int) ((microtime(true) - $started) * 1000)]);
