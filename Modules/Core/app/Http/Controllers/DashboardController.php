<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Services\ModuleRegistryService;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
        public function __construct(
            private ModuleRegistryService $registry,
        ) {}

        public function index()
        {
            $appName = config('app.name', 'Nexus');

            return inertia('Core/Pages/Dashboard', [
                'modules' => $this->registry->getAllModules(),
                'appName' => $appName,
            ]);
        }
}
