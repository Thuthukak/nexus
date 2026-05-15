<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Bookings\app\Models\Resource;

class ResourceController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookings/Pages/Resources/Index', [
            'resources' => Resource::withCount('bookings')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Bookings/Pages/Resources/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:room,equipment,staff,vehicle,other',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string',
            'colour'   => 'nullable|string|max:7',
        ]);

        Resource::create($validated);

        return redirect()
            ->route('bookings.resources.index')
            ->with('toast', ['type' => 'success', 'title' => 'Resource added']);
    }
}
