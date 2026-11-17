<?php
declare(strict_types=1);

namespace Tests\Http;

use App\Core\Config;
use App\Core\Crypto;
use App\Core\Db;
use App\Core\Tokens;
use App\Core\Totp;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end checks against a running server (GATE-002 evidence for CTL-AUTHZ-001/002, CTL-SES-001,
 * CTL-INP-001, CTL-DATA-002, CTL-CFG-001). Needs BASE_URL and ATLANTIC_CONFIG pointing at the same
 * database the server uses. Skipped otherwise. Uses synthetic data only.
 */
final class AccessTest extends TestCase
{
    private static string $base = '';
    private string $jar = '';

    public static function setUpBeforeClass(): void
    {
        self::$base = rtrim((string) getenv('BASE_URL'), '/');
        if (self::$base === '' || !getenv('ATLANTIC_CONFIG')) {
            return;
        }
        Config::load(dirname(__DIR__, 2));
        Db::run('DELETE FROM rate_limits');
        Db::run("DELETE FROM staff WHERE email LIKE '%@test.invalid'");
        Db::run("DELETE FROM shipments WHERE customer_name = 'Test Customer'");
    }

    protected function setUp(): void
    {
        if (self::$base === '' || !getenv('ATLANTIC_CONFIG')) {
            $this->markTestSkipped('BASE_URL and ATLANTIC_CONFIG not set');
        }
        $this->jar = tempnam(sys_get_temp_dir(), 'jar') ?: '';
    }

    protected function tearDown(): void
    {
        if ($this->jar !== '') {
            @unlink($this->jar);
        }
    }

    /** @return array{code: int, body: string, headers: string, location: string} */
    private function req(string $method, string $path, array $form = []): array
    {
        $ch = curl_init(self::$base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hsize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $headers = substr($raw, 0, $hsize);
        preg_match('/^Location:\s*(\S+)/mi', $headers, $m);
        return ['code' => $code, 'body' => substr($raw, $hsize), 'headers' => $headers, 'location' => $m[1] ?? ''];
    }

    private function csrf(string $body): string
    {
        $this->assertSame(1, preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $body, $m), 'csrf field present');
        return $m[1];
    }

    private function started(): string
    {
        $t = (string) (time() - 30);
        return $t . '.' . substr(hash_hmac('sha256', $t, Config::str('app.key')), 0, 16);
    }

    public function testSecurityHeadersOnPublicPages(): void
    {
        $r = $this->req('GET', '/');
        $this->assertSame(200, $r['code']);
        $this->assertMatchesRegularExpression("/Content-Security-Policy: default-src 'self'; script-src 'self'/i", $r['headers']);
        $this->assertStringContainsStringIgnoringCase('X-Content-Type-Options: nosniff', $r['headers']);
        $this->assertStringContainsStringIgnoringCase("frame-ancestors 'none'", $r['headers']);
        $this->assertDoesNotMatchRegularExpression('#<script[^>]+src="https?://#', $r['body'], 'no third-party scripts');
        $this->assertStringNotContainsString(' style="', $r['body'], 'no inline style attributes (CSP)');
    }

    public function testAdminRoutesRequireSignIn(): void
    {
        foreach (['/admin/inbox', '/admin/shipments', '/admin/shipments/1', '/admin/staff', '/admin/audit', '/admin/photos/1'] as $p) {
            $r = $this->req('GET', $p);
            $this->assertSame(303, $r['code'], $p);
            $this->assertSame('/admin/login', $r['location'], $p);
        }
        $r = $this->req('POST', '/admin/shipments/new', ['customer_name' => 'x']);
        $this->assertContains($r['code'], [303, 419]);
    }

    public function testFormsRejectMissingCsrf(): void
    {
        $this->assertSame(419, $this->req('POST', '/quote', ['name' => 'x'])['code']);
        $this->assertSame(419, $this->req('POST', '/track', ['reference' => 'x'])['code']);
        $this->assertSame(419, $this->req('POST', '/admin/login', ['email' => 'x'])['code']);
    }

