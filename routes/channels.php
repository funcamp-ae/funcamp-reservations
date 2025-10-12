<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat channels
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    return $chat && $chat->hasUser($user->id);
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Unread message count channel
Broadcast::channel('unread_message_count_for_user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});