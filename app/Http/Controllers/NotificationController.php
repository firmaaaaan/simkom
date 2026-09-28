<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /**
     * Halaman "Semua Notifikasi" — daftar lengkap + hapus per item / semua.
     */
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $notifications->getCollection()->transform(function (Notification $n) use ($request) {
            $n->is_unread = $n->read_at === null
                || ($request->user()->notifications_read_at && $n->read_at > $request->user()->notifications_read_at);

            return $n;
        });

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Hapus satu notifikasi (global — hilang untuk semua user).
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notifikasi dihapus.');
    }

    /**
     * Hapus seluruh riwayat notifikasi.
     */
    public function destroyAll(): RedirectResponse
    {
        Notification::query()->delete();

        return redirect()->route('notifications.index')->with('success', 'Semua notifikasi dihapus.');
    }

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
     * Lonceng (fetch + Accept JSON) menerima JSON; form halaman di-redirect.
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        Notification::markAllReadFor($request->user());

        if ($request->expectsJson()) {
            return response()->json(['unread_count' => 0]);
        }

        return redirect()->route('notifications.index')->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
