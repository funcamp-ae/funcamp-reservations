<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get all chats for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        
        $chats = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['latestMessage.user', 'users'])
        ->withCount(['messages as unread_count' => function($query) use ($user) {
            $query->where('user_id', '!=', $user->id)->where('is_read', false);
        }])
        ->orderBy('updated_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'chats' => $chats
        ]);
    }

    /**
     * Create a new group chat.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $chat = Chat::create([
            'type' => 'group',
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'is_active' => true
        ]);

        // Add creator and other users to the chat
        $userIds = array_merge([Auth::id()], $request->user_ids);
        $chat->users()->attach($userIds);

        $chat->load(['users', 'creator']);

        return response()->json([
            'success' => true,
            'chat' => $chat,
            'message' => 'Group chat created successfully'
        ], 201);
    }

    /**
     * Get a specific chat with messages.
     */
    public function show(Chat $chat)
    {
        $user = Auth::user();

        // Check if user is a member of this chat
        if (!$chat->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this chat'
            ], 403);
        }

        $messages = $chat->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'chat' => $chat->load('users'),
            'messages' => $messages
        ]);
    }

    /**
     * Send a message to a chat.
     */
    public function sendMessage(Request $request, Chat $chat)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if user is a member of this chat
        if (!$chat->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this chat'
            ], 403);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => 'text',
            'is_read' => false
        ]);

        $message->load('user');

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message
        ], 201);
    }

    /**
     * Create or get a private chat with another user.
     */
    public function createPrivateChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $otherUserId = $request->user_id;
        $currentUserId = Auth::id();

        if ($otherUserId == $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create a private chat with yourself'
            ], 422);
        }

        $chat = Chat::createOrGetPrivateChat($currentUserId, $otherUserId);
        $chat->load(['users', 'latestMessage.user']);

        return response()->json([
            'success' => true,
            'chat' => $chat
        ]);
    }

    /**
     * Get all users for creating group chats.
     */
    public function getUsers()
    {
        $users = User::where('id', '!=', Auth::id())
            ->where('is_active', 1) // Only active users
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
            'count' => $users->count()
        ]);
    }

    /**
     * Get unread messages count for the authenticated user.
     */
    public function getUnreadCount()
    {
        $count = Message::getUnreadCount(Auth::id());

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Get unread messages for the authenticated user.
     */
    public function getUnreadMessages()
    {
        $messages = Message::getUnreadMessages(Auth::id());

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Mark all messages in a chat as read.
     */
    public function markAsRead(Chat $chat)
    {
        $user = Auth::user();

        if (!$chat->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this chat'
            ], 403);
        }

        $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }

    /**
     * Leave a group chat.
     */
    public function leaveChat(Chat $chat)
    {
        $user = Auth::user();

        if (!$chat->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this chat'
            ], 403);
        }

        if ($chat->type === 'private') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot leave a private chat'
            ], 422);
        }

        $chat->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Left the chat successfully'
        ]);
    }
}
