<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Proposal;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mail\ProposalMail;
use Illuminate\Support\Facades\Mail;


class ProposalController extends Controller
{
    public function store(Request $request, Deal $deal)
    {
        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $file = $data['file'];

        $path = $file->store(
            'proposals/' . $deal->id,
            'local'
        );


        $proposal = Proposal::create([
            'tenant_id' => app('tenant')->id,
            'deal_id' => $deal->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        app(ActivityLogger::class)->log(
            action: 'proposal.uploaded',
            subject: $proposal,
            metadata: [
                'deal_id' => $deal->id,
                'file' => $proposal->original_name,
            ]
        );

        return back();
    }

    public function send(Request $request, Proposal $proposal)
    {
        abort_if($proposal->tenant_id !== app('tenant')->id, 403);

        $deal = $proposal->deal;
        $person = $deal->person;

        abort_if(! $person || ! $person->email, 422);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        Mail::to($person->email)->send(
            new ProposalMail(
                proposal: $proposal,
                subjectLine: $data['subject'],
                body: $data['body']
            )
        );

        $proposal->update([
            'sent_at' => now(),
            'sent_by' => auth()->id(),
        ]);

        app(ActivityLogger::class)->log(
            action: 'proposal.sent',
            subject: $proposal,
            metadata: [
                'deal_id' => $deal->id,
                'to' => $person->email,
                'file' => $proposal->original_name,
            ]
        );

        return back();
    }

    public function download(Proposal $proposal)
    {
        $this->authorize('view', $proposal);

        return Storage::download($proposal->file_path, $proposal->original_name);
    }

}
