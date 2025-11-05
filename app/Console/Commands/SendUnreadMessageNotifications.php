<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use App\Mail\UnreadMessagesNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendUnreadMessageNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:send-unread-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for unread chat messages every 5 minutes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for unread messages...');
        $emailsSent = 0;

        try {
            // Get all users who have unread messages that haven't been notified yet
            $usersWithUnreadMessages = DB::table('messages')
                ->join('chats', 'messages.chat_id', '=', 'chats.id')
                ->join('chat_users', 'chats.id', '=', 'chat_users.chat_id')
                ->where('messages.is_read', false)
                ->whereNull('messages.unread_notification_sent_at') // Only messages that haven't been notified
                ->whereColumn('messages.user_id', '!=', 'chat_users.user_id')
                ->select('chat_users.user_id')
                ->distinct()
                ->pluck('user_id');

            $this->info('Found ' . $usersWithUnreadMessages->count() . ' users with unread messages.');

            foreach ($usersWithUnreadMessages as $userId) {
                $user = User::find($userId);

                if (!$user || !$user->email) {
                    $this->warn("User #{$userId} has no email. Skipping.");
                    continue;
                }

                // Get all chats where the user is a member
                $userChats = Chat::whereHas('users', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->pluck('id');

                // Get unread messages that haven't been notified yet
                $unreadMessages = Message::whereIn('chat_id', $userChats)
                    ->where('user_id', '!=', $userId)  // Messages sent by others
                    ->where('is_read', false)          // Messages not yet read
                    ->whereNull('unread_notification_sent_at')  // Not yet notified
                    ->with(['chat', 'user'])
                    ->latest()
                    ->get();
                
                if ($unreadMessages->isEmpty()) {
                    continue;
                }

                // Group messages by chat
                $messagesByChat = $unreadMessages->groupBy('chat_id');
                
                // Get count
                $totalUnread = $unreadMessages->count();
                $chatsWithUnread = $messagesByChat->count();

                try {
                    // Send the email notification
                    Mail::to($user->email)->send(
                        new UnreadMessagesNotification($user, $unreadMessages, $totalUnread, $chatsWithUnread)
                    );

                    // Mark messages as notified
                    Message::whereIn('id', $unreadMessages->pluck('id'))
                        ->update(['unread_notification_sent_at' => now()]);

                    $this->info("Email sent to {$user->name} ({$user->email}) - {$totalUnread} unread messages");
                    $emailsSent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send email to user #{$userId}: " . $e->getMessage());
                    Log::error('Failed to send unread message notification', [
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Unread message notifications completed. Sent {$emailsSent} emails.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error in SendUnreadMessageNotifications command: ' . $e->getMessage());
            Log::error('SendUnreadMessageNotifications command error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
