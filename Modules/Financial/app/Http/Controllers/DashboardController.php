<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Financial/Pages/Dashboard', [
            'stats' => [
                'total_invoices'    => 0,
                'outstanding'       => 0,
                'paid_this_month'   => 0,
                'overdue'           => 0,
            ],
        ]);
    }
}
