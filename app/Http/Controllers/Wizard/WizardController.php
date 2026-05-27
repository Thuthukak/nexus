<?php

declare(strict_types=1);

namespace App\Http\Controllers\Wizard;

use App\Http\Controllers\Controller;
use App\Services\LicenceService;
use App\Services\ModuleRegistryService;
use App\Services\Wizard\Steps\DatabaseSetupStep;
use App\Services\Wizard\Steps\EnvironmentCheckStep;
use App\Services\Wizard\Steps\LicenceValidationStep;
use App\Services\Wizard\WizardStateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class WizardController extends Controller
{
    public function __construct(
        private WizardStateManager $state,
    ) {}

    public function index()
    {
        return redirect()->route('install.step', 1);
    }

    public function show(int $step)
    {
        if (! $this->state->canAccessStep($step)) {
            $lastCompleted = max($this->state->completedSteps() ?: [0]);
            return redirect()->route('install.step', $lastCompleted + 1);
        }

        return match($step) {
            1 => $this->showStep1(),
            2 => $this->showStep2(),
            3 => $this->showStep3(),
            4 => $this->showStep4(),
            5 => $this->showStep5(),
            6 => $this->showStep6(),
            7 => $this->showStep7(),
            default => redirect()->route('install.step', 1),
        };
    }

    public function process(Request $request, int $step)
    {
        if (! $this->state->canAccessStep($step)) {
            return redirect()->route('install.step', 1);
        }

        return match($step) {
            1 => $this->processStep1(),
            2 => $this->processStep2($request),
            3 => $this->processStep3(),
            4 => $this->processStep4($request),
            5 => $this->processStep5($request),
            6 => $this->processStep6($request),
            default => redirect()->route('install.step', $step),
        };
    }

    // ── Step 1: Environment check ─────────────────────────────
    private function showStep1()
    {
        $checker = new EnvironmentCheckStep();
        $result  = $checker->check();

        return Inertia::render('Install/Step1Environment', [
            'checks'     => $result['checks'],
            'allPassed'  => $result['all_passed'],
            'currentStep'=> 1,
        ]);
    }

    private function processStep1()
    {
        $checker = new EnvironmentCheckStep();
        $result  = $checker->check();

        if (! $result['all_passed']) {
            return back()->with('error', 'Please fix all failed checks before continuing.');
        }

        $this->state->markStepComplete(1);
        return redirect()->route('install.step', 2);
    }

    // ── Step 2: Database ──────────────────────────────────────
    private function showStep2()
    {
        return Inertia::render('Install/Step2Database', [
            'currentStep' => 2,
            'saved' => [
                'host'     => $this->state->get('db_host', '127.0.0.1'),
                'port'     => $this->state->get('db_port', '3306'),
                'database' => $this->state->get('db_database', 'nexus'),
                'username' => $this->state->get('db_username', 'root'),
            ],
        ]);
    }

    private function processStep2(Request $request)
    {
        \Log::info('Testing DB connection with', $request->only('host', 'port', 'database', 'username'));
        
        $validated = $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|string',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $step = new DatabaseSetupStep();
        $test = $step->test($validated);

        if (! $test['success']) {
            return back()->withErrors(['connection' => $test['message']]);
        }

        // Write to .env
        $step->writeToEnv($validated);

        // Save to wizard state
        $this->state->setMany([
            'db_host'     => $validated['host'],
            'db_port'     => $validated['port'],
            'db_database' => $validated['database'],
            'db_username' => $validated['username'],
        ]);

        $this->state->markStepComplete(2);
        \Log::info('Step 2 completed successfully');
        return redirect()->route('install.step', 3);
    }

    // ── Step 3: Migrations ────────────────────────────────────
    private function showStep3()
    {
        \Log::info('Showing Step 3: Migrations');
        return Inertia::render('Install/Step3Migrations', [
            'currentStep' => 3,
        ]);
    }

    private function processStep3()
    {
        try {
            // Reload DB config from .env in case it was just written
            $this->refreshDatabaseConfig();

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            $this->state->markStepComplete(3);
            return redirect('/install/step/4?migrated=1');
        } catch (\Throwable $e) {
            return back()->withErrors(['migration' => $e->getMessage()]);
        }
    }

    private function refreshDatabaseConfig(): void
    {
        // Re-read .env and update the live DB config
        $envPath = base_path('.env');
        if (! file_exists($envPath)) return;

        $env = [];
        foreach (file($envPath) as $line) {
            $line = trim($line);
            if (str_contains($line, '=') && ! str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }

        $connection = $env['DB_CONNECTION'] ?? 'mysql';

        config([
            'database.default'                          => $connection,
            "database.connections.{$connection}.host"   => $env['DB_HOST']     ?? '127.0.0.1',
            "database.connections.{$connection}.port"   => $env['DB_PORT']     ?? '3306',
            "database.connections.{$connection}.database"=> $env['DB_DATABASE'] ?? '',
            "database.connections.{$connection}.username"=> $env['DB_USERNAME'] ?? '',
            "database.connections.{$connection}.password"=> $env['DB_PASSWORD'] ?? '',
        ]);

        // Force reconnect
        \Illuminate\Support\Facades\DB::purge($connection);
        \Illuminate\Support\Facades\DB::reconnect($connection);
    }

    // Polling endpoint for migration progress
    public function migrationProgress()
    {
        // Simple check — if users table exists, migrations ran
        $done = \Illuminate\Support\Facades\Schema::hasTable('users');
        return response()->json(['done' => $done]);
    }

    // Test DB connection (AJAX)
    public function checkDb(Request $request)
    {
        $step   = new DatabaseSetupStep();
        $result = $step->test([
            'host'     => $request->host     ?? '127.0.0.1',
            'port'     => $request->port     ?? '3306',
            'database' => $request->database ?? '',
            'username' => $request->username ?? '',
            'password' => $request->password ?? '',
        ]);
        return response()->json($result);
    }

    // ── Step 4: Licence ───────────────────────────────────────
    private function showStep4()
    {
        $savedLicence = $this->state->get('licence_data');

        return Inertia::render('Install/Step4Licence', [
            'currentStep'  => 4,
            'licenceData'  => $savedLicence,
            'isDev'        => app()->environment('local'),
        ]);
    }

    private function processStep4(Request $request)
    {
        // Dev bypass
        if (app()->environment('local') && $request->input('skip_licence')) {
            $this->state->set('licence_data', [
                'valid'    => true,
                'licensee' => 'Development',
                'modules'  => ['Core', 'Financial', 'HR', 'Bookings'],
                'dev'      => true,
            ]);
            $this->state->markStepComplete(4);
            return redirect()->route('install.step', 5);
        }

        $validated = $request->validate([
            'licence_key' => 'required|string',
        ]);

        $step   = new LicenceValidationStep();
        $result = $step->validate($validated['licence_key']);

        if (! $result['valid']) {
            return back()->withErrors(['licence_key' => $result['message']]);
        }

        $this->state->set('licence_data', $result);
        $this->state->markStepComplete(4);
        return redirect()->route('install.step', 5);
    }

    // ── Step 5: Module selection ──────────────────────────────
    private function showStep5()
    {
        $licenceData    = $this->state->get('licence_data', []);
        $licensedModules = $licenceData['modules'] ?? ['Core'];

        $modules = collect([
            'Core'      => ['description' => 'Authentication, users, roles and settings', 'required' => true],
            'Financial' => ['description' => 'Invoicing, quotations, payments and reports', 'required' => false],
            'HR'        => ['description' => 'Employee management and leave tracking', 'required' => false],
            'Bookings'  => ['description' => 'Appointment and resource booking', 'required' => false],
        ])
        ->filter(fn ($m, $name) => in_array($name, $licensedModules, true))
        ->map(fn ($m, $name) => [
            'name'        => $name,
            'description' => $m['description'],
            'required'    => $m['required'],
            'licensed'    => true,
        ])
        ->values();

        return Inertia::render('Install/Step5Modules', [
            'currentStep' => 5,
            'modules'     => $modules,
            'selected'    => $this->state->get('selected_modules', ['Core']),
        ]);
    }

    private function processStep5(Request $request)
    {
        $validated = $request->validate([
            'modules'   => 'required|array|min:1',
            'modules.*' => 'string',
        ]);

        // Core always included
        $selected = array_unique(array_merge(['Core'], $validated['modules']));

        $this->state->set('selected_modules', $selected);
        $this->state->markStepComplete(5);
        return redirect()->route('install.step', 6);
    }

    // ── Step 6: Admin account ─────────────────────────────────
    private function showStep6()
    {
        return Inertia::render('Install/Step6Admin', [
            'currentStep' => 6,
        ]);
    }

    private function processStep6(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed',
        ]);

        // Create super admin
        $user = \App\Models\User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'guard'             => 'internal',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Super Admin');

        // Enable selected modules
        $registry        = app(ModuleRegistryService::class);
        $selectedModules = $this->state->get('selected_modules', ['Core']);

        foreach ($selectedModules as $module) {
            $registry->enable($module);
        }

        $this->state->set('admin_email', $validated['email']);
        $this->state->markStepComplete(6);
        return redirect()->route('install.step', 7);
    }

    // ── Step 7: Complete ──────────────────────────────────────
    private function showStep7()
    {
        $adminEmail      = $this->state->get('admin_email');
        $selectedModules = $this->state->get('selected_modules', ['Core']);

        // Write APP_INSTALLED=true to .env
        $this->writeInstalled();

        // Cache config
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
        } catch (\Throwable) {
            // Non-fatal — app still works without cache
        }

        // Clear wizard state
        $this->state->clear();

        return Inertia::render('Install/Step7Complete', [
            'currentStep'    => 7,
            'adminEmail'     => $adminEmail,
            'activeModules'  => $selectedModules,
        ]);
    }

    private function writeInstalled(): void
    {
        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        if (preg_match('/^APP_INSTALLED=/m', $env)) {
            $env = preg_replace('/^APP_INSTALLED=.*/m', 'APP_INSTALLED=true', $env);
        } else {
            $env .= "\nAPP_INSTALLED=true";
        }

        file_put_contents($envPath, $env);
    }
}
