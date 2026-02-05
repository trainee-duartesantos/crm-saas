<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Mail;
use App\Mail\FollowUpMail;
use App\Services\AIService;

class SendAutoFollowUps extends Command
{
    protected $signature = 'ai:follow-ups';
    protected $description = 'Send AI follow-ups for overdue activities';

    public function handle(AIService $ai, ActivityLogger $logger)
    {
        $activities = Activity::query()
            ->where('type', 'email')
            ->whereNull('completed_at')
            ->where('due_at', '<=', now())
            ->with(['person', 'deal', 'tenant'])
            ->limit(20)
            ->get();

        foreach ($activities as $activity) {
            if (! $activity->person?->email) {
                continue;
            }

            // 🔑 Garantir tenant correto
            app()->instance('tenant', $activity->tenant);

            // 🤖 AI gera email
            $email = $ai->draftEmail(
                goal: "following up on '{$activity->deal->title}'"
            );

            Mail::to($activity->person->email)->send(
                new FollowUpMail(
                    $email['subject'],
                    $email['body']
                )
            );

            // ✅ marcar como concluída
            $activity->update([
                'completed_at' => now(),
            ]);

            // 🧾 log visível na timeline
            $logger->log(
                action: 'follow_up.sent',
                subject: $activity->deal,
                metadata: [
                    'activity_id' => $activity->id,
                    'to' => $activity->person->email,
                ]
            );
        }

        return self::SUCCESS;
    }

}
