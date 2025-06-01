<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get all chat rooms for the authenticated user
     */
    public function getChatRooms(): JsonResponse
    {
        $user = Auth::user();
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
        $user = Auth::user();

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

        // Mark messages as read
        $chatRoom->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('isRead', false)
            ->update(['isRead' => true, 'read_at' => now()]);

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() === $limit
        ]);
    }

    /**
     * Search messages in a specific chat room
     */
    public function searchMessages(ChatRoom $chatRoom, Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        $user = Auth::user();
        $searchTerm = $request->input('query'); // Fix: use input() method

        if (!$chatRoom->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $chatRoom->messages()
            ->with('sender')
            ->where('content', 'LIKE', "%{$searchTerm}%")
            ->orderBy('created_at', 'desc')
            ->limit(50)
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
                    ]
                ];
            });

        return response()->json([
            'messages' => $messages,
            'total' => $messages->count()
        ]);
    }

    /**
     * Create a new chat room or return existing one
     */
    public function createOrGetChatRoom(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = Auth::user();
        $otherUser = User::findOrFail($request->user_id);
        // Check if chat room already exists
        $existingRoom = ChatRoom::whereHas('users', function ($query) use ($currentUser) {
                $query->where('users.id', $currentUser->id);
            })
            ->whereHas('users', function ($query) use ($otherUser) {
                $query->where('users.id', $otherUser->id);
            })
            ->first();

        if ($existingRoom) {
            return response()->json([
                'chat_room_id' => $existingRoom->id,
                'is_new' => false
            ]);
        }

        DB::beginTransaction();
        try {
            $chatRoom = ChatRoom::create([
                'status' => 'active',
                'lastActivity' => now(),
            ]);

            $chatRoom->users()->attach([$currentUser->id, $otherUser->id]);

            DB::commit();

            return response()->json([
                'chat_room_id' => $chatRoom->id,
                'is_new' => true
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create chat room'], 500);
        }
    }

    /**
     * Send a message in a chat room
     */
    public function sendMessage(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000'
        ]);

        $user = Auth::user();

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

            $chatRoom->update(['last_activity' => now()]);

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
     * Mark messages as read
     */
    public function markAsRead(ChatRoom $chatRoom): JsonResponse
    {
        $user = Auth::user();

        if (!$chatRoom->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chatRoom->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('isRead', false)
            ->update([
                'isRead' => true,
                'read_at' => now()
            ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Update user's last activity timestamp
     */
    public function updateLastActivity(): JsonResponse
    {
        $user = Auth::user();
        User::where('id', $user->id)->update(['last_activity_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Get chat room details
     */
    public function getChatRoomDetails(ChatRoom $chatRoom): JsonResponse
    {
        $user = Auth::user();

        if (!$chatRoom->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $otherUser = $chatRoom->users->where('id', '!=', $user->id)->first();

        return response()->json([
            'id' => $chatRoom->id,
            'status' => $chatRoom->status,
            'created_at' => $chatRoom->created_at,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->first_name . ' ' . $otherUser->last_name,
                'profile_picture' => $otherUser->profile_picture,
                'last_activity_at' => $otherUser->last_activity_at,
                'is_online' => $otherUser->last_activity_at >= now()->subMinutes(5)
            ],
            'messages_count' => $chatRoom->messages()->count(),
            'last_activity' => $chatRoom->last_activity
        ]);
    }

    /**
     * Search for conversations by contact name
     */
    public function searchConversations(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1'
        ]);

        $user = Auth::user();
        $searchTerm = $request->input('query'); // Fix: use input() method instead of query property

        $chatRooms = ChatRoom::whereHas('users', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->whereHas('users', function($query) use ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
            });
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

        return response()->json([
            'conversations' => $chatRooms,
            'total' => $chatRooms->count()
        ]);
    }
}
