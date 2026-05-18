<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Financial\app\Models\Product;

class ProductApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::active()->orderBy('name')->get(['id', 'name', 'default_price', 'default_tax_rate', 'unit'])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'default_price'    => 'required|numeric|min:0',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'unit'             => 'nullable|string|max:50',
        ]);

        $product = Product::create([
            ...$validated,
            'is_active' => true,
        ]);

        return response()->json([
            'id'               => $product->id,
            'name'             => $product->name,
            'default_price'    => $product->default_price,
            'default_tax_rate' => $product->default_tax_rate,
            'unit'             => $product->unit,
        ], 201);
    }
}
