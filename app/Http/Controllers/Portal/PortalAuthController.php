<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return inertia('Portal/Auth/Login', [
            'app' => $this->appProps(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Only allow customer-guard users
        $user = User::where('email', $validated['email'])
                    ->where('guard', 'customer')
                    ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'email' => 'Your portal access has been suspended. Please contact us.',
            ]);
        }

        Auth::guard('customer')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }

    public function showAcceptInvite(Request $request, User $user)
    {
        // Validate the signed URL
        if (! $request->hasValidSignature()) {
            return inertia('Portal/Auth/InviteExpired', [
                'app' => $this->appProps(),
            ]);
        }

        // Must match email in URL
        if ($request->query('email') !== $user->email) {
            abort(403);
        }

        return inertia('Portal/Auth/AcceptInvite', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'name'    => $user->name,
            'app'     => $this->appProps(),
        ]);
    }

    public function acceptInvite(Request $request, User $user, CustomerPortalService $service)
    {
        if (! $request->hasValidSignature()) {
            return back()->withErrors(['password' => 'This invite link has expired.']);
        }

        $validated = $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $service->acceptInvite($user, $validated['password']);

        // Auto-login
        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('toast', [
            'type'    => 'success',
            'title'   => 'Welcome!',
            'message' => 'Your account is ready. Welcome to the client portal.',
        ]);
    }

    private function appProps(): array
    {
        return [
            'name'     => \App\Facades\Settings::group('general')
                ->get('app_name', config('app.name')),
            'logo_url' => \App\Facades\Settings::group('general')
                ->get('logo_url'),
        ];
    }
}
