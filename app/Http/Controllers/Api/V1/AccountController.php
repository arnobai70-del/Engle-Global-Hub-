<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AccountController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'locale' => app()->getLocale(),
            'unread_notifications' => $user->unreadNotifications()->count(),
        ]]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $page = $request->user()->notifications()->latest()->paginate(20);

        return response()->json([
            'data' => collect($page->items())->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? null,
                'body' => $notification->data['body'] ?? null,
                'url' => $notification->data['url'] ?? null,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toAtomString(),
            ])->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(null, 204);
    }
}
