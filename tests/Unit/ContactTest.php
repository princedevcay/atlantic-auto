<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use PHPUnit\Framework\TestCase;

final class ContactTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set(test_config());
    }

    public function testSocialLinksOnlyShowHttpsProfilesOnTheRightSite(): void
    {
        Config::set(test_config(['contact' => [
            'tiktok' => 'https://www.tiktok.com/@atlanticautogh',
            'youtube' => 'http://www.youtube.com/@atlanticautogh',
            'instagram' => 'https://instagram.com.evil.example/atlantic',
        ]]));
        $links = social_links();
        $this->assertCount(1, $links, 'plain http and lookalike hosts are dropped');
        $this->assertSame('tiktok', $links[0]['key']);
    }

    public function testNoSocialLinksWhenEmpty(): void
    {
        Config::set(test_config(['contact' => ['tiktok' => '', 'youtube' => '', 'instagram' => '']]));
        $this->assertSame([], social_links());
    }

    public function testWhatsappNumberIsShownReadably(): void
    {
        Config::set(test_config(['contact' => ['whatsapp' => '233241234567']]));
        $this->assertSame('+233 24 123 4567', whatsapp_display());
        $this->assertSame('https://wa.me/233241234567?text=Hi%20there', whatsapp_url('Hi there'));
        Config::set(test_config(['contact' => ['whatsapp' => '']]));
        $this->assertSame('', whatsapp_display());
    }
}
