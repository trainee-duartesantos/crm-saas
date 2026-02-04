<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal,
        public string $subjectLine,
        public string $body
    ) {}

    public function build()
    {
        return $this
            ->subject($this->subjectLine)
            ->view('emails.proposal')
            ->attach(
                Storage::disk('local')->path($this->proposal->file_path),
                [
                    'as' => $this->proposal->original_name,
                    'mime' => 'application/pdf',
                ]
            );
    }
}
