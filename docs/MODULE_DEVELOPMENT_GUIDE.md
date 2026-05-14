# Nexus — Module Development Guide

> This document is the single source of truth for building new modules on the
> Nexus platform. Every collaborator must read this before writing a single line
> of code. Consistency is not optional — it is what makes a modular codebase
> maintainable as it grows.

---

## Table of Contents

1. [Platform Overview](#1-platform-overview)
2. [Before You Start](#2-before-you-start)
3. [Scaffolding a New Module](#3-scaffolding-a-new-module)
4. [Module Directory Structure](#4-module-directory-structure)
5. [Backend Coding Guidelines](#5-backend-coding-guidelines)
6. [Frontend Coding Guidelines](#6-frontend-coding-guidelines)
7. [Inter-Module Communication](#7-inter-module-communication)
8. [Database Conventions](#8-database-conventions)
9. [Testing Requirements](#9-testing-requirements)
10. [Git Workflow](#10-git-workflow)
11. [Definition of Done](#11-definition-of-done)

---

## 1. Platform Overview

Nexus is a self-hosted modular business platform built on:

| Layer       | Technology                              |
|-------------|-----------------------------------------|
| Backend     | Laravel 11 · PHP 8.3 · nwidart/modules  |
| Frontend    | Vue 3 · Inertia.js · Pinia · Tailwind 3 |
| Auth        | Laravel Fortify · Spatie Permission     |
| Database    | MySQL 8 · Eloquent · UUID primary keys  |
| Build       | Vite · ESLint                           |

The codebase is split into self-contained modules under `Modules/`. Each module
owns its own routes, controllers, models, migrations, services, events, and Vue
pages. **Modules never import each other directly.** Cross-module communication
flows exclusively through Laravel events and typed DTOs.

---

## 2. Before You Start

### Environment requirements
- PHP 8.3+ with extensions: openssl, pdo, mbstring, tokenizer, xml, bcmath, gd
- Node.js 20+
- MySQL 8+
- Composer 2+

### Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
```

### Understand the existing modules first
Read through at least one existing module end-to-end before building a new one.
The Financial module (`Modules/Financial/`) is the reference implementation.
Study it in this order:
1. `module.json`
2. `app/Providers/`
3. `database/migrations/`
4. `app/Models/`
5. `app/Services/`
6. `app/Http/Controllers/`
7. `routes/web.php`
8. `resources/js/Pages/`

---

## 3. Scaffolding a New Module

### Step 1 — Generate the skeleton
```bash
php artisan module:make {ModuleName}
```
Use PascalCase. Examples: `Bookings`, `Payroll`, `CRM`.

### Step 2 — Update `module.json`
```json
{
    "name": "YourModule",
    "alias": "yourmodule",
    "description": "One sentence describing what this module does.",
    "keywords": [],
    "priority": 30,
    "version": "1.0.0",
    "requires": ["Core"],
    "providers": [
        "Modules\\YourModule\\app\\Providers\\YourModuleServiceProvider"
    ],
    "aliases": {},
    "files": []
}
```
`priority` controls load order. Core = 0, Financial = 10, HR = 20.
Increment by 10 for each new module.

### Step 3 — Create the three Service Providers

Every module needs exactly three providers. Copy the pattern from
`Modules/Financial/app/Providers/` and update the namespace and module name.

**YourModuleServiceProvider.php** — main provider, registers the other two,
loads migrations, views, and config.

**RouteServiceProvider.php** — maps `routes/web.php` under the correct
middleware, prefix, and route name prefix.
```php
Route::middleware(['web', 'auth'])
    ->prefix('yourmodule')
    ->name('yourmodule.')
    ->group(module_path($this->moduleName, 'routes/web.php'));
```

**EventServiceProvider.php** — maps Events to Listeners for this module.
Start with an empty `$listen = []` array and add to it as you build features.

### Step 4 — Register in `bootstrap/providers.php`
```php
Modules\YourModule\app\Providers\YourModuleServiceProvider::class,
```

### Step 5 — Create the module config
```bash
# Modules/YourModule/config/config.php
return [
    'name' => 'YourModule',
    // module-specific config values
];
```

### Step 6 — Create migrations
See [Database Conventions](#8-database-conventions) for naming rules.
All migrations live in `Modules/YourModule/database/migrations/`.

### Step 7 — Run the module migrations
```bash
php artisan module:migrate YourModule
```

### Step 8 — Create a PermissionSeeder
Every module seeds its own permissions on installation.
See [Backend Coding Guidelines §5.5](#55-permissions) for the naming convention.

### Step 9 — Add navigation to the Sidebar
Open `resources/js/shared/components/navigation/Sidebar.vue` and add your
module's entry to the `navItems` array. Use an SVG path from Heroicons v1
outline set (https://v1.heroicons.com) for consistency.

### Step 10 — Add a Dashboard Inertia page
Every module must have a dashboard as its landing page at
`/yourmodule/dashboard`. This is what the sidebar link points to.

---

## 4. Module Directory Structure

```
Modules/YourModule/
├── app/
│   ├── DTOs/               # Typed data transfer objects
│   ├── Events/             # Events dispatched BY this module
│   ├── Http/
│   │   ├── Controllers/    # Thin controllers — delegate to Services
│   │   ├── Middleware/     # Module-specific middleware
│   │   └── Requests/       # Form Request validation classes
│   ├── Listeners/          # Listeners for Core or other module events
│   ├── Models/             # Eloquent models (module-owned tables only)
│   ├── Observers/          # Model observers for intra-module side effects
│   ├── Policies/           # Gate policies for this module's models
│   ├── Providers/
│   │   ├── YourModuleServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Repositories/       # Optional — use for complex query logic
│   └── Services/           # All business logic lives here
├── config/
│   └── config.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   └── Pages/          # Inertia page components
│   └── views/              # Blade views (Inertia entry points only)
├── routes/
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── module.json
```

---

## 5. Backend Coding Guidelines

### 5.1 PHP fundamentals

**Every PHP file must start with:**
```php
<?php

declare(strict_types=1);
```

**All method parameters and return types must be typed:**
```php
// WRONG
function createInvoice($data, $userId) {
    return Invoice::create($data);
}

// CORRECT
public function createInvoice(array $data, int $userId): Invoice
{
    return Invoice::create($data);
}
```

**No `dd()`, `dump()`, or `var_dump()` in committed code.** Ever.
Use `\Log::debug()` for temporary debugging and remove before committing.

### 5.2 Controllers

Controllers are thin. They do three things only:
1. Receive the request
2. Delegate to a Service
3. Return a response

```php
// WRONG — business logic in controller
public function store(Request $request): RedirectResponse
{
    $reference = 'INV-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
    $invoice = Invoice::create([...$request->validated(), 'reference' => $reference]);
    $invoice->lines()->createMany($request->lines);
    // ... 30 more lines
}

// CORRECT — delegate to service
public function store(Request $request): RedirectResponse
{
    $invoice = $this->invoiceService->create(
        $request->validated(),
        $request->user()->id
    );

    return redirect()
        ->route('financial.invoices.show', $invoice)
        ->with('toast', ['type' => 'success', 'title' => 'Invoice created']);
}
```

Always use **Form Requests** for validation. Never call `$request->validate()`
directly in the controller method for anything beyond trivial cases.
```php
public function store(CreateInvoiceRequest $request): RedirectResponse
```

### 5.3 Services

Services contain all business logic. They are injected via the constructor.
Services may call other services within the same module but must never
instantiate or import a class from another module's namespace.

```php
// WRONG — importing across module boundary
use Modules\Financial\app\Models\Invoice;  // inside HR module

// CORRECT — fire an event instead
event(new InvoiceRelatedToEmployee($dto));
```

Services must wrap multi-step database operations in transactions:
```php
public function createEmployee(array $data): Employee
{
    return DB::transaction(function () use ($data) {
        $user     = $this->createUser($data);
        $employee = $this->createEmployeeRecord($user, $data);
        $this->assignDefaultRole($user);
        return $employee;
    });
}
```

### 5.4 Models

- All models use `HasUuids` — no integer primary keys on business entities
- All primary business entities use `SoftDeletes`
- All models have explicit `$fillable` arrays — never use `$guarded = []`
- All JSON columns use Eloquent casts
- Scopes are named descriptively: `scopeActive`, `scopePending`, `scopeForUser`

```php
protected $table    = 'mod_tablename';  // always explicit
protected $fillable = ['col1', 'col2']; // always explicit

protected function casts(): array
{
    return [
        'options'  => 'array',           // JSON columns
        'is_active'=> 'boolean',
        'amount'   => 'decimal:2',
        'due_date' => 'date',
    ];
}
```

**Never pass Eloquent model instances across module boundaries.**
Map them to arrays or DTOs before firing events.

### 5.5 Permissions

All permissions follow this exact pattern — no exceptions:

```
{module}.{resource}.{action}
```

Valid actions: `view`, `create`, `edit`, `delete`, `approve`, `export`, `manage`

Examples:

```
bookings.appointments.view
bookings.appointments.create
bookings.appointments.approve
bookings.resources.manage
```

Portal-facing permissions use a `.portal.` infix:

```
bookings.portal.appointments.view
bookings.portal.appointments.create
```

Seed permissions in a dedicated `PermissionSeeder` inside
`Modules/YourModule/database/seeders/`. Register it to run on module install.

### 5.6 Events and DTOs

When a significant business operation completes, fire an event.
Always attach a typed DTO — never a raw array or model instance.

```php
// 1. Define the DTO in Modules/YourModule/app/DTOs/
final class AppointmentCreatedDTO extends BaseDTO
{
    public function __construct(
        public readonly string  $appointmentId,
        public readonly int     $userId,
        public readonly string  $serviceName,
        public readonly Carbon  $scheduledAt,
    ) {}
}

// 2. Define the Event in Modules/YourModule/app/Events/
final class AppointmentCreated
{
    public function __construct(
        public readonly AppointmentCreatedDTO $appointment
    ) {}
}

// 3. Dispatch from the Service
event(new AppointmentCreated(new AppointmentCreatedDTO(
    appointmentId: $appointment->id,
    userId:        $userId,
    serviceName:   $appointment->service->name,
    scheduledAt:   $appointment->scheduled_at,
)));
```

Document every new cross-module event in `docs/event-contracts.md`.

---

## 6. Frontend Coding Guidelines

### 6.1 Vue component rules

- All components use `<script setup>` — no Options API
- All components in `resources/js/shared/` are imported via `@shared` alias
- Never use relative cross-module imports (`../../`)
- All props must have type definitions

```vue
<!-- WRONG -->
<script setup>
const props = defineProps(['title', 'items'])
</script>

<!-- CORRECT -->
<script setup>
defineProps({
  title: { type: String, required: true },
  items: { type: Array,  default: () => [] },
})
</script>
```

### 6.2 Page components

Every Inertia page component must:
1. Declare its layout explicitly
2. Define all props with types
3. Live in `Modules/YourModule/resources/js/Pages/`

```vue
<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

defineProps({
  records: { type: Array, required: true },
})
</script>
```

Available layouts:
| Layout | Use when |
|--------|----------|
| `AppLayout` | Standard internal back-office page |
| `AuthLayout` | Login, register, password reset |

### 6.3 Shared component library

Always check `resources/js/shared/components/` before building a new component.
If a suitable component already exists, use it. If it does not exist and you
need to build it, add it to the shared library — not inside the module.

Current shared components:
- **Buttons:** `Button`, (IconButton — coming)
- **Display:** `Badge`
- **Form:** `Input`
- **Data:** `DataTable`
- **Navigation:** `Sidebar`, `Topbar`
- **Layouts:** `AppLayout`, `AuthLayout`

### 6.4 Forms

Use `useForm` from Inertia for all form submissions. Never use `axios` directly
for form posts that navigate.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name:  '',
  email: '',
})

function submit() {
  form.post('/route', {
    onFinish: () => form.reset('password'),
  })
}
</script>
```

Always bind `:error="form.errors.fieldname"` on every Input component.
Always disable the submit button while `form.processing` is true.

### 6.5 Permissions in Vue

Use the `usePermission` composable for all UI permission checks.
**Never hard-code role names in templates.**

```vue
<script setup>
import { usePermission } from '@shared/composables/usePermission.js'
const { can } = usePermission()
</script>

<template>
  <Button v-if="can('financial.invoices.approve')">Approve</Button>
</template>
```

Frontend permission checks are UI-only. The backend always re-validates.

### 6.6 Spacing and layout rules

These rules apply to every page — never deviate:

| Element | Class |
|---------|-------|
| Page title to first content block | `mb-6` |
| Between cards / sections | `space-y-6` |
| Card internal padding | `p-6` (standard) · `p-4` (compact) |
| Form fields vertical gap | `space-y-4` |
| Table row minimum height | `h-12` |
| Section headings within a card | `text-xs font-semibold uppercase tracking-wider text-app-text/50` |

### 6.7 Colour usage

Never use raw hex values or arbitrary Tailwind colours in component code.
Always use the semantic CSS variable-backed classes:

```vue
<!-- WRONG -->
<div class="bg-[#1E3A5F] text-white">

<!-- CORRECT -->
<div class="bg-primary text-primary-text">
```

Available semantic colour classes:
`bg-primary`, `text-primary`, `bg-secondary`, `bg-accent`,
`bg-sidebar-bg`, `text-sidebar-text`, `bg-surface`, `bg-background`,
`text-app-text`

Status colours (fixed, not themeable):
Use Tailwind's `green-`, `yellow-`, `red-`, `blue-` scales for status states.
Wrap them in the `Badge` component with the appropriate `type` prop.

---

## 7. Inter-Module Communication

This is the most critical rule in the codebase:

> **A module must never import, instantiate, or reference any class from
> another module's namespace.**

### Allowed
- Firing a Laravel event with a DTO payload
- Listening to events from other modules in your own EventServiceProvider
- Reading from Core's shared tables (users, settings, roles) via Eloquent
- Using shared DTOs from `app/DTOs/`
- Using shared services from `app/Services/` (e.g. LicenceService)

### Not allowed
```php
// NEVER do this inside the HR module:
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoiceService;
$invoice = new Invoice();
```

### The correct pattern
```php
// HR module fires an event
event(new EmployeeSalaryProcessed(new SalaryProcessedDTO(...)));

// Financial module listens in its own EventServiceProvider
protected $listen = [
    EmployeeSalaryProcessed::class => [
        CreateSalaryInvoiceListener::class,
    ],
];
```

Document all cross-module events in `docs/event-contracts.md`.

---

## 8. Database Conventions

### Table naming
Every module prefixes all its tables with a short module abbreviation:

| Module | Prefix | Example |
|--------|--------|---------|
| Core | *(none)* | `users`, `settings` |
| Financial | `fin_` | `fin_invoices` |
| HR / LMS | `hr_` / `lms_` | `hr_employees` |
| Bookings | `bk_` | `bk_bookings` |
| Your module | `abc_` | `abc_records` |

Choose a 2–3 character prefix that does not clash with existing ones.
Register your prefix in this table when you add a new module.

### Migration file naming

YYYY_MM_DD_NNNNNN_create_{prefix}_{table}_table.php

Number migrations within a module sequentially:

2024_01_05_000001_create_bk_resources_table.php
2024_01_05_000002_create_bk_services_table.php
2024_01_05_000003_create_bk_bookings_table.php

### Column conventions
- Primary keys: `$table->uuid('id')->primary()` — always UUID, never int
- Soft deletes: required on all primary business entities
- Foreign keys referencing another module: no database-level FK constraint,
  resolved in application code only (preserves module independence)
- Foreign keys within the same module: use FK constraints normally
- JSON columns: always cast in the model, use `json` column type
- Monetary values: `decimal(15, 2)` — never float
- Status columns: `enum` with all valid values listed explicitly

### No cross-module foreign key constraints
```php
// WRONG — creates a hard database dependency on the Financial module
$table->foreign('invoice_id')->references('id')->on('fin_invoices');

// CORRECT — reference by UUID, resolve in application code
$table->uuid('invoice_id')->nullable()->index();
```

---

## 9. Testing Requirements

Every feature must include tests. No exceptions.

### Backend — PestPHP

Tests live in `Modules/YourModule/tests/`.

**Feature tests** cover the full HTTP request-response cycle:
```php
it('creates an invoice', function () {
    $user     = User::factory()->create()->assignRole('Admin');
    $customer = Customer::factory()->create();

    $response = actingAs($user)
        ->post('/financial/invoices', [
            'customer_id' => $customer->id,
            'due_date'    => now()->addDays(30)->format('Y-m-d'),
            'lines'       => [
                ['description' => 'Service', 'qty' => 1, 'unit_price' => 1000, 'tax_rate' => 15],
            ],
        ]);

    $response->assertRedirect();
    assertDatabaseHas('fin_invoices', ['customer_id' => $customer->id]);
});
```

**Unit tests** cover service classes and business logic in isolation:
```php
it('calculates invoice totals correctly', function () {
    $service = new InvoiceService();
    // test in isolation
});
```

**Event assertions** — always verify events are dispatched:
```php
it('fires InvoiceCreated event on store', function () {
    Event::fake();
    // ... create invoice ...
    Event::assertDispatched(InvoiceCreated::class);
});
```

### Frontend — Vitest
Component tests live alongside the component:
`resources/js/shared/components/Button.test.js`

---

## 10. Git Workflow

### Branch naming

```
feature/{module}-{description}    # feature/financial-recurring-invoices
fix/{issue-number}-{description}  # fix/142-invoice-total-rounding
chore/{description}               # chore/update-dependencies
```

### Commit message format (Conventional Commits)

```
type(scope): short summary under 72 chars
Optional body explaining WHY the change was made. The diff shows what
changed — the commit message explains the intent and trade-offs.
```

Types: `feat`, `fix`, `chore`, `refactor`, `test`, `docs`
Scope: module name in lowercase — `financial`, `hr`, `bookings`, `core`, `ui`

Examples:

```
feat(bookings): add buffer time between appointments
fix(financial): correct compound tax calculation on invoice totals
refactor(hr): extract leave calculation into dedicated service
test(financial): add feature tests for invoice approval workflow
docs(core): update event contracts with BookingConfirmed payload
```

### Pull request checklist
Before opening a PR, confirm:
- [ ] `php artisan test` passes with no failures
- [ ] `npm run build` completes without errors
- [ ] No `dd()`, `dump()`, `console.log()`, or debug code in the diff
- [ ] All new permissions are seeded in a PermissionSeeder
- [ ] All cross-module events are documented in `docs/event-contracts.md`
- [ ] New tables follow the prefix and UUID conventions
- [ ] No direct cross-module class imports

---

## 11. Definition of Done

A feature is not done until all of the following are true:

1. **Works end-to-end** — can be demonstrated in the browser from start to finish
2. **Validated** — Form Requests cover all inputs with meaningful error messages
3. **Authorised** — every controller action calls `$this->authorize()` or uses a
   Form Request with `authorize()`
4. **Tested** — at least one feature test per controller action
5. **Events fired** — significant state changes dispatch events with DTOs
6. **Permissions seeded** — all permissions exist in the database via seeder
7. **Builds clean** — `npm run build` and `php artisan test` both pass
8. **No debug code** — no `dd()`, `dump()`, `console.log()` left in
9. **Committed correctly** — conventional commit message, correct branch name

---

*Document maintained by the Nexus core team.
Last updated: 14 May 2026 — v1.0*