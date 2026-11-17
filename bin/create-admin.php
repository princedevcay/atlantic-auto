<?php
declare(strict_types=1);

// Creates the first Admin account. CLI only: php bin/create-admin.php "Full Name" email@example.com
// Prints a temporary password once; the Admin must change it and set up an authenticator at first sign-in.

use App\Core\Auth;
use App\Core\Db;
use App\Core\Tokens;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

[$name, $email] = [$argv[1] ?? '', strtolower($argv[2] ?? '')];
if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Usage: php bin/create-admin.php \"Full Name\" email@example.com\n");
    exit(1);
}
if (Db::value('SELECT id FROM staff WHERE email = ?', [$email]) !== null) {
    fwrite(STDERR, "A staff account with that email already exists.\n");
    exit(1);
}
$temp = implode('-', array_map(fn () => strtolower(Tokens::reference()), range(1, 4)));
$id = Db::insert('staff', [
    'email' => $email, 'name' => $name, 'role' => 'admin', 'password_hash' => Auth::hashPassword($temp),
    'must_change_password' => 1, 'active' => 1, 'failed_logins' => 0, 'created_at' => Db::now(), 'updated_at' => Db::now(),
]);
App\Core\Audit::log('staff_created', 'staff', $id, 'admin via CLI', null);
echo "Admin created (id {$id}). Temporary password, shown once:\n\n  {$temp}\n\nSign in at /admin/login, then change it and set up the authenticator.\n";
