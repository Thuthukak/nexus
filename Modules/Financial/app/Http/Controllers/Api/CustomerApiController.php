<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Financial\app\Models\Customer;

class CustomerApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'vat_number'   => 'nullable|string|max:50',
        ]);

        $customer = Customer::create([
            ...$validated,
            'is_active' => true,
        ]);

        return response()->json([
            'id'           => $customer->id,
            'company_name' => $customer->company_name,
            'email'        => $customer->email,
        ], 201);
    }
}
