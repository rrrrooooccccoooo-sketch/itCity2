<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Grab branch IDs (created by TenantDemoSeeder)
        $branches = Branch::query()->pluck('id', 'name');

        $centralId   = $branches->first();           // fallback: first branch
        $northId     = $branches->get('Campus Norte');
        $westId      = $branches->get('Campus Occidente');

        $users = [
            [
                'name'           => 'Super Admin Demo',
                'email'          => 'superadmin@demo.test',
                'password'       => Hash::make('password'),
                'role'           => 'admin',
                'auth_source'    => 'local',
                'is_active'      => true,
                'access_profile' => 'full_admin',
                'branch_id'      => null,
                'branch_scopes'  => [],
            ],
            [
                'name'           => 'Ops Admin Demo',
                'email'          => 'opsadmin@demo.test',
                'password'       => Hash::make('password'),
                'role'           => 'admin',
                'auth_source'    => 'local',
                'is_active'      => true,
                'access_profile' => 'operations_admin',
                'branch_id'      => $centralId,
                'branch_scopes'  => array_filter([$centralId, $northId]),
            ],
            [
                'name'           => 'Auditor Demo',
                'email'          => 'auditor@demo.test',
                'password'       => Hash::make('password'),
                'role'           => 'user',
                'auth_source'    => 'local',
                'is_active'      => true,
                'access_profile' => 'read_only_auditor',
                'branch_id'      => $centralId,
                'branch_scopes'  => array_filter([$centralId, $northId, $westId]),
            ],
            [
                'name'           => 'Usuario Sin Perfil',
                'email'          => 'sinperfil@demo.test',
                'password'       => Hash::make('password'),
                'role'           => 'user',
                'auth_source'    => 'local',
                'is_active'      => true,
                'access_profile' => null,
                'branch_id'      => $centralId,
                'branch_scopes'  => array_filter([$centralId]),
            ],
            [
                'name'           => 'Usuario Inactivo Demo',
                'email'          => 'inactivo@demo.test',
                'password'       => Hash::make('password'),
                'role'           => 'user',
                'auth_source'    => 'local',
                'is_active'      => false,
                'access_profile' => 'read_only_auditor',
                'branch_id'      => $northId ?? $centralId,
                'branch_scopes'  => [],
            ],
        ];

        foreach ($users as $data) {
            $scopeIds = $data['branch_scopes'];
            unset($data['branch_scopes']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            if (!empty($scopeIds)) {
                $user->branchScopes()->sync($scopeIds);
            }
        }
    }
}
