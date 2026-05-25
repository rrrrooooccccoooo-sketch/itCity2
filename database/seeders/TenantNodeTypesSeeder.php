<?php

namespace Database\Seeders;

use App\Models\NodeType;
use Illuminate\Database\Seeder;

class TenantNodeTypesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['slug' => 'router', 'name' => 'Router', 'icon' => 'R'],
            ['slug' => 'switch', 'name' => 'Switch', 'icon' => 'SW'],
            ['slug' => 'firewall', 'name' => 'Firewall', 'icon' => 'FW'],
            ['slug' => 'access-point', 'name' => 'Access Point', 'icon' => 'AP'],
            ['slug' => 'vpn-gateway', 'name' => 'VPN Gateway', 'icon' => 'VPN'],
            ['slug' => 'server', 'name' => 'Servidor', 'icon' => 'SV'],
            ['slug' => 'database', 'name' => 'Base de datos', 'icon' => 'DB'],
            ['slug' => 'load-balancer', 'name' => 'Balanceador', 'icon' => 'LB'],
            ['slug' => 'pbx', 'name' => 'PBX / Telefonia', 'icon' => 'PBX'],
            ['slug' => 'ip-camera', 'name' => 'Camara IP', 'icon' => 'CAM'],
            ['slug' => 'printer', 'name' => 'Impresora', 'icon' => 'PRN'],
            ['slug' => 'storage', 'name' => 'Storage', 'icon' => 'ST'],
        ] as $type) {
            NodeType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => $type['name'],
                    'icon' => $type['icon'],
                ]
            );
        }
    }
}