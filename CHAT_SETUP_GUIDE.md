# Chat System Setup Guide

This guide will help you set up the real-time chat system for your Laravel application using Pusher, Echo, and jQuery.

## Features

- ✅ Real-time messaging with Pusher and Laravel Echo
- ✅ Private chats between users
- ✅ Group chats with multiple members
- ✅ Unread message notifications
- ✅ Message read status
- ✅ User search and filtering
- ✅ Responsive design with Bootstrap

## Installation Steps

### 1. Run Database Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `chats` - Stores chat rooms (private/group)
- `messages` - Stores chat messages
- `chat_users` - Pivot table for chat membership

### 2. Install Pusher Package

```bash
composer require pusher/pusher-php-server
```

### 3. Configure Environment Variables

Add these variables to your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=pusher

# Pusher Configuration
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=your_pusher_cluster

# Optional Pusher Settings
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### 4. Get Pusher Credentials

1. Sign up at [pusher.com](https://pusher.com)
2. Create a new app
3. Go to "App Keys" tab
4. Copy your credentials to the `.env` file

### 5. Update Broadcasting Configuration

The broadcasting configuration has been updated to include the cluster option. Make sure your `config/broadcasting.php` includes:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'encrypted' => true,
        'useTLS' => true,
    ],
],
```

### 6. Enable Broadcasting Routes

Make sure your `routes/channels.php` includes the chat channels:

```php
// Chat channels
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    return $chat && $chat->hasUser($user->id);
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### 7. Update Queue Configuration (Optional)

For better performance, configure queue workers to handle broadcasting:

```bash
php artisan queue:work
```

## Usage

### 1. Chat Icon

The chat icon has been added to the header with a notification badge showing unread messages.

### 2. Opening Chat

- Click the chat icon in the header
- Choose between "Chats" and "Contacts" tabs
- Click on any chat to open it
- Click on a user to start a private chat

### 3. Creating Groups

- Click "New Group" button in the Chats tab
- Enter group name and description
- Select members
- Click "Create Group"

### 4. Sending Messages

- Type your message in the input field
- Press Enter or click the send button
- Messages are sent in real-time to all chat members

## API Endpoints

The following API endpoints are available:

- `GET /api/chat` - Get all chats for authenticated user
- `POST /api/chat` - Create a new group chat
- `GET /api/chat/{chat}` - Get specific chat with messages
- `POST /api/chat/{chat}/messages` - Send a message
- `POST /api/chat/private` - Create or get private chat
- `GET /api/chat/users` - Get all users for chat creation
- `GET /api/chat/unread-count` - Get unread messages count
- `POST /api/chat/{chat}/mark-read` - Mark messages as read

## File Structure

```
app/
├── Models/
│   ├── Chat.php
│   └── Message.php
├── Http/Controllers/
│   └── ChatController.php
└── Events/
    ├── MessageSent.php
    └── ChatCreated.php

database/migrations/
├── 2025_01_15_000000_create_chats_table.php
├── 2025_01_15_000001_create_messages_table.php
└── 2025_01_15_000002_create_chat_users_table.php

resources/views/dashboard/components/
└── chat-sidebar.blade.php

public/js/
└── chat.js
```

## Customization

### Styling

The chat system uses Bootstrap classes and custom CSS. You can customize the appearance by modifying the styles in `chat-sidebar.blade.php`.

### Authentication

The system uses Laravel Sanctum for API authentication. Make sure your users have proper authentication tokens.

### Real-time Features

The chat system uses Laravel Echo with Pusher for real-time functionality. All messages are broadcast instantly to connected users.

## Troubleshooting

### Common Issues

1. **Messages not appearing in real-time**
   - Check Pusher credentials in `.env`
   - Verify broadcasting driver is set to `pusher`
   - Check browser console for JavaScript errors

2. **Authentication errors**
   - Ensure users are authenticated
   - Check Sanctum token generation
   - Verify API routes are protected

3. **Database errors**
   - Run migrations: `php artisan migrate`
   - Check database connection
   - Verify model relationships

### Debug Mode

Enable debug mode in Pusher dashboard to see connection logs and troubleshoot issues.

## Security Considerations

- All chat channels are private and require authentication
- Users can only access chats they are members of
- Messages are validated before storage
- CSRF protection is enabled for all requests

## Performance Tips

1. Use queue workers for broadcasting
2. Implement message pagination for large chats
3. Add message caching for frequently accessed chats
4. Consider implementing message archiving for old chats

## Support

For issues or questions about the chat system, check the Laravel Echo and Pusher documentation:

- [Laravel Echo Documentation](https://laravel.com/docs/broadcasting)
- [Pusher Documentation](https://pusher.com/docs)
- [Laravel Broadcasting Documentation](https://laravel.com/docs/broadcasting)
