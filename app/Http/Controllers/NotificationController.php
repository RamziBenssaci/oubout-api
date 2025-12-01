<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
class NotificationController extends Controller
{
    // Admin: Send notification to user
    public function sendToClient(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $notification = Notification::create([
            'client_id' => $data['client_id'],
            'message' => $data['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'data' => $notification,
        ]);
    }

    // User: Get their own notifications (with pagination and filter)
    public function index(Request $request)
    {
        $query = Notification::where('client_id', Auth::id());

        if ($request->has('status')) {
            if ($request->status === 'read') {
                $query->where('is_read', true);
            } elseif ($request->status === 'unread') {
                $query->where('is_read', false);
            }
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'total_items' => $notifications->total(),
                'items_per_page' => $notifications->perPage(),
            ]
        ]);
    }

    // User: Mark one notification as read
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    // User: Mark all as read
    public function markAllAsRead()
    {
        $count = Notification::where('client_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'updated_count' => $count
            ]
        ]);
    }

    // User: Delete notification
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }
}
