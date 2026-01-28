<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Person;
use App\Models\Deal;
use App\Models\Activity;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 🏢 Tenant
        $tenant = Tenant::create([
            'name' => 'Demo Company',
            'plan' => 'pro',
        ]);

        // 👤 User
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Owner',
            'email' => 'owner@demo.com',
            'password' => Hash::make('password'),
        ]);

        // 👥 People
        $people = Person::factory()
            ->count(5)
            ->create([
                'tenant_id' => $tenant->id,
            ]);

        // 💼 Deals
        $deals = collect([
            ['Website Redesign', 3500, 'lead'],
            ['CRM Subscription', 1200, 'proposal'],
            ['Consulting Retainer', 5000, 'follow_up'],
        ])->map(function ($data) use ($tenant, $people) {
            return Deal::create([
                'tenant_id' => $tenant->id,
                'title' => $data[0],
                'value' => $data[1],
                'status' => $data[2],
                'person_id' => $people->random()->id,
                'last_activity_at' => now()->subDays(rand(1, 10)),
            ]);
        });

        // 📅 Activities
        foreach ($deals as $deal) {
            Activity::create([
                'tenant_id' => $tenant->id,
                'type' => 'task',
                'title' => "Follow up on {$deal->title}",
                'due_at' => now()->addDays(rand(-3, 5)),
                'deal_id' => $deal->id,
                'person_id' => $deal->person_id,
            ]);
        }

        // 🧾 Timeline logs
        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.timeline.summary',
            'metadata' => [
                'message' => 'AI detected moderate engagement with several pending follow-ups.',
            ],
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.tenant.insight',
            'metadata' => [
                'message' => 'Engagement is moderate. Following up on pending deals could improve conversion.',
            ],
        ]);
    }
}
