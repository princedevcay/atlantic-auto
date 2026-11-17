<?php
declare(strict_types=1);

namespace App\Core;

/** Who did what, when (CTL-LOG-001). Details must not contain secrets or full contact details. */
final class Audit
{
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null, ?int $staffId = null): void
    {
        try {
            Db::insert('audit_log', [
                'staff_id' => $staffId ?? Auth::id(),
                'action' => substr($action, 0, 40),
                'target_type' => $targetType,
                'target_id' => $targetId,
                'details' => $details !== null ? mb_substr($details, 0, 255) : null,
                'ip_hash' => Http::ipHash(),
                'created_at' => Db::now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('audit write failed', ['action' => $action]);
        }
    }
}
