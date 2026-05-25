<?php

namespace Database\Seeders;

use App\Models\EquipmentBrand;
use App\Models\EquipmentModel;
use Illuminate\Database\Seeder;

class EquipmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [

            // ── FORTINET ────────────────────────────────────────────────────
            'Fortinet' => [
                // Firewalls / UTM
                ['type' => 'firewall',      'name' => 'FortiGate 40F'],
                ['type' => 'firewall',      'name' => 'FortiGate 60F'],
                ['type' => 'firewall',      'name' => 'FortiGate 80F'],
                ['type' => 'firewall',      'name' => 'FortiGate 100F'],
                ['type' => 'firewall',      'name' => 'FortiGate 200F'],
                ['type' => 'firewall',      'name' => 'FortiGate 400F'],
                ['type' => 'firewall',      'name' => 'FortiGate 600F'],
                ['type' => 'firewall',      'name' => 'FortiGate 1000F'],
                // Access Points
                ['type' => 'access-point',  'name' => 'FortiAP 221E',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'FortiAP 223E',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'FortiAP 231F',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'FortiAP 234F',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'FortiAP 431F',
                    'radius_min' => 35, 'radius_max' => 80, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'FortiAP 433F',
                    'radius_min' => 35, 'radius_max' => 80, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'FortiAP U431F',
                    'radius_min' => 40, 'radius_max' => 90, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'FortiAP U433F',
                    'radius_min' => 40, 'radius_max' => 100, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                // Switches
                ['type' => 'switch',        'name' => 'FortiSwitch 108F'],
                ['type' => 'switch',        'name' => 'FortiSwitch 124F'],
                ['type' => 'switch',        'name' => 'FortiSwitch 148F'],
                ['type' => 'switch',        'name' => 'FortiSwitch 248F'],
                ['type' => 'switch',        'name' => 'FortiSwitch 424F'],
                ['type' => 'switch',        'name' => 'FortiSwitch 448F'],
            ],

            // ── XTREAM / XTREME ─────────────────────────────────────────────
            'Xtream' => [
                ['type' => 'access-point',  'name' => 'Xtream AC1200',
                    'radius_min' => 15, 'radius_max' => 40, 'signal' => -60,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Xtream AC1800',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -58,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Xtream AC2100',
                    'radius_min' => 25, 'radius_max' => 55, 'signal' => -57,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Xtream AX1800',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Xtream AX3000',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'router',        'name' => 'Xtream Router N300'],
                ['type' => 'router',        'name' => 'Xtream Router AC1200'],
                ['type' => 'switch',        'name' => 'Xtream Switch 8P'],
                ['type' => 'switch',        'name' => 'Xtream Switch 16P'],
                ['type' => 'switch',        'name' => 'Xtream Switch 24P PoE'],
            ],

            // ── UBIQUITI ─────────────────────────────────────────────────────
            'Ubiquiti' => [
                ['type' => 'access-point',  'name' => 'UAP-AC-Lite',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -58,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'UAP-AC-LR',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'UAP-AC-PRO',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'UAP-AC-HD',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'U6-Lite',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -58,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'U6-LR',
                    'radius_min' => 30, 'radius_max' => 80, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'U6-Pro',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'U6-Mesh',
                    'radius_min' => 30, 'radius_max' => 75, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'UAP-BeaconHD',
                    'radius_min' => 15, 'radius_max' => 35, 'signal' => -60,
                    'pattern' => 'sphere', 'height' => 1.0],
                ['type' => 'router',        'name' => 'UniFi Dream Machine Pro'],
                ['type' => 'router',        'name' => 'UniFi Dream Router'],
                ['type' => 'switch',        'name' => 'UniFi Switch 8'],
                ['type' => 'switch',        'name' => 'UniFi Switch 16 PoE'],
                ['type' => 'switch',        'name' => 'UniFi Switch 24'],
                ['type' => 'switch',        'name' => 'UniFi Switch 48'],
            ],

            // ── CISCO ────────────────────────────────────────────────────────
            'Cisco' => [
                ['type' => 'access-point',  'name' => 'Cisco Aironet 1830',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -57,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Cisco Aironet 2800',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'Cisco Aironet 3800',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'Cisco Catalyst 9115',
                    'radius_min' => 25, 'radius_max' => 65, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'Cisco Catalyst 9120',
                    'radius_min' => 30, 'radius_max' => 75, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'switch',        'name' => 'Cisco Catalyst 2960-X'],
                ['type' => 'switch',        'name' => 'Cisco Catalyst 3650'],
                ['type' => 'switch',        'name' => 'Cisco Catalyst 3850'],
                ['type' => 'switch',        'name' => 'Cisco Catalyst 9200'],
                ['type' => 'switch',        'name' => 'Cisco Catalyst 9300'],
                ['type' => 'router',        'name' => 'Cisco ISR 1111'],
                ['type' => 'router',        'name' => 'Cisco ISR 4321'],
                ['type' => 'router',        'name' => 'Cisco ISR 4331'],
                ['type' => 'firewall',      'name' => 'Cisco ASA 5506-X'],
                ['type' => 'firewall',      'name' => 'Cisco ASA 5516-X'],
                ['type' => 'firewall',      'name' => 'Cisco Firepower 1010'],
                ['type' => 'firewall',      'name' => 'Cisco Firepower 1120'],
            ],

            // ── TP-LINK / TP-LINK OMADA ──────────────────────────────────────
            'TP-Link' => [
                ['type' => 'access-point',  'name' => 'EAP225',
                    'radius_min' => 15, 'radius_max' => 40, 'signal' => -60,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'EAP245',
                    'radius_min' => 20, 'radius_max' => 50, 'signal' => -58,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'EAP265 HD',
                    'radius_min' => 20, 'radius_max' => 55, 'signal' => -57,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'EAP620 HD',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'EAP670',
                    'radius_min' => 25, 'radius_max' => 65, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'EAP773',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'switch',        'name' => 'TL-SG1016PE'],
                ['type' => 'switch',        'name' => 'TL-SG2428P'],
                ['type' => 'router',        'name' => 'TL-ER7206'],
                ['type' => 'router',        'name' => 'TL-ER8411'],
            ],

            // ── MIKROTIK ─────────────────────────────────────────────────────
            'MikroTik' => [
                ['type' => 'access-point',  'name' => 'hAP ac²',
                    'radius_min' => 15, 'radius_max' => 40, 'signal' => -60,
                    'pattern' => 'omni-donut', 'height' => 2.0],
                ['type' => 'access-point',  'name' => 'cAP ac',
                    'radius_min' => 15, 'radius_max' => 35, 'signal' => -62,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'access-point',  'name' => 'wAP ac',
                    'radius_min' => 10, 'radius_max' => 30, 'signal' => -62,
                    'pattern' => 'sector-120', 'height' => 2.6],
                ['type' => 'router',        'name' => 'hEX S RB760iGS'],
                ['type' => 'router',        'name' => 'CCR1009-7G-1C'],
                ['type' => 'router',        'name' => 'CCR2004-1G-12S+2XS'],
                ['type' => 'switch',        'name' => 'CRS326-24G-2S+RM'],
                ['type' => 'switch',        'name' => 'CRS354-48P-4S+2Q+RM'],
            ],

            // ── HP / ARUBA ───────────────────────────────────────────────────
            'HP / Aruba' => [
                ['type' => 'access-point',  'name' => 'Aruba AP-515',
                    'radius_min' => 25, 'radius_max' => 60, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'Aruba AP-535',
                    'radius_min' => 30, 'radius_max' => 70, 'signal' => -55,
                    'pattern' => 'omni-donut', 'height' => 3.0],
                ['type' => 'access-point',  'name' => 'Aruba AP-615',
                    'radius_min' => 25, 'radius_max' => 65, 'signal' => -56,
                    'pattern' => 'omni-donut', 'height' => 2.6],
                ['type' => 'switch',        'name' => 'Aruba 2530-24G'],
                ['type' => 'switch',        'name' => 'Aruba 2930F-24G'],
                ['type' => 'switch',        'name' => 'Aruba 6300F-24G'],
                ['type' => 'server',        'name' => 'HP ProLiant DL360 Gen10'],
                ['type' => 'server',        'name' => 'HP ProLiant DL380 Gen10'],
                ['type' => 'server',        'name' => 'HP ProLiant ML350 Gen10'],
                ['type' => 'desktop',       'name' => 'HP EliteDesk 800 G6'],
                ['type' => 'desktop',       'name' => 'HP ProDesk 400 G7'],
                ['type' => 'laptop',        'name' => 'HP EliteBook 840 G8'],
                ['type' => 'laptop',        'name' => 'HP ProBook 450 G9'],
            ],

            // ── DELL ─────────────────────────────────────────────────────────
            'Dell' => [
                ['type' => 'server',        'name' => 'PowerEdge R650'],
                ['type' => 'server',        'name' => 'PowerEdge R750'],
                ['type' => 'server',        'name' => 'PowerEdge T350'],
                ['type' => 'desktop',       'name' => 'OptiPlex 7090'],
                ['type' => 'desktop',       'name' => 'OptiPlex 5090'],
                ['type' => 'laptop',        'name' => 'Latitude 5420'],
                ['type' => 'laptop',        'name' => 'Latitude 7420'],
            ],

            // ── LENOVO ───────────────────────────────────────────────────────
            'Lenovo' => [
                ['type' => 'desktop',       'name' => 'ThinkCentre M70q Gen3'],
                ['type' => 'desktop',       'name' => 'ThinkCentre M80q Gen3'],
                ['type' => 'laptop',        'name' => 'ThinkPad L14 Gen3'],
                ['type' => 'laptop',        'name' => 'ThinkPad T14 Gen3'],
                ['type' => 'laptop',        'name' => 'ThinkPad X1 Carbon Gen10'],
                ['type' => 'server',        'name' => 'ThinkSystem SR530'],
                ['type' => 'server',        'name' => 'ThinkSystem SR630'],
            ],

            // ── APC / SCHNEIDER ──────────────────────────────────────────────
            'APC' => [
                ['type' => 'ups',           'name' => 'Smart-UPS 750VA'],
                ['type' => 'ups',           'name' => 'Smart-UPS 1500VA'],
                ['type' => 'ups',           'name' => 'Smart-UPS 3000VA'],
                ['type' => 'ups',           'name' => 'Back-UPS 650VA'],
                ['type' => 'ups',           'name' => 'Back-UPS Pro 1500VA'],
            ],

            // ── HIKVISION ────────────────────────────────────────────────────
            'Hikvision' => [
                ['type' => 'camera',        'name' => 'DS-2CD2143G2-I'],
                ['type' => 'camera',        'name' => 'DS-2CD2347G2-LU'],
                ['type' => 'camera',        'name' => 'DS-2DE4A425IWG-E'],
                ['type' => 'camera',        'name' => 'DS-2CD2T47G2-L'],
            ],

            // ── DAHUA ────────────────────────────────────────────────────────
            'Dahua' => [
                ['type' => 'camera',        'name' => 'IPC-HDW2831T-AS'],
                ['type' => 'camera',        'name' => 'IPC-HFW2849S-S-IL'],
                ['type' => 'camera',        'name' => 'SD49425XB-HNR'],
            ],
        ];

        foreach ($catalog as $brandName => $models) {
            $brand = EquipmentBrand::query()->firstOrCreate(['name' => $brandName]);

            foreach ($models as $model) {
                EquipmentModel::query()->updateOrCreate(
                    [
                        'brand_id'       => $brand->id,
                        'equipment_type' => $model['type'],
                        'name'           => $model['name'],
                    ],
                    [
                        'coverage_radius_min_m' => $model['radius_min'] ?? null,
                        'coverage_radius_max_m' => $model['radius_max'] ?? null,
                        'default_signal_dbm'    => $model['signal'] ?? null,
                        'radiation_pattern'     => $model['pattern'] ?? null,
                        'mount_height_m'        => $model['height'] ?? null,
                    ]
                );
            }
        }
    }
}
