<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadMessagesNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $unreadMessages;
    public $totalUnread;
    public $chatsWithUnread;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $unreadMessages, $totalUnread, $chatsWithUnread)
    {
        $this->user = $user;
        $this->unreadMessages = $unreadMessages;
        $this->totalUnread = $totalUnread;
        $this->chatsWithUnread = $chatsWithUnread;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'لديك رسائل غير مقروءة - You have unread messages',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.unread-messages',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
