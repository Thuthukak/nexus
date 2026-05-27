<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Services\LicenceService;
use App\Services\ModuleRegistryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ModuleManagerController extends Controller
{
    public function __construct(
        private ModuleRegistryService $registry,
        private LicenceService        $licence,
    ) {}

    public function index()
    {
        return Inertia::render('Core/Pages/Modules/Index', [
            'modules' => $this->registry->getAllModules(),
            'licence' => [
                'licensee'   => $this->licence->getLicensee(),
                'expires_at' => $this->licence->getExpiresAt()?->format('d M Y'),
                'tier'       => $this->licence->getTier(),
                'max_users'  => $this->licence->getMaxUsers(),
                'is_dev'     => app()->environment('local'),
            ],
        ]);
    }

    public function enable(Request $request, string $moduleName)
    {
        if (! $this->licence->isModuleAllowed($moduleName) && ! app()->environment('local')) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Not licensed',
                'message' => "The {$moduleName} module is not included in your licence.",
            ]);
        }

        $this->registry->enable($moduleName);

        // Clear caches so middleware picks up new state immediately
        $this->registry->clearCache();
        try { \Illuminate\Support\Facades\Artisan::call('route:clear'); } catch (\Throwable) {}
        try { \Illuminate\Support\Facades\Artisan::call('cache:clear'); } catch (\Throwable) {}

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => "{$moduleName} enabled",
            'message' => "The {$moduleName} module is now active.",
        ]);
    }

    public function disable(string $moduleName)
    {
        if ($moduleName === 'Core') {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Cannot disable Core',
                'message' => 'The Core module is required and cannot be disabled.',
            ]);
        }

        $this->registry->disable($moduleName);

        // Clear caches so middleware picks up new state immediately
        $this->registry->clearCache();
        try { \Illuminate\Support\Facades\Artisan::call('route:clear'); } catch (\Throwable) {}
        try { \Illuminate\Support\Facades\Artisan::call('cache:clear'); } catch (\Throwable) {}

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => "{$moduleName} disabled",
        ]);
    }

    public function updateLicence(Request $request)
    {
        $validated = $request->validate([
            'licence_key' => 'required|string',
        ]);

        $storagePath = storage_path('licence');
        if (! is_dir($storagePath)) mkdir($storagePath, 0755, true);
        file_put_contents($storagePath . '/key.txt', trim($validated['licence_key']));

        // Re-validate
        $service = new LicenceService();

        if (! $service->isValid()) {
            unlink($storagePath . '/key.txt');
            return back()->withErrors(['licence_key' => 'Invalid licence key.']);
        }

        // Re-seed module registry with new licence
        $this->registry->seedForExistingInstall();

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Licence updated',
            'message' => "Licensed to {$service->getLicensee()}.",
        ]);
    }
}
