<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $chat = ChatMessage::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'message' => $request->message,
        ]);

        // Optionnel : Envoyer une notification interne à la cloche
        \App\Models\InternalNotification::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->recipient_id,
            'title' => 'Nouveau message de ' . Auth::user()->name,
            'message' => mb_substr($request->message, 0, 50) . '...',
            'type' => 'chat',
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $chat,
            'formatted_time' => $chat->created_at->format('d/m/Y H:i'),
        ]);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $userId = Auth::id();
        $recipientId = $request->recipient_id;

        $messages = ChatMessage::where(function ($query) use ($userId, $recipientId) {
            $query->where('sender_id', $userId)
                  ->where('recipient_id', $recipientId);
        })->orWhere(function ($query) use ($userId, $recipientId) {
            $query->where('sender_id', $recipientId)
                  ->where('recipient_id', $userId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        $formatted = $messages->map(function ($msg) use ($userId) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'message' => $msg->message,
                'is_me' => $msg->sender_id === $userId,
                'time' => $msg->created_at->format('H:i'),
                'date' => $msg->created_at->format('d/m/Y'),
            ];
        });

        return response()->json($formatted);
    }
}
