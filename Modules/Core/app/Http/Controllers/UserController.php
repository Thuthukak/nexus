<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $service
    ) {}

    public function index(Request $request)
    {
        $query = User::with('roles')
            ->where('guard', 'internal')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) =>
                $q->where('name', $request->role)
            );
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->get()->map(fn ($u) => $this->formatUser($u));

        return Inertia::render('Core/Pages/Users/Index', [
            'users'   => $users,
            'roles'   => Role::where('guard_name', 'web')->pluck('name'),
            'filters' => $request->only(['search', 'role', 'status']),
            'stats'   => [
                'total'    => User::where('guard', 'internal')->count(),
                'active'   => User::where('guard', 'internal')->where('is_active', true)->count(),
                'inactive' => User::where('guard', 'internal')->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Core/Pages/Users/Create', [
            'roles' => Role::where('guard_name', 'web')
                ->whereNotIn('name', ['Super Admin'])
                ->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:8',
        ]);

        $this->service->create($validated);

        return redirect()
            ->route('core.users.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'User created',
                'message' => "{$validated['name']} has been added.",
            ]);
    }

    public function show(User $user)
    {
        $user->load('roles');

        return Inertia::render('Core/Pages/Users/Show', [
            'user' => $this->formatUser($user),
        ]);
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return Inertia::render('Core/Pages/Users/Edit', [
            'user'  => $this->formatUser($user),
            'roles' => Role::where('guard_name', 'web')
                ->whereNotIn('name', ['Super Admin'])
                ->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'role'          => 'required|string|exists:roles,name',
            'portal_access' => 'boolean',
        ]);

        $this->service->update($user, $validated);

        return redirect()
            ->route('core.users.show', $user)
            ->with('toast', ['type' => 'success', 'title' => 'User updated']);
    }

    public function deactivate(Request $request, User $user)
    {
        $this->service->deactivate($user, $request->user());

        return back()->with('toast', [
            'type'  => 'success',
            'title' => "{$user->name} deactivated",
        ]);
    }

    public function activate(User $user)
    {
        $this->service->activate($user);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => "{$user->name} reactivated",
        ]);
    }

    public function resetPassword(User $user)
    {
        $newPassword = $this->service->resetPassword($user);

        return back()->with('toast', [
            'type'    => 'info',
            'title'   => 'Password reset',
            'message' => "New password: {$newPassword} — share this securely.",
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->service->delete($user, $request->user());

        return redirect()
            ->route('core.users.index')
            ->with('toast', ['type' => 'success', 'title' => 'User deleted']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'guard'         => $user->guard,
            'is_active'     => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->format('d M Y'),
            'invite_pending' => is_null($user->email_verified_at),
            'portal_access' => $user->portal_access,
            'roles'         => $user->roles->pluck('name'),
            'last_login_at' => $user->last_login_at?->diffForHumans(),
            'created_at'    => $user->created_at?->format('d M Y'),
        ];
    }
}
