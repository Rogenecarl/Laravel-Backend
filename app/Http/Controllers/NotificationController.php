<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch the authenticated user's notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Laravel's Notifiable trait gives you these handy relationships
        $unreadNotifications = $user->unreadNotifications;
        $readNotifications = $user->readNotifications()->limit(10)->get(); // Get the 10 most recent read ones

        return response()->json([
            'unread' => $unreadNotifications,
            'read' => $readNotifications,
            'unread_count' => $unreadNotifications->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     *
     * @param Request $request
     * @param string $notificationId
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        $user = $request->user();

        // Find the specific notification that belongs to this user
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return response()->noContent(); // Success, no content to return
        }

        return response()->json(['message' => 'Notification not found.'], 404);
    }

    /**
     * Mark all of the user's unread notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }
}