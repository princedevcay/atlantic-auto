<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Config;

/**
 * Website chat, phase 1: predefined answers only. Nothing a visitor types is sent or stored.
 * Phase 2 (AI agent with live staff hand-off) is a material change to the security baseline
 * (new AI_AGENT controls, data flow and processor) and must be approved before it is built.
 */
final class Chat
{
    /** Online when the current Accra time falls inside chat.online_hours for today. */
    public static function isOnline(?\DateTimeImmutable $now = null): bool
    {
        $hours = Config::get('chat.online_hours', []);
        if (!is_array($hours) || $hours === []) {
            return false;
        }
        $now ??= new \DateTimeImmutable('now', new \DateTimeZone('Africa/Accra'));
        $range = $hours[strtolower($now->format('D'))] ?? null;
        if (!is_string($range) || !preg_match('/^(\d{2}):(\d{2})-(\d{2}):(\d{2})$/', $range, $m)) {
            return false;
        }
        $minutes = (int) $now->format('G') * 60 + (int) $now->format('i');
        return $minutes >= (int) $m[1] * 60 + (int) $m[2] && $minutes < (int) $m[3] * 60 + (int) $m[4];
    }

    /**
     * Pre-service questions shown as chips, with their predefined answers.
     * No prices, duty rates or transit times (DESIGN.md rule 4).
     *
     * @return list<array{q: string, a: string, link: string, label: string}>
     */
    public static function questions(): array
    {
        $list = [
            ['q' => 'How do I get a quote?', 'a' => 'Send us a short import quote request with the vehicle and what you need help with. Our team replies by WhatsApp or phone.', 'link' => '/quote', 'label' => 'Request an Import Quote'],
            ['q' => 'Can you find a car for me?', 'a' => 'Yes. Tell us the make, model, year range and budget. We look for suitable vehicles in the US and share the options with you before anything is bought.', 'link' => '/quote', 'label' => 'Start with a quote'],
            ['q' => 'What does it cost?', 'a' => 'Every vehicle is priced individually. Your total is made of the purchase price, US towing, ocean freight, duty and clearance at Tema, and delivery. Your quote explains each part.', 'link' => '/quote', 'label' => 'Get my quote'],
            ['q' => 'How long does shipping take?', 'a' => 'It depends on where the vehicle is in the US, the shipping schedule and clearing at Tema. We give you an estimate with your quote, and your tracking page shows the expected arrival once it is on a ship.', 'link' => '/how-it-works', 'label' => 'See how it works'],
            ['q' => 'Where is my vehicle?', 'a' => 'Open your tracking page with your private link, or use your tracking reference and the last 4 digits of your phone number.', 'link' => '/track', 'label' => 'Track my vehicle'],
            ['q' => 'Do you clear at Tema?', 'a' => 'Yes. We handle import documentation, customs clearance and port release at Tema, and tell you if we need anything from you, such as your ID.', 'link' => '/services', 'label' => 'Our services'],
        ];
        $wa = whatsapp_url('Hello Atlantic, I have a question about importing a vehicle.');
        $list[] = $wa !== ''
            ? ['q' => 'Talk to a person', 'a' => 'Our team is happy to help. Message us on WhatsApp and a member of the team will reply.', 'link' => $wa, 'label' => 'Chat on WhatsApp']
            : ['q' => 'Talk to a person', 'a' => 'Our team is happy to help. Send a quote or update request and a member of the team will contact you.', 'link' => '/contact', 'label' => 'Contact us'];
        return $list;
    }
}
