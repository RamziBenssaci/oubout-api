<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
class AdminChatController extends Controller {
    public function getUsers() {
        $users = User::whereIn('id', function ($q) {
            $q->select('user_id')
              ->from('chat_messages')
              ->distinct();
        })->get()->map(function ($user) {
            $lastMessage = $user->chatMessages()->latest()->first();
            $unread = $user->chatMessages()->where('sender_type', 'user')->count();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_message' => $lastMessage?->message,
                'last_message_time' => $lastMessage?->created_at,
                'unread_count' => $unread,
            ];
        });

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function getMessages(Request $request) {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $messages = ChatMessage::where('user_id', $request->user_id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'admin' ? 'Admin' : optional($msg->user)->name,
                    'user_id' => $msg->user_id,
                    'created_at' => $msg->created_at
                ];
            });

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request) {
        $request->validate([
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        $msg = ChatMessage::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
            'sender_type' => 'admin'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_type' => 'admin',
                'sender_name' => 'Admin',
                'user_id' => $msg->user_id,
                'created_at' => $msg->created_at
            ],
            'message' => 'Message sent successfully'
        ]);
    }
}