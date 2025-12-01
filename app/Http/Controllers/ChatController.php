<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
class ChatController extends Controller {
    public function getMessages() {
        $messages = ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'admin' ? 'Admin' : optional($msg->user)->name,
                    'created_at' => $msg->created_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request) {
        $request->validate(['message' => 'required|string']);

        $msg = ChatMessage::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'sender_type' => 'user'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_name' => optional($msg->user)->name,
                'created_at' => $msg->created_at
            ],
            'message' => 'Message sent successfully'
        ]);
    }
}

