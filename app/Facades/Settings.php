<?php

declare(strict_types=1);

namespace App\Facades;

use App\Settings\SettingsService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static SettingsService group(string $group)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string $key, mixed $value)
 * @method static array all()
 * @method static void forget(string $key)
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsService::class;
    }
}