    public function testQuoteIsSavedAndEscaped(): void
    {
        $page = $this->req('GET', '/quote');
        $name = '<script>alert(1)</script> Ama';
        $r = $this->req('POST', '/quote', [
            '_csrf' => $this->csrf($page['body']), 'started' => $this->started(), 'website' => '',
            'name' => $name, 'phone' => '024 123 4567', 'email' => '', 'services' => ['shipping', 'clearance'],
            'year' => '2019', 'make' => 'Toyota', 'model' => "RAV4'; DROP TABLE enquiries;--", 'source' => 'auction',
            'listing_url' => '', 'notes' => '',
        ]);
        $this->assertSame(303, $r['code']);
        $this->assertSame('/thanks', $r['location']);
        $row = Db::one('SELECT * FROM enquiries WHERE name = ? ORDER BY id DESC LIMIT 1', [$name]);
        $this->assertNotNull($row, 'stored verbatim, not executed');
        $this->assertSame('+233241234567', $row['phone']);
        $thanks = $this->req('GET', '/thanks');
        $this->assertStringContainsString((string) $row['reference'], $thanks['body']);
        Db::run('DELETE FROM enquiries WHERE id = ?', [$row['id']]);
    }

    public function testHoneypotIsDroppedSilently(): void
    {
        $page = $this->req('GET', '/quote');
        $before = (int) Db::value('SELECT COUNT(*) FROM enquiries');
        $r = $this->req('POST', '/quote', ['_csrf' => $this->csrf($page['body']), 'started' => $this->started(), 'website' => 'spam.example', 'name' => 'Bot']);
        $this->assertSame(303, $r['code']);
        $this->assertSame($before, (int) Db::value('SELECT COUNT(*) FROM enquiries'));
    }

    public function testTrackingAccessRules(): void
    {
        $token = Tokens::trackingToken();
        $ref = Tokens::reference();
        $id = Db::insert('shipments', [
            'reference' => $ref, 'token_hash' => Tokens::hash($token), 'token_created_at' => Db::now(),
            'customer_name' => 'Test Customer', 'phone' => '+233209876543', 'phone_last4' => '6543', 'email' => 'secret@test.invalid',
            'vehicle_year' => 2018, 'vehicle_make' => 'Honda', 'vehicle_model' => 'Accord', 'vin' => '1HGCM82633A004352',
            'stage' => 'shipped', 'created_at' => Db::now(), 'updated_at' => Db::now(),
        ]);
        Db::insert('shipment_updates', ['shipment_id' => $id, 'stage' => 'shipped', 'note' => 'Internal margin note', 'is_public' => 0, 'created_at' => Db::now()]);

        // Wrong digits, VIN instead of reference, garbage: same generic answer.
        $form = $this->req('GET', '/track');
        $csrf = $this->csrf($form['body']);
        foreach ([[$ref, '0000'], ['1HGCM82633A004352', '6543'], ['ZZZZZZZZ', '6543']] as [$r1, $d]) {
            $r = $this->req('POST', '/track', ['_csrf' => $csrf, 'reference' => $r1, 'phone_last4' => $d]);
            $this->assertSame(404, $r['code']);
            $this->assertStringContainsString('We could not find a vehicle with those details', $r['body']);
        }
        $this->assertSame(303, $this->req('GET', '/track/view')['code'], 'no access without a grant');

        // Correct reference + digits.
        $r = $this->req('POST', '/track', ['_csrf' => $csrf, 'reference' => strtolower($ref), 'phone_last4' => '6543']);
        $this->assertSame('/track/view', $r['location']);
        $view = $this->req('GET', '/track/view');
        $this->assertSame(200, $view['code']);
        $this->assertStringContainsString('004352', $view['body']);
        $this->assertStringNotContainsString('1HGCM82633A004352', $view['body'], 'VIN masked');
        $this->assertStringNotContainsString('209876543', $view['body'], 'phone hidden');
        $this->assertStringNotContainsString('secret@test.invalid', $view['body'], 'email hidden');
        $this->assertStringNotContainsString('Test Customer', $view['body'], 'name hidden');
        $this->assertStringNotContainsString('Internal margin note', $view['body'], 'internal notes hidden');
        $this->assertStringContainsStringIgnoringCase('Cache-Control: no-store', $view['headers']);

        // Private link works in a fresh session; a regenerated or revoked link does not.
        $this->jar = tempnam(sys_get_temp_dir(), 'jar') ?: '';
        $this->assertSame('/track/view', $this->req('GET', '/t/' . $token)['location']);
        Db::run('UPDATE shipments SET token_hash = NULL, link_disabled_at = ? WHERE id = ?', [Db::now(), $id]);
        $this->jar = tempnam(sys_get_temp_dir(), 'jar') ?: '';
        $this->assertSame(404, $this->req('GET', '/t/' . $token)['code']);
        $this->assertSame(404, $this->req('GET', '/t/not-a-real-token')['code']);
        Db::run('DELETE FROM shipments WHERE id = ?', [$id]);
    }

