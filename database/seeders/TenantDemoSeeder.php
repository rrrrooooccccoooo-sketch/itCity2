<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Node;
use App\Models\NodeRelation;
use App\Models\NodeType;
use App\Models\PhysicalSpace;
use App\Models\SoftwareSystem;
use Illuminate\Database\Seeder;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantNodeTypesSeeder::class);
        $this->call(EquipmentCatalogSeeder::class);

        $nodeTypes = NodeType::query()
            ->whereIn('slug', ['router', 'switch', 'firewall', 'access-point', 'vpn-gateway', 'server', 'database', 'load-balancer', 'pbx', 'ip-camera', 'printer', 'storage'])
            ->get()
            ->keyBy('slug');

        $branchConfigs = [
            'central' => [
                'name' => 'Campus Central',
                'address' => 'Av. Reforma 123',
                'city' => 'CDMX',
                'state' => 'CDMX',
                'country' => 'México',
                'description' => 'Campus principal con servicios corporativos y core de red.',
                'spaces' => [
                    'dc' => ['name' => 'Data Center Central', 'space_type' => 'site', 'floor' => 'PB', 'room' => 'DC-01'],
                    'idf' => ['name' => 'IDF Principal', 'space_type' => 'idf', 'floor' => 'PB', 'room' => 'IDF-01'],
                    'lobby' => ['name' => 'Lobby Principal', 'space_type' => 'zone', 'floor' => 'PB', 'room' => 'LOBBY'],
                ],
                'nodes' => [
                    'router' => ['name' => 'RTR-CORE-CDMX', 'type' => 'router', 'space' => 'idf', 'ip' => '10.10.0.1', 'code' => 'RTR-CDMX-01'],
                    'firewall' => ['name' => 'FW-PERIM-CDMX', 'type' => 'firewall', 'space' => 'idf', 'ip' => '10.10.0.2', 'code' => 'FW-CDMX-01'],
                    'switch_core' => ['name' => 'SW-CORE-CDMX', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.10.0.10', 'code' => 'SWC-CDMX-01'],
                    'switch_dist' => ['name' => 'SW-DIST-CDMX-P1', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.10.1.10', 'code' => 'SWD-CDMX-01'],
                    'ap' => ['name' => 'AP-LOBBY-CDMX', 'type' => 'access-point', 'space' => 'lobby', 'ip' => '10.10.1.50', 'code' => 'AP-CDMX-01'],
                    'vpn' => ['name' => 'VPN-GW-CDMX', 'type' => 'vpn-gateway', 'space' => 'idf', 'ip' => '10.10.0.20', 'code' => 'VPN-CDMX-01'],
                    'lb' => ['name' => 'LB-APP-CDMX-01', 'type' => 'load-balancer', 'space' => 'dc', 'ip' => '10.10.20.5', 'code' => 'LB-CDMX-01'],
                    'srv_app' => ['name' => 'SRV-APP-CDMX-01', 'type' => 'server', 'space' => 'dc', 'ip' => '10.10.20.10', 'code' => 'APP-CDMX-01'],
                    'srv_db' => ['name' => 'SRV-DB-CDMX-01', 'type' => 'database', 'space' => 'dc', 'ip' => '10.10.20.20', 'code' => 'DB-CDMX-01'],
                    'pbx' => ['name' => 'PBX-CDMX-01', 'type' => 'pbx', 'space' => 'idf', 'ip' => '10.10.5.10', 'code' => 'PBX-CDMX-01'],
                    'storage' => ['name' => 'NAS-CDMX-01', 'type' => 'storage', 'space' => 'dc', 'ip' => '10.10.20.30', 'code' => 'NAS-CDMX-01'],
                ],
            ],
            'norte' => [
                'name' => 'Campus Norte',
                'address' => 'Av. Constitución 880',
                'city' => 'Monterrey',
                'state' => 'Nuevo León',
                'country' => 'México',
                'description' => 'Campus con operaciones regionales y servicios de observabilidad.',
                'spaces' => [
                    'dc' => ['name' => 'Data Center Norte', 'space_type' => 'site', 'floor' => 'PB', 'room' => 'DC-NTE'],
                    'idf' => ['name' => 'IDF Norte', 'space_type' => 'idf', 'floor' => 'PB', 'room' => 'IDF-NTE'],
                    'lobby' => ['name' => 'Lobby Norte', 'space_type' => 'zone', 'floor' => 'PB', 'room' => 'LOBBY'],
                ],
                'nodes' => [
                    'router' => ['name' => 'RTR-CORE-MTY', 'type' => 'router', 'space' => 'idf', 'ip' => '10.20.0.1', 'code' => 'RTR-MTY-01'],
                    'firewall' => ['name' => 'FW-PERIM-MTY', 'type' => 'firewall', 'space' => 'idf', 'ip' => '10.20.0.2', 'code' => 'FW-MTY-01'],
                    'switch_core' => ['name' => 'SW-CORE-MTY', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.20.0.10', 'code' => 'SWC-MTY-01'],
                    'switch_dist' => ['name' => 'SW-DIST-MTY-P2', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.20.2.10', 'code' => 'SWD-MTY-02'],
                    'ap' => ['name' => 'AP-LOBBY-MTY', 'type' => 'access-point', 'space' => 'lobby', 'ip' => '10.20.2.50', 'code' => 'AP-MTY-01'],
                    'vpn' => ['name' => 'VPN-GW-MTY', 'type' => 'vpn-gateway', 'space' => 'idf', 'ip' => '10.20.0.20', 'code' => 'VPN-MTY-01'],
                    'srv_app' => ['name' => 'SRV-APP-MTY-01', 'type' => 'server', 'space' => 'dc', 'ip' => '10.20.20.10', 'code' => 'APP-MTY-01'],
                    'srv_db' => ['name' => 'SRV-DB-MTY-01', 'type' => 'database', 'space' => 'dc', 'ip' => '10.20.20.20', 'code' => 'DB-MTY-01'],
                    'printer' => ['name' => 'PRN-OPS-MTY-01', 'type' => 'printer', 'space' => 'lobby', 'ip' => '10.20.2.80', 'code' => 'PRN-MTY-01'],
                    'storage' => ['name' => 'NAS-MTY-01', 'type' => 'storage', 'space' => 'dc', 'ip' => '10.20.20.30', 'code' => 'NAS-MTY-01'],
                ],
            ],
            'occidente' => [
                'name' => 'Campus Occidente',
                'address' => 'Av. Vallarta 4200',
                'city' => 'Guadalajara',
                'state' => 'Jalisco',
                'country' => 'México',
                'description' => 'Campus enfocado en analítica y continuidad operativa.',
                'spaces' => [
                    'dc' => ['name' => 'Data Center Occidente', 'space_type' => 'site', 'floor' => 'PB', 'room' => 'DC-OCC'],
                    'idf' => ['name' => 'IDF Occidente', 'space_type' => 'idf', 'floor' => 'PB', 'room' => 'IDF-OCC'],
                    'lobby' => ['name' => 'Lobby Occidente', 'space_type' => 'zone', 'floor' => 'PB', 'room' => 'LOBBY'],
                ],
                'nodes' => [
                    'router' => ['name' => 'RTR-CORE-GDL', 'type' => 'router', 'space' => 'idf', 'ip' => '10.30.0.1', 'code' => 'RTR-GDL-01'],
                    'firewall' => ['name' => 'FW-PERIM-GDL', 'type' => 'firewall', 'space' => 'idf', 'ip' => '10.30.0.2', 'code' => 'FW-GDL-01'],
                    'switch_core' => ['name' => 'SW-CORE-GDL', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.30.0.10', 'code' => 'SWC-GDL-01'],
                    'switch_dist' => ['name' => 'SW-DIST-GDL-P3', 'type' => 'switch', 'space' => 'idf', 'ip' => '10.30.3.10', 'code' => 'SWD-GDL-03'],
                    'ap' => ['name' => 'AP-LOBBY-GDL', 'type' => 'access-point', 'space' => 'lobby', 'ip' => '10.30.3.50', 'code' => 'AP-GDL-01'],
                    'vpn' => ['name' => 'VPN-GW-GDL', 'type' => 'vpn-gateway', 'space' => 'idf', 'ip' => '10.30.0.20', 'code' => 'VPN-GDL-01'],
                    'srv_app' => ['name' => 'SRV-APP-GDL-01', 'type' => 'server', 'space' => 'dc', 'ip' => '10.30.20.10', 'code' => 'APP-GDL-01'],
                    'srv_db' => ['name' => 'SRV-DB-GDL-01', 'type' => 'database', 'space' => 'dc', 'ip' => '10.30.20.20', 'code' => 'DB-GDL-01'],
                    'camera' => ['name' => 'CAM-LOBBY-GDL-01', 'type' => 'ip-camera', 'space' => 'lobby', 'ip' => '10.30.3.90', 'code' => 'CAM-GDL-01'],
                    'storage' => ['name' => 'NAS-GDL-01', 'type' => 'storage', 'space' => 'dc', 'ip' => '10.30.20.30', 'code' => 'NAS-GDL-01'],
                ],
            ],
        ];

        $branches = [];
        $spaces = [];
        $nodes = [];

        foreach ($branchConfigs as $branchKey => $branchConfig) {
            $branch = Branch::query()->updateOrCreate(
                ['name' => $branchConfig['name']],
                [
                    'address' => $branchConfig['address'],
                    'city' => $branchConfig['city'],
                    'state' => $branchConfig['state'],
                    'country' => $branchConfig['country'],
                    'description' => $branchConfig['description'],
                ]
            );

            $branches[$branchKey] = $branch;

            foreach ($branchConfig['spaces'] as $spaceKey => $spaceConfig) {
                $spaces[$branchKey . ':' . $spaceKey] = PhysicalSpace::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $spaceConfig['name'],
                    ],
                    [
                        'space_type' => $spaceConfig['space_type'],
                        'floor' => $spaceConfig['floor'],
                        'room' => $spaceConfig['room'],
                    ]
                );
            }

            foreach ($branchConfig['nodes'] as $nodeKey => $nodeConfig) {
                $space = $spaces[$branchKey . ':' . $nodeConfig['space']] ?? null;
                $type = $nodeTypes[$nodeConfig['type']];

                $nodes[$branchKey . ':' . $nodeKey] = Node::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $nodeConfig['name'],
                    ],
                    [
                        'physical_space_id' => $space?->id,
                        'node_type_id' => $type->id,
                        'code' => $nodeConfig['code'],
                        'floor' => $space?->floor,
                        'room' => $space?->room,
                        'ip_address' => $nodeConfig['ip'],
                        'mac_address' => $this->macFor($nodeConfig['ip']),
                        'cable_type' => in_array($nodeConfig['type'], ['router', 'firewall', 'switch', 'server', 'database', 'load-balancer', 'pbx', 'storage'], true) ? 'Fibra' : 'WiFi',
                        'status' => 'active',
                        'is_monitored' => true,
                        'details' => [
                            'environment' => 'demo',
                            'role' => $nodeConfig['type'],
                            'branch' => $branch->name,
                        ],
                    ]
                );
            }
        }

        foreach (array_keys($branchConfigs) as $branchKey) {
            $this->upsertRelation($nodes[$branchKey . ':router'], $nodes[$branchKey . ':firewall'], 'wan', 'WAN1', 'WAN-IN', false, null, 'Enlace proveedor de internet');
            $this->upsertRelation($nodes[$branchKey . ':firewall'], $nodes[$branchKey . ':vpn'], 'linked_to', 'DMZ-1', 'ETH0', false, null, 'Acoplamiento de gateway VPN');
            $this->upsertRelation($nodes[$branchKey . ':firewall'], $nodes[$branchKey . ':switch_core'], 'fiber', 'LAN-CORE', 'Gi0/1', false, null, 'Troncal hacia core');
            $this->upsertRelation($nodes[$branchKey . ':switch_core'], $nodes[$branchKey . ':switch_dist'], 'fiber', 'Te1/0/1', 'Te1/0/24', false, null, 'Backbone de distribución');
            $this->upsertRelation($nodes[$branchKey . ':switch_dist'], $nodes[$branchKey . ':ap'], 'wireless', 'PoE-12', 'Radio0', false, null, 'Cobertura WiFi de lobby');
            $this->upsertRelation($nodes[$branchKey . ':switch_core'], $nodes[$branchKey . ':srv_app'], 'fiber', 'Gi1/0/10', 'eth0', false, null, 'Aplicaciones corporativas');
            $this->upsertRelation($nodes[$branchKey . ':srv_app'], $nodes[$branchKey . ':srv_db'], 'linked_to', 'app-db', 'db-main', false, null, 'Tráfico de aplicación a base de datos');
            $this->upsertRelation($nodes[$branchKey . ':srv_db'], $nodes[$branchKey . ':storage'], 'linked_to', 'backup-net', 'replica-net', false, null, 'Respaldo y replicación local');
        }

        $this->upsertRelation($nodes['central:switch_core'], $nodes['central:lb'], 'fiber', 'Gi1/0/5', 'eth0', false, null, 'Balanceador frontal de aplicaciones');
        $this->upsertRelation($nodes['central:lb'], $nodes['central:srv_app'], 'linked_to', 'vip-erp', 'eth1', false, null, 'Distribucion hacia aplicaciones corporativas');
        $this->upsertRelation($nodes['central:switch_dist'], $nodes['central:pbx'], 'linked_to', 'PoE-18', 'LAN', false, null, 'PBX corporativa para voz interna');
        $this->upsertRelation($nodes['norte:switch_dist'], $nodes['norte:printer'], 'linked_to', 'PoE-07', 'LAN', false, null, 'Impresora de operaciones regionales');
        $this->upsertRelation($nodes['occidente:switch_dist'], $nodes['occidente:camera'], 'linked_to', 'PoE-09', 'LAN', false, null, 'Camara IP para lobby principal');

        $this->upsertRelation($nodes['central:vpn'], $nodes['norte:vpn'], 'vpn', 'Tunnel.10', 'Tunnel.20', true, 'VPN-CDMX-MTY', 'Túnel inter-campus principal');
        $this->upsertRelation($nodes['central:vpn'], $nodes['occidente:vpn'], 'vpn', 'Tunnel.11', 'Tunnel.30', true, 'VPN-CDMX-GDL', 'Túnel inter-campus secundario');
        $this->upsertRelation($nodes['norte:vpn'], $nodes['occidente:vpn'], 'vpn', 'Tunnel.21', 'Tunnel.31', true, 'VPN-MTY-GDL', 'Túnel de respaldo inter-campus');

        $this->upsertRelation($nodes['central:router'], $nodes['norte:router'], 'inter-campus', 'MPLS-A', 'MPLS-A', true, null, 'Backhaul WAN entre CDMX y MTY');
        $this->upsertRelation($nodes['central:router'], $nodes['occidente:router'], 'inter-campus', 'MPLS-B', 'MPLS-B', true, null, 'Backhaul WAN entre CDMX y GDL');

        $this->upsertSoftware($nodes['central:srv_app'], 'ERP ITCity', '4.2.1', 'ITCity Labs', 'erp@itcity.local', 'Proyecto Corporativo', ['stack' => 'Laravel + Vue']);
        $this->upsertSoftware($nodes['central:srv_app'], 'Keycloak IAM', '24.0', 'Red Hat', 'iam@itcity.local', 'Identidad Unificada', ['protocols' => ['OIDC', 'SAML']]);
        $this->upsertSoftware($nodes['central:srv_db'], 'PostgreSQL Cluster', '16', 'PostgreSQL Global', 'dba@itcity.local', 'Core Data', ['replication' => 'sync']);
        $this->upsertSoftware($nodes['central:srv_db'], 'Redis Cache', '7.2', 'Redis', 'dba@itcity.local', 'Cache Corporativo', ['mode' => 'sentinel']);
        $this->upsertSoftware($nodes['central:storage'], 'Veeam Backup', '12.1', 'Veeam', 'backup@itcity.local', 'Respaldo Central', ['policy' => '3-2-1']);
        $this->upsertSoftware($nodes['central:firewall'], 'FortiOS', '7.4', 'Fortinet', 'noc@itcity.local', 'Perímetro CDMX', ['ha' => 'active-passive']);
        $this->upsertSoftware($nodes['central:lb'], 'HAProxy Enterprise', '3.0', 'HAProxy Technologies', 'noc@itcity.local', 'Front Door Apps', ['vip' => '10.10.20.5', 'mode' => 'http']);
        $this->upsertSoftware($nodes['central:pbx'], 'Asterisk PBX', '20', 'Sangoma', 'voz@itcity.local', 'Telefonia Corporativa', ['extensions' => 180, 'sip_trunks' => 2]);

        $this->upsertSoftware($nodes['norte:srv_app'], 'CRM Comercial', '3.8.0', 'ITCity Labs', 'crm@itcity.local', 'Ventas Región Norte', ['integrations' => ['ERP', 'Email']]);
        $this->upsertSoftware($nodes['norte:srv_app'], 'Grafana', '11.0', 'Grafana Labs', 'observabilidad@itcity.local', 'Dashboards Norte', ['datasources' => ['Prometheus', 'Loki']]);
        $this->upsertSoftware($nodes['norte:srv_db'], 'MariaDB Replica', '10.6', 'MariaDB Foundation', 'dba@itcity.local', 'Replica Norte', ['replication' => 'async']);
        $this->upsertSoftware($nodes['norte:firewall'], 'pfSense Plus', '24.03', 'Netgate', 'noc@itcity.local', 'Perímetro MTY', ['vpn' => 'IPsec']);
        $this->upsertSoftware($nodes['norte:switch_core'], 'LibreNMS Agent', '1.93', 'LibreNMS', 'noc@itcity.local', 'Monitoreo Norte', ['snmp' => 'v3']);

        $this->upsertSoftware($nodes['occidente:srv_app'], 'Portal Operaciones', '2.5.4', 'ITCity Labs', 'ops@itcity.local', 'Operaciones Occidente', ['auth' => 'SSO']);
        $this->upsertSoftware($nodes['occidente:srv_app'], 'RabbitMQ', '3.13', 'VMware', 'ops@itcity.local', 'Mensajería Occidente', ['cluster' => true]);
        $this->upsertSoftware($nodes['occidente:srv_db'], 'MySQL Analytics', '8.0', 'Oracle', 'dba@itcity.local', 'Analítica Regional', ['etl' => 'nightly']);
        $this->upsertSoftware($nodes['occidente:storage'], 'MinIO', 'RELEASE.2026-03', 'MinIO', 'storage@itcity.local', 'Objeto S3 Compat', ['tiering' => true]);
        $this->upsertSoftware($nodes['occidente:vpn'], 'StrongSwan', '5.9', 'StrongSwan', 'noc@itcity.local', 'Gateway VPN GDL', ['ike' => 'v2']);

        $this->call(TenantInventorySeeder::class);
    }

    private function upsertRelation(
        Node $from,
        Node $to,
        string $relationType,
        ?string $fromEndpoint,
        ?string $toEndpoint,
        bool $isInterCampus,
        ?string $vpnProfile,
        ?string $notes
    ): void {
        NodeRelation::query()->updateOrCreate(
            [
                'from_node_id' => $from->id,
                'to_node_id' => $to->id,
                'relation_type' => $relationType,
            ],
            [
                'from_endpoint' => $fromEndpoint,
                'to_endpoint' => $toEndpoint,
                'is_inter_campus' => $isInterCampus,
                'vpn_profile' => $vpnProfile,
                'notes' => $notes,
            ]
        );
    }

    private function upsertSoftware(
        Node $node,
        string $name,
        ?string $version,
        ?string $vendor,
        ?string $email,
        ?string $project,
        array $details = []
    ): void {
        SoftwareSystem::query()->updateOrCreate(
            [
                'node_id' => $node->id,
                'name' => $name,
            ],
            [
                'version' => $version,
                'vendor' => $vendor,
                'contact_email' => $email,
                'project_name' => $project,
                'details' => $details,
            ]
        );
    }

    private function macFor(string $ip): string
    {
        $parts = explode('.', $ip);
        $last = array_slice($parts, -2);
        return sprintf('02:11:%02X:%02X:%02X:%02X',
            (int) ($parts[0] ?? 10),
            (int) ($parts[1] ?? 0),
            (int) ($last[0] ?? 0),
            (int) ($last[1] ?? 0)
        );
    }
}
