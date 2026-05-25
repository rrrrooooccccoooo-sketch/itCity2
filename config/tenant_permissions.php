<?php

return [
    'profiles' => [
        'full_admin' => [
            'label' => 'Administrador completo',
            'permissions' => ['*'],
        ],
        'operations_admin' => [
            'label' => 'Administrador operativo',
            'permissions' => [
                'tenant.admin',
                'users.view',
                'users.manage',
                'users.reset',
                'inventory.view',
                'inventory.manage',
                'monitoring.view',
                'topology.view',
                'topology.manage',
            ],
        ],
        'read_only_auditor' => [
            'label' => 'Auditor (solo lectura)',
            'permissions' => [
                'users.view',
                'monitoring.view',
                'inventory.view',
                'topology.view',
            ],
        ],
    ],
];
