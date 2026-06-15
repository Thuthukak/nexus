<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'guard',
        'portal_access',
        'notification_preferences',
        'is_active',      
        'last_login_at',    
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'portal_access'              => 'boolean',
            'is_active'                  => 'boolean',            
            'last_login_at'              => 'datetime',            
            'notification_preferences'   => 'array',
        ];
    }

    public function notificationPreference(string $channel, string $type): bool
    {
        $prefs = $this->notification_preferences ?? [];
        return $prefs[$type][$channel] ?? true;
    }
}
