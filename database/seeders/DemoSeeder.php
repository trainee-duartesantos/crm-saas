<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Entity;       // ⬅️ ADICIONAR
use App\Models\Company;
use App\Models\Person;
use App\Models\Deal;
use App\Models\Product;
use App\Models\DealProduct;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\UserInvite;

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
            'name' => 'Demo Company SA',
            'plan' => 'pro',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@demo.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'User',
            'email' => 'user@demo.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Invites
        |--------------------------------------------------------------------------
        */
        UserInvite::create([
            'tenant_id' => $tenant->id,
            'email' => 'pending@demo.com',
            'token' => Str::uuid(),
            'accepted_at' => null,
        ]);

        UserInvite::create([
            'tenant_id' => $tenant->id,
            'email' => 'accepted@demo.com',
            'token' => Str::uuid(),
            'accepted_at' => now()->subDays(3),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Entities (4 entidades como pediste)
        |--------------------------------------------------------------------------
        */
        $entities = collect([
            Entity::create([
                'tenant_id' => $tenant->id,
                'name' => 'TechSolutions Lda',
                'vat' => 'PT500123456',
                'email' => 'info@techsolutions.pt',
                'phone' => '+351 210 123 456',
                'website' => 'https://techsolutions.pt',
                'status' => 'active',
                'notes' => 'Cliente desde 2022, excelente pagador.',
            ]),
            Entity::create([
                'tenant_id' => $tenant->id,
                'name' => 'GlobalExport SA',
                'vat' => 'PT501987654',
                'email' => 'geral@globalexport.pt',
                'phone' => '+351 220 987 654',
                'website' => 'https://globalexport.pt',
                'status' => 'active',
                'notes' => 'Potencial cliente internacional.',
            ]),
            Entity::create([
                'tenant_id' => $tenant->id,
                'name' => 'InnovaTech',
                'vat' => 'PT502456789',
                'email' => 'contact@innova.tech',
                'phone' => '+351 230 456 789',
                'website' => 'https://innova.tech',
                'status' => 'inactive',
                'notes' => 'Cliente antigo, sem atividade recente.',
            ]),
            Entity::create([
                'tenant_id' => $tenant->id,
                'name' => 'StartUpFast Lda',
                'vat' => 'PT503789123',
                'email' => 'hello@startupfast.pt',
                'phone' => '+351 910 789 123',
                'website' => 'https://startupfast.pt',
                'status' => 'active',
                'notes' => 'Startup em crescimento rápido.',
            ]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Companies (3 empresas simples)
        |--------------------------------------------------------------------------
        */
        $companies = collect([
            Company::create([
                'tenant_id' => $tenant->id,
                'name' => 'ACME Corporation',
            ]),
            Company::create([
                'tenant_id' => $tenant->id,
                'name' => 'Globex Industries',
            ]),
            Company::create([
                'tenant_id' => $tenant->id,
                'name' => 'Inactive Holdings',
            ]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | People
        |--------------------------------------------------------------------------
        */
        $people = collect([
            Person::create([
                'tenant_id' => $tenant->id,
                'company_id' => $companies[0]->id,
                'first_name' => 'Ana',
                'last_name' => 'Silva',
                'email' => 'ana.silva@acme.com',
            ]),
            Person::create([
                'tenant_id' => $tenant->id,
                'company_id' => $companies[0]->id,
                'first_name' => 'João',
                'last_name' => 'Costa',
                'email' => 'joao.costa@acme.com',
            ]),
            Person::create([
                'tenant_id' => $tenant->id,
                'company_id' => $companies[1]->id,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria@globex.com',
            ]),
            Person::create([
                'tenant_id' => $tenant->id,
                'company_id' => $companies[3]->id ?? $companies[2]->id,
                'first_name' => 'Pedro',
                'last_name' => 'Ferreira',
                'email' => 'pedro@startupfast.pt',
            ]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Products (6 produtos como pediste)
        |--------------------------------------------------------------------------
        */
        $products = collect([
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'CRM Basic Plan',
                'unit_price' => 29,
                'cost' => 5,
            ]),
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'CRM Pro Plan',
                'unit_price' => 59,
                'cost' => 15,
            ]),
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Enterprise Add-on',
                'unit_price' => 199,
                'cost' => 40,
            ]),
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'AI Analytics Module',
                'unit_price' => 89,
                'cost' => 25,
            ]),
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'API Integration',
                'unit_price' => 149,
                'cost' => 35,
            ]),
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => 'Priority Support',
                'unit_price' => 39,
                'cost' => 10,
            ]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Deals (8 deals como pediste)
        |--------------------------------------------------------------------------
        */
        $deals = collect([
            // Lead
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Website Redesign Project',
                'status' => 'lead',
                'value' => 7500,
                'person_id' => $people[0]->id,
                'last_activity_at' => now()->subDays(2),
            ]),
            // Proposal
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'CRM Implementation',
                'status' => 'proposal',
                'value' => 3200,
                'person_id' => $people[2]->id,
                'last_activity_at' => now()->subDays(1),
            ]),
            // Follow-up
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Marketing Automation',
                'status' => 'follow_up',
                'value' => 5800,
                'person_id' => $people[1]->id,
                'last_activity_at' => now()->subDays(3),
            ]),
            // Won
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Enterprise Migration',
                'status' => 'won',
                'value' => 18500,
                'person_id' => $people[3]->id,
                'last_activity_at' => now()->subDays(5),
            ]),
            // Lost
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'E-commerce Platform',
                'status' => 'lost',
                'value' => 6200,
                'person_id' => $people[0]->id,
                'last_activity_at' => now()->subDays(10),
            ]),
            // Lead
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Mobile App Development',
                'status' => 'lead',
                'value' => 12500,
                'person_id' => $people[2]->id,
                'last_activity_at' => now()->subDays(1),
            ]),
            // Proposal
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Data Analytics Suite',
                'status' => 'proposal',
                'value' => 8900,
                'person_id' => $people[1]->id,
                'last_activity_at' => now()->subHours(12),
            ]),
            // Won
            Deal::create([
                'tenant_id' => $tenant->id,
                'title' => 'Cloud Infrastructure',
                'status' => 'won',
                'value' => 22500,
                'person_id' => $people[3]->id,
                'last_activity_at' => now()->subDays(7),
            ]),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Deal Products (mais associações)
        |--------------------------------------------------------------------------
        */
        // Deal 3 (Won) - Enterprise Migration
        DealProduct::create([
            'tenant_id' => $tenant->id,
            'deal_id' => $deals[3]->id,
            'product_id' => $products[1]->id, // CRM Pro
            'quantity' => 15,
            'unit_price' => $products[1]->unit_price,
        ]);

        DealProduct::create([
            'tenant_id' => $tenant->id,
            'deal_id' => $deals[3]->id,
            'product_id' => $products[2]->id, // Enterprise Add-on
            'quantity' => 2,
            'unit_price' => $products[2]->unit_price,
        ]);

        // Deal 7 (Won) - Cloud Infrastructure
        DealProduct::create([
            'tenant_id' => $tenant->id,
            'deal_id' => $deals[7]->id,
            'product_id' => $products[4]->id, // API Integration
            'quantity' => 3,
            'unit_price' => $products[4]->unit_price,
        ]);

        DealProduct::create([
            'tenant_id' => $tenant->id,
            'deal_id' => $deals[7]->id,
            'product_id' => $products[5]->id, // Priority Support
            'quantity' => 12,
            'unit_price' => $products[5]->unit_price,
        ]);

        // Deal 1 (Proposal) - CRM Implementation
        DealProduct::create([
            'tenant_id' => $tenant->id,
            'deal_id' => $deals[1]->id,
            'product_id' => $products[0]->id, // CRM Basic
            'quantity' => 8,
            'unit_price' => $products[0]->unit_price,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Activities (uma por deal + extras)
        |--------------------------------------------------------------------------
        */
        foreach ($deals as $deal) {
            Activity::create([
                'tenant_id' => $tenant->id,
                'title' => "Follow up: {$deal->title}",
                'type' => 'task',
                'due_at' => now()->addDays(rand(-3, 7)),
                'deal_id' => $deal->id,
                'person_id' => $deal->person_id,
                'assigned_to' => rand(0, 1) ? $owner->id : $admin->id,
            ]);
        }

        // Atividades adicionais
        Activity::create([
            'tenant_id' => $tenant->id,
            'title' => 'Reunião mensal com equipa',
            'type' => 'meeting',
            'due_at' => now()->addDays(2)->setTime(10, 0),
            'assigned_to' => $owner->id,
        ]);

        Activity::create([
            'tenant_id' => $tenant->id,
            'title' => 'Preparar proposta para TechSolutions',
            'type' => 'task',
            'due_at' => now()->addDays(1)->setTime(14, 30),
            'person_id' => $people[0]->id,
            'assigned_to' => $admin->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Activity Logs
        |--------------------------------------------------------------------------
        */
        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.tenant.insight',
            'metadata' => [
                'message' => 'Pipeline sólido com 2 deals ganhos este mês. Foco em converter propostas pendentes pode aumentar receita em 35%.',
                'confidence' => 0.92,
            ],
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'ai.timeline.summary',
            'metadata' => [
                'message' => 'Atividade intensa nos últimos 7 dias com 5 follow-ups realizados. Próxima semana crítica para negociações.',
            ],
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'deal.status.changed',
            'metadata' => [
                'deal_id' => $deals[3]->id,
                'old_status' => 'proposal',
                'new_status' => 'won',
                'value' => 18500,
            ],
        ]);

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'action' => 'user.login',
            'metadata' => [
                'user_id' => $owner->id,
                'ip' => '192.168.1.100',
            ],
        ]);
    }
}