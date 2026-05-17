<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Bookings\app\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Bookings/Pages/Services/Index', [
            'services' => Service::withCount('bookings')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Bookings/Pages/Services/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15',
            'buffer_minutes'   => 'nullable|integer|min:0',
            'price'            => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'description'      => 'nullable|string',
        ]);

        Service::create($validated);

        return redirect()
            ->route('bookings.services.index')
            ->with('toast', ['type' => 'success', 'title' => 'Service added']);
    }

    public function show(Service $service)
    {
        return Inertia::render('Bookings/Pages/Services/Show', [
            'service' => $service,
        ]);
    }

    public function edit(Service $service)
    {
        return Inertia::render('Bookings/Pages/Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15',
            'buffer_minutes'   => 'nullable|integer|min:0',
            'price'            => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'description'      => 'nullable|string',
        ]);

        $service->update($validated);

        return redirect()
            ->route('bookings.services.index')
            ->with('toast', ['type' => 'success', 'title' => 'Service updated']);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()
            ->route('bookings.services.index')
            ->with('toast', ['type' => 'success', 'title' => 'Service deleted']);
    }
}
