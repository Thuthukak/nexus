<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\UserInviteMail;
use App\Models\User;
use App\Notifications\NewUserCreatedNotification;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementService
{
    /**
     * Create a user WITHOUT a usable password, then send an invite email
     * with a signed URL so they can set their own password.
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make(Str::random(64)), // unusable placeholder
            'guard'             => 'internal',
            'is_active'         => true,
            'email_verified_at' => null, // not verified until they accept invite
        ]);

        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        app(ActivityLogService::class)->log(
            $user,
            'User account created',
            ['role' => $data['role'] ?? null],
            'user'
        );

        // Notify super admins
        User::role('Super Admin')
            ->where('id', '!=', $user->id)
            ->each(fn ($admin) => $admin->notify(new NewUserCreatedNotification($user)));

        // Send invite email
        $this->sendInvite($user);

        return $user;
    }

    /**
     * (Re-)send invite email to a user who hasn't set their password yet.
     */
    public function sendInvite(User $user): void
    {
        $roleName = $user->roles->first()?->name ?? 'Staff';

        $inviteUrl = URL::temporarySignedRoute(
            'auth.accept-invite',
            now()->addHours(72),
            ['user' => $user->id, 'email' => $user->email]
        );

        Mail::to($user->email)
            ->queue(new UserInviteMail($user, $inviteUrl, $roleName));
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        app(ActivityLogService::class)->log($user, 'User account updated', [], 'user');

        return $user->fresh();
    }

    public function deactivate(User $user): void
    {
        $user->update(['is_active' => false]);
        app(ActivityLogService::class)->log($user, 'User account deactivated', [], 'user');
    }

    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
        app(ActivityLogService::class)->log($user, 'User account reactivated', [], 'user');
    }

    /**
     * Admin-triggered password reset — sends the standard Laravel reset email.
     */
    public function sendPasswordReset(User $user): void
    {
        \Illuminate\Support\Facades\Password::sendResetLink(
            ['email' => $user->email]
        );

        app(ActivityLogService::class)->log(
            $user, 'Password reset email sent by administrator', [], 'user'
        );
    }

    /**
     * Complete the invite — user sets their own password.
     */
    public function acceptInvite(User $user, string $password): void
    {
        $user->update([
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        app(ActivityLogService::class)->log($user, 'User accepted invite and set password', [], 'user');
    }
}
