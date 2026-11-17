<?php
declare(strict_types=1);

namespace App\Core;

/** Plain PHP templates. All output goes through e() (CTL-INP-001). */
final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = [], ?string $layout = null): void
    {
        $content = self::fetch($template, $data);
        $layout ??= str_starts_with($template, 'admin/') ? 'layouts/admin' : 'layouts/public';
        echo self::fetch($layout, $data + ['content' => $content]);
    }

    /** @param array<string, mixed> $data */
    public static function fetch(string $template, array $data = []): string
    {
        $file = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!preg_match('#^[a-z0-9_/-]+$#', $template) || !is_file($file)) {
            throw new \RuntimeException('Unknown view');
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
