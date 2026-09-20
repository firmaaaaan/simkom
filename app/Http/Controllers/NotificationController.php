<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Endpoint polling: daftar notifikasi terbaru + jumlah belum dibaca.
     * Dipanggil tiap 2 detik oleh komponen lonceng di header.
     */
    public function poll(Request $request): JsonResponse
    {
        $unreadOnly = $request->boolean('unread');

        $notifications = Notification::query()
            ->when($unreadOnly, fn ($q) => $q->unreadFor($request->user()))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'url' => $n->url,
                'is_unread' => $n->read_at === null || ($request->user()->notifications_read_at && $n->read_at > $request->user()->notifications_read_at),
                'time' => $n->created_at->locale('id')->diffForHumans(),
            ]);

        $unreadCount = Notification::unreadFor($request->user())->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Tandai semua notifikasi sudah dibaca oleh user yang sedang login.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        Notification::markAllReadFor($request->user());

        return response()->json(['unread_count' => 0]);
    }
}
