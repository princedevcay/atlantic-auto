<?php
declare(strict_types=1);

namespace Tests\Http;

use App\Core\Config;
use App\Core\Crypto;
use App\Core\Db;
use App\Core\Tokens;
use App\Core\Totp;
use PHPUnit\Framework\TestCase;

/** Cars for sale access rules (CTL-AUTHZ-003, GATE-002). Needs BASE_URL and ATLANTIC_CONFIG; synthetic data only. */
final class CarsTest extends TestCase
{
    private static string $base = '';
    private string $jar = '';
    /** @var list<int> */
    private array $cars = [];

    public static function setUpBeforeClass(): void
    {
        self::$base = rtrim((string) getenv('BASE_URL'), '/');
        if (self::$base !== '' && getenv('ATLANTIC_CONFIG')) {
            Config::load(dirname(__DIR__, 2));
            Db::run('DELETE FROM rate_limits');
        }
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
        foreach ($this->cars as $id) {
            Db::run('DELETE FROM enquiries WHERE car_id = ?', [$id]);
            Db::run('DELETE FROM cars WHERE id = ?', [$id]);
        }
        Db::run("DELETE FROM staff WHERE email LIKE '%@cars-test.invalid'");
        @unlink($this->jar);
    }

    /** @return array{code: int, body: string, location: string} */
    private function req(string $method, string $path, array $form = []): array
    {
        $ch = curl_init(self::$base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_CUSTOMREQUEST => $method]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $raw = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $h = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        preg_match('/^Location:\s*(\S+)/mi', substr($raw, 0, $h), $m);
        return ['code' => $code, 'body' => substr($raw, $h), 'location' => $m[1] ?? ''];
    }

