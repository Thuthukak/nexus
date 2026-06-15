<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Services\ModuleRegistryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __construct(
        private ModuleRegistryService $registry,
    ) {}
    public function index(Request $request)
    {
        $activeModules = $this->registry->getEnabledModules();

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type']   ?? '',
                'title'      => $n->data['title']  ?? '',
                'body'       => $n->data['body']   ?? '',
                'module'     => $n->data['module'] ?? '',
                'colour'     => $n->data['colour'] ?? 'blue',
                'action'     => $n->data['action'] ?? null,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->diffForHumans(),
            ])
            ->filter(fn ($n) => in_array($n['module'], $activeModules))
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
