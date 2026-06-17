<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject')
            ->latest();

        // Filter by log name / module
        if ($request->filled('module')) {
            $query->where('log_name', strtolower($request->module));
        }

        // Filter by causer (user)
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->paginate(50)->through(fn ($a) => $this->format($a));

        // Log names for filter dropdown
        $modules = Activity::distinct()->pluck('log_name')->sort()->values();

        // Users for filter dropdown
        $users = User::where('guard', 'web')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Core/Pages/ActivityLog/Index', [
            'activities' => $activities,
            'modules'    => $modules,
            'users'      => $users,
            'filters'    => $request->only([
                'module', 'user_id', 'event', 'from', 'to', 'search',
            ]),
        ]);
    }

    /**
     * Per-record activity — used by invoice/quotation/employee show pages.
     */
    public function forSubject(Request $request, string $type, string $id)
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json([]);
        }

        $activities = Activity::where('subject_type', $modelClass)
            ->where('subject_id', $id)
            ->with('causer')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($a) => $this->format($a));

        return response()->json($activities);
    }

    private function format(Activity $a): array
    {
        $properties = $a->properties?->toArray() ?? [];
        $changes    = $this->formatChanges($properties);

        return [
            'id'          => $a->id,
            'log_name'    => $a->log_name,
            'description' => $a->description,
            'event'       => $a->event,
            'causer'      => $a->causer ? [
                'id'   => $a->causer->id,
                'name' => $a->causer->name,
            ] : null,
            'subject_type'=> $a->subject_type
                ? class_basename($a->subject_type)
                : null,
            'subject_id'  => $a->subject_id,
            'changes'     => $changes,
            'properties'  => $properties,
            'created_at'  => $a->created_at->diffForHumans(),
            'created_at_full' => $a->created_at->format('d M Y H:i'),
        ];
    }

    private function formatChanges(array $properties): ?array
    {
        if (empty($properties['old']) && empty($properties['new'])) {
            return null;
        }

        $old = $properties['old'] ?? [];
        $new = $properties['new'] ?? [];

        // Skip sensitive fields
        $skip = ['password', 'remember_token', 'payment_token'];

        $changes = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $key) {
            if (in_array($key, $skip, true)) continue;

            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[] = [
                    'field' => str_replace('_', ' ', ucfirst($key)),
                    'old'   => is_array($oldVal) ? json_encode($oldVal) : (string) ($oldVal ?? '—'),
                    'new'   => is_array($newVal) ? json_encode($newVal) : (string) ($newVal ?? '—'),
                ];
            }
        }

        return $changes ?: null;
    }

    private function resolveModel(string $type): ?string
    {
        return match ($type) {
            'invoice'    => \Modules\Financial\app\Models\Invoice::class,
            'quotation'  => \Modules\Financial\app\Models\Quotation::class,
            'customer'   => \Modules\Financial\app\Models\Customer::class,
            'employee'   => \Modules\HR\app\Models\Employee::class,
            'booking'    => \Modules\Bookings\app\Models\Booking::class,
            'user'       => \App\Models\User::class,
            default      => null,
        };
    }
}
