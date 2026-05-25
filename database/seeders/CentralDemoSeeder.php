<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@itcity.local'],
            [
                'name' => 'Admin ITCity',
                'password' => Hash::make('Admin123*'),
            ]
        );

        $tenant = Tenant::query()->firstOrCreate(
            ['id' => 'demo-itcity'],
            [
                'company_name' => 'ITCity Demo',
                'logo_url' => null,
                'plan' => 'business',
                'is_active' => true,
                'data' => [
                    'company_name' => 'ITCity Demo',
                    'billing_email' => 'billing@itcity.local',
                    'plan' => 'business',
                ],
            ]
        );

        $tenant->domains()->firstOrCreate([
            'domain' => 'demo-itcity.localhost',
        ]);
    }
}
