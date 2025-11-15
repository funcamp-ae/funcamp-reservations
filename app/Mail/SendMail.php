<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $data ;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->data['title'] ?? 'Notification';
        $content = $this->data['content'] ?? $this->data['message'] ?? '';
        
        return $this->subject($subject)
            ->with([
                'data' => $this->data,
                'content' => $content,
                'title' => $subject
            ])
            ->view('emails.send_mail');
    }
}
