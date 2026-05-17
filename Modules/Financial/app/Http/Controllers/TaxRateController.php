<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\TaxRate;

class TaxRateController extends Controller
{
    public function index()
    {
        return Inertia::render('Financial/Pages/TaxRates/Index', [
            'taxRates' => TaxRate::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'rate'        => 'required|numeric|min:0|max:100',
            'is_compound' => 'boolean',
            'is_default'  => 'boolean',
        ]);

        if (! empty($validated['is_default'])) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        TaxRate::create($validated);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Tax rate created',
        ]);
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'rate'        => 'required|numeric|min:0|max:100',
            'is_compound' => 'boolean',
            'is_default'  => 'boolean',
            'is_active'   => 'boolean',
        ]);

        if (! empty($validated['is_default'])) {
            TaxRate::where('id', '!=', $taxRate->id)
                   ->update(['is_default' => false]);
        }

        $taxRate->update($validated);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Tax rate updated',
        ]);
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Tax rate deleted',
        ]);
    }
}
