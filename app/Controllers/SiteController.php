<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class SiteController
{
    public static function home(): void
    {
        View::render('pages/home', ['title' => 'Import Your Vehicle from the USA', 'nav' => 'home']);
    }

    public static function services(): void
    {
        View::render('pages/services', ['title' => 'Services', 'nav' => 'services']);
    }

    public static function howItWorks(): void
    {
        View::render('pages/how-it-works', ['title' => 'How It Works', 'nav' => 'how']);
    }

    public static function about(): void
    {
        View::render('pages/about', ['title' => 'About Us', 'nav' => 'about']);
    }

    public static function contact(): void
    {
        View::render('pages/contact', ['title' => 'Contact', 'nav' => 'contact']);
    }

    public static function privacy(): void
    {
        View::render('pages/privacy', ['title' => 'Privacy', 'nav' => '']);
    }
}
