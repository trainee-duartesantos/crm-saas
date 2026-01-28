<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendAutoFollowUps extends Command
{
    protected $signature = 'ai:follow-ups';
    protected $description = 'Send AI follow-ups for overdue activities';

    public function handle(AIService $ai, ActivityLogger $logger)
    {
        $activities = Activity::query()
            ->whereNull('completed_at')
            ->where('due_at', '<', now()->subDay())
            ->with(['person', 'tenant'])
            ->limit(10)
            ->get();

        foreach ($activities as $activity) {
            if (! $activity->person?->email) {
                continue;
            }

            app()->instance('tenant', $activity->tenant);

            $email = $ai->draftEmail(
                goal: "following up on '{$activity->title}'"
            );

            Mail::to($activity->person->email)
                ->send(new FollowUpMail(
                    $email['subject'],
                    $email['body']
                ));

            $logger->log(
                action: 'email.follow_up.auto_sent',
                metadata: [
                    'activity_id' => $activity->id,
                    'to' => $activity->person->email,
                ]
            );
        }

        return self::SUCCESS;
    }
}