    public function testStaffCannotUseAdminOnlyActions(): void
    {
        $secret = Totp::newSecret();
        $password = 'harbour lamp blue 2026';
        $sid = Db::insert('staff', [
            'email' => 'staff@test.invalid', 'name' => 'Test Staff', 'role' => 'staff',
            'password_hash' => password_hash($password, PASSWORD_BCRYPT), 'must_change_password' => 0,
            'totp_secret_enc' => Crypto::encrypt($secret), 'active' => 1, 'failed_logins' => 0,
            'created_at' => Db::now(), 'updated_at' => Db::now(),
        ]);
        $login = $this->req('GET', '/admin/login');
        $r = $this->req('POST', '/admin/login', ['_csrf' => $this->csrf($login['body']), 'email' => 'staff@test.invalid', 'password' => 'wrong password here']);
        $this->assertSame(401, $r['code']);
        $this->assertStringContainsString('do not match a staff account', $r['body']);

        $r = $this->req('POST', '/admin/login', ['_csrf' => $this->csrf($r['body']), 'email' => 'staff@test.invalid', 'password' => $password]);
        $this->assertSame('/admin/mfa', $r['location']);
        $this->assertSame(303, $this->req('GET', '/admin/inbox')['code'], 'password alone is not enough');

        $mfa = $this->req('GET', '/admin/mfa');
        $r = $this->req('POST', '/admin/mfa', ['_csrf' => $this->csrf($mfa['body']), 'code' => Totp::code($secret, intdiv(time(), 30))]);
        $this->assertSame('/admin/inbox', $r['location']);

        $inbox = $this->req('GET', '/admin/inbox');
        $this->assertSame(200, $inbox['code']);
        $csrf = $this->csrf($inbox['body']);
        $this->assertSame(403, $this->req('GET', '/admin/staff')['code']);
        $this->assertSame(403, $this->req('GET', '/admin/audit')['code']);
        $this->assertSame(403, $this->req('POST', '/admin/staff', ['_csrf' => $csrf, 'name' => 'X', 'email' => 'x@test.invalid', 'role' => 'admin'])['code']);
        $this->assertSame(403, $this->req('POST', '/admin/enquiries/1/delete', ['_csrf' => $csrf, 'confirm' => 'DELETE'])['code']);
        $this->assertSame(419, $this->req('POST', '/admin/shipments/new', ['customer_name' => 'x'])['code'], 'CSRF enforced when signed in');

        $cookies = (string) file_get_contents($this->jar);
        $this->assertStringContainsString('aa_admin', $cookies);
        $this->assertStringContainsString('#HttpOnly_', $cookies, 'session cookie is HttpOnly');

        Db::run('DELETE FROM staff WHERE id = ?', [$sid]);
        Db::run('DELETE FROM audit_log WHERE staff_id = ?', [$sid]);
    }

    public function testLockoutAfterFiveFailures(): void
    {
        $sid = Db::insert('staff', [
            'email' => 'lock@test.invalid', 'name' => 'Lock Test', 'role' => 'staff', 'password_hash' => password_hash('correct horse battery', PASSWORD_BCRYPT),
            'must_change_password' => 0, 'active' => 1, 'failed_logins' => 0, 'created_at' => Db::now(), 'updated_at' => Db::now(),
        ]);
        $page = $this->req('GET', '/admin/login');
        $csrf = $this->csrf($page['body']);
        for ($i = 0; $i < 5; $i++) {
            $r = $this->req('POST', '/admin/login', ['_csrf' => $csrf, 'email' => 'lock@test.invalid', 'password' => 'nope nope nope ' . $i]);
        }
        $this->assertStringContainsString('locked', $r['body']);
        $r = $this->req('POST', '/admin/login', ['_csrf' => $csrf, 'email' => 'lock@test.invalid', 'password' => 'correct horse battery']);
        $this->assertStringContainsString('locked', $r['body'], 'right password still refused while locked');
        Db::run('DELETE FROM staff WHERE id = ?', [$sid]);
        Db::run('DELETE FROM rate_limits');
    }
}
