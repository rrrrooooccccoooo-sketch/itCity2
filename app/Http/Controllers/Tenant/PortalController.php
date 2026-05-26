<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ComputerAsset;
use App\Models\Node;
use App\Models\NodeObservedDevice;
use App\Models\NodeProbe;
use App\Models\NodeRelation;
use App\Models\NodeType;
use App\Models\SoftwareSystem;
use App\Support\NodeConnectionSnapshot;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PortalController extends Controller
{
    public function city(): View
    {
        $branches = Branch::query()
            ->withCount('nodes')
            ->orderBy('name')
            ->get();

        $tenantModel = tenant();
        $tenantName = $tenantModel->company_name ?? data_get($tenantModel, 'data.company_name') ?? 'ITCity';
        $tenantLogo = $tenantModel->logo_url ?? data_get($tenantModel, 'data.logo_url');

        return view('tenant.city', compact('branches', 'tenantName', 'tenantLogo'));
    }

    public function branch(Request $request, Branch $branch): View
    {
        $this->rememberCurrentBranchContext($request, (int) $branch->id);

        $nodeTypes = NodeType::query()
            ->withCount(['nodes' => function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->orderBy('name')
            ->get();

        $systems = SoftwareSystem::query()
            ->whereHas('node', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->with('node:id,name')
            ->latest()
            ->take(20)
            ->get();

        $monitoredNodes = Node::query()
            ->where('branch_id', $branch->id)
            ->where('is_monitored', true)
            ->orderBy('name')
            ->take(12)
            ->get(['id', 'name', 'status']);

        $branchNodes = Node::query()
            ->where('branch_id', $branch->id)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'ip_address']);

        $branchNodesExplorer = Node::query()
            ->where('branch_id', $branch->id)
            ->with([
                'nodeType:id,name',
                'physicalSpace:id,name,space_type,floor,room',
                'softwareSystems:id,node_id,name,version,vendor,project_name',
                'outgoingRelations.toNode:id,name,ip_address',
            ])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'status',
                'ip_address',
                'floor',
                'room',
                'node_type_id',
                'details',
            ]);

        $systemsExplorer = SoftwareSystem::query()
            ->whereHas('node', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->with(['node:id,name,floor,room,ip_address,physical_space_id', 'node.physicalSpace:id,name,space_type,floor,room'])
            ->orderBy('name')
            ->get(['id', 'name', 'version', 'vendor', 'project_name', 'node_id']);

        return view('tenant.branch', compact(
            'branch',
            'nodeTypes',
            'systems',
            'monitoredNodes',
            'branchNodes',
            'branchNodesExplorer',
            'systemsExplorer'
        ));
    }

    public function globalTopology(): View
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name', 'city', 'state']);
        $nodeTypes = NodeType::query()->orderBy('name')->get(['id', 'name', 'slug', 'icon']);

        $allNodes = Node::query()
            ->with(['nodeType:id,name,slug', 'softwareSystems:id,node_id,name,version'])
            ->orderBy('name')
            ->get(['id', 'branch_id', 'node_type_id', 'name', 'status', 'ip_address', 'layout_x', 'layout_y']);

        $allNodeIds = $allNodes->pluck('id');

        $assetsByNode = ComputerAsset::query()
            ->whereIn('node_id', $allNodeIds)
            ->whereNotNull('node_id')
            ->orderBy('asset_tag')
            ->orderBy('hostname')
            ->get(['id', 'branch_id', 'node_id', 'asset_tag', 'hostname', 'equipment_type', 'status'])
            ->groupBy('node_id');

        $observedDevicesByNode = NodeObservedDevice::query()
            ->whereIn('node_id', $allNodeIds)
            ->selectRaw('node_id, COUNT(*) as observed_count')
            ->groupBy('node_id')
            ->get()
            ->keyBy('node_id');

        $relations = NodeRelation::query()
            ->whereIn('from_node_id', $allNodeIds)
            ->whereIn('to_node_id', $allNodeIds)
            ->get([
                'id',
                'from_node_id',
                'to_node_id',
                'relation_type',
                'preferred_weight',
                'from_endpoint',
                'to_endpoint',
                'is_inter_campus',
                'vpn_profile',
                'notes',
            ]);

        $graphNodes = $allNodes->map(function (Node $node) use ($branches, $assetsByNode) {
            $branch = $branches->firstWhere('id', $node->branch_id);
            $connectedAssets = ($assetsByNode->get($node->id) ?? collect())
                ->map(fn (ComputerAsset $asset) => [
                    'id' => $asset->id,
                    'label' => $asset->asset_tag ?: ($asset->hostname ?: ('Activo #' . $asset->id)),
                    'hostname' => $asset->hostname,
                    'equipment_type' => $asset->equipment_type,
                    'status' => $asset->status,
                ])
                ->values();
            $softwareSystems = $node->softwareSystems
                ->map(fn (SoftwareSystem $software) => [
                    'id' => $software->id,
                    'name' => $software->name,
                    'version' => $software->version,
                ])
                ->values();

            return [
                'id'         => $node->id,
                'label'      => $node->name,
                'type'       => optional($node->nodeType)->name ?? 'Nodo',
                'type_slug'  => optional($node->nodeType)->slug ?? '',
                'branch_id'  => $node->branch_id,
                'branch_name' => $branch?->name,
                'status'     => $node->status ?? 'inactive',
                'ip'         => $node->ip_address,
                'layout_x'   => $node->layout_x,
                'layout_y'   => $node->layout_y,
                'branch_url' => url('/sede/' . $node->branch_id),
                'branch_network_url' => url('/sede/' . $node->branch_id . '/red'),
                'detail_url' => url('/nodos/' . $node->id),
                'connected_assets_count' => $connectedAssets->count(),
                'connected_assets' => $connectedAssets,
                'software_count' => $softwareSystems->count(),
                'software_systems' => $softwareSystems,
            ];
        })->map(function (array $nodeData) use ($observedDevicesByNode) {
            $observedCount = (int) optional($observedDevicesByNode->get($nodeData['id']))->observed_count;

            return [
                ...$nodeData,
                'observed_devices_count' => $observedCount,
                'connections_url' => url('/red/nodos/' . $nodeData['id']),
            ];
        })->values();

        $graphEdges = $relations->map(function (NodeRelation $relation) {
            return [
                'id'              => $relation->id,
                'from'            => $relation->from_node_id,
                'to'              => $relation->to_node_id,
                'label'           => $relation->relation_type,
                'preferred_weight' => $relation->preferred_weight,
                'from_endpoint'   => $relation->from_endpoint,
                'to_endpoint'     => $relation->to_endpoint,
                'is_inter_campus' => (bool) $relation->is_inter_campus,
                'vpn_profile'     => $relation->vpn_profile,
                'notes'           => $relation->notes,
            ];
        })->values();

        $branchZones = $branches->map(function (Branch $branch) use ($allNodes, $assetsByNode, $observedDevicesByNode) {
            $branchNodes = $allNodes->where('branch_id', $branch->id);
            $connectedAssetsCount = $branchNodes
                ->sum(fn (Node $node) => ($assetsByNode->get($node->id) ?? collect())->count());
            $observedDevicesCount = $branchNodes
                ->sum(fn (Node $node) => (int) optional($observedDevicesByNode->get($node->id))->observed_count);

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'city' => $branch->city,
                'state' => $branch->state,
                'node_count' => $branchNodes->count(),
                'active_nodes' => $branchNodes->where('status', 'active')->count(),
                'connected_assets_count' => $connectedAssetsCount,
                'observed_devices_count' => $observedDevicesCount,
                'branch_url' => url('/sede/' . $branch->id),
                'network_url' => url('/sede/' . $branch->id . '/red'),
            ];
        })->values();

        return view('tenant.topology', [
            'graphNodes'        => $graphNodes,
            'graphEdges'        => $graphEdges,
            'branchZones'       => $branchZones,
            'branchOptions'     => $branches->map(fn(Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])->values(),
            'nodeTypeOptions'   => $nodeTypes->map(fn(NodeType $nodeType) => [
                'id' => $nodeType->id,
                'name' => $nodeType->name,
                'slug' => $nodeType->slug,
                'icon' => $nodeType->icon,
            ])->values(),
            'createRelationUrl' => url('/red/relacion'),
            'createNodeUrl'     => url('/red/nodos'),
            'createSoftwareUrl' => url('/red/software'),
            'saveLayoutUrl'     => url('/red/layout'),
        ]);
    }

    public function nodeSoftwareJson(Node $node): JsonResponse
    {
        $software = SoftwareSystem::query()
            ->where('node_id', $node->id)
            ->orderBy('name')
            ->get(['id', 'node_id', 'name', 'version', 'vendor', 'contact_email', 'contact_phone', 'project_name', 'details']);

        return response()->json([
            'ok' => true,
            'software' => $software,
        ]);
    }

    public function showNodeJson(Node $node): JsonResponse
    {
        $node->load('nodeType:id,name,slug');
        $snapshot = NodeConnectionSnapshot::build($node);

        return response()->json([
            'ok' => true,
            'node' => [
                'id' => $node->id,
                'branch_id' => $node->branch_id,
                'node_type_id' => $node->node_type_id,
                'name' => $node->name,
                'status' => $node->status,
                'ip_address' => $node->ip_address,
                'floor' => $node->floor,
                'room' => $node->room,
                'cable_type' => $node->cable_type,
                'is_monitored' => (bool) $node->is_monitored,
                'details' => $node->details,
                'layout_x' => $node->layout_x,
                'layout_y' => $node->layout_y,
                'type_slug' => optional($node->nodeType)->slug,
                'type_name' => optional($node->nodeType)->name,
                'connections_url' => url('/red/nodos/' . $node->id),
                'connection_snapshot' => $snapshot,
            ],
        ]);
    }

    public function storeNodeJson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'node_type_id' => ['required', 'integer', 'exists:node_types,id'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'max:40'],
            'ip_address' => [
                'nullable',
                'ip',
                Rule::unique('nodes', 'ip_address')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->input('branch_id'));
                }),
            ],
            'mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'floor' => ['nullable', 'string', 'max:40'],
            'room' => ['nullable', 'string', 'max:80'],
            'cable_type' => ['nullable', 'string', 'max:80'],
            'is_monitored' => ['nullable', 'boolean'],
            'details' => ['nullable', 'array'],
            'layout_x' => ['nullable', 'numeric'],
            'layout_y' => ['nullable', 'numeric'],
        ]);

        $node = Node::query()->create([
            'branch_id' => $validated['branch_id'],
            'node_type_id' => $validated['node_type_id'],
            'name' => $validated['name'],
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'] ?? null,
            'mac_address' => $this->normalizeMacAddress($validated['mac_address'] ?? null),
            'floor' => $validated['floor'] ?? null,
            'room' => $validated['room'] ?? null,
            'cable_type' => $validated['cable_type'] ?? null,
            'is_monitored' => (bool) ($validated['is_monitored'] ?? false),
            'details' => $validated['details'] ?? null,
            'layout_x' => isset($validated['layout_x']) ? round((float) $validated['layout_x'], 2) : null,
            'layout_y' => isset($validated['layout_y']) ? round((float) $validated['layout_y'], 2) : null,
        ]);

        $node->load('nodeType:id,name,slug');

        return response()->json([
            'ok' => true,
            'node' => $this->toGraphNode($node),
        ]);
    }

    public function updateNodeJson(Request $request, Node $node): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'node_type_id' => ['required', 'integer', 'exists:node_types,id'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'max:40'],
            'ip_address' => [
                'nullable',
                'ip',
                Rule::unique('nodes', 'ip_address')
                    ->ignore($node->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('branch_id', $request->input('branch_id'));
                    }),
            ],
            'mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'floor' => ['nullable', 'string', 'max:40'],
            'room' => ['nullable', 'string', 'max:80'],
            'cable_type' => ['nullable', 'string', 'max:80'],
            'is_monitored' => ['nullable', 'boolean'],
            'details' => ['nullable', 'array'],
            'layout_x' => ['nullable', 'numeric'],
            'layout_y' => ['nullable', 'numeric'],
        ]);

        $node->update([
            'branch_id' => $validated['branch_id'],
            'node_type_id' => $validated['node_type_id'],
            'name' => $validated['name'],
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'] ?? null,
            'mac_address' => $this->normalizeMacAddress($validated['mac_address'] ?? null),
            'floor' => $validated['floor'] ?? null,
            'room' => $validated['room'] ?? null,
            'cable_type' => $validated['cable_type'] ?? null,
            'is_monitored' => (bool) ($validated['is_monitored'] ?? false),
            'details' => $validated['details'] ?? null,
            'layout_x' => isset($validated['layout_x']) ? round((float) $validated['layout_x'], 2) : $node->layout_x,
            'layout_y' => isset($validated['layout_y']) ? round((float) $validated['layout_y'], 2) : $node->layout_y,
        ]);

        $node->refresh()->load('nodeType:id,name,slug');

        return response()->json([
            'ok' => true,
            'node' => $this->toGraphNode($node),
        ]);
    }

    public function destroyNodeJson(Node $node): JsonResponse
    {
        $node->delete();

        return response()->json([
            'ok' => true,
            'id' => $node->id,
        ]);
    }

    public function storeSoftwareJson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id' => ['required', 'integer', 'exists:nodes,id'],
            'name' => ['required', 'string', 'max:150'],
            'version' => ['nullable', 'string', 'max:80'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'details' => ['nullable', 'array'],
        ]);

        $software = SoftwareSystem::query()->create($validated);

        return response()->json([
            'ok' => true,
            'software' => $software,
        ]);
    }

    public function updateSoftwareJson(Request $request, SoftwareSystem $software): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'version' => ['nullable', 'string', 'max:80'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'details' => ['nullable', 'array'],
        ]);

        $software->update($validated);

        return response()->json([
            'ok' => true,
            'software' => $software,
        ]);
    }

    public function destroySoftwareJson(SoftwareSystem $software): JsonResponse
    {
        $software->delete();
        return response()->json(['ok' => true]);
    }

    public function storeRelationJson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_node_id'  => ['required', 'integer', 'exists:nodes,id'],
            'to_node_id'    => ['required', 'integer', 'exists:nodes,id', 'different:from_node_id'],
            'relation_type' => ['required', 'string', 'max:80'],
            'preferred_weight' => ['nullable', 'integer', 'min:1', 'max:999'],
            'from_endpoint' => ['nullable', 'string', 'max:120'],
            'to_endpoint'   => ['nullable', 'string', 'max:120'],
            'is_inter_campus' => ['nullable', 'boolean'],
            'vpn_profile'   => ['nullable', 'string', 'max:120'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $fromNode = Node::query()->findOrFail($validated['from_node_id'], ['id', 'branch_id']);
        $toNode = Node::query()->findOrFail($validated['to_node_id'], ['id', 'branch_id']);
        $autoInterCampus = (int) $fromNode->branch_id !== (int) $toNode->branch_id;
        $isInterCampus = (bool) ($validated['is_inter_campus'] ?? false) || $autoInterCampus;

        try {
            $relation = NodeRelation::query()->create([
                'from_node_id'  => $validated['from_node_id'],
                'to_node_id'    => $validated['to_node_id'],
                'relation_type' => $validated['relation_type'],
                'preferred_weight' => $validated['preferred_weight'] ?? null,
                'from_endpoint' => $validated['from_endpoint'] ?? null,
                'to_endpoint' => $validated['to_endpoint'] ?? null,
                'is_inter_campus' => $isInterCampus,
                'vpn_profile' => $validated['vpn_profile'] ?? null,
                'notes'         => $validated['notes'] ?? null,
            ]);
        } catch (QueryException) {
            return response()->json(['ok' => false, 'message' => 'La relación ya existe.'], 409);
        }

        return response()->json([
            'ok' => true,
            'id' => $relation->id,
            'preferred_weight' => $relation->preferred_weight,
            'is_inter_campus' => (bool) $relation->is_inter_campus,
        ]);
    }

    public function destroyRelationJson(NodeRelation $relation): JsonResponse
    {
        $relation->delete();
        return response()->json(['ok' => true]);
    }

    public function updateRelationJson(Request $request, NodeRelation $relation): JsonResponse
    {
        $validated = $request->validate([
            'from_node_id'  => ['required', 'integer', 'exists:nodes,id'],
            'to_node_id'    => ['required', 'integer', 'exists:nodes,id', 'different:from_node_id'],
            'relation_type' => ['required', 'string', 'max:80'],
            'preferred_weight' => ['nullable', 'integer', 'min:1', 'max:999'],
            'from_endpoint' => ['nullable', 'string', 'max:120'],
            'to_endpoint'   => ['nullable', 'string', 'max:120'],
            'is_inter_campus' => ['nullable', 'boolean'],
            'vpn_profile'   => ['nullable', 'string', 'max:120'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $fromNode = Node::query()->findOrFail($validated['from_node_id'], ['id', 'branch_id']);
        $toNode = Node::query()->findOrFail($validated['to_node_id'], ['id', 'branch_id']);
        $autoInterCampus = (int) $fromNode->branch_id !== (int) $toNode->branch_id;
        $isInterCampus = (bool) ($validated['is_inter_campus'] ?? false) || $autoInterCampus;

        try {
            $relation->update([
                'from_node_id' => $validated['from_node_id'],
                'to_node_id' => $validated['to_node_id'],
                'relation_type' => $validated['relation_type'],
                'preferred_weight' => $validated['preferred_weight'] ?? null,
                'from_endpoint' => $validated['from_endpoint'] ?? null,
                'to_endpoint' => $validated['to_endpoint'] ?? null,
                'is_inter_campus' => $isInterCampus,
                'vpn_profile' => $validated['vpn_profile'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (QueryException) {
            return response()->json(['ok' => false, 'message' => 'Ya existe una conexión con esos nodos y tipo.'], 409);
        }

        return response()->json([
            'ok' => true,
            'id' => $relation->id,
            'preferred_weight' => $relation->preferred_weight,
            'is_inter_campus' => (bool) $relation->is_inter_campus,
        ]);
    }

    public function saveGlobalLayout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nodes'      => ['required', 'array', 'min:1'],
            'nodes.*.id' => ['required', 'integer', 'exists:nodes,id'],
            'nodes.*.x'  => ['required', 'numeric'],
            'nodes.*.y'  => ['required', 'numeric'],
        ]);

        foreach ($validated['nodes'] as $nodeData) {
            Node::query()->where('id', $nodeData['id'])->update([
                'layout_x' => round((float) $nodeData['x'], 2),
                'layout_y' => round((float) $nodeData['y'], 2),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function topologySummary(): JsonResponse
    {
        $totalBranches = Branch::query()->count();
        $totalNodes = Node::query()->count();
        $activeNodes = Node::query()->where('status', 'active')->count();
        $monitoredNodes = Node::query()->where('is_monitored', true)->count();

        $latestProbeIds = NodeProbe::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('node_id')
            ->pluck('id');

        $latestProbes = NodeProbe::query()
            ->whereIn('id', $latestProbeIds)
            ->get(['id', 'reachable', 'latency_ms']);

        $probedNodes = $latestProbes->count();
        $reachableNodes = $latestProbes->where('reachable', true)->count();
        $availabilityRate = $probedNodes > 0
            ? round(($reachableNodes / $probedNodes) * 100, 1)
            : null;

        $averageLatency = $latestProbes
            ->where('reachable', true)
            ->pluck('latency_ms')
            ->filter(fn($value) => $value !== null)
            ->avg();

        return response()->json([
            'ok' => true,
            'summary' => [
                'branches' => $totalBranches,
                'nodes_total' => $totalNodes,
                'nodes_active' => $activeNodes,
                'nodes_monitored' => $monitoredNodes,
                'nodes_probed' => $probedNodes,
                'nodes_reachable' => $reachableNodes,
                'availability_rate' => $availabilityRate,
                'avg_latency_ms' => $averageLatency !== null ? round((float) $averageLatency, 2) : null,
            ],
        ]);
    }

    public function mnemonicMemoryView(Request $request): View
    {
        $branches = Branch::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tenant.mnemonic-memory', [
            'branches' => $branches,
            'initialFilters' => [
                'branch_id' => $request->input('branch_id'),
                'status' => $request->input('status'),
                'with_relations' => $request->boolean('with_relations'),
            ],
        ]);
    }

    public function mnemonicMemory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'string', 'max:60'],
            'with_relations' => ['nullable', 'boolean'],
        ]);

        $statusFilter = isset($validated['status'])
            ? strtolower(trim((string) $validated['status']))
            : null;

        $branchFilter = $validated['branch_id'] ?? null;
        $withRelationsOnly = (bool) ($validated['with_relations'] ?? false);

        $nodes = Node::query()
            ->when($branchFilter !== null, function ($query) use ($branchFilter) {
                $query->where('branch_id', $branchFilter);
            })
            ->when($statusFilter !== null && $statusFilter !== '', function ($query) use ($statusFilter) {
                $query->whereRaw('LOWER(status) = ?', [$statusFilter]);
            })
            ->when($withRelationsOnly, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->whereHas('outgoingRelations')
                        ->orWhereHas('incomingRelations');
                });
            })
            ->with([
                'branch:id,name',
                'nodeType:id,name,slug',
                'physicalSpace:id,name,space_type,floor,room',
                'softwareSystems:id,node_id,name,version,vendor,project_name',
                'outgoingRelations.toNode:id,name,details',
                'incomingRelations.fromNode:id,name,details',
            ])
            ->orderBy('name')
            ->get([
                'id',
                'branch_id',
                'node_type_id',
                'physical_space_id',
                'name',
                'code',
                'status',
                'ip_address',
                'mac_address',
                'details',
            ]);

        $memory = $nodes->map(function (Node $node) {
            $details = is_array($node->details) ? $node->details : [];
            $mnemonicData = $this->resolveMnemonic($node);

            $ports = collect(data_get($details, 'ports', []))
                ->filter(fn($port) => is_array($port))
                ->map(function (array $port, int $index) {
                    return [
                        'position' => $index + 1,
                        'name' => (string) ($port['name'] ?? ('P' . ($index + 1))),
                        'status' => (string) ($port['status'] ?? 'unused'),
                        'connected_to' => $port['connected_to'] ?? null,
                    ];
                })
                ->values();

            $outgoing = $node->outgoingRelations->map(function (NodeRelation $relation) {
                $targetMnemonicData = $this->resolveMnemonic($relation->toNode);

                return [
                    'relation_id' => $relation->id,
                    'relation_type' => $relation->relation_type,
                    'to_node_id' => $relation->to_node_id,
                    'to_node_name' => $relation->toNode?->name,
                    'to_mnemonic' => $targetMnemonicData['value'],
                    'to_mnemonic_source' => $targetMnemonicData['source'],
                    'from_endpoint' => $relation->from_endpoint,
                    'to_endpoint' => $relation->to_endpoint,
                    'inter_campus' => (bool) $relation->is_inter_campus,
                ];
            })->values();

            $incoming = $node->incomingRelations->map(function (NodeRelation $relation) {
                $sourceMnemonicData = $this->resolveMnemonic($relation->fromNode);

                return [
                    'relation_id' => $relation->id,
                    'relation_type' => $relation->relation_type,
                    'from_node_id' => $relation->from_node_id,
                    'from_node_name' => $relation->fromNode?->name,
                    'from_mnemonic' => $sourceMnemonicData['value'],
                    'from_mnemonic_source' => $sourceMnemonicData['source'],
                    'from_endpoint' => $relation->from_endpoint,
                    'to_endpoint' => $relation->to_endpoint,
                    'inter_campus' => (bool) $relation->is_inter_campus,
                ];
            })->values();

            $software = $node->softwareSystems->map(function (SoftwareSystem $software) {
                return [
                    'id' => $software->id,
                    'name' => $software->name,
                    'version' => $software->version,
                    'vendor' => $software->vendor,
                    'project_name' => $software->project_name,
                ];
            })->values();

            $quality = $this->buildMemoryQuality(
                mnemonic: $mnemonicData['value'],
                status: (string) ($node->status ?? ''),
                ipAddress: $node->ip_address,
                ports: $ports,
                outgoing: $outgoing,
                incoming: $incoming,
                software: $software
            );

            return [
                'node_id' => $node->id,
                'mnemonic' => $mnemonicData['value'],
                'mnemonic_source' => $mnemonicData['source'],
                'name' => $node->name,
                'code' => $node->code,
                'status' => $node->status,
                'ip_address' => $node->ip_address,
                'mac_address' => $node->mac_address,
                'branch' => [
                    'id' => $node->branch_id,
                    'name' => $node->branch?->name,
                ],
                'node_type' => [
                    'id' => $node->node_type_id,
                    'name' => $node->nodeType?->name,
                    'slug' => $node->nodeType?->slug,
                ],
                'physical_space' => $node->physicalSpace ? [
                    'id' => $node->physicalSpace->id,
                    'name' => $node->physicalSpace->name,
                    'type' => $node->physicalSpace->space_type,
                    'floor' => $node->physicalSpace->floor,
                    'room' => $node->physicalSpace->room,
                ] : null,
                'ports' => $ports,
                'software' => $software,
                'relations' => [
                    'outgoing' => $outgoing,
                    'incoming' => $incoming,
                ],
                'has_related_data' => $ports->isNotEmpty() || $outgoing->isNotEmpty() || $incoming->isNotEmpty() || $node->softwareSystems->isNotEmpty(),
                'completeness_score' => $quality['score'],
                'alerts' => $quality['alerts'],
            ];
        })->values();

        $mnemonicDuplicates = $memory
            ->pluck('mnemonic')
            ->filter(fn($value) => is_string($value) && trim($value) !== '')
            ->map(fn($value) => strtoupper(trim((string) $value)))
            ->countBy();

        $memory = $memory->map(function (array $item) use ($mnemonicDuplicates) {
            $alerts = collect($item['alerts'] ?? []);
            $mnemonicKey = strtoupper(trim((string) ($item['mnemonic'] ?? '')));

            if ($mnemonicKey !== '' && ($mnemonicDuplicates[$mnemonicKey] ?? 0) > 1) {
                $alerts->push([
                    'code' => 'duplicate_mnemonic',
                    'level' => 'warning',
                    'message' => 'Mnemónico duplicado en varios nodos.',
                ]);
            }

            return [
                ...$item,
                'alerts' => $alerts->values()->all(),
                'alerts_count' => $alerts->count(),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toDateTimeString(),
            'count' => $memory->count(),
            'applied_filters' => [
                'branch_id' => $branchFilter,
                'status' => $statusFilter,
                'with_relations' => $withRelationsOnly,
            ],
            'items' => $memory,
        ]);
    }

    public function network(Request $request, Branch $branch): View
    {
        $this->rememberCurrentBranchContext($request, (int) $branch->id);

        $nodes = Node::query()
            ->where('branch_id', $branch->id)
            ->with('nodeType:id,name')
            ->orderBy('name')
            ->get(['id', 'branch_id', 'node_type_id', 'name', 'status', 'ip_address', 'layout_x', 'layout_y']);

        $nodeIds = $nodes->pluck('id');

        $relations = NodeRelation::query()
            ->whereIn('from_node_id', $nodeIds)
            ->whereIn('to_node_id', $nodeIds)
            ->orderBy('id')
            ->get(['id', 'from_node_id', 'to_node_id', 'relation_type']);

        $graphNodes = $nodes->map(function (Node $node) {
            return [
                'id' => $node->id,
                'label' => $node->name,
                'type' => optional($node->nodeType)->name ?? 'Nodo',
                'status' => $node->status,
                'ip' => $node->ip_address,
                'layout_x' => $node->layout_x,
                'layout_y' => $node->layout_y,
                'detail_url' => url('/nodos/' . $node->id),
            ];
        })->values();

        $graphEdges = $relations->map(function (NodeRelation $relation) {
            return [
                'id' => $relation->id,
                'from' => $relation->from_node_id,
                'to' => $relation->to_node_id,
                'label' => $relation->relation_type,
            ];
        })->values();

        return view('tenant.network', [
            'branch' => $branch,
            'graphNodes' => $graphNodes,
            'graphEdges' => $graphEdges,
        ]);
    }

    public function saveNetworkLayout(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.id' => ['required', 'integer', 'exists:nodes,id'],
            'nodes.*.x' => ['required', 'numeric'],
            'nodes.*.y' => ['required', 'numeric'],
        ]);

        $payload = collect($validated['nodes']);
        $nodeIds = $payload->pluck('id')->all();

        $branchNodeIds = Node::query()
            ->where('branch_id', $branch->id)
            ->whereIn('id', $nodeIds)
            ->pluck('id')
            ->all();

        $allowedIds = array_flip($branchNodeIds);

        foreach ($payload as $nodeData) {
            if (!isset($allowedIds[$nodeData['id']])) {
                continue;
            }

            Node::query()
                ->where('id', $nodeData['id'])
                ->update([
                    'layout_x' => round((float) $nodeData['x'], 2),
                    'layout_y' => round((float) $nodeData['y'], 2),
                ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Layout guardado correctamente.',
        ]);
    }

    public function node(Request $request, Node $node): View
    {
        $this->rememberCurrentBranchContext($request, (int) $node->branch_id);

        $node->load([
            'branch:id,name,address,city,state,country',
            'nodeType:id,name,slug',
            'physicalSpace:id,name,space_type,floor,room,description',
            'softwareSystems',
            'outgoingRelations.toNode:id,name,status,ip_address',
            'incomingRelations.fromNode:id,name,status,ip_address',
        ]);

        $probeHistory = NodeProbe::query()
            ->where('node_id', $node->id)
            ->latest('probed_at')
            ->take(12)
            ->get();

        $probeStatsBase = NodeProbe::query()
            ->where('node_id', $node->id)
            ->latest('probed_at')
            ->take(20)
            ->get();

        $probeTotal = $probeStatsBase->count();
        $probeReachable = $probeStatsBase->where('reachable', true)->count();
        $successRate = $probeTotal > 0 ? round(($probeReachable / $probeTotal) * 100, 1) : null;
        $avgLatency = $probeStatsBase
            ->where('reachable', true)
            ->pluck('latency_ms')
            ->filter(fn($v) => $v !== null)
            ->avg();

        return view('tenant.node', [
            'node' => $node,
            'softwareSystems' => $node->softwareSystems,
            'outgoingRelations' => $node->outgoingRelations,
            'incomingRelations' => $node->incomingRelations,
            'probeHistory' => $probeHistory,
            'probeSuccessRate' => $successRate,
            'probeAvgLatency' => $avgLatency !== null ? round((float) $avgLatency, 2) : null,
        ]);
    }

    private function rememberCurrentBranchContext(Request $request, int $branchId): void
    {
        if ($branchId <= 0 || !$request->hasSession()) {
            return;
        }

        $request->session()->put('tenant_portal_context_branch_id', $branchId);
    }

    public function branchNodeStatus(Branch $branch): JsonResponse
    {
        $statuses = Node::query()
            ->where('branch_id', $branch->id)
            ->orderBy('id')
            ->get(['id', 'status'])
            ->map(fn(Node $node) => [
                'id'     => $node->id,
                'status' => $node->status ?? 'inactive',
            ])
            ->values();

        return response()->json(['nodes' => $statuses]);
    }

    public function nodeMetrics(Node $node): JsonResponse
    {
        return response()->json([
            'node_id' => $node->id,
            'node_name' => $node->name,
            'status' => $node->status,
            'cpu_percent' => random_int(5, 92),
            'ram_percent' => random_int(10, 88),
            'disk_percent' => random_int(15, 95),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    public function nodeProbe(Node $node): JsonResponse
    {
        $ipAddress = trim((string) ($node->ip_address ?? ''));

        if ($ipAddress === '' || !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $response = [
                'node_id' => $node->id,
                'node_name' => $node->name,
                'reachable' => false,
                'latency_ms' => null,
                'open_ports' => [],
                'checked_ports' => [],
                'message' => 'El nodo no tiene una IP válida para sondeo automático.',
                'updated_at' => now()->toDateTimeString(),
            ];

            $this->storeProbe($node, false, null, [], [], $response['message']);

            return response()->json($response, 422);
        }

        $ping = $this->runPing($ipAddress);
        $checkedPorts = [22, 80, 443, 3389];
        $openPorts = $this->scanPorts($ipAddress, $checkedPorts, 0.4);

        $message = $ping['reachable']
            ? 'Sondeo completado correctamente.'
            : 'El equipo no respondió al ping en este intento.';

        $this->storeProbe(
            $node,
            $ping['reachable'],
            $ping['latency_ms'],
            $checkedPorts,
            $openPorts,
            $message
        );

        return response()->json([
            'node_id' => $node->id,
            'node_name' => $node->name,
            'ip' => $ipAddress,
            'reachable' => $ping['reachable'],
            'latency_ms' => $ping['latency_ms'],
            'open_ports' => $openPorts,
            'checked_ports' => $checkedPorts,
            'message' => $message,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function storeProbe(
        Node $node,
        bool $reachable,
        ?float $latencyMs,
        array $checkedPorts,
        array $openPorts,
        string $message
    ): void {
        NodeProbe::query()->create([
            'node_id' => $node->id,
            'reachable' => $reachable,
            'latency_ms' => $latencyMs,
            'checked_ports' => $checkedPorts,
            'open_ports' => $openPorts,
            'message' => $message,
            'probed_at' => now(),
        ]);
    }

    private function runPing(string $ipAddress): array
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $command = $isWindows
            ? sprintf('ping -n 1 -w 1000 %s', escapeshellarg($ipAddress))
            : sprintf('ping -c 1 -W 1 %s', escapeshellarg($ipAddress));

        try {
            $output = [];
            $exitCode = 1;
            @exec($command, $output, $exitCode);

            $joined = implode("\n", $output);
            $latency = null;

            if (preg_match('/time[=<]\s*([0-9]+(?:\.[0-9]+)?)\s*ms/i', $joined, $matches)) {
                $latency = (float) $matches[1];
            }

            return [
                'reachable' => $exitCode === 0,
                'latency_ms' => $latency,
            ];
        } catch (Throwable) {
            return [
                'reachable' => false,
                'latency_ms' => null,
            ];
        }
    }

    private function scanPorts(string $ipAddress, array $ports, float $timeoutSeconds = 0.4): array
    {
        $openPorts = [];

        foreach ($ports as $port) {
            $connection = @fsockopen($ipAddress, (int) $port, $errorCode, $errorMessage, $timeoutSeconds);

            if (is_resource($connection)) {
                $openPorts[] = (int) $port;
                fclose($connection);
            }
        }

        return $openPorts;
    }

    private function toGraphNode(Node $node): array
    {
        $branch = Branch::query()->find($node->branch_id, ['id', 'name']);
        $connectedAssetsCount = $node->relationLoaded('computerAssets')
            ? $node->computerAssets->count()
            : ComputerAsset::query()->where('node_id', $node->id)->count();
        $observedDevicesCount = $node->relationLoaded('observedDevices')
            ? $node->observedDevices->count()
            : NodeObservedDevice::query()->where('node_id', $node->id)->count();

        return [
            'id' => $node->id,
            'label' => $node->name,
            'type' => optional($node->nodeType)->name ?? 'Nodo',
            'type_slug' => optional($node->nodeType)->slug ?? '',
            'branch_id' => $node->branch_id,
            'branch_name' => $branch?->name,
            'status' => $node->status ?? 'inactive',
            'ip' => $node->ip_address,
            'layout_x' => $node->layout_x,
            'layout_y' => $node->layout_y,
            'branch_url' => url('/sede/' . $node->branch_id),
            'branch_network_url' => url('/sede/' . $node->branch_id . '/red'),
            'detail_url' => url('/nodos/' . $node->id),
            'connections_url' => url('/red/nodos/' . $node->id),
            'connected_assets_count' => $connectedAssetsCount,
            'observed_devices_count' => $observedDevicesCount,
        ];
    }

    private function normalizeMacAddress(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strtoupper(str_replace('-', ':', trim($value)));
    }

    private function resolveMnemonic(?Node $node): array
    {
        if (!$node) {
            return ['value' => null, 'source' => null];
        }

        $details = is_array($node->details) ? $node->details : [];

        $candidates = [
            'details.mnemonic' => data_get($details, 'mnemonic'),
            'details.device_mnemonic' => data_get($details, 'device_mnemonic'),
            'code' => $node->code,
            'name' => $node->name,
        ];

        foreach ($candidates as $source => $value) {
            $normalized = trim((string) ($value ?? ''));
            if ($normalized !== '') {
                return ['value' => $normalized, 'source' => $source];
            }
        }

        return ['value' => null, 'source' => null];
    }

    private function buildMemoryQuality(
        ?string $mnemonic,
        string $status,
        ?string $ipAddress,
        \Illuminate\Support\Collection $ports,
        \Illuminate\Support\Collection $outgoing,
        \Illuminate\Support\Collection $incoming,
        \Illuminate\Support\Collection $software
    ): array {
        $alerts = collect();
        $score = 0;

        if ($mnemonic !== null && trim($mnemonic) !== '') {
            $score += 20;
        } else {
            $alerts->push([
                'code' => 'missing_mnemonic',
                'level' => 'warning',
                'message' => 'Nodo sin mnemónico explícito.',
            ]);
        }

        if ($ipAddress !== null && trim((string) $ipAddress) !== '') {
            $score += 20;
        } else {
            $alerts->push([
                'code' => 'missing_ip',
                'level' => 'warning',
                'message' => 'Nodo sin IP registrada.',
            ]);
        }

        $portsCount = $ports->count();
        if ($portsCount > 0) {
            $score += 20;

            $unlabeledPorts = $ports->filter(function (array $port) {
                $name = trim((string) ($port['name'] ?? ''));

                if ($name === '') {
                    return true;
                }

                return (bool) preg_match('/^P\d+$/i', $name);
            })->count();

            if ($unlabeledPorts > 0) {
                $alerts->push([
                    'code' => 'unlabeled_ports',
                    'level' => 'info',
                    'message' => "{$unlabeledPorts} puerto(s) con etiqueta genérica.",
                ]);
            }
        } else {
            $alerts->push([
                'code' => 'missing_ports',
                'level' => 'warning',
                'message' => 'Nodo sin puertos modelados en memoria.',
            ]);
        }

        $relationsCount = $outgoing->count() + $incoming->count();
        if ($relationsCount > 0) {
            $score += 20;

            $relations = collect($outgoing->all())
                ->concat($incoming->all());

            $missingEndpoints = $relations
                ->filter(function (array $relation) {
                    return trim((string) ($relation['from_endpoint'] ?? '')) === ''
                        || trim((string) ($relation['to_endpoint'] ?? '')) === '';
                })->count();

            if ($missingEndpoints > 0) {
                $alerts->push([
                    'code' => 'relations_without_endpoint',
                    'level' => 'info',
                    'message' => "{$missingEndpoints} relación(es) sin endpoint completo.",
                ]);
            }
        } else {
            $alerts->push([
                'code' => 'no_relations',
                'level' => 'info',
                'message' => 'Nodo sin relaciones registradas.',
            ]);
        }

        if ($software->count() > 0) {
            $score += 20;
        }

        if (strtolower(trim($status)) === 'active' && ($ipAddress === null || trim((string) $ipAddress) === '')) {
            $alerts->push([
                'code' => 'active_without_ip',
                'level' => 'critical',
                'message' => 'Nodo activo sin IP.',
            ]);
        }

        return [
            'score' => min(100, max(0, $score)),
            'alerts' => $alerts->values()->all(),
        ];
    }
}
