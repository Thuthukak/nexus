<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Financial/Pages/Invoices/Index', [
            'invoices' => [],
        ]);
    }

    public function create()
    {
        return Inertia::render('Financial/Pages/Invoices/Create');
    }

    public function show(string $id)
    {
        return Inertia::render('Financial/Pages/Invoices/Show', [
            'invoice' => ['id' => $id],
        ]);
    }
}
