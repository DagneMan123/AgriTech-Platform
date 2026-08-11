<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for user
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate($request->get('limit', 20));

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
        $notifications = Auth::user()
            ->unreadNotifications()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id)
    {
        $notification = DatabaseNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
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
        Auth::user()->unreadNotifications->markAsRead();

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
        $notification = DatabaseNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
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
        Auth::user()->notifications()->delete();

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
        $preferences = Auth::user()
            ->notificationPreferences()
            ->first() ?? [];

        return response()->json([
            'success' => true,
            'data' => $preferences,
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

        Auth::user()->notificationPreferences()->updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => Auth::user()->notificationPreferences,
        ]);
    }

    /**
     * Get notification count by type
     */
    public function count()
    {
        $unread = Auth::user()->unreadNotifications()->count();
        $total = Auth::user()->notifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread' => $unread,
                'total' => $total,
            ],
        ]);
    }
}
