<?php

namespace App\Support;

use App\Models\ComputerAsset;
use App\Models\Node;
use App\Models\NodeObservedDevice;
use App\Models\NodeRelation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NodeConnectionSnapshot
{
    public static function build(Node $node): array
    {
        $node->loadMissing([
            'branch:id,name',
            'nodeType:id,name,slug',
            'physicalSpace:id,name,space_type,floor,room',
            'softwareSystems:id,node_id,name,version,vendor,project_name',
            'computerAssets:id,branch_id,node_id,asset_tag,hostname,equipment_type,status,domain_name,primary_mac_address,primary_ip_address,last_seen_at',
            'observedDevices:id,branch_id,node_id,observed_via,mac_address,ip_address,hostname,domain_name,vendor_name,ssid,switch_port,device_type,is_managed,first_seen_at,last_seen_at,meta',
            'outgoingRelations.toNode:id,branch_id,node_type_id,name,status,ip_address',
            'outgoingRelations.toNode.nodeType:id,name,slug',
            'incomingRelations.fromNode:id,branch_id,node_type_id,name,status,ip_address',
            'incomingRelations.fromNode.nodeType:id,name,slug',
        ]);

        $tenantDomains = self::tenantDomains();
        $assets = $node->computerAssets instanceof Collection ? $node->computerAssets : collect($node->computerAssets);
        $assetsByMac = $assets
            ->filter(fn (ComputerAsset $asset) => self::normalizeMacAddress($asset->primary_mac_address) !== null)
            ->keyBy(fn (ComputerAsset $asset) => self::normalizeMacAddress($asset->primary_mac_address));
        $assetsByHostname = $assets
            ->filter(fn (ComputerAsset $asset) => self::normalizeText($asset->hostname) !== null)
            ->keyBy(fn (ComputerAsset $asset) => self::normalizeText($asset->hostname));

        $associatedAssets = $assets->map(function (ComputerAsset $asset) use ($tenantDomains) {
            $ownership = self::classifyOwnership(
                domainName: $asset->domain_name,
                tenantDomains: $tenantDomains,
                matchedManagedAsset: true
            );

            return [
                'id' => $asset->id,
                'source' => 'inventory',
                'label' => $asset->asset_tag ?: ($asset->hostname ?: ('Activo #' . $asset->id)),
                'hostname' => $asset->hostname,
                'equipment_type' => $asset->equipment_type,
                'status' => $asset->status,
                'domain_name' => $asset->domain_name,
                'mac_address' => self::normalizeMacAddress($asset->primary_mac_address),
                'ip_address' => $asset->primary_ip_address,
                'last_seen_at' => $asset->last_seen_at?->toIso8601String(),
                'ownership' => $ownership,
                'detail_url' => url('/admin?edit_asset=' . $asset->id . '#crud-assets'),
            ];
        })->values();

        $observedDevices = $node->observedDevices
            ->sortByDesc(fn (NodeObservedDevice $device) => $device->last_seen_at?->getTimestamp() ?? 0)
            ->map(function (NodeObservedDevice $device) use ($assetsByMac, $assetsByHostname, $tenantDomains) {
                $matchedAsset = null;
                $deviceMac = self::normalizeMacAddress($device->mac_address);
                $deviceHostname = self::normalizeText($device->hostname);

                if ($deviceMac !== null && $assetsByMac->has($deviceMac)) {
                    $matchedAsset = $assetsByMac->get($deviceMac);
                } elseif ($deviceHostname !== null && $assetsByHostname->has($deviceHostname)) {
                    $matchedAsset = $assetsByHostname->get($deviceHostname);
                }

                $ownership = self::classifyOwnership(
                    domainName: $device->domain_name,
                    tenantDomains: $tenantDomains,
                    matchedManagedAsset: $matchedAsset !== null || $device->is_managed === true
                );

                return [
                    'id' => $device->id,
                    'source' => 'observed',
                    'observed_via' => $device->observed_via,
                    'hostname' => $device->hostname,
                    'domain_name' => $device->domain_name,
                    'mac_address' => $deviceMac,
                    'ip_address' => $device->ip_address,
                    'vendor_name' => $device->vendor_name,
                    'ssid' => $device->ssid,
                    'switch_port' => $device->switch_port,
                    'device_type' => $device->device_type,
                    'first_seen_at' => $device->first_seen_at?->toIso8601String(),
                    'last_seen_at' => $device->last_seen_at?->toIso8601String(),
                    'meta' => is_array($device->meta) ? $device->meta : null,
                    'ownership' => $ownership,
                    'matched_asset' => $matchedAsset ? [
                        'id' => $matchedAsset->id,
                        'label' => $matchedAsset->asset_tag ?: ($matchedAsset->hostname ?: ('Activo #' . $matchedAsset->id)),
                        'detail_url' => url('/admin?edit_asset=' . $matchedAsset->id . '#crud-assets'),
                    ] : null,
                ];
            })
            ->values();

        $relatedNodes = collect($node->outgoingRelations)
            ->map(function (NodeRelation $relation) {
                $peer = $relation->toNode;

                return self::mapRelatedNode($peer, $relation, 'outgoing');
            })
            ->concat(collect($node->incomingRelations)->map(function (NodeRelation $relation) {
                $peer = $relation->fromNode;

                return self::mapRelatedNode($peer, $relation, 'incoming');
            }))
            ->filter()
            ->values();

        $ports = self::extractPorts(is_array($node->details) ? $node->details : []);

        return [
            'tenant_domains' => array_values($tenantDomains),
            'supports_discovery' => self::supportsDiscovery($node),
            'associated_assets' => $associatedAssets->all(),
            'observed_devices' => $observedDevices->all(),
            'related_nodes' => $relatedNodes->all(),
            'ports' => $ports,
            'summary' => [
                'associated_assets_count' => $associatedAssets->count(),
                'observed_devices_count' => $observedDevices->count(),
                'managed_assets_count' => $associatedAssets->count(),
                'managed_observed_devices_count' => $observedDevices->filter(fn (array $device) => data_get($device, 'ownership.key') === 'managed')->count(),
                'external_observed_devices_count' => $observedDevices->filter(fn (array $device) => data_get($device, 'ownership.key') === 'external')->count(),
                'unknown_observed_devices_count' => $observedDevices->filter(fn (array $device) => data_get($device, 'ownership.key') === 'unknown')->count(),
            ],
        ];
    }

    public static function tenantDomains(): array
    {
        $tenant = tenant();
        if (!$tenant || !method_exists($tenant, 'domains')) {
            return [];
        }

        return $tenant->domains()
            ->pluck('domain')
            ->map(fn ($value) => self::normalizeDomain($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function classifyOwnership(?string $domainName, array $tenantDomains, bool $matchedManagedAsset = false): array
    {
        if ($matchedManagedAsset) {
            return [
                'key' => 'managed',
                'label' => 'Propio / inventariado',
                'tone' => 'success',
                'is_managed' => true,
            ];
        }

        if (self::domainMatchesTenant($domainName, $tenantDomains)) {
            return [
                'key' => 'managed',
                'label' => 'Dominio del tenant',
                'tone' => 'success',
                'is_managed' => true,
            ];
        }

        if (self::normalizeDomain($domainName) !== null) {
            return [
                'key' => 'external',
                'label' => 'Dominio ajeno',
                'tone' => 'danger',
                'is_managed' => false,
            ];
        }

        return [
            'key' => 'unknown',
            'label' => 'Sin clasificar',
            'tone' => 'secondary',
            'is_managed' => null,
        ];
    }

    public static function normalizeMacAddress(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(str_replace('-', ':', trim($value)));

        return $normalized !== '' ? $normalized : null;
    }

    public static function normalizeDomain(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::of($value)->trim()->lower()->trim('.')->value();

        return $normalized !== '' ? $normalized : null;
    }

    private static function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::of($value)->trim()->lower()->value();

        return $normalized !== '' ? $normalized : null;
    }

    private static function domainMatchesTenant(?string $domainName, array $tenantDomains): bool
    {
        $normalized = self::normalizeDomain($domainName);
        if ($normalized === null) {
            return false;
        }

        foreach ($tenantDomains as $tenantDomain) {
            if ($tenantDomain === null || $tenantDomain === '') {
                continue;
            }

            if ($normalized === $tenantDomain || Str::endsWith($normalized, '.' . $tenantDomain)) {
                return true;
            }
        }

        return false;
    }

    private static function supportsDiscovery(Node $node): bool
    {
        $haystack = Str::lower(trim((string) (optional($node->nodeType)->slug ?: optional($node->nodeType)->name ?: $node->name)));

        return Str::contains($haystack, ['switch', 'router', 'access', 'wifi', 'ap', 'firewall']);
    }

    private static function extractPorts(array $details): array
    {
        return collect(data_get($details, 'ports', []))
            ->filter(fn ($port) => is_array($port))
            ->map(fn (array $port) => [
                'name' => trim((string) ($port['name'] ?? $port['label'] ?? '')),
                'status' => $port['status'] ?? null,
                'vlan' => $port['vlan'] ?? null,
                'speed' => $port['speed'] ?? null,
                'mac_address' => self::normalizeMacAddress($port['mac_address'] ?? null),
                'description' => $port['description'] ?? null,
            ])
            ->filter(fn (array $port) => ($port['name'] ?? '') !== '')
            ->values()
            ->all();
    }

    private static function mapRelatedNode(?Node $peer, NodeRelation $relation, string $direction): ?array
    {
        if (!$peer) {
            return null;
        }

        return [
            'id' => $peer->id,
            'name' => $peer->name,
            'status' => $peer->status,
            'ip_address' => $peer->ip_address,
            'type' => optional($peer->nodeType)->name ?? 'Nodo',
            'type_slug' => optional($peer->nodeType)->slug ?? '',
            'direction' => $direction,
            'relation_type' => $relation->relation_type,
            'from_endpoint' => $relation->from_endpoint,
            'to_endpoint' => $relation->to_endpoint,
            'detail_url' => url('/nodos/' . $peer->id),
        ];
    }
}