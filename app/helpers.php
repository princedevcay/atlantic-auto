<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

/** Escape for HTML text and attributes. The only way views print values. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** Inline a Lucide icon copied by scripts/copy-assets.mjs. Icons are decorative unless labelled. */
function icon(string $name, string $class = 'icon'): string
{
    static $cache = [];
    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return '';
    }
    if (!isset($cache[$name])) {
        $file = dirname(__DIR__) . '/public/assets/icons/' . $name . '.svg';
        $cache[$name] = is_file($file) ? (string) file_get_contents($file) : '';
        $cache[$name] = str_replace('<svg', '<svg aria-hidden="true" focusable="false"', $cache[$name]);
    }
    return '<span class="' . e($class) . '">' . $cache[$name] . '</span>';
}

function contact(string $key): string
{
    return Config::str('contact.' . $key);
}

function whatsapp_url(string $text = ''): string
{
    $num = preg_replace('/\D/', '', contact('whatsapp')) ?? '';
    if ($num === '') {
        return '';
    }
    return 'https://wa.me/' . $num . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function tel_url(string $phone): string
{
    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

/** Asset URL with a cache-busting version from the file time. */
function asset(string $path): string
{
    $file = dirname(__DIR__) . '/public/assets/' . ltrim($path, '/');
    $v = is_file($file) ? (string) filemtime($file) : '0';
    return '/assets/' . ltrim($path, '/') . '?v=' . $v;
}

function stage_label(string $stage): string
{
    return App\Models\Stages::LABELS[$stage] ?? ucfirst($stage);
}

function fmt_date(?string $utc, string $format = 'j M Y'): string
{
    if ($utc === null || $utc === '') {
        return '';
    }
    $dt = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
    return $dt->setTimezone(new DateTimeZone('Africa/Accra'))->format($format);
}

/** Show only the last 6 characters of a VIN (CTL-DATA-002). */
function mask_vin(?string $vin): string
{
    if ($vin === null || $vin === '') {
        return '';
    }
    return str_repeat('•', max(0, strlen($vin) - 6)) . substr($vin, -6);
}

/**
 * Client-supplied photo slots (PG-014). Returns the URL when the client's file exists in
 * public/assets/img/site/, otherwise '' so the view shows a plain panel. Never a stock image.
 */
function site_image(string $name): string
{
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $rel = 'img/site/' . $name . '.' . $ext;
        if (is_file(dirname(__DIR__) . '/public/assets/' . $rel)) {
            return asset($rel);
        }
    }
    return '';
}

/** aria attributes for a field with a possible error and hint. @param array<string, string> $errors */
function field_attrs(array $errors, string $name, bool $hint = false): string
{
    $ids = [];
    if ($hint) {
        $ids[] = $name . '-hint';
    }
    if (isset($errors[$name])) {
        $ids[] = $name . '-error';
    }
    $out = $ids !== [] ? ' aria-describedby="' . e(implode(' ', $ids)) . '"' : '';
    return $out . (isset($errors[$name]) ? ' aria-invalid="true"' : '');
}

/** @param array<string, string> $errors */
function field_error(array $errors, string $name): string
{
    if (!isset($errors[$name])) {
        return '';
    }
    return '<p id="' . e($name) . '-error" class="field-error">' . icon('circle-alert', 'icon h-4 w-4') . e($errors[$name]) . '</p>';
}

/** @param array<string, mixed> $old */
function old(array $old, string $key): string
{
    $v = $old[$key] ?? '';
    return is_string($v) ? $v : '';
}

/** Customer-facing tracking pages are switched off unless features.customer_tracking is true. */
function customer_tracking(): bool
{
    return Config::get('features.customer_tracking', false) === true;
}
