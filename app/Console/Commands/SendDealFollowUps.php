<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DealFollowUp;
use App\Models\Activity;
use Illuminate\Support\Facades\Mail;
use App\Mail\FollowUpMail;

class SendDealFollowUps extends Command
{
    protected $signature = 'deals:send-follow-ups';
    protected $description = 'Send automatic deal follow-up emails';

    public function handle(): int
    {
        $followUps = DealFollowUp::query()
            ->where('active', true)
            ->where('next_run_at', '<=', now())
            ->with('deal.person')
            ->get();

        foreach ($followUps as $followUp) {
            $deal = $followUp->deal;

            // STOP CONDITIONS
            if ($deal->status !== 'proposal') {
                $this->stop($followUp, 'deal status changed');
                continue;
            }

            if (! $deal->person?->email) {
                continue;
            }

            // HORÁRIO LABORAL
            $hour = now()->hour;
            if ($hour < config('followups.work_hours.start')
                || $hour >= config('followups.work_hours.end')) {
                $followUp->update([
                    'next_run_at' => nextWorkTime(now()),
                ]);
                continue;
            }

            // TEMPLATE
            $templates = config('followups.templates');
            $template = $templates[$followUp->step % count($templates)];

            Mail::to($deal->person->email)->send(
                new FollowUpMail(
                    $template['subject'],
                    str_replace('{{name}}', $deal->person->first_name, $template['body'])
                )
            );

            Activity::create([
                'tenant_id' => $deal->tenant_id,
                'deal_id' => $deal->id,
                'person_id' => $deal->person_id,
                'type' => 'email',
                'title' => 'Automatic follow-up',
                'notes' => 'Automated follow-up sent.',
            ]);

            activity_log('deal.follow_up.sent', $deal);

            $followUp->increment('step');
            $followUp->update([
                'next_run_at' => nextWorkTime(
                    now()->addDays(config('followups.interval_days'))
                ),
            ]);
        }

        return self::SUCCESS;
    }

    private function stop(DealFollowUp $followUp, string $reason): void
    {
        $followUp->update([
            'active' => false,
            'stopped_at' => now(),
            'stop_reason' => $reason,
        ]);
    }
}
