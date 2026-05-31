<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->withCount('users')
            ->with('permissions')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'name'         => $r->name,
                'users_count'  => $r->users_count,
                'permissions'  => $r->permissions->pluck('name'),
                'is_system'    => in_array($r->name, [
                    'Super Admin', 'Admin', 'Manager', 'Staff', 'Read Only',
                    'Customer Admin', 'Customer User',
                ]),
            ]);

        // Group permissions by module
        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($p) {
                return explode('.', $p->name)[0];
            })
            ->map(fn ($perms) => $perms->pluck('name')->toArray())
            ->toArray();

        return Inertia::render('Core/Pages/Roles/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('toast', [
            'type'  => 'success',
            'title' => "Role '{$role->name}' created",
        ]);
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->name === 'Super Admin', 403, 'Super Admin role cannot be modified.');

        $validated = $request->validate([
            'permissions' => 'array',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => "Role '{$role->name}' updated",
        ]);
    }

    public function destroy(Role $role)
    {
        abort_if(
            in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'Staff', 'Read Only']),
            403,
            'System roles cannot be deleted.'
        );

        abort_if(
            $role->users()->count() > 0,
            422,
            "Cannot delete '{$role->name}' — it has active users assigned."
        );

        $role->delete();

        return back()->with('toast', [
            'type'  => 'success',
            'title' => "Role '{$role->name}' deleted",
        ]);
    }
}
