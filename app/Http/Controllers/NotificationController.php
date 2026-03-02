<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationLog::where('user_id', auth()->id())
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = NotificationLog::where('user_id', auth()->id())
            ->findOrFail($id);

        // Mark as read
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('notifications.show', compact('notification'));
    }

    public function getUnreadCount()
    {
        return response()->json([
            'count' => NotificationLog::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count()
        ]);
    }

    public function markAsRead($id)
    {
        $notification = NotificationLog::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        NotificationLog::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Send notification to a specific user
     */
    public static function notifyUser($userId, $type, $relatedId, $title, $message)
    {
        $user = User::find($userId);
        if ($user) {
            NotificationLog::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $userId,
                'type' => $type,
                'related_id' => $relatedId,
                'title' => $title,
                'message' => $message,
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Send notification to all residents in a tenant
     */
    public static function notifyResidents($tenantId, $type, $relatedId, $title, $message)
    {
        $residents = User::where('tenant_id', $tenantId)
            ->where('role', 'resident')
            ->get();

        foreach ($residents as $resident) {
            NotificationLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $resident->id,
                'type' => $type,
                'related_id' => $relatedId,
                'title' => $title,
                'message' => $message,
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ]);
        }
    }
}
