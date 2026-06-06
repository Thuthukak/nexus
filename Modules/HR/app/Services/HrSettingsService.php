<?php

declare(strict_types=1);

namespace Modules\HR\app\Services;

use Illuminate\Support\Facades\DB;

class HrSettingsService
{
    private const DEFAULTS = [
        'document_expiry_warning_days' => 30,
        'company_name'                 => '',
        'payroll_period_start_day'     => 1,
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $row = DB::table('hr_settings')->where('key', $key)->first();
        return $row ? $row->value : ($default ?? self::DEFAULTS[$key] ?? null);
    }

    public function set(string $key, mixed $value): void
    {
        DB::table('hr_settings')->upsert(
            ['key' => $key, 'value' => $value, 'updated_at' => now(), 'created_at' => now()],
            ['key'],
            ['value', 'updated_at']
        );
    }

    public function all(): array
    {
        $stored   = DB::table('hr_settings')->pluck('value', 'key')->toArray();
        return array_merge(self::DEFAULTS, $stored);
    }

    public function expiryWarningDays(): int
    {
        return (int) $this->get('document_expiry_warning_days', 30);
    }
}
