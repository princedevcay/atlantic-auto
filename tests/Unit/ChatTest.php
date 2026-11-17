<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Models\Chat;
use PHPUnit\Framework\TestCase;

final class ChatTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set(test_config());
    }

    private function at(string $local): \DateTimeImmutable
    {
        return new \DateTimeImmutable($local, new \DateTimeZone('Africa/Accra'));
    }

    public function testOnlineFollowsConfiguredHours(): void
    {
        Config::set(test_config(['chat' => ['online_hours' => ['mon' => '08:00-18:00', 'sat' => '09:00-14:00']]]));
        $this->assertTrue(Chat::isOnline($this->at('2026-11-16 08:00')), 'Monday opening minute');
        $this->assertTrue(Chat::isOnline($this->at('2026-11-16 17:59')));
        $this->assertFalse(Chat::isOnline($this->at('2026-11-16 18:00')), 'closing time is exclusive');
        $this->assertFalse(Chat::isOnline($this->at('2026-11-16 07:59')));
        $this->assertTrue(Chat::isOnline($this->at('2026-11-21 10:00')), 'Saturday');
        $this->assertFalse(Chat::isOnline($this->at('2026-11-22 10:00')), 'Sunday not listed');
    }

    public function testNoHoursMeansOffline(): void
    {
        Config::set(test_config(['chat' => ['online_hours' => []]]));
        $this->assertFalse(Chat::isOnline($this->at('2026-11-16 10:00')));
        Config::set(test_config(['chat' => ['online_hours' => ['mon' => 'nonsense']]]));
        $this->assertFalse(Chat::isOnline($this->at('2026-11-16 10:00')));
    }

    public function testQuestionsHaveNoFiguresAndAlwaysOfferAPerson(): void
    {
        Config::set(test_config(['contact' => ['whatsapp' => '']]));
        $qs = Chat::questions();
        $this->assertGreaterThanOrEqual(6, count($qs));
        foreach ($qs as $q) {
            $this->assertDoesNotMatchRegularExpression('/(GHS|USD|\$|cedi|\d+\s*(days|weeks))/i', $q['a'], 'no prices or transit times');
            $this->assertNotSame('', $q['link']);
        }
        $this->assertSame('Talk to a person', end($qs)['q']);
    }
}
