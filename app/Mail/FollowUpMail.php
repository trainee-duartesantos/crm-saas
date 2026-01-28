<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpMail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $body
    ) {}

    public function build()
    {
        return $this
            ->subject($this->subjectLine)
            ->view('emails.follow-up');
    }
}
