<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\UserManagementService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::check()) return redirect()->intended('/dashboard');

        return inertia('Auth/Login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Check if user exists but hasn't accepted invite yet
        if ($user && ! $user->email_verified_at) {
            return back()->withErrors([
                'email' => 'You need to accept your invite first. Check your email for the invite link.',
            ]);
        }

        if (! Auth::attempt([
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // ── Accept Invite ─────────────────────────────────────────

    public function showAcceptInvite(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            return inertia('Auth/InviteExpired', [
                'email' => $user->email,
            ]);
        }

        if ($request->query('email') !== $user->email) {
            abort(403);
        }

        // Already accepted
        if ($user->email_verified_at) {
            return redirect('/login')->with('toast', [
                'type'    => 'info',
                'title'   => 'Invite already used',
                'message' => 'Your account is ready. Please log in.',
            ]);
        }

        return inertia('Auth/AcceptInvite', [
            'user_id' => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
        ]);
    }

    public function acceptInvite(
        Request               $request,
        User                  $user,
        UserManagementService $service,
    ) {
        if (! $request->hasValidSignature()) {
            return back()->withErrors([
                'password' => 'This invite link has expired. Ask an administrator to resend your invite.',
            ]);
        }

        $request->validate([
            'password'              => ['required', 'confirmed', new StrongPassword()],
            'password_confirmation' => 'required',
        ]);

        $service->acceptInvite($user, $request->input('password'));

        // Auto-login
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('toast', [
            'type'    => 'success',
            'title'   => 'Welcome to ' . config('app.name') . '!',
            'message' => 'Your account is ready.',
        ]);
    }

    // ── Resend Invite (admin action) ───────────────────────────

    public function resendInvite(User $user, UserManagementService $service)
    {
        abort_if($user->email_verified_at, 422, 'This user has already accepted their invite.');

        $service->sendInvite($user);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Invite resent',
            'message' => "A new invite has been sent to {$user->email}",
        ]);
    }

    // ── Forgot Password ───────────────────────────────────────

    public function showForgotPassword()
    {
        return inertia('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('toast', [
                'type'    => 'success',
                'title'   => 'Reset link sent',
                'message' => 'If that email exists, a reset link has been sent.',
            ]);
        }

        // Don't reveal whether the email exists — always show success
        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Reset link sent',
            'message' => 'If that email exists, a reset link has been sent.',
        ]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => ['required', 'confirmed', new StrongPassword()],
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'          => Hash::make($password),
                    'remember_token'    => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('toast', [
                'type'    => 'success',
                'title'   => 'Password updated',
                'message' => 'Your password has been reset. Please log in.',
            ]);
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}
