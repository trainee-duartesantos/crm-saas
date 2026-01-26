<?php

namespace App\Mail;

use App\Models\UserInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserInvite $invite) {}

    public function build()
    {
        return $this
            ->subject('You have been invited')
            ->view('emails.user-invite');
    }
}
