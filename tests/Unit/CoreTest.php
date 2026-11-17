<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Crypto;
use App\Core\Tokens;
use App\Core\Totp;
use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase
{
    public function testTotpMatchesRfc6238Vector(): void
    {
        // RFC 6238 SHA-1 secret "12345678901234567890", T=59 -> 94287082 (last 6 digits 287082)
        $secret = Totp::base32Encode('12345678901234567890');
        $this->assertSame('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', $secret);
        $this->assertSame('287082', Totp::code($secret, intdiv(59, 30)));
        $this->assertSame('005924', Totp::code($secret, intdiv(1234567890, 30)));
    }

    public function testTotpVerifyBlocksReplayAndAcceptsDrift(): void
    {
        $secret = Totp::newSecret();
        $now = 1_800_000_000;
        $step = intdiv($now, 30);
        $code = Totp::code($secret, $step);
        $this->assertSame($step, Totp::verify($secret, $code, null, $now));
        $this->assertNull(Totp::verify($secret, $code, $step, $now), 'same step must not be reused');
        $this->assertSame($step - 1, Totp::verify($secret, Totp::code($secret, $step - 1), null, $now));
        $this->assertNull(Totp::verify($secret, Totp::code($secret, $step - 3), null, $now));
        $this->assertNull(Totp::verify($secret, 'abcdef', null, $now));
    }

    public function testPhoneNormalisation(): void
    {
        $this->assertSame('+233241234567', Validator::normalisePhone('024 123 4567'));
        $this->assertSame('+233241234567', Validator::normalisePhone('+233 24 123 4567'));
        $this->assertSame('+233241234567', Validator::normalisePhone('00233241234567'));
        $this->assertSame('+233241234567', Validator::normalisePhone('241234567'));
        $this->assertSame('+14155550123', Validator::normalisePhone('+1 (415) 555-0123'));
        $this->assertNull(Validator::normalisePhone('12345'));
        $this->assertNull(Validator::normalisePhone('024123456'));
        $this->assertNull(Validator::normalisePhone("024 123 4567\r\nBcc: x@y.z"));
    }

    public function testVin(): void
    {
        $this->assertTrue(Validator::isVin(Validator::normaliseVin('1hgcm82633a004352')));
        $this->assertFalse(Validator::isVin('1HGCM82633A00435O'), 'O is not allowed in a VIN');
        $this->assertFalse(Validator::isVin('SHORT'));
    }

    public function testReferencesAndTokens(): void
    {
        $refs = [];
        for ($i = 0; $i < 200; $i++) {
            $r = Tokens::reference();
            $this->assertTrue(Tokens::isReference($r));
            $this->assertDoesNotMatchRegularExpression('/[01OIL]/', $r);
            $refs[$r] = true;
        }
        $this->assertGreaterThan(195, count($refs));
        $t = Tokens::trackingToken();
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{22}$/', $t);
        $this->assertSame(64, strlen(Tokens::hash($t)));
        $this->assertSame('K7MP3QXA', Tokens::normaliseReference(' k7mp-3qxa '));
    }

    public function testCryptoRoundTripAndTamper(): void
    {
        $c = Crypto::encrypt('JBSWY3DPEHPK3PXP');
        $this->assertSame('JBSWY3DPEHPK3PXP', Crypto::decrypt($c));
        $raw = base64_decode($c);
        $raw[strlen($raw) - 1] = chr(ord($raw[strlen($raw) - 1]) ^ 1);
        $this->expectException(\RuntimeException::class);
        Crypto::decrypt(base64_encode($raw));
    }

    public function testEscapingAndMasking(): void
    {
        $this->assertSame('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', e("<script>alert('x')</script>"));
        $this->assertSame('•••••••••••004352', mask_vin('1HGCM82633A004352'));
    }

    public function testValidatorRejectsControlCharactersAndBadUrls(): void
    {
        $v = new Validator();
        $v->required('name', "Ama\x00", 'name');
        $v->url('listing_url', 'javascript:alert(1)');
        $v->email('email', "a@b.com\r\nBcc: c@d.com");
        $this->assertArrayHasKey('name', $v->errors);
        $this->assertArrayHasKey('listing_url', $v->errors);
        $this->assertArrayHasKey('email', $v->errors);
    }

    public function testPasswordPolicy(): void
    {
        $this->assertNotNull(\App\Core\Auth::passwordProblem('short'));
        $this->assertNotNull(\App\Core\Auth::passwordProblem('Password12345'));
        $this->assertNotNull(\App\Core\Auth::passwordProblem('aaaaaaaaaaaaaa'));
        $this->assertNull(\App\Core\Auth::passwordProblem('blue harbour lamp 47'));
    }
}
