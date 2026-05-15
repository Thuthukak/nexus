<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\HR\app\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('HR/Pages/Departments/Index', [
            'departments' => Department::withCount('employees')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|uuid',
        ]);

        Department::create($validated);

        return back()->with('toast', [
            'type' => 'success', 'title' => 'Department created',
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($request->validate(['name' => 'required|string|max:255']));
        return back()->with('toast', ['type' => 'success', 'title' => 'Department updated']);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('toast', ['type' => 'success', 'title' => 'Department deleted']);
    }
}
