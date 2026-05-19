<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use App\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class PaymentSettingsController extends Controller
{
    public function show()
    {
        return Inertia::render('Financial/Pages/Settings/Payments', [
            'settings' => $this->currentSettings(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gateway'              => 'required|in:payfast,paystack,none',
            'test_mode'            => 'boolean',

            // PayFast
            'payfast_merchant_id'  => 'nullable|string',
            'payfast_merchant_key' => 'nullable|string',
            'payfast_passphrase'   => 'nullable|string',

            // Paystack
            'paystack_public_key'  => 'nullable|string',
            'paystack_secret_key'  => 'nullable|string',

            // Display
            'bank_name'            => 'nullable|string|max:100',
            'bank_account_name'    => 'nullable|string|max:100',
            'bank_account_number'  => 'nullable|string|max:50',
            'bank_branch_code'     => 'nullable|string|max:20',
            'bank_reference_prefix'=> 'nullable|string|max:20',
            'payment_instructions' => 'nullable|string|max:1000',
        ]);

        $group = Settings::group('payments');
        foreach ($validated as $key => $value) {
            $group->set($key, $value);
        }

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Payment settings saved',
        ]);
    }

    private function currentSettings(): array
    {
        $group    = Settings::group('payments');
        $defaults = [
            'gateway'              => 'none',
            'test_mode'            => true,
            'payfast_merchant_id'  => '',
            'payfast_merchant_key' => '',
            'payfast_passphrase'   => '',
            'paystack_public_key'  => '',
            'paystack_secret_key'  => '',
            'bank_name'            => '',
            'bank_account_name'    => '',
            'bank_account_number'  => '',
            'bank_branch_code'     => '',
            'bank_reference_prefix'=> 'INV-',
            'payment_instructions' => '',
        ];

        return array_merge($defaults, array_filter($group->all(), fn($v) => $v !== null));
    }
}