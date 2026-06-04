<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\PortalInviteMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Modules\Financial\app\Models\Customer;

class CustomerPortalService
{
    /**
     * Send a portal invite to the customer.
     * Creates a User account (guard=customer) if one doesn't exist.
     */
    public function invite(Customer $customer): void
    {
        abort_if(! $customer->email, 422, 'This customer has no email address.');

        // Create or retrieve the portal user
        $user = $customer->portalUser;

        if (! $user) {
            $user = User::create([
                'name'     => $customer->contact_name ?? $customer->company_name,
                'email'    => $customer->email,
                'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                'guard'    => 'customer',
                'is_active'=> true,
            ]);

            $customer->update([
                'user_id'          => $user->id,
                'portal_enabled'   => true,
                'portal_invited_at'=> now(),
            ]);
        } else {
            // Re-invite — just update timestamp
            $customer->update(['portal_invited_at' => now()]);
        }

        // Generate a signed URL for password setup (valid 72 hours)
        $inviteUrl = URL::temporarySignedRoute(
            'portal.accept-invite',
            now()->addHours(72),
            ['user' => $user->id, 'email' => $user->email]
        );

        Mail::to($customer->email)
            ->queue(new PortalInviteMail($customer, $inviteUrl));
    }

    /**
     * Disable portal access for a customer.
     */
    public function revoke(Customer $customer): void
    {
        if ($customer->portalUser) {
            $customer->portalUser->update(['is_active' => false]);
        }
        $customer->update(['portal_enabled' => false]);
    }

    /**
     * Re-enable previously revoked access.
     */
    public function restore(Customer $customer): void
    {
        if ($customer->portalUser) {
            $customer->portalUser->update(['is_active' => true]);
        }
        $customer->update(['portal_enabled' => true]);
    }

    /**
     * Complete the invite — set password and mark user as verified.
     */
    public function acceptInvite(User $user, string $password): void
    {
        $user->update([
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Link customer record
        $customer = Customer::where('user_id', $user->id)->first();
        if ($customer) {
            $customer->update(['portal_enabled' => true]);
        }
    }
}
