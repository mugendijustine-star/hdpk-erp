<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $module,
        string $action,
        string $recordType,
        ?int $recordId,
        $oldValue = null,
        $newValue = null
    ): AuditLog {
        $encode = static function ($value) {
            if ($value === null) {
                return null;
            }

            return is_string($value) ? $value : json_encode($value);
        };

        return AuditLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'old_value' => $encode($oldValue),
            'new_value' => $encode($newValue),
        ]);
    }
}
