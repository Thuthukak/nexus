<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\LeaveApplication;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('HR/Pages/Dashboard', [
            'stats' => [
                'total_employees'   => Employee::active()->count(),
                'on_leave_today'    => Employee::where('status', 'on_leave')->count(),
                'pending_leave'     => LeaveApplication::pending()->count(),
                'new_this_month'    => Employee::whereMonth('start_date', now()->month)->count(),
            ],
        ]);
    }
}
