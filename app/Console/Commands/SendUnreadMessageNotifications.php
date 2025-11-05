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
            // Get all users who have unread messages
            $usersWithUnreadMessages = DB::table('messages')
                ->join('chats', 'messages.chat_id', '=', 'chats.id')
                ->join('chat_users', 'chats.id', '=', 'chat_users.chat_id')
                ->where('messages.is_read', false)
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

                // Get all unread messages for this user
                $unreadMessages = Message::getUnreadMessages($userId);
                
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
