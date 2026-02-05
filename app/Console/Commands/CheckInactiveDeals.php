<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;
use App\Models\Activity;
use App\Models\Tenant;
use App\Services\ActivityLogger;

class CheckInactiveDeals extends Command
{
    protected $signature = 'deals:check-inactive';
    protected $description = 'Create follow-up activities for inactive deals';

    public function handle(ActivityLogger $logger): int
    {
        $threshold = now()->subDays(7);

        Tenant::query()->each(function (Tenant $tenant) use ($threshold, $logger) {

            // 🔑 Definir tenant no contexto da app
            app()->instance('tenant', $tenant);

            $deals = Deal::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['qualified', 'proposal'])
                ->where(function ($q) use ($threshold) {
                    $q->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<=', $threshold);
                })
                ->get();

            foreach ($deals as $deal) {

                // ⛔ evitar duplicar follow-ups
                $alreadyExists = Activity::query()
                    ->where('deal_id', $deal->id)
                    ->where('type', 'email')
                    ->whereNull('completed_at')
                    ->where('title', 'Automatic inactivity follow-up')
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                Activity::create([
                    'tenant_id'  => $tenant->id,
                    'created_by'=> null,
                    'deal_id'   => $deal->id,
                    'type'      => 'email',
                    'title'     => 'Automatic inactivity follow-up',
                    'notes'     => 'No activity detected in the last 7 days.',
                    'due_at'    => now()->addDay(),
                ]);

                // 🧾 Log seguro (tenant explícito)
                $logger->log(
                    action: 'deal.inactive.follow_up.created',
                    subject: $deal,
                    metadata: [
                        'days_inactive' => 7,
                    ],
                    actorId: null,
                    tenantId: $tenant->id
                );
            }
        });

        return self::SUCCESS;
    }
}
