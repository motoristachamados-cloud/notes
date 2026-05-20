<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingsService
{
    public function getInt(string $key, int $default): int
    {
        $value = SystemSetting::query()->where('key', $key)->value('value');

        if ($value === null || ! is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }
}
