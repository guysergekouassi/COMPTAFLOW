<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = InternalNotification::where('receiver_id', Auth::id())
            ->with('sender:id,name,last_name')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => InternalNotification::where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count()
        ]);
    }

    public function markAsRead($id)
    {
        $notification = InternalNotification::where('receiver_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,message'
        ]);

        $notification = InternalNotification::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'company_id' => Auth::user()->company_id,
            'is_read' => false
        ]);

        return response()->json([
            'success' => true,
            'notification' => $notification
        ]);
    }
}