    private function csrf(string $body): string
    {
        $this->assertSame(1, preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $body, $m));
        return $m[1];
    }

    private function started(): string
    {
        $t = (string) (time() - 30);
        return $t . '.' . substr(hash_hmac('sha256', $t, Config::str('app.key')), 0, 16);
    }

    /** @return array{id: int, ref: string, photo: int} */
    private function car(array $overrides): array
    {
        $ref = Tokens::reference();
        $id = Db::insert('cars', $overrides + [
            'reference' => $ref, 'year' => 2019, 'make' => 'Testmake', 'model' => 'Hiddenmodel', 'body_type' => 'suv', 'location' => 'usa',
            'price_amount' => '12345.00', 'currency' => 'USD', 'status' => 'available', 'featured' => 1, 'published' => 0, 'sample' => 0,
            'created_at' => Db::now(), 'updated_at' => Db::now(), 'published_at' => Db::now(),
        ]);
        $this->cars[] = $id;
        $file = (string) (Db::value('SELECT file_name FROM car_photos LIMIT 1') ?? str_repeat('a', 32) . '.jpg');
        $photo = Db::insert('car_photos', ['car_id' => $id, 'file_name' => $file, 'width' => 10, 'height' => 10, 'is_main' => 1, 'sort' => 0, 'created_at' => Db::now()]);
        return ['id' => $id, 'ref' => $ref, 'photo' => $photo];
    }

    public function testHiddenCarsAndPhotosAreNotReachable(): void
    {
        $c = $this->car(['published' => 0]);
        $this->assertSame(404, $this->req('GET', '/cars/' . $c['ref'])['code']);
        $this->assertSame(404, $this->req('GET', '/cars/photo/' . $c['photo'])['code']);
        $this->assertStringNotContainsString('Hiddenmodel', $this->req('GET', '/cars')['body']);
        $this->assertStringNotContainsString('Hiddenmodel', $this->req('GET', '/')['body'], 'featured but hidden must not show');

        Db::run('UPDATE cars SET published = 1 WHERE id = ?', [$c['id']]);
        $this->assertSame(200, $this->req('GET', '/cars/' . $c['ref'])['code']);
        $this->assertStringContainsString('Hiddenmodel', $this->req('GET', '/cars')['body']);
        $this->assertSame(404, $this->req('GET', '/cars/ZZZZZZZZ')['code']);
        $this->assertSame(404, $this->req('GET', '/cars/' . $c['id'])['code'], 'numeric ids are not references');
    }

    public function testEnquiriesOnlyForAvailableRealCars(): void
    {
        foreach ([['status' => 'sold'], ['status' => 'reserved'], ['sample' => 1]] as $o) {
            $c = $this->car($o + ['published' => 1]);
            $page = $this->req('GET', '/cars/' . $c['ref']);
            $this->assertStringNotContainsString('/enquire"', $page['body'], 'no enquiry form for ' . json_encode($o));
            $token = $this->csrf($this->req('GET', '/quote')['body']); // these pages have no form, so no token of their own
            $r = $this->req('POST', '/cars/' . $c['ref'] . '/enquire', ['_csrf' => $token, 'started' => $this->started(), 'website' => '', 'name' => 'Kofi Test', 'phone' => '0241234567']);
            $this->assertSame('/cars/' . $c['ref'], $r['location']);
            $this->assertSame(0, (int) Db::value('SELECT COUNT(*) FROM enquiries WHERE car_id = ?', [$c['id']]));
        }
        $c = $this->car(['published' => 1]);
        $page = $this->req('GET', '/cars/' . $c['ref']);
        $r = $this->req('POST', '/cars/' . $c['ref'] . '/enquire', ['_csrf' => $this->csrf($page['body']), 'started' => $this->started(), 'website' => '', 'name' => 'Kofi Test', 'phone' => '0241234567', 'message' => '<b>hi</b>']);
        $this->assertSame('/thanks', $r['location']);
        $row = Db::one("SELECT type, phone, message FROM enquiries WHERE car_id = ?", [$c['id']]);
        $this->assertSame('car', $row['type']);
        $this->assertSame('+233241234567', $row['phone']);
    }

    public function testVisitorsCannotChangeCars(): void
    {
        $c = $this->car(['published' => 1]);
        $this->assertSame(405, $this->req('POST', '/cars/' . $c['ref'], ['price_amount' => '1'])['code']);
        foreach (['/admin/cars', '/admin/cars/' . $c['id'], '/admin/car-photos/' . $c['photo']] as $p) {
            $this->assertSame('/admin/login', $this->req('GET', $p)['location'], $p);
        }
        $this->assertSame('12345.00', (string) Db::value('SELECT price_amount FROM cars WHERE id = ?', [$c['id']]));
    }

    public function testStaffCannotDeleteCars(): void
    {
        $c = $this->car(['published' => 1]);
        $secret = Totp::newSecret();
        Db::insert('staff', [
            'email' => 'staff@cars-test.invalid', 'name' => 'Cars Test', 'role' => 'staff', 'password_hash' => password_hash('harbour lamp blue 2026', PASSWORD_BCRYPT),
            'must_change_password' => 0, 'totp_secret_enc' => Crypto::encrypt($secret), 'active' => 1, 'failed_logins' => 0, 'created_at' => Db::now(), 'updated_at' => Db::now(),
        ]);
        $login = $this->req('GET', '/admin/login');
        $this->req('POST', '/admin/login', ['_csrf' => $this->csrf($login['body']), 'email' => 'staff@cars-test.invalid', 'password' => 'harbour lamp blue 2026']);
        $mfa = $this->req('GET', '/admin/mfa');
        $this->req('POST', '/admin/mfa', ['_csrf' => $this->csrf($mfa['body']), 'code' => Totp::code($secret, intdiv(time(), 30))]);
        $edit = $this->req('GET', '/admin/cars/' . $c['id']);
        $this->assertSame(200, $edit['code']);
        $csrf = $this->csrf($edit['body']);
        $this->assertSame(403, $this->req('POST', '/admin/cars/' . $c['id'] . '/delete', ['_csrf' => $csrf, 'confirm' => $c['ref']])['code']);
        $this->assertNotNull(Db::value('SELECT id FROM cars WHERE id = ?', [$c['id']]));
        // Staff can hide and the car disappears from the site at once.
        $this->req('POST', '/admin/cars/' . $c['id'] . '/publish', ['_csrf' => $csrf]);
        $this->assertSame(0, (int) Db::value('SELECT published FROM cars WHERE id = ?', [$c['id']]));
        $this->jar = tempnam(sys_get_temp_dir(), 'jar') ?: '';
        $this->assertSame(404, $this->req('GET', '/cars/' . $c['ref'])['code']);
    }
}
