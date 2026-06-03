<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Notifications\NewUserCreatedNotification;
use App\Services\ActivityLogService;

class UserManagementService
{
    public function create(array $data): User
    {
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password'] ?? Str::random(16)),
            'guard'             => $data['guard'] ?? 'internal',
            'portal_access'     => $data['portal_access'] ?? false,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        // Notify super admins
        app(ActivityLogService::class)->log($user, 'User account created', ['role' => $data['role'] ?? null], 'user');
        User::role('Super Admin')
            ->each(fn ($admin) => $admin->notify(new NewUserCreatedNotification($user)));

        return $user;;
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'portal_access'=> $data['portal_access'] ?? false,
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh();
    }

    public function deactivate(User $user, User $actor): void
    {
        abort_if($user->id === $actor->id, 422, 'You cannot deactivate your own account.');
        abort_if($user->hasRole('Super Admin'), 422, 'The Super Admin account cannot be deactivated.');

        $user->update(['is_active' => false]);

        app(ActivityLogService::class)->log($user, 'User account deactivated', [], 'user');
    }

    public function activate(User $user): void
    {
        $user->update(['is_active' => true]);
        app(ActivityLogService::class)->log($user, 'User account activated', [], 'user');
    }

    public function resetPassword(User $user): string
    {
        $password = Str::random(12) . '!1Aa';
        $user->update(['password' => Hash::make($password)]);
        return $password;
    }

    public function delete(User $user, User $actor): void
    {
        abort_if($user->id === $actor->id, 422, 'You cannot delete your own account.');
        abort_if($user->hasRole('Super Admin'), 422, 'The Super Admin account cannot be deleted.');

        app(ActivityLogService::class)->log($user, 'User account deleted', [], 'user');
        $user->delete();
    }
}
