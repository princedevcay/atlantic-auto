<?php
// Copy to config.php OUTSIDE the web root and fill in real values. Never commit config.php.
// Production: ~/atlantic-auto-private/config.php   Local development: private/config.php
// Leave a contact value empty to hide it on the site; never ship placeholders.

return [
    'app' => [
        'env' => 'production',            // production | local
        'url' => 'https://example.com',   // public base URL, no trailing slash
        'name' => 'Atlantic Auto and Shipping Services',
        // 32 random bytes, base64. Generate with: php -r 'echo base64_encode(random_bytes(32)), "\n";'
        // Encrypts staff TOTP secrets. Rotating it requires staff to re-enrol MFA.
        'key' => '',
    ],
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=CPANELUSER_atlantic;charset=utf8mb4',
        'user' => '',
        'pass' => '',
    ],
    'mail' => [
        'host' => 'mail.example.com',
        'port' => 465,
        'encryption' => 'ssl',            // ssl (465) or tls (587)
        'user' => '',
        'pass' => '',
        'from' => 'no-reply@example.com',
        'from_name' => 'Atlantic Auto website',
        'inbox' => '',                    // the shared inbox that receives both request types
        'alerts' => '',                   // developer address for security alerts
    ],
    'contact' => [
        'phone' => '',                    // +233 format, e.g. +233 XX XXX XXXX
        'whatsapp' => '',                 // digits only with country code, e.g. 233XXXXXXXXX
        'email' => '',
        'address' => '',
        'hours' => '',
        'map_url' => '',
    ],
    'features' => [
        // Customer tracking pages (/track, private links). Off for now: tracking is for the team (client, 18 Nov 2026).
        'customer_tracking' => false,
    ],
    'chat' => [
        // When the chat shows "Online" (green), in Africa/Accra time. Confirm with the client.
        // Format: day => 'HH:MM-HH:MM'; days missing from the list are offline. Empty array = always offline.
        'online_hours' => [],
    ],
    'carrier' => [
        'provider' => null,               // null until DEC-005 picks a provider
        'base_url' => '',
        'api_key' => '',
        'allowed_hosts' => [],            // exact hostnames the sync may call
    ],
    'paths' => [
        // Absolute paths outside the web root.
        'storage' => '/home/CPANELUSER/atlantic-auto-private/storage',
        'backups' => '/home/CPANELUSER/atlantic-auto-private/backups',
    ],
    'security' => [
        'hsts' => false,                  // turn on after launch checks pass (CTL-CRY-001)
    ],
];
