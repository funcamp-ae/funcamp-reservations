<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'description',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the messages for the chat.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the users that belong to the chat.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_users')
            ->withPivot(['joined_at', 'role'])
            ->withTimestamps();
    }

    /**
     * Get the creator of the chat.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the latest message for the chat.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Check if user is a member of this chat.
     */
    public function hasUser($userId)
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Get unique identifier for private chat between two users.
     */
    public static function getPrivateChatId($user1Id, $user2Id)
    {
        $chat = self::where('type', 'private')
            ->whereHas('users', function($query) use ($user1Id) {
                $query->where('user_id', $user1Id);
            })
            ->whereHas('users', function($query) use ($user2Id) {
                $query->where('user_id', $user2Id);
            })
            ->first();

        return $chat ? $chat->id : null;
    }

    /**
     * Create or get private chat between two users.
     */
    public static function createOrGetPrivateChat($user1Id, $user2Id)
    {
        $chatId = self::getPrivateChatId($user1Id, $user2Id);
        
        if ($chatId) {
            return self::find($chatId);
        }

        $chat = self::create([
            'type' => 'private',
            'name' => 'Private Chat',
            'created_by' => $user1Id,
            'is_active' => true
        ]);

        $chat->users()->attach([$user1Id, $user2Id]);

        return $chat;
    }
}
