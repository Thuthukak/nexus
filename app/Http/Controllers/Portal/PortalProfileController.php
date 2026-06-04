<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Financial\app\Models\Customer;

class PortalProfileController extends Controller
{
    public function show()
    {
        $user     = Auth::guard('customer')->user();
        $customer = Customer::where('user_id', $user->id)->first();

        return inertia('Portal/Profile', [
            'user'     => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'customer' => $customer ? [
                'company_name' => $customer->company_name,
                'phone'        => $customer->phone,
                'address'      => $customer->address,
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $user     = Auth::guard('customer')->user();
        $customer = Customer::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update(['name' => $validated['name']]);

        if ($customer && isset($validated['phone'])) {
            $customer->update([
                'contact_name' => $validated['name'],
                'phone'        => $validated['phone'],
            ]);
        }

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Profile updated',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Password updated',
        ]);
    }
}
