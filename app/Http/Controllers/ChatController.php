<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UnreadMessagesUpdated;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\UserType;
use App\Services\ChatService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected $chatService;
    protected $fileUploadService;

    /**
     * Create a new controller instance.
     *
     * @param ChatService $chatService
     * @param FileUploadService $fileUploadService
     * @return void
     */
    public function __construct(ChatService $chatService, FileUploadService $fileUploadService)
    {
        $this->chatService = $chatService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Show the chat page.
     */
    public function chatPage()
    {
        return view('dashboard.chat.index');
    }

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
        

        // Update and broadcast unread counts for all participants except sender
        $this->chatService->updateUnreadCounts($chat, $user->id, $message);

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
            ->where('is_active', true) // Only active users
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

        $updated = $chat->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        if ($updated > 0) {
            // Update and broadcast unread counts for all participants except current user
            $this->chatService->updateUnreadCounts($chat, $user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
            'updated' => $updated
        ]);
    }
    /**
     * Mark a specific message as read.
     */
    public function markMessageAsRead(Message $message)
    {
        $message->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
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

    /**
     * Handle file upload for chat messages using Plupload.
     */
    public function uploadChatFile(Request $request)
    {
        try {
            $result = $this->fileUploadService->handleChunkedUpload(
                'file',
                'chat/uploads',
                [], // Use default allowed types
                1024 // Max 1024MB
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'file_path' => $result['path'],
                    'file_url' => $result['url'],
                    'file_type' => $this->fileUploadService->getFileType($result['path']),
                    'message' => $result['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a file message to a chat.
     */
    public function sendFileMessage(Request $request, Chat $chat)
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
            'file_url' => 'required|string',
            'file_type' => 'required|in:image,video,file',
            'message' => 'nullable|string|max:500' // Optional caption
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
            'message' => $request->message ?? '',
            'type' => $request->file_type,
            'file_path' => $request->file_path,
            'file_url' => $request->file_url,
            'file_type' => $request->file_type,
            'is_read' => false
        ]);

        $message->load('user');

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        // Update and broadcast unread counts
        $this->chatService->updateUnreadCounts($chat, $user->id, $message);

        return response()->json([
            'success' => true,
            'message' => $message
        ], 201);
    }

    /**
     * Add members to a group chat.
     */
    public function addMembers(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Check if user has permission (creator or super-admin)
        if ($chat->created_by != $user->id || !$user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have permission to add members'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Add new members
        $chat->users()->syncWithoutDetaching($request->user_ids);

        return response()->json([
            'success' => true,
            'message' => 'Members added successfully'
        ]);
    }

    /**
     * Remove a member from a group chat.
     */
    public function removeMember(Request $request, Chat $chat, $userId)
    {
        $currentUser = Auth::user();

        // Check if user has permission (creator or super-admin)
        if ($chat->created_by != $currentUser->id || !$currentUser->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have permission to remove members'
            ], 403);
        }

        // Cannot remove the creator
        if ($userId == $chat->created_by) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the group creator'
            ], 422);
        }

        $chat->users()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    /**
     * Update group chat details.
     */
    public function updateGroup(Request $request, Chat $chat)
    {
        $user = Auth::user();

        // Check if user has permission (creator or super-admin)
        if ($chat->created_by != $user->id || !$user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have permission to update this group'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $chat->update($request->only(['name', 'description']));

        return response()->json([
            'success' => true,
            'chat' => $chat,
            'message' => 'Group updated successfully'
        ]);
    }

    /**
     * Promote a member to admin.
     */
    public function promoteToAdmin(Request $request, Chat $chat, $userId)
    {
        $currentUser = Auth::user();

        // Only creator or super-admin can promote
        if ($chat->created_by != $currentUser->id || !$currentUser->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have permission to promote members'
            ], 403);
        }

        // Update user's role in the chat
        $chat->users()->updateExistingPivot($userId, ['role' => 'admin']);

        return response()->json([
            'success' => true,
            'message' => 'Member promoted to admin'
        ]);
    }

    /**
     * Demote an admin to member.
     */
    public function demoteAdmin(Request $request, Chat $chat, $userId)
    {
        $currentUser = Auth::user();

        // Only creator or super-admin can demote
        if ($chat->created_by != $currentUser->id && !$currentUser->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have permission to demote admins'
            ], 403);
        }

        // Update user's role in the chat
        $chat->users()->updateExistingPivot($userId, ['role' => 'member']);

        return response()->json([
            'success' => true,
            'message' => 'Admin demoted to member'
        ]);
    }
}
