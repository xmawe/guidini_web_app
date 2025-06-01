<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestChatController extends Controller
{
    /**
     * Get all chat rooms for a specific user
     */
    public function getChatRooms(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        $chatRooms = ChatRoom::whereHas('users', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
            ->with(['latestMessage', 'users'])
            ->orderBy('lastActivity', 'desc')
            ->get()
            ->map(function ($room) use ($user) {
                $otherUser = $room->users->where('id', '!=', $user->id)->first();
                return [
                    'id' => $room->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->first_name . ' ' . $otherUser->last_name,
                        'profile_picture' => $otherUser->profile_picture,
                        'last_activity_at' => $otherUser->last_activity_at,
                        'is_online' => $otherUser->last_activity_at >= now()->subMinutes(5)
                    ],
                    'last_message' => $room->latestMessage ? [
                        'content' => $room->latestMessage->content,
                        'created_at' => $room->latestMessage->created_at,
                        'is_from_me' => $room->latestMessage->sender_id === $user->id,
                    ] : null,
                    'unread_count' => $room->messages()
                        ->where('sender_id', '!=', $user->id)
                        ->where('isRead', false)
                        ->count(),
                ];
            });

        return response()->json($chatRooms);
    }

    /**
     * Get messages for a specific chat room with pagination
     */
    public function getMessages(ChatRoom $chatRoom, Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$chatRoom->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = $request->input('limit', 50);
        $beforeId = $request->input('before_id'); // For pagination

        $query = $chatRoom->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc');

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->limit($limit)
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'is_from_me' => $message->sender_id === $user->id,
                    'is_read' => $message->isRead,
                    'read_at' => $message->read_at,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->first_name . ' ' . $message->sender->last_name,
                        'profile_picture' => $message->sender->profile_picture,
                    ],
                ];
            });

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() === $limit
        ]);
    }

    /**
     * Send a test message in a chat room
     */
    public function sendMessage(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000'
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$chatRoom->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $message = $chatRoom->messages()->create([
                'sender_id' => $user->id,
                'content' => $request->content,
                'isRead' => false,
            ]);

            $chatRoom->update(['lastActivity' => now()]);

            DB::commit();

            return response()->json([
                'id' => $message->id,
                'content' => $message->content,
                'created_at' => $message->created_at,
                'is_from_me' => true,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get all chat rooms (for testing purposes)
     */
    public function getAllChatRooms(): JsonResponse
    {
        $chatRooms = ChatRoom::with(['users', 'latestMessage'])
            ->orderBy('lastActivity', 'desc')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'users' => $room->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                        ];
                    }),
                    'last_message' => $room->latestMessage ? [
                        'content' => $room->latestMessage->content,
                        'created_at' => $room->latestMessage->created_at,
                        'sender_id' => $room->latestMessage->sender_id,
                    ] : null,
                    'message_count' => $room->messages()->count(),
                ];
            });

        return response()->json($chatRooms);
    }
}
