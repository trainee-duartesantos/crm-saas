<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Entity;
use App\Models\Person;
use App\Models\Deal;
use App\Models\Activity;
use App\Models\ActivityLog;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tenant
        |--------------------------------------------------------------------------
        */
        $tenant = Tenant::create([
            'name' => 'Demo Company',
            'plan' => 'pro',
        ]);

        /*
        |--------------------------------------------------------------------------
        | User (Owner)
        |--------------------------------------------------------------------------
        */
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Owner',
            'email' => 'owner@demo.com',
            'password' => Hash::make('password'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Entities
        |--------------------------------------------------------------------------
        */
        $entities = collect([
            [
                'name' => 'ACME Corp',
                'vat' => 'PT123456789',
                'email' => 'info@acme.com',
                'status' => 'active',
            ],
            [
                'name' => 'Globex Solutions',
                'vat' => 'PT987654321',
                'email' => 'contact@globex.com',
                'status' => 'active',
            ],
            [
                'name' => 'Inactive Holdings',
                'vat' => 'PT555666777',
                'email' => 'admin@inactive.com',
                'status' => 'inactive',
            ],
        ])->map(fn ($data) => Entity::create([
            ...$data,
            'tenant_id' => $tenant->id,
            'notes' => 'Seeded demo entity.',
        ]));

        /*
        |--------------------------------------------------------------------------
        | People (linked to entities)
        |--------------------------------------------------------------------------
        */
        $people = collect();

        foreach ($entities as $entity) {
            $people = $people->merge(
                Person::factory()
                    ->count(rand(2, 4))
                    ->create([
                        'tenant_id' => $tenant->id,
                        'entity_id' => $entity->id,
                    ])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Deals (multiple pipeline stages)
        |--------------------------------------------------------------------------
        */
        $deals = collect([
            ['Website Redesign', 3500, 'lead'],
            ['CRM Subscription', 1200, 'proposal'],
            ['Consulting Retainer', 5000, 'follow_up'],
            ['Enterprise Migration', 12000, 'won'],
        ])->map(function ($data) use ($tenant, $entities, $people) {
            return Deal::create([
                'tenant_id' => $tenant->id,
                'title' => $data[0],
                'value' => $data[1],
                'status' => $data[2],
                'entity_id' => $entities->random()->id,
                'person_id' => $people->random()->id,
                'last_activity_at' => now()->subDays(rand(1, 10)),
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Activities (overdue / today / upcoming)
        |--------------------------------------------------------------------------
        */
        foreach ($deals as $deal) {
            Activity::create([
                'tenant_id' => $tenant->id,
                'type' => 'task',
                'title' => "Follow up on {$deal->title}",
                'notes' => 'Seeded activity for demo purposes.',
                'due_at' => now()->addDays(rand(-3, 5)),
                'deal_id' => $deal->id,
                'person_id' => $deal->person_id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Timeline / Activity Logs
        |--------------------------------------------------------------------------
        */
        activity_log('entity.created', $entities->first(), [
            'name' => $entities->first()->name,
        ]);

        activity_log('deal.created', $deals->first(), [
            'title' => $deals->first()->title,
            'value' => $deals->first()->value,
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.tenant.insight',
            'metadata' => [
                'message' =>
                    'AI detected multiple active deals with pending follow-ups. Prioritizing follow-up activities could improve conversion.',
            ],
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.timeline.summary',
            'metadata' => [
                'message' =>
                    'This week shows moderate engagement. One deal was won, several require follow-up.',
            ],
        ]);
    }
}
