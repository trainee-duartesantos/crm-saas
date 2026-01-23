<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Criar Tenant de teste
        $tenant = Tenant::create([
            'name' => 'Demo Company',
            'plan' => 'free',
        ]);

        // 2️⃣ Owner
        User::create([
            'name' => 'Owner User',
            'email' => 'owner@demo.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]);

        // 3️⃣ Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        // 4️⃣ User normal
        User::create([
            'name' => 'Normal User',
            'email' => 'user@demo.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role' => 'user',
        ]);
    }
}
