<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    private string $group = 'general';
    private int $cacheTtl = 3600;

    public function group(string $group): static
    {
        $clone        = clone $this;
        $clone->group = $group;
        return $clone;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        DB::table('settings')->upsert(
            [
                'group'      => $this->group,
                'key'        => $key,
                'value'      => json_encode($value),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            ['group', 'key'],
            ['value', 'updated_at']
        );

        $this->clearCache();
    }

    public function all(): array
    {
        return Cache::remember(
            "settings.{$this->group}",
            $this->cacheTtl,
            fn () => DB::table('settings')
                ->where('group', $this->group)
                ->pluck('value', 'key')
                ->map(fn ($v) => json_decode($v, true))
                ->toArray()
        );
    }

    public function forget(string $key): void
    {
        DB::table('settings')
            ->where('group', $this->group)
            ->where('key', $key)
            ->delete();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget("settings.{$this->group}");
    }
}
