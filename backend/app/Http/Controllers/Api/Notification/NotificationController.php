<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for user
     */
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 20);
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread notifications
     */
    public function unread()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Get specific notification
     */
    public function show(string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        Notification::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted',
        ]);
    }

    /**
     * Get notification preferences
     */
    public function preferences()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'email_notifications' => true,
                'sms_notifications' => true,
                'push_notifications' => true,
                'order_updates' => true,
                'product_updates' => true,
                'promotional' => false,
            ],
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'order_updates' => 'sometimes|boolean',
            'product_updates' => 'sometimes|boolean',
            'promotional' => 'sometimes|boolean',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => array_merge([
                'email_notifications' => true,
                'sms_notifications' => true,
                'push_notifications' => true,
                'order_updates' => true,
                'product_updates' => true,
                'promotional' => false,
            ], $validated),
        ]);
    }

    /**
     * Get notification count by type
     */
    public function count()
    {
        $unread = Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
        $total = Notification::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread' => $unread,
                'total' => $total,
            ],
        ]);
    }
}
