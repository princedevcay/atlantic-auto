<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class SiteController
{
    public static function home(): void
    {
        View::render('pages/home', ['title' => 'Import Your Vehicle from the USA', 'nav' => 'home', 'description' => 'Atlantic Auto And Shipping Services sells quality used cars from the USA and ships, clears and delivers vehicles to customers in Ghana. Request an import quote today.']);
    }

    public static function services(): void
    {
        View::render('pages/services', ['title' => 'Services', 'nav' => 'services', 'description' => 'Sourcing, purchase, pickup in the USA, shipping to Tema, clearance and delivery in Ghana: the services of Atlantic Auto And Shipping Services.']);
    }

    public static function howItWorks(): void
    {
        View::render('pages/how-it-works', ['title' => 'How It Works', 'nav' => 'how', 'description' => 'How Atlantic Auto And Shipping Services moves your vehicle from a US seller to your door in Ghana, in six stages tracked by our team.']);
    }

    public static function about(): void
    {
        View::render('pages/about', ['title' => 'About Us', 'nav' => 'about', 'description' => 'About Atlantic Auto And Shipping Services, a Ghanaian team importing, shipping and selling vehicles from the USA.']);
    }

    public static function contact(): void
    {
        View::render('pages/contact', ['title' => 'Contact', 'nav' => 'contact', 'description' => 'Contact Atlantic Auto And Shipping Services by WhatsApp, phone or email about importing or buying a vehicle.']);
    }

    public static function terms(): void
    {
        View::render('pages/terms', ['title' => 'Terms of Use', 'nav' => '']);
    }

    public static function privacy(): void
    {
        View::render('pages/privacy', ['title' => 'Privacy', 'nav' => '']);
    }
}
