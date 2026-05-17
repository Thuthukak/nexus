<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('invoices')
            ->orderBy('company_name')
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'company_name'  => $c->company_name,
                'contact_name'  => $c->contact_name,
                'email'         => $c->email,
                'phone'         => $c->phone,
                'is_active'     => $c->is_active,
                'invoices_count'=> $c->invoices_count,
            ]);

        return Inertia::render('Financial/Pages/Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Financial/Pages/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string|max:50',
            'vat_number'   => 'nullable|string|max:50',
            'address.line1'=> 'nullable|string',
            'address.line2'=> 'nullable|string',
            'address.city' => 'nullable|string',
            'address.code' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'email'        => $validated['email']        ?? null,
            'phone'        => $validated['phone']        ?? null,
            'vat_number'   => $validated['vat_number']   ?? null,
            'address'      => $validated['address']      ?? null,
            'is_active'    => true,
        ]);

        return redirect()
            ->route('financial.customers.show', $customer)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Customer created',
                'message' => $customer->company_name . ' has been added.',
            ]);
    }

    public function show(Customer $customer)
    {
        $customer->load('invoices');

        return Inertia::render('Financial/Pages/Customers/Show', [
            'customer' => [
                'id'           => $customer->id,
                'company_name' => $customer->company_name,
                'contact_name' => $customer->contact_name,
                'email'        => $customer->email,
                'phone'        => $customer->phone,
                'vat_number'   => $customer->vat_number,
                'address'      => $customer->address,
                'is_active'    => $customer->is_active,
                'invoices'     => $customer->invoices->map(fn ($inv) => [
                    'id'        => $inv->id,
                    'reference' => $inv->reference,
                    'total'     => $inv->total,
                    'status'    => $inv->status,
                    'due_date'  => $inv->due_date?->format('d M Y'),
                ]),
            ],
        ]);
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Financial/Pages/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string|max:50',
            'vat_number'   => 'nullable|string|max:50',
            'is_active'    => 'boolean',
            'address.line1'=> 'nullable|string',
            'address.line2'=> 'nullable|string',
            'address.city' => 'nullable|string',
            'address.code' => 'nullable|string',
        ]);

        $customer->update([
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'email'        => $validated['email']        ?? null,
            'phone'        => $validated['phone']        ?? null,
            'vat_number'   => $validated['vat_number']   ?? null,
            'is_active'    => $validated['is_active']    ?? true,
            'address'      => $validated['address']      ?? null,
        ]);

        return redirect()
            ->route('financial.customers.show', $customer)
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Customer updated',
            ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()
            ->route('financial.customers.index')
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Customer deleted',
            ]);
    }
}
