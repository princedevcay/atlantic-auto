<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Staff photo uploads (CTL-FILE-001): JPEG/PNG/WebP by content, 8 MB max, re-encoded with GD
 * (which drops metadata and any embedded payload), random name, stored outside the web root.
 */
final class Uploads
{
    public const MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_EDGE = 2000;

    public static function dir(): string
    {
        $dir = rtrim(Config::str('paths.storage'), '/') . '/uploads';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir;
    }

    /**
     * @param array<string, mixed> $file one entry of $_FILES
     * @return array{name: string, width: int, height: int}|string result or error message
     */
    public static function storeImage(array $file): array|string
    {
        if (!isset($file['error'], $file['tmp_name'], $file['size']) || is_array($file['error'])) {
            return 'Choose one photo to upload.';
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || (int) $file['size'] > self::MAX_BYTES) {
            return 'The photo is larger than 8 MB.';
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            return 'The upload did not finish. Please try again.';
        }
        $tmp = (string) $file['tmp_name'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return 'Only JPEG, PNG or WebP photos can be uploaded.';
        }
        $info = @getimagesize($tmp);
        if ($info === false || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 40_000_000) {
            return 'That file is not a usable image.';
        }
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/webp' => @imagecreatefromwebp($tmp),
        };
        if ($src === false) {
            return 'That file is not a usable image.';
        }
        [$w, $h] = [imagesx($src), imagesy($src)];
        $scale = min(1, self::MAX_EDGE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $name = bin2hex(random_bytes(16)) . '.jpg';
        $ok = imagejpeg($dst, self::dir() . '/' . $name, 82);
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            return 'The photo could not be saved. Please try again.';
        }
        @chmod(self::dir() . '/' . $name, 0600);
        return ['name' => $name, 'width' => $nw, 'height' => $nh];
    }

    /**
     * Imports an image from a trusted local path (CLI seeding only), with the same re-encoding as uploads.
     * @return array{name: string, width: int, height: int}|string
     */
    public static function importFile(string $path): array|string
    {
        if (PHP_SAPI !== 'cli' || !is_file($path)) {
            return 'Import is only available from the command line.';
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };
        if ($src === false) {
            return 'Not a usable image.';
        }
        $name = bin2hex(random_bytes(16)) . '.jpg';
        $ok = imagejpeg($src, self::dir() . '/' . $name, 85);
        [$w, $h] = [imagesx($src), imagesy($src)];
        imagedestroy($src);
        if (!$ok) {
            return 'Could not save the image.';
        }
        @chmod(self::dir() . '/' . $name, 0600);
        return ['name' => $name, 'width' => $w, 'height' => $h];
    }

    /** Streams a stored image after the caller has checked access. */
    public static function stream(string $name, bool $public = false): never
    {
        if (!preg_match('/^[a-f0-9]{32}\.jpg$/', $name) || !is_file(self::dir() . '/' . $name)) {
            Http::abort(404);
        }
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize(self::dir() . '/' . $name));
        header('Cache-Control: ' . ($public ? 'public, max-age=600' : 'private, max-age=3600'));
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'none'");
        readfile(self::dir() . '/' . $name);
        exit;
    }

    public static function delete(string $name): void
    {
        if (preg_match('/^[a-f0-9]{32}\.jpg$/', $name)) {
            @unlink(self::dir() . '/' . $name);
        }
    }
}
