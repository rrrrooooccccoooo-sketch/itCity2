<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ComputerAsset;
use App\Models\ComputerAssetTransferRequest;
use App\Models\ComputerAssetMetric;
use App\Models\EquipmentBrand;
use App\Models\EquipmentModel;
use App\Models\FloorPlan;
use App\Models\Node;
use App\Models\NodeObservedDevice;
use App\Models\NodeRelation;
use App\Models\NodeType;
use App\Models\PhysicalSpace;
use App\Models\SoftwareSystem;
use App\Support\ComputerAssetInventorySummary;
use App\Support\NodeConnectionSnapshot;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\Process\Process;

class AdminController extends Controller
{
    private const BLANK_PLAN_GRID_PX = 25;
    private const MONITORING_ONLINE_WINDOW_MINUTES = 10;

    public function dashboardPanel1(Request $request): View
    {
        $request->query->set('panel', '1');
        $data = $this->prepareDashboardData($request);
        return view('tenant.admin.dashboard-panel-1', $data);
    }

    public function dashboardPanel2(Request $request): View
    {
        $request->query->set('panel', '2');
        $data = $this->prepareDashboardData($request);
        return view('tenant.admin.dashboard-panel-2', $data);
    }

    public function dashboardPanel3(Request $request): View
    {
        $request->query->set('panel', '3');
        $data = $this->prepareDashboardData($request);
        return view('tenant.admin.dashboard-panel-3', $data);
    }

    public function dashboardLegacy(Request $request): View
    {
        $request->query->set('panel', '1');
        $data = $this->prepareDashboardData($request);
        return view('tenant.admin.dashboard', $data);
    }

    public function dashboard(Request $request): View
    {
        $request->query->set('panel', '1');
        $data = $this->prepareDashboardData($request);

        if ($request->filled('floor_plan')) {
            return view('tenant.admin.dashboard', $data);
        }

        return view('tenant.admin.dashboard-panel-1', $data);
    }

    private function prepareDashboardData(Request $request): array
    {
        $panelVariant = max(1, min(3, (int) $request->integer('panel', 1)));
        $branchScopeIds = $this->currentUserBranchScopeIds($request);

        $branches = Branch::query()
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('id', $branchScopeIds))
            ->orderBy('name')
            ->get();
        $nodeTypes = NodeType::query()->orderBy('name')->get();
        $spaces = PhysicalSpace::query()
            ->with('branch:id,name')
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->orderBy('name')
            ->get();
        $nodes = Node::query()
            ->with(['branch:id,name', 'nodeType:id,name', 'physicalSpace:id,name,space_type'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->orderBy('name')
            ->get();
        $floorPlans = FloorPlan::query()
            ->with(['branch:id,name', 'physicalSpace:id,name,floor,room'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->latest()
            ->get();

        $apNodeTypeIds = NodeType::query()
            ->where(function ($query) {
                $query->where('slug', 'like', '%access%')
                    ->orWhere('slug', 'like', '%ap%')
                    ->orWhere('name', 'like', '%access%')
                    ->orWhere('name', 'like', '%wifi%');
            })
            ->pluck('id');

        $accessPointNodes = Node::query()
            ->with(['branch:id,name', 'physicalSpace:id,name'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->when($apNodeTypeIds->isNotEmpty(), fn ($query) => $query->whereIn('node_type_id', $apNodeTypeIds))
            ->orderBy('name')
            ->get(['id', 'branch_id', 'physical_space_id', 'name', 'code', 'node_type_id']);
        $computerAssets = ComputerAsset::query()
            ->with(['branch:id,name', 'node:id,name'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->latest()
            ->get();
        $currentUser = $request->user();
        $currentUserId = (int) ($currentUser->id ?? 0);
        $transferAgents = User::query()
            ->select(['id', 'name', 'email', 'branch_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $transferRequestHistory = ComputerAssetTransferRequest::query()
            ->with([
                'computerAsset:id,asset_tag,hostname,branch_id,assigned_user',
                'requestedFromBranch:id,name',
                'requestedToBranch:id,name',
            ])
            ->when(
                $branchScopeIds !== null,
                fn (Builder $query) => $query->where(function (Builder $scoped) use ($branchScopeIds) {
                    $scoped
                        ->whereIn('requested_from_branch_id', $branchScopeIds)
                        ->orWhereIn('requested_to_branch_id', $branchScopeIds);
                })
            )
            ->latest('requested_at')
            ->limit(200)
            ->get();

        $pendingTransferRequests = $transferRequestHistory
            ->where('status', 'pending')
            ->values();

        $incomingTransferRequests = $pendingTransferRequests
            ->filter(fn (ComputerAssetTransferRequest $transfer) => (int) $transfer->requested_to_user_id === $currentUserId)
            ->values();
        $outgoingTransferRequests = $pendingTransferRequests
            ->filter(fn (ComputerAssetTransferRequest $transfer) => (int) $transfer->requested_by_user_id === $currentUserId)
            ->values();
        $equipmentBrands = EquipmentBrand::query()->orderBy('name')->get();
        $equipmentModels = EquipmentModel::query()->with('brand:id,name')->orderBy('name')->get();
        $apModels = $equipmentModels->filter(fn ($m) => $m->equipment_type === 'access-point')->values();
        $monitoringAssets = ComputerAsset::query()
            ->with(['branch:id,name', 'node:id,name'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
            ->whereNotNull('last_seen_at')
            ->orderByDesc('last_seen_at')
            ->limit(20)
            ->get();
        $systems = SoftwareSystem::query()
            ->with('node:id,name,branch_id')
            ->when(
                $branchScopeIds !== null,
                fn (Builder $query) => $query->whereHas('node', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
            )
            ->latest()
            ->get();
        $relations = NodeRelation::query()
            ->with(['fromNode:id,name,branch_id', 'toNode:id,name,branch_id'])
            ->when(
                $branchScopeIds !== null,
                fn (Builder $query) => $query
                    ->whereHas('fromNode', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
                    ->whereHas('toNode', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
            )
            ->latest()
            ->get();
        $monitoringOnlineThreshold = now()->subMinutes(self::MONITORING_ONLINE_WINDOW_MINUTES);

        $editingBranch = $request->filled('edit_branch')
            ? Branch::query()
                ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('id', $branchScopeIds))
                ->findOrFail($request->integer('edit_branch'))
            : new Branch();

        $editingNodeType = $request->filled('edit_node_type')
            ? NodeType::query()->findOrFail($request->integer('edit_node_type'))
            : new NodeType();

        $editingSpace = $request->filled('edit_space')
            ? PhysicalSpace::query()
                ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                ->findOrFail($request->integer('edit_space'))
            : new PhysicalSpace(['space_type' => 'room']);

        $editingNode = $request->filled('edit_node')
            ? Node::query()
                ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                ->findOrFail($request->integer('edit_node'))
            : new Node(['status' => 'active']);

        $editingComputerAsset = $request->filled('edit_asset')
            ? ComputerAsset::query()
                ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                ->findOrFail($request->integer('edit_asset'))
            : new ComputerAsset(['equipment_type' => 'desktop', 'status' => 'in_use']);

        $editingSoftware = $request->filled('edit_software')
            ? SoftwareSystem::query()
                ->when(
                    $branchScopeIds !== null,
                    fn (Builder $query) => $query->whereHas('node', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
                )
                ->findOrFail($request->integer('edit_software'))
            : new SoftwareSystem();

        $editingRelation = $request->filled('edit_relation')
            ? NodeRelation::query()
                ->when(
                    $branchScopeIds !== null,
                    fn (Builder $query) => $query
                        ->whereHas('fromNode', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
                        ->whereHas('toNode', fn (Builder $nodeQuery) => $nodeQuery->whereIn('branch_id', $branchScopeIds))
                )
                ->findOrFail($request->integer('edit_relation'))
            : new NodeRelation(['relation_type' => 'linked_to']);

        return [
            'branches' => $branches,
            'nodeTypes' => $nodeTypes,
            'spaces' => $spaces,
            'nodes' => $nodes,
            'floorPlans' => $floorPlans,
            'accessPointNodes' => $accessPointNodes,
            'computerAssets' => $computerAssets,
            'transferAgents' => $transferAgents,
            'incomingTransferRequests' => $incomingTransferRequests,
            'outgoingTransferRequests' => $outgoingTransferRequests,
            'transferRequestHistory' => $transferRequestHistory,
            'monitoringAssets' => $monitoringAssets,
            'monitoringSummary' => [
                'tracked_assets' => ComputerAsset::query()
                    ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                    ->whereNotNull('last_seen_at')
                    ->count(),
                'online_assets' => ComputerAsset::query()
                    ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                    ->where('last_seen_at', '>=', $monitoringOnlineThreshold)
                    ->count(),
                'critical_assets' => ComputerAsset::query()
                    ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds))
                    ->where(function ($query) {
                        $query->where('last_cpu_usage_percent', '>=', 90)
                            ->orWhere('last_memory_usage_percent', '>=', 90)
                            ->orWhere('last_disk_usage_percent', '>=', 90);
                    })
                    ->count(),
            ],
            'agentIngestUrl' => url('/agent/heartbeat'),
            'agentHeaderName' => 'X-Agent-Key',
            'monitoringOnlineWindowMinutes' => self::MONITORING_ONLINE_WINDOW_MINUTES,
            'assetEquipmentTypes' => ComputerAsset::equipmentTypeOptions(),
            'assetStorageTypes' => ComputerAsset::storageTypeOptions(),
            'assetStatusOptions' => ComputerAsset::statusOptions(),
            'systems' => $systems,
            'relations' => $relations,
            'editingBranch' => $editingBranch,
            'editingNodeType' => $editingNodeType,
            'editingSpace' => $editingSpace,
            'editingNode' => $editingNode,
            'editingComputerAsset' => $editingComputerAsset,
            'editingSoftware' => $editingSoftware,
            'editingRelation' => $editingRelation,
            'equipmentBrands' => $equipmentBrands,
            'equipmentModels' => $equipmentModels,
            'apModels' => $apModels,
            'equipmentModelTypes' => EquipmentModel::equipmentTypeOptions(),
            'editingEquipmentBrand' => $request->filled('edit_brand')
                ? EquipmentBrand::query()->findOrFail($request->integer('edit_brand'))
                : new EquipmentBrand(),
            'editingEquipmentModel' => $request->filled('edit_eqmodel')
                ? EquipmentModel::query()->findOrFail($request->integer('edit_eqmodel'))
                : new EquipmentModel(['equipment_type' => 'other']),
            'currentUserHasSignature' => !empty((string) optional($request->user())->signature_data_url),
            'currentUserSignatureDataUrl' => (string) (optional($request->user())->signature_data_url ?? ''),
            'panelVariant' => $panelVariant,
            'scopedBranchId' => $branchScopeIds[0] ?? null,
            'scopedBranchIds' => $branchScopeIds,
        ];
    }

    public function ingestAgentHeartbeat(Request $request): JsonResponse
    {
        $configuredKey = (string) config('services.agent.ingest_key', '');
        if ($configuredKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'TENANT_AGENT_INGEST_KEY no está configurado en el entorno.',
            ], 503);
        }

        $providedKey = (string) ($request->header('X-Agent-Key') ?? $request->input('agent_key', ''));
        if ($providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado. Llave de agente inválida.',
            ], 401);
        }

        $validator = validator($request->all(), [
            'asset_tag' => ['nullable', 'string', 'max:120'],
            'hostname' => ['required', 'string', 'max:120'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'node_id' => ['nullable', 'exists:nodes,id'],
            'ip_address' => ['nullable', 'ip'],
            'mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'equipment_type' => ['nullable', Rule::in(array_keys(ComputerAsset::equipmentTypeOptions()))],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:150'],
            'operating_system' => ['nullable', 'string', 'max:120'],
            'office_version' => ['nullable', 'string', 'max:120'],
            'cpu' => ['nullable', 'string', 'max:150'],
            'ram_gb' => ['nullable', 'integer', 'min:1', 'max:4096'],
            'storage_type' => ['nullable', Rule::in(array_keys(ComputerAsset::storageTypeOptions()))],
            'storage_gb' => ['nullable', 'integer', 'min:1', 'max:200000'],
            'status' => ['nullable', Rule::in(array_keys(ComputerAsset::statusOptions()))],
            'agent_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:120'],
            'details' => ['nullable', 'array'],
            'metrics' => ['nullable', 'array'],
            'metrics.cpu_usage_percent' => ['nullable', 'numeric', 'between:0,100'],
            'metrics.memory_usage_percent' => ['nullable', 'numeric', 'between:0,100'],
            'metrics.disk_usage_percent' => ['nullable', 'numeric', 'between:0,100'],
            'metrics.uptime_seconds' => ['nullable', 'integer', 'min:0'],
            'metrics.net_rx_kbps' => ['nullable', 'numeric', 'min:0'],
            'metrics.net_tx_kbps' => ['nullable', 'numeric', 'min:0'],
            'metrics.process_count' => ['nullable', 'integer', 'min:0'],
            'metrics.details' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Payload inválido para heartbeat de agente.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $asset = null;
        if (!empty($validated['asset_tag'])) {
            $asset = ComputerAsset::query()->where('asset_tag', $validated['asset_tag'])->first();
        }
        if (!$asset && !empty($validated['serial_number'])) {
            $asset = ComputerAsset::query()->where('serial_number', $validated['serial_number'])->first();
        }
        if (!$asset) {
            $asset = ComputerAsset::query()->where('hostname', $validated['hostname'])->first();
        }

        $requestedBranchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;
        [$node, $nodeResolution] = $this->resolveHeartbeatNode($validated, $asset, $requestedBranchId);

        $branchId = $validated['branch_id'] ?? null;
        if ($node) {
            $branchId = $node->branch_id;
        }
        if (!$branchId && $asset) {
            $branchId = $asset->branch_id;
        }

        if (!$branchId) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo resolver branch_id. Envía branch_id o node_id.',
            ], 422);
        }

        $details = $asset?->details;
        if (!is_array($details)) {
            $details = [];
        }
        if (isset($validated['details']) && is_array($validated['details'])) {
            $details = $this->mergeHeartbeatDetails($details, $validated['details']);
        }
        $details['agent'] = [
            'name' => $validated['agent_name'] ?? 'generic-agent',
            'platform' => $validated['platform'] ?? null,
            'last_ip' => $request->ip(),
            'last_heartbeat_at' => now()->toIso8601String(),
            'node_resolution' => $nodeResolution,
        ];

        $inventorySummary = $this->extractInventorySummaryFields($details);

        $payload = [
            'branch_id' => $branchId,
            'node_id' => $node?->id ?? ($asset?->node_id ?? null),
            'equipment_type' => $validated['equipment_type'] ?? 'desktop',
            'asset_tag' => $validated['asset_tag'] ?? ($asset?->asset_tag ?? null),
            'hostname' => $validated['hostname'],
            'serial_number' => $validated['serial_number'] ?? ($asset?->serial_number ?? null),
            'brand' => $validated['brand'] ?? ($asset?->brand ?? null),
            'model' => $validated['model'] ?? ($asset?->model ?? null),
            'operating_system' => $validated['operating_system'] ?? ($asset?->operating_system ?? null),
            'office_version' => $validated['office_version'] ?? ($asset?->office_version ?? null),
            'cpu' => $validated['cpu'] ?? ($asset?->cpu ?? null),
            'ram_gb' => $validated['ram_gb'] ?? ($asset?->ram_gb ?? null),
            'storage_type' => $validated['storage_type'] ?? ($asset?->storage_type ?? null),
            'storage_gb' => $validated['storage_gb'] ?? ($asset?->storage_gb ?? null),
            'status' => $validated['status'] ?? ($asset?->status ?? 'in_use'),
            'last_seen_at' => now(),
            'details' => $details,
            'inventory_last_captured_at' => $inventorySummary['inventory_last_captured_at'] ?? ($asset?->inventory_last_captured_at ?? null),
            'domain_name' => $inventorySummary['domain_name'] ?? ($asset?->domain_name ?? null),
            'bios_version' => $inventorySummary['bios_version'] ?? ($asset?->bios_version ?? null),
            'motherboard_product' => $inventorySummary['motherboard_product'] ?? ($asset?->motherboard_product ?? null),
            'motherboard_serial' => $inventorySummary['motherboard_serial'] ?? ($asset?->motherboard_serial ?? null),
            'antivirus_summary' => $inventorySummary['antivirus_summary'] ?? ($asset?->antivirus_summary ?? null),
            'installed_programs_count' => $inventorySummary['installed_programs_count'] ?? ($asset?->installed_programs_count ?? null),
            'hotfix_count' => $inventorySummary['hotfix_count'] ?? ($asset?->hotfix_count ?? null),
            'primary_ip_address' => $validated['ip_address']
                ?? ($inventorySummary['primary_ip_address'] ?? null)
                ?? ($asset?->primary_ip_address ?? null),
            'primary_mac_address' => $this->normalizeMacAddress($validated['mac_address'] ?? null)
                ?? ($inventorySummary['primary_mac_address'] ?? null)
                ?? ($asset?->primary_mac_address ?? null),
            'operating_system_version' => $inventorySummary['operating_system_version'] ?? ($asset?->operating_system_version ?? null),
            'operating_system_build' => $inventorySummary['operating_system_build'] ?? ($asset?->operating_system_build ?? null),
            'primary_gpu' => $inventorySummary['primary_gpu'] ?? ($asset?->primary_gpu ?? null),
            'memory_modules_count' => $inventorySummary['memory_modules_count'] ?? ($asset?->memory_modules_count ?? null),
            'physical_disks_count' => $inventorySummary['physical_disks_count'] ?? ($asset?->physical_disks_count ?? null),
        ];

        if (!$asset) {
            $asset = ComputerAsset::query()->create($payload);
        } else {
            $asset->update($payload);
        }

        $metrics = isset($validated['metrics']) && is_array($validated['metrics']) ? $validated['metrics'] : null;
        if ($metrics !== null) {
            ComputerAssetMetric::query()->create([
                'computer_asset_id' => $asset->id,
                'captured_at' => now(),
                'cpu_usage_percent' => $metrics['cpu_usage_percent'] ?? null,
                'memory_usage_percent' => $metrics['memory_usage_percent'] ?? null,
                'disk_usage_percent' => $metrics['disk_usage_percent'] ?? null,
                'uptime_seconds' => $metrics['uptime_seconds'] ?? null,
                'net_rx_kbps' => $metrics['net_rx_kbps'] ?? null,
                'net_tx_kbps' => $metrics['net_tx_kbps'] ?? null,
                'process_count' => $metrics['process_count'] ?? null,
                'details' => $metrics['details'] ?? null,
            ]);

            $asset->update([
                'last_cpu_usage_percent' => $metrics['cpu_usage_percent'] ?? null,
                'last_memory_usage_percent' => $metrics['memory_usage_percent'] ?? null,
                'last_disk_usage_percent' => $metrics['disk_usage_percent'] ?? null,
                'last_uptime_seconds' => $metrics['uptime_seconds'] ?? null,
            ]);
        }

        $observedSummary = ['upserted' => 0, 'skipped' => 0];
        $networkDiscovery = data_get($validated, 'details.network_discovery');
        if ($node && is_array($networkDiscovery) && is_array(data_get($networkDiscovery, 'devices'))) {
            $observedSummary = $this->ingestObservedDevicesForNode(
                $node,
                data_get($networkDiscovery, 'devices', []),
                (string) (data_get($networkDiscovery, 'source') ?: 'agent-heartbeat'),
                data_get($networkDiscovery, 'observed_at')
            );
        }

        return response()->json([
            'ok' => true,
            'asset_id' => $asset->id,
            'node_id' => $asset->node_id,
            'node_resolution_source' => $nodeResolution['source'] ?? 'none',
            'hostname' => $asset->hostname,
            'last_seen_at' => optional($asset->last_seen_at)->toIso8601String(),
            'observed_devices_upserted' => $observedSummary['upserted'],
            'observed_devices_skipped' => $observedSummary['skipped'],
        ]);
    }

    public function ingestAgentNetworkObservations(Request $request): JsonResponse
    {
        $configuredKey = (string) config('services.agent.ingest_key', '');
        if ($configuredKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'TENANT_AGENT_INGEST_KEY no está configurado en el entorno.',
            ], 503);
        }

        $providedKey = (string) ($request->header('X-Agent-Key') ?? $request->input('agent_key', ''));
        if ($providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado. Llave de agente inválida.',
            ], 401);
        }

        $validated = $request->validate([
            'node_id' => ['required', 'exists:nodes,id'],
            'observed_via' => ['nullable', 'string', 'max:60'],
            'observed_at' => ['nullable', 'date'],
            'devices' => ['required', 'array', 'min:1'],
            'devices.*.mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'devices.*.ip_address' => ['nullable', 'ip'],
            'devices.*.hostname' => ['nullable', 'string', 'max:255'],
            'devices.*.domain_name' => ['nullable', 'string', 'max:255'],
            'devices.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'devices.*.ssid' => ['nullable', 'string', 'max:255'],
            'devices.*.switch_port' => ['nullable', 'string', 'max:120'],
            'devices.*.device_type' => ['nullable', 'string', 'max:80'],
            'devices.*.meta' => ['nullable', 'array'],
            'devices.*.last_seen_at' => ['nullable', 'date'],
        ]);

        $node = Node::query()->findOrFail((int) $validated['node_id']);
        $summary = $this->ingestObservedDevicesForNode(
            $node,
            $validated['devices'],
            (string) ($validated['observed_via'] ?? 'agent-discovery'),
            $validated['observed_at'] ?? null
        );

        return response()->json([
            'ok' => true,
            'node_id' => $node->id,
            'upserted' => $summary['upserted'],
            'skipped' => $summary['skipped'],
        ]);
    }

    private function mergeHeartbeatDetails(array $currentDetails, array $incomingDetails): array
    {
        foreach ($incomingDetails as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                continue;
            }

            if (is_array($value)) {
                $existing = $currentDetails[$key] ?? null;
                if (!is_array($existing) || array_is_list($value) || array_is_list($existing)) {
                    $currentDetails[$key] = $value;
                    continue;
                }

                $currentDetails[$key] = $this->mergeHeartbeatDetails($existing, $value);
                continue;
            }

            $currentDetails[$key] = $value;
        }

        return $currentDetails;
    }

    private function ingestObservedDevicesForNode(Node $node, array $devices, string $observedVia, mixed $observedAt = null): array
    {
        $tenantDomains = NodeConnectionSnapshot::tenantDomains();
        $observedTimestamp = $this->parseObservedAt($observedAt);
        $upserted = 0;
        $skipped = 0;

        foreach ($devices as $device) {
            if (!is_array($device)) {
                $skipped++;
                continue;
            }

            $macAddress = NodeConnectionSnapshot::normalizeMacAddress($device['mac_address'] ?? null);
            $hostname = $this->normalizeDiscoveryText($device['hostname'] ?? null);
            $ipAddress = $this->normalizeDiscoveryText($device['ip_address'] ?? null);

            if ($macAddress === null && $hostname === null && $ipAddress === null) {
                $skipped++;
                continue;
            }

            $matchedAsset = $this->findManagedAssetForObservedDevice(
                branchId: (int) $node->branch_id,
                macAddress: $macAddress,
                hostname: $hostname
            );

            $ownership = NodeConnectionSnapshot::classifyOwnership(
                domainName: $device['domain_name'] ?? null,
                tenantDomains: $tenantDomains,
                matchedManagedAsset: $matchedAsset !== null
            );

            $existing = $this->findExistingObservedDevice(
                nodeId: (int) $node->id,
                macAddress: $macAddress,
                hostname: $hostname,
                ipAddress: $ipAddress
            );

            $payload = [
                'branch_id' => $node->branch_id,
                'node_id' => $node->id,
                'observed_via' => trim($observedVia) !== '' ? trim($observedVia) : 'manual',
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'hostname' => $hostname,
                'domain_name' => NodeConnectionSnapshot::normalizeDomain($device['domain_name'] ?? null),
                'vendor_name' => $this->normalizeDiscoveryText($device['vendor_name'] ?? null),
                'ssid' => $this->normalizeDiscoveryText($device['ssid'] ?? null),
                'switch_port' => $this->normalizeDiscoveryText($device['switch_port'] ?? null),
                'device_type' => $this->normalizeDiscoveryText($device['device_type'] ?? null),
                'is_managed' => $ownership['is_managed'],
                'last_seen_at' => isset($device['last_seen_at']) ? $this->parseObservedAt($device['last_seen_at']) : $observedTimestamp,
                'meta' => isset($device['meta']) && is_array($device['meta']) ? $device['meta'] : null,
            ];

            if ($existing) {
                $existing->fill($payload);
                if ($existing->first_seen_at === null) {
                    $existing->first_seen_at = $observedTimestamp;
                }
                $existing->save();
            } else {
                NodeObservedDevice::query()->create([
                    ...$payload,
                    'first_seen_at' => $observedTimestamp,
                ]);
            }

            $upserted++;
        }

        return [
            'upserted' => $upserted,
            'skipped' => $skipped,
        ];
    }

    private function findManagedAssetForObservedDevice(int $branchId, ?string $macAddress, ?string $hostname): ?ComputerAsset
    {
        if ($macAddress !== null) {
            $asset = ComputerAsset::query()
                ->where('branch_id', $branchId)
                ->where('primary_mac_address', $macAddress)
                ->first();

            if ($asset) {
                return $asset;
            }
        }

        if ($hostname !== null) {
            return ComputerAsset::query()
                ->where('branch_id', $branchId)
                ->whereRaw('LOWER(hostname) = ?', [mb_strtolower($hostname)])
                ->first();
        }

        return null;
    }

    private function findExistingObservedDevice(int $nodeId, ?string $macAddress, ?string $hostname, ?string $ipAddress): ?NodeObservedDevice
    {
        if ($macAddress !== null) {
            $existing = NodeObservedDevice::query()
                ->where('node_id', $nodeId)
                ->where('mac_address', $macAddress)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($hostname !== null && $ipAddress !== null) {
            $existing = NodeObservedDevice::query()
                ->where('node_id', $nodeId)
                ->whereRaw('LOWER(hostname) = ?', [mb_strtolower($hostname)])
                ->where('ip_address', $ipAddress)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($hostname !== null) {
            $existing = NodeObservedDevice::query()
                ->where('node_id', $nodeId)
                ->whereRaw('LOWER(hostname) = ?', [mb_strtolower($hostname)])
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($ipAddress !== null) {
            return NodeObservedDevice::query()
                ->where('node_id', $nodeId)
                ->where('ip_address', $ipAddress)
                ->first();
        }

        return null;
    }

    private function parseObservedAt(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
            }
        }

        return now();
    }

    private function normalizeDiscoveryText(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    public function monitoringOverview(Request $request): JsonResponse|RedirectResponse
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect('/admin#crud-monitoring');
        }

        $onlineThreshold = now()->subMinutes(self::MONITORING_ONLINE_WINDOW_MINUTES);
        $branchScopeIds = $this->currentUserBranchScopeIds($request);
        $requestedBranchId = (int) $request->integer('branch_id', 0);

        $effectiveBranchIds = $branchScopeIds;
        if ($effectiveBranchIds === null && $requestedBranchId > 0) {
            $effectiveBranchIds = [$requestedBranchId];
        }

        $assets = ComputerAsset::query()
            ->with(['branch:id,name', 'node:id,name'])
            ->when($effectiveBranchIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $effectiveBranchIds))
            ->whereNotNull('last_seen_at')
            ->orderByDesc('last_seen_at')
            ->limit(20)
            ->get();

        $rows = $assets->map(function (ComputerAsset $asset) use ($onlineThreshold) {
            $isOnline = $asset->last_seen_at !== null && $asset->last_seen_at->gte($onlineThreshold);
            $isCritical = (float) ($asset->last_cpu_usage_percent ?? 0) >= 90
                || (float) ($asset->last_memory_usage_percent ?? 0) >= 90
                || (float) ($asset->last_disk_usage_percent ?? 0) >= 90;

            return [
                'id' => $asset->id,
                'name' => $asset->asset_tag ?: ($asset->hostname ?: 'Activo #' . $asset->id),
                'hostname' => $asset->hostname,
                'branch' => optional($asset->branch)->name,
                'node_name' => optional($asset->node)->name,
                'node_resolution_source' => (string) (data_get($asset->details, 'agent.node_resolution.source')
                    ?: ($asset->node_id ? 'manual' : 'none')),
                'last_seen_human' => $asset->last_seen_at?->diffForHumans(),
                'cpu' => $asset->last_cpu_usage_percent,
                'memory' => $asset->last_memory_usage_percent,
                'disk' => $asset->last_disk_usage_percent,
                'online' => $isOnline,
                'critical' => $isCritical,
            ];
        });

        return response()->json([
            'ok' => true,
            'summary' => [
                'tracked_assets' => ComputerAsset::query()
                    ->when($effectiveBranchIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $effectiveBranchIds))
                    ->whereNotNull('last_seen_at')
                    ->count(),
                'online_assets' => ComputerAsset::query()
                    ->when($effectiveBranchIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $effectiveBranchIds))
                    ->where('last_seen_at', '>=', $onlineThreshold)
                    ->count(),
                'critical_assets' => ComputerAsset::query()
                    ->when($effectiveBranchIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $effectiveBranchIds))
                    ->where(function ($query) {
                        $query->where('last_cpu_usage_percent', '>=', 90)
                            ->orWhere('last_memory_usage_percent', '>=', 90)
                            ->orWhere('last_disk_usage_percent', '>=', 90);
                    })
                    ->count(),
            ],
            'assets' => $rows->values(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function monitoringAssetDetail(Request $request, int $id): JsonResponse
    {
        $asset = ComputerAsset::with(['branch:id,name'])->findOrFail($id);
        $this->assertBranchAccess($asset->branch_id, $this->currentUserBranchScopeIds($request));

        $onlineThreshold = now()->subMinutes(self::MONITORING_ONLINE_WINDOW_MINUTES);
        $isOnline = $asset->last_seen_at !== null && $asset->last_seen_at->gte($onlineThreshold);

        $metrics = $asset->metrics()
            ->orderByDesc('captured_at')
            ->limit(30)
            ->get()
            ->map(fn ($m) => [
                'captured_at'    => $m->captured_at->toIso8601String(),
                'captured_human' => $m->captured_at->diffForHumans(),
                'cpu'            => $m->cpu_usage_percent,
                'memory'         => $m->memory_usage_percent,
                'disk'           => $m->disk_usage_percent,
                'net_rx_kbps'    => $m->net_rx_kbps,
                'net_tx_kbps'    => $m->net_tx_kbps,
                'uptime_seconds' => $m->uptime_seconds,
                'process_count'  => $m->process_count,
            ]);

        $inventory = $this->buildMonitoringInventory($asset);

        return response()->json([
            'ok'    => true,
            'asset' => [
                'id'           => $asset->id,
                'name'         => $asset->asset_tag ?: ($asset->hostname ?: 'Activo #' . $asset->id),
                'hostname'     => $asset->hostname,
                'serial'       => $asset->serial_number,
                'assigned_user' => $asset->assigned_user,
                'branch'       => optional($asset->branch)->name,
                'online'       => $isOnline,
                'last_seen_at' => $asset->last_seen_at?->toIso8601String(),
                'last_seen_human' => $asset->last_seen_at?->diffForHumans(),
                'cpu'          => $asset->last_cpu_usage_percent,
                'memory'       => $asset->last_memory_usage_percent,
                'disk'         => $asset->last_disk_usage_percent,
                'uptime'       => $asset->last_uptime_seconds,
            ],
            'inventory' => $inventory,
            'metrics' => $metrics->values(),
        ]);
    }

    private function buildMonitoringInventory(ComputerAsset $asset): array
    {
        $inventory = data_get($asset->details, 'inventory');
        $hardware = is_array(data_get($inventory, 'hardware')) ? data_get($inventory, 'hardware') : [];
        $software = is_array(data_get($inventory, 'software')) ? data_get($inventory, 'software') : [];
        $installedPrograms = $this->normalizeInventoryList(data_get($software, 'installed_programs'));
        $antivirus = $this->normalizeInventoryList(data_get($software, 'antivirus'));

        return [
            'captured_at' => data_get($inventory, 'captured_at'),
            'capture_scope' => data_get($inventory, 'capture_scope'),
            'last_extended_captured_at' => data_get($inventory, 'last_extended_captured_at'),
            'summary' => [
                'brand' => $asset->brand,
                'model' => $asset->model,
                'serial' => $asset->serial_number,
                'equipment_type' => $asset->equipment_type,
                'equipment_type_label' => ComputerAsset::equipmentTypeOptions()[$asset->equipment_type] ?? ucfirst((string) $asset->equipment_type),
                'cpu' => $asset->cpu,
                'ram_gb' => $asset->ram_gb,
                'storage_type' => $asset->storage_type,
                'storage_type_label' => ComputerAsset::storageTypeOptions()[$asset->storage_type] ?? ucfirst((string) $asset->storage_type),
                'storage_gb' => $asset->storage_gb,
                'operating_system' => $asset->operating_system,
                'office_version' => $asset->office_version,
                'domain_name' => $asset->domain_name,
                'bios_version' => $asset->bios_version,
                'motherboard_product' => $asset->motherboard_product,
                'motherboard_serial' => $asset->motherboard_serial,
                'antivirus_summary' => $asset->antivirus_summary,
                'installed_programs_count' => $asset->installed_programs_count,
                'hotfix_count' => $asset->hotfix_count,
                'primary_ip_address' => $asset->primary_ip_address,
                'primary_mac_address' => $asset->primary_mac_address,
                'operating_system_version' => $asset->operating_system_version,
                'operating_system_build' => $asset->operating_system_build,
                'primary_gpu' => $asset->primary_gpu,
                'memory_modules_count' => $asset->memory_modules_count,
                'physical_disks_count' => $asset->physical_disks_count,
            ],
            'hardware' => [
                'system' => is_array(data_get($hardware, 'system')) ? data_get($hardware, 'system') : [],
                'bios' => is_array(data_get($hardware, 'bios')) ? data_get($hardware, 'bios') : [],
                'motherboard' => is_array(data_get($hardware, 'motherboard')) ? data_get($hardware, 'motherboard') : [],
                'processors' => $this->normalizeInventoryList(data_get($hardware, 'processors')),
                'memory_modules' => $this->normalizeInventoryList(data_get($hardware, 'memory_modules')),
                'physical_disks' => $this->normalizeInventoryList(data_get($hardware, 'physical_disks')),
                'logical_disks' => $this->normalizeInventoryList(data_get($hardware, 'logical_disks')),
                'video_controllers' => $this->normalizeInventoryList(data_get($hardware, 'video_controllers')),
                'network_adapters' => $this->normalizeInventoryList(data_get($hardware, 'network_adapters')),
            ],
            'software' => [
                'operating_system' => is_array(data_get($software, 'operating_system')) ? data_get($software, 'operating_system') : [],
                'office_version' => data_get($software, 'office_version') ?: $asset->office_version,
                'hotfix_count' => data_get($software, 'hotfix_count'),
                'antivirus' => $antivirus,
                'installed_programs' => $installedPrograms,
                'installed_programs_count' => is_numeric(data_get($software, 'installed_programs_total'))
                    ? (int) data_get($software, 'installed_programs_total')
                    : count($installedPrograms),
            ],
        ];
    }

    private function normalizeInventoryList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, fn ($item) => is_array($item)));
    }

    private function extractInventorySummaryFields(array $details): array
    {
        return ComputerAssetInventorySummary::extract($details);
    }

    private function currentUserBranchScopeIds(Request $request): ?array
    {
        $user = $request->user();
        if (!$user instanceof User) {
            return null;
        }

        $scopeIds = $user->effectiveBranchScopeIds();
        if ($scopeIds === null) {
            return null;
        }

        if (empty($scopeIds)) {
            abort(403, 'Tu usuario no tiene un campus asignado. Contacta al administrador.');
        }

        return $scopeIds;
    }

    private function assertBranchAccess(?int $resourceBranchId, ?array $branchScopeIds): void
    {
        if ($branchScopeIds === null) {
            return;
        }

        if ($resourceBranchId === null || !in_array((int) $resourceBranchId, $branchScopeIds, true)) {
            abort(403, 'No tienes acceso a información de otro campus.');
        }
    }

    public function downloadAgentInstaller(Request $request)
    {
        $agentKey = (string) config('services.agent.ingest_key', '');
        $endpoint = url('/agent/heartbeat');
        $observationsEndpoint = url('/agent/network-observations');
        $tenantHost = (string) request()->getHost();
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $branchScopeIds = $this->currentUserBranchScopeIds($request);

        $requestedBranchId = (int) $request->integer('branch_id', 0);
        $requestedInterval = max(30, min(3600, (int) $request->integer('interval', 60)));
        $requestedInventoryHours = max(1, min(168, (int) $request->integer('inventory_hours', 12)));
        $requestedNetworkDiscoveryMinutes = max(1, min(240, (int) $request->integer('network_discovery_minutes', 10)));
        $requestedNetworkDiscoverySampleLimit = max(10, min(5000, (int) $request->integer('network_discovery_sample_limit', 256)));
        $requestedSnmpDiscoveryMinutes = max(1, min(240, (int) $request->integer('snmp_discovery_minutes', 15)));
        $requestedSnmpSampleLimit = max(10, min(10000, (int) $request->integer('snmp_sample_limit', 500)));
        $requestedAssetTag = trim((string) $request->input('asset_tag', ''));
        $snmpTargetsTemplate = $this->buildSnmpTargetsTemplate($branchScopeIds, $requestedBranchId > 0 ? $requestedBranchId : null);
        $snmpTargetsJson = json_encode($snmpTargetsTemplate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($snmpTargetsJson === false) {
            $snmpTargetsJson = '[]';
        }

        $branchLine = $requestedBranchId > 0
            ? "\$BranchId = {$requestedBranchId}"
            : "\$BranchId = 1  # TODO: reemplaza con el ID de sede correcto";

        $assetTagLine = $requestedAssetTag !== ''
            ? "\$AssetTag = '{$requestedAssetTag}'"
            : "\$AssetTag = \$env:COMPUTERNAME  # Se usa el hostname si no se especifica";

        $agentKeyPlaceholder = $agentKey !== ''
            ? '# Llave de ingestión pre-configurada (protegida con DPAPI al instalar)'
            : '# ADVERTENCIA: TENANT_AGENT_INGEST_KEY no está configurado en el servidor';

        $bundledAgentScript = @file_get_contents(base_path('scripts/agent/windows-heartbeat-agent.ps1'));
        $bundledAgentScriptBase64 = base64_encode($bundledAgentScript !== false ? $bundledAgentScript : '');

        $agentScript = <<<PS
#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Instalador pre-configurado del agente ITCity2 · {$tenantHost}
    Generado automáticamente el {$this->nowForScript()}
    Ejecutar como Administrador en Windows PowerShell 5.1+

.EXAMPLE
    .\install-agent-{$tenantHost}.ps1
    .\install-agent-{$tenantHost}.ps1 -AssetTag "PC-CONTAB-012" -BranchId 3
#>
[CmdletBinding()]
param(
    [string]\$AssetTag = "",
    [int]\$BranchId = 0,
    [string]\$RunAsUser = "",       # Cuenta Windows para la tarea programada (dominio\usuario)
    [string]\$RunAsPassword = "",   # Contraseña para la cuenta (dejar vacío para pedir interactivo)
    [switch]\$NoTask               # Solo instala archivos, no registra la tarea programada
)

Set-StrictMode -Version Latest
\$ErrorActionPreference = 'Stop'

# ── Parámetros pre-configurados por el servidor ──────────────────────────────
\$Endpoint                  = '{$endpoint}'
\$ObservationsEndpoint      = '{$observationsEndpoint}'
\$TenantHost                = '{$tenantHost}'
{$agentKeyPlaceholder}
\$AgentKey                  = '{$agentKey}'
\$IntervalSeconds           = {$requestedInterval}
\$InventoryHours            = {$requestedInventoryHours}
\$NetworkDiscoveryMinutes   = {$requestedNetworkDiscoveryMinutes}
\$NetworkDiscoverySample    = {$requestedNetworkDiscoverySampleLimit}
\$SnmpDiscoveryMinutes      = {$requestedSnmpDiscoveryMinutes}
\$SnmpSampleLimit           = {$requestedSnmpSampleLimit}
\$SnmpTargetsJson = @'
{$snmpTargetsJson}
'@

try {
    \$SnmpTargets = @(ConvertFrom-Json -InputObject \$SnmpTargetsJson)
} catch {
    \$SnmpTargets = @()
}

if (\$BranchId -eq 0) {
    {$branchLine}
}

if ([string]::IsNullOrWhiteSpace(\$AssetTag)) {
    {$assetTagLine}
}

# ── Rutas de instalación ──────────────────────────────────────────────────────
\$InstallDir  = 'C:\\ProgramData\\ITCity\\Agent'
\$AgentPs1    = Join-Path \$InstallDir 'windows-heartbeat-agent.ps1'
\$ConfigPath  = Join-Path \$InstallDir 'agent-config.json'
\$StatePath   = Join-Path \$InstallDir 'agent-state.json'
\$TaskName    = 'ITCityAgent'
\$BundledAgentScriptBase64 = '{$bundledAgentScriptBase64}'

# ── Crear directorio ──────────────────────────────────────────────────────────
if (-not (Test-Path \$InstallDir)) {
    New-Item -ItemType Directory -Path \$InstallDir -Force | Out-Null
}

# ── Descargar el script del agente desde el servidor ─────────────────────────
Write-Host "Descargando agente desde {$tenantHost}..."
try {
    \$headers = @{ 'Accept' = 'text/plain' }
    if (-not [string]::IsNullOrWhiteSpace(\$TenantHost)) {
        \$headers['Host'] = \$TenantHost
    }
    \$agentContent = (New-Object System.Net.WebClient).DownloadString(
        '{$endpoint}/../../../scripts/agent/windows-heartbeat-agent.ps1'
    )
    \$agentContent | Set-Content -Path \$AgentPs1 -Encoding UTF8
} catch {
    Write-Warning "No se pudo descargar el agente: \$_"
    if (-not [string]::IsNullOrWhiteSpace(\$BundledAgentScriptBase64)) {
        Write-Host "Usando copia embebida de windows-heartbeat-agent.ps1..."
        \$bundledBytes = [Convert]::FromBase64String(\$BundledAgentScriptBase64)
        \$bundledText = [System.Text.Encoding]::UTF8.GetString(\$bundledBytes)
        \$bundledText | Set-Content -Path \$AgentPs1 -Encoding UTF8
    } else {
        Write-Warning "Copia windows-heartbeat-agent.ps1 manualmente a: \$AgentPs1"
        if (-not (Test-Path \$AgentPs1)) {
            throw "Archivo del agente no encontrado en \$AgentPs1. Instalación cancelada."
        }
    }
}

# ── Proteger la llave con DPAPI (LocalMachine scope) ─────────────────────────
Write-Host "Protegiendo llave de agente con DPAPI (LocalMachine)..."
Add-Type -AssemblyName System.Security
\$keyBytes      = [System.Text.Encoding]::UTF8.GetBytes(\$AgentKey)
\$entropy       = [System.Text.Encoding]::UTF8.GetBytes('ITCityAgentKey')
\$protectedBytes = [System.Security.Cryptography.ProtectedData]::Protect(
    \$keyBytes, \$entropy,
    [System.Security.Cryptography.DataProtectionScope]::LocalMachine
)
\$protectedB64  = [Convert]::ToBase64String(\$protectedBytes)

# ── Escribir configuración ────────────────────────────────────────────────────
\$config = [ordered]@{
    endpoint                     = \$Endpoint
    observations_endpoint        = \$ObservationsEndpoint
    tenant_host                  = \$TenantHost
    branch_id                    = \$BranchId
    asset_tag                    = \$AssetTag
    interval_seconds             = \$IntervalSeconds
    inventory_interval_hours = \$InventoryHours
    network_discovery_interval_minutes = \$NetworkDiscoveryMinutes
    network_discovery_sample_limit = \$NetworkDiscoverySample
    snmp_discovery_interval_minutes = \$SnmpDiscoveryMinutes
    snmp_sample_limit = \$SnmpSampleLimit
    snmp_targets = \$SnmpTargets
    agent_key_protected          = \$protectedB64
}
\$config | ConvertTo-Json | Set-Content -Path \$ConfigPath -Encoding UTF8
Write-Host "Configuración guardada en: \$ConfigPath"

if (\$NoTask) {
    Write-Host "Opción -NoTask activa. Archivos instalados pero tarea programada NO registrada."
    Write-Host "Para registrar manualmente: Register-ScheduledTask ..."
    exit 0
}

# ── Registrar tarea programada ───────────────────────────────────────────────
Write-Host "Registrando tarea programada: \$TaskName"
\$psExe  = "\$env:SystemRoot\\System32\\WindowsPowerShell\\v1.0\\powershell.exe"
\$action = New-ScheduledTaskAction -Execute \$psExe `
    -Argument "-NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"\$AgentPs1`" -ConfigPath `"\$ConfigPath`" -StatePath `"\$StatePath`""

\$triggerAtStart = New-ScheduledTaskTrigger -AtStartup
\$triggerRepeat  = New-ScheduledTaskTrigger -Once -At (Get-Date) `
    -RepetitionInterval (New-TimeSpan -Seconds \$IntervalSeconds) `
    -RepetitionDuration (New-TimeSpan -Days 3650)

\$settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -StartWhenAvailable

if (-not [string]::IsNullOrWhiteSpace(\$RunAsUser)) {
    if ([string]::IsNullOrWhiteSpace(\$RunAsPassword)) {
        \$cred = Get-Credential -UserName \$RunAsUser -Message "Contraseña para la tarea programada"
        \$RunAsPassword = \$cred.GetNetworkCredential().Password
    }
    \$principal = New-ScheduledTaskPrincipal -UserId \$RunAsUser -LogonType Password -RunLevel Highest
    try {
        Register-ScheduledTask -TaskName \$TaskName `
            -Action \$action `
            -Trigger @(\$triggerAtStart, \$triggerRepeat) `
            -Settings \$settings `
            -Principal \$principal `
            -Password \$RunAsPassword `
            -Force `
            -ErrorAction Stop | Out-Null
    } catch {
        throw "No se pudo registrar la tarea programada '\$TaskName': \$(\$_.Exception.Message)"
    }
} else {
    \$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
    try {
        Register-ScheduledTask -TaskName \$TaskName `
            -Action \$action `
            -Trigger @(\$triggerAtStart, \$triggerRepeat) `
            -Settings \$settings `
            -Principal \$principal `
            -Force `
            -ErrorAction Stop | Out-Null
    } catch {
        throw "No se pudo registrar la tarea programada '\$TaskName': \$(\$_.Exception.Message)"
    }
}

Write-Host ""
Write-Host "=== Instalación completada ==="
Write-Host "  Agente      : \$AgentPs1"
Write-Host "  Configuración: \$ConfigPath"
Write-Host "  Tarea        : \$TaskName"
Write-Host "  Endpoint     : \$Endpoint"
Write-Host "  Observaciones: \$ObservationsEndpoint"
Write-Host "  Sede (ID)    : \$BranchId"
Write-Host "  Intervalo    : \$IntervalSeconds s"
Write-Host "  Inventario   : cada \$InventoryHours h"
Write-Host "  Discovery red: cada \$NetworkDiscoveryMinutes min (max \$NetworkDiscoverySample)"
Write-Host "  Discovery SNMP: cada \$SnmpDiscoveryMinutes min (max \$SnmpSampleLimit por target)"
Write-Host "  SNMP targets : \$((@(\$SnmpTargets)).Count)"
Write-Host ""
Write-Host "Iniciando agente ahora para verificar primer heartbeat..."
try {
    Start-ScheduledTask -TaskName \$TaskName -ErrorAction Stop
} catch {
    throw "La tarea '\$TaskName' no pudo iniciarse: \$(\$_.Exception.Message)"
}
Write-Host "Para desinstalar: Unregister-ScheduledTask -TaskName '\$TaskName' -Confirm:\`\$false; Remove-Item '\$InstallDir' -Recurse -Force"
PS;

        $filename = 'install-agent-' . preg_replace('/[^A-Za-z0-9\-_.]/', '-', $tenantHost) . '.ps1';

        return response($agentScript, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"; filename*=UTF-8''{$filename}",
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadAgentInstallerZip(Request $request)
    {
        $ps1Response = $this->downloadAgentInstaller($request);
        $scriptContent = (string) $ps1Response->getContent();

        $contentDisposition = (string) $ps1Response->headers->get('Content-Disposition', '');
        $ps1Filename = 'install-agent.ps1';
        if (preg_match('/filename="?([^";]+)"?/i', $contentDisposition, $matches) === 1) {
            $ps1Filename = (string) ($matches[1] ?? $ps1Filename);
        }

        $zipFilename = preg_replace('/\.ps1$/i', '.zip', $ps1Filename) ?: 'install-agent.zip';
        $tempBase = tempnam(sys_get_temp_dir(), 'itcity-agent-');
        if ($tempBase === false) {
            abort(500, 'No se pudo preparar el archivo temporal del instalador ZIP.');
        }

        $zipPath = $tempBase . '.zip';
        @unlink($tempBase);

        $zip = new \ZipArchive();
        $opened = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($opened !== true) {
            abort(500, 'No se pudo generar el instalador ZIP.');
        }

        $zip->addFromString($ps1Filename, $scriptContent);
        $zip->close();

        return response()->download($zipPath, $zipFilename, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    public function downloadSnmpTargetsTemplate(Request $request)
    {
        $branchScopeIds = $this->currentUserBranchScopeIds($request);
        $requestedBranchId = (int) $request->integer('branch_id', 0);

        $targets = $this->buildSnmpTargetsTemplate(
            branchScopeIds: $branchScopeIds,
            requestedBranchId: $requestedBranchId > 0 ? $requestedBranchId : null
        );

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'tenant_host' => request()->getHost(),
            'observations_endpoint' => url('/agent/network-observations'),
            'heartbeat_endpoint' => url('/agent/heartbeat'),
            'targets_count' => count($targets),
            'snmp_targets' => $targets,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{}';
        }

        $filename = 'snmp-targets-template-' . preg_replace('/[^A-Za-z0-9\-_.]/', '-', (string) request()->getHost()) . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buildSnmpTargetsTemplate(?array $branchScopeIds, ?int $requestedBranchId = null): array
    {
        $query = Node::query()
            ->with(['nodeType:id,name,slug', 'branch:id,name'])
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '');

        if ($branchScopeIds !== null) {
            $query->whereIn('branch_id', $branchScopeIds);
        }

        if ($requestedBranchId !== null && $requestedBranchId > 0) {
            if ($branchScopeIds !== null && !in_array($requestedBranchId, $branchScopeIds, true)) {
                return [];
            }
            $query->where('branch_id', $requestedBranchId);
        }

        $nodes = $query
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get(['id', 'branch_id', 'node_type_id', 'name', 'ip_address']);

        return $nodes
            ->filter(function (Node $node) {
                $slug = Str::lower((string) optional($node->nodeType)->slug);
                $typeName = Str::lower((string) optional($node->nodeType)->name);
                $nodeName = Str::lower((string) $node->name);
                $haystack = trim($slug . ' ' . $typeName . ' ' . $nodeName);

                return Str::contains($haystack, ['switch', 'router', 'access', 'wifi', 'ap', 'firewall']);
            })
            ->map(function (Node $node) {
                $branchName = optional($node->branch)->name;

                return [
                    'name' => trim(($node->name ?: 'Nodo ' . $node->id) . ($branchName ? ' · ' . $branchName : '')),
                    'host' => $node->ip_address,
                    'node_id' => $node->id,
                    'community' => 'public',
                    'version' => '2c',
                    'port' => 161,
                    'timeout_seconds' => 5,
                    'retries' => 1,
                    'sample_limit' => 500,
                    'security_name' => null,
                    'security_level' => 'authPriv',
                    'auth_protocol' => 'SHA',
                    'auth_passphrase' => null,
                    'priv_protocol' => 'AES',
                    'priv_passphrase' => null,
                    'context_name' => null,
                ];
            })
            ->values()
            ->all();
    }

    public function downloadAgentInstallerExe(Request $request)
    {
        $tenantHost = (string) request()->getHost();
        $safeTenantHost = preg_replace('/[^A-Za-z0-9\-_.]/', '-', $tenantHost) ?: 'tenant';
        $tenantExePath = storage_path('app/agent-installers' . DIRECTORY_SEPARATOR . 'install-agent-' . $safeTenantHost . '.exe');
        $fallbackExePath = base_path('scripts/agent/install-scheduled-task.exe');

        $exePath = is_file($fallbackExePath) ? $fallbackExePath : $tenantExePath;

        if (!is_file($exePath)) {
            abort(404, 'No se encontró el instalador .exe.');
        }

        $versionSuffix = '-' . substr((string) filemtime($exePath), -6);
        $filename = 'install-agent-' . $safeTenantHost . $versionSuffix . '.exe';

        return response()->download($exePath, $filename, [
            'Content-Type' => 'application/vnd.microsoft.portable-executable',
            'Cache-Control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function compileAgentInstallerExe(string $sourcePath, string $outputPath): void
    {
        if (!is_file($sourcePath)) {
            throw new \RuntimeException('No existe el script fuente del instalador.');
        }

        $escapedSource = str_replace("'", "''", $sourcePath);
        $escapedOutput = str_replace("'", "''", $outputPath);

        $process = new Process([
            'powershell',
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-Command',
            "Import-Module ps2exe -ErrorAction Stop; Invoke-PS2EXE -InputFile '$escapedSource' -OutputFile '$escapedOutput' -NoConsole -NoOutput -ErrorAction Stop",
        ], base_path(), null, null, 180);

        $process->run();

        if (!$process->isSuccessful() || !is_file($outputPath)) {
            throw new \RuntimeException('Falló la compilación de EXE con PS2EXE.');
        }
    }

    private function nowForScript(): string
    {
        return now()->format('Y-m-d H:i:s T');
    }

    public function storeSpace(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'space_branch_id' => ['required', 'exists:branches,id'],
            'space_name' => ['required', 'string', 'max:120'],
            'space_code' => ['nullable', 'string', 'max:80'],
            'space_type' => ['required', Rule::in(['site', 'idf', 'room', 'zone'])],
            'space_floor' => ['nullable', 'string', 'max:80'],
            'space_room' => ['nullable', 'string', 'max:80'],
            'space_description' => ['nullable', 'string'],
        ]);

        PhysicalSpace::query()->create([
            'branch_id' => $validated['space_branch_id'],
            'name' => $validated['space_name'],
            'code' => $validated['space_code'] ?? null,
            'space_type' => $validated['space_type'],
            'floor' => $validated['space_floor'] ?? null,
            'room' => $validated['space_room'] ?? null,
            'description' => $validated['space_description'] ?? null,
        ]);

        return $this->redirectToCrud('crud-spaces', 'Espacio físico creado correctamente.');
    }

    public function updateSpace(Request $request, PhysicalSpace $space): RedirectResponse
    {
        $validated = $request->validate([
            'space_branch_id' => ['required', 'exists:branches,id'],
            'space_name' => ['required', 'string', 'max:120'],
            'space_code' => ['nullable', 'string', 'max:80'],
            'space_type' => ['required', Rule::in(['site', 'idf', 'room', 'zone'])],
            'space_floor' => ['nullable', 'string', 'max:80'],
            'space_room' => ['nullable', 'string', 'max:80'],
            'space_description' => ['nullable', 'string'],
        ]);

        $space->update([
            'branch_id' => $validated['space_branch_id'],
            'name' => $validated['space_name'],
            'code' => $validated['space_code'] ?? null,
            'space_type' => $validated['space_type'],
            'floor' => $validated['space_floor'] ?? null,
            'room' => $validated['space_room'] ?? null,
            'description' => $validated['space_description'] ?? null,
        ]);

        return $this->redirectToCrud('crud-spaces', 'Espacio físico actualizado correctamente.');
    }

    public function destroySpace(PhysicalSpace $space): RedirectResponse
    {
        Node::query()
            ->where('physical_space_id', $space->id)
            ->update(['physical_space_id' => null]);

        $space->delete();

        return $this->redirectToCrud('crud-spaces', 'Espacio físico eliminado correctamente.');
    }

    public function storeFloorPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'floor_plan_branch_id' => ['required', 'exists:branches,id'],
            'floor_plan_space_id' => ['nullable', 'exists:physical_spaces,id'],
            'floor_plan_name' => ['required', 'string', 'max:140'],
            'floor_plan_mode' => ['nullable', Rule::in(['upload', 'blank'])],
            'floor_plan_file' => ['nullable', 'file', 'mimes:png,pdf,dwg,dxf,svg', 'max:40960'],
            'floor_plan_blank_width' => ['nullable', 'integer', 'min:400', 'max:6000'],
            'floor_plan_blank_height' => ['nullable', 'integer', 'min:300', 'max:4000'],
        ]);

        $spaceId = $validated['floor_plan_space_id'] ?? null;
        if ($spaceId !== null) {
            $spaceMatchesBranch = PhysicalSpace::query()
                ->where('id', $spaceId)
                ->where('branch_id', $validated['floor_plan_branch_id'])
                ->exists();

            if (!$spaceMatchesBranch) {
                throw ValidationException::withMessages([
                    'floor_plan_space_id' => 'El piso/espacio no pertenece a la sede seleccionada.',
                ]);
            }
        }

        $mode = (string) ($validated['floor_plan_mode'] ?? 'upload');
        $uploadedFile = $request->file('floor_plan_file');

        if ($mode === 'upload' && !$uploadedFile) {
            throw ValidationException::withMessages([
                'floor_plan_file' => 'Debes seleccionar un archivo para cargar el plano.',
            ]);
        }

        $storedPath = null;
        $extension = null;
        $mimeType = null;
        $originalName = null;
        $sizeBytes = null;

        if ($uploadedFile) {
            $safeName = preg_replace('/[^A-Za-z0-9\-_]+/', '-', strtolower(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))) ?: 'plano';
            $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
            $filename = sprintf('floor-plans/%s-%s.%s', $safeName, uniqid(), $extension ?: 'bin');
            $canonicalPublicRoot = base_path('storage/app/public');
            $floorPlansDir = $canonicalPublicRoot . '/floor-plans';
            $filePath = $floorPlansDir . '/' . basename($filename);

            if (!is_dir($floorPlansDir) && !mkdir($floorPlansDir, 0755, true) && !is_dir($floorPlansDir)) {
                throw ValidationException::withMessages([
                    'floor_plan_file' => 'No se pudo crear la carpeta de planos.',
                ]);
            }

            $uploadedContent = file_get_contents($uploadedFile->getRealPath());
            if ($uploadedContent === false) {
                throw ValidationException::withMessages([
                    'floor_plan_file' => 'No se pudo leer el archivo subido.',
                ]);
            }

            $byteCount = file_put_contents($filePath, $uploadedContent);
            if ($byteCount === false || !file_exists($filePath)) {
                throw ValidationException::withMessages([
                    'floor_plan_file' => 'Error al guardar el archivo subido.',
                ]);
            }

            @chmod($filePath, 0644);

            $storedPath = $filename;
            $mimeType = $uploadedFile->getClientMimeType();
            $originalName = $uploadedFile->getClientOriginalName();
            $sizeBytes = $uploadedFile->getSize();
        } else {
            $blankWidth = (int) ($validated['floor_plan_blank_width'] ?? 1400);
            $blankHeight = (int) ($validated['floor_plan_blank_height'] ?? 900);
            $safeName = preg_replace('/[^A-Za-z0-9\-_]+/', '-', strtolower($validated['floor_plan_name'])) ?: 'plano';
            $filename = sprintf('floor-plans/%s-%s.svg', $safeName, uniqid());

            $svg = $this->buildBlankFloorPlanSvg($blankWidth, $blankHeight);
            
            try {
                // Use canonical root to bypass tenant-prefixed storage path rewriting
                $storagePublicPath = base_path('storage/app/public');
                $floorPlansDir = $storagePublicPath . '/floor-plans';
                $filePath = $floorPlansDir . '/' . basename($filename);
                
                \Log::info("Floor plan generation:", [
                    'filename' => $filename,
                    'svg_length' => strlen($svg),
                    'storage_public_path' => $storagePublicPath,
                    'target_path' => $filePath,
                ]);
                
                // Create directory if it doesn't exist
                if (!is_dir($floorPlansDir)) {
                    if (!mkdir($floorPlansDir, 0755, true) && !is_dir($floorPlansDir)) {
                        throw new \Exception("No se pudo crear la carpeta destino: {$floorPlansDir}");
                    }
                    \Log::info("Directory created: {$floorPlansDir}");
                } else {
                    \Log::info("Directory already exists: {$floorPlansDir}");
                }
                
                // Write file directly to filesystem
                $byteCount = file_put_contents($filePath, $svg);
                \Log::info("File write result: {$byteCount} bytes");
                
                if ($byteCount === false) {
                    throw new \Exception("file_put_contents() failed");
                }
                
                // Verify file exists
                if (!file_exists($filePath)) {
                    throw new \Exception("File verification failed - file not found at {$filePath}");
                }
                
                // Set proper permissions
                @chmod($filePath, 0644);
                
                \Log::info("Floor plan saved successfully: {$filename}");
            } catch (\Exception $e) {
                \Log::error("Floor plan save error: {$e->getMessage()} | " . $e->getTraceAsString());
                throw ValidationException::withMessages([
                    'floor_plan_file' => "Error al guardar el plano: {$e->getMessage()}",
                ]);
            }

            $storedPath = $filename;
            $extension = 'svg';
            $mimeType = 'image/svg+xml';
            $originalName = 'blank-' . $safeName . '.svg';
            $sizeBytes = strlen($svg);
        }

        FloorPlan::query()->create([
            'branch_id' => $validated['floor_plan_branch_id'],
            'physical_space_id' => $spaceId,
            'name' => $validated['floor_plan_name'],
            'file_path' => $storedPath,
            'file_type' => $extension,
            'mime_type' => $mimeType,
            'overlay_points' => [
                'points' => [],
                'walls' => [],
            ],
            'meta' => [
                'original_name' => $originalName,
                'size_bytes' => $sizeBytes,
                'source_mode' => $uploadedFile ? 'upload' : 'blank',
                'scale' => $uploadedFile ? null : $this->defaultBlankPlanScale($blankWidth, $blankHeight),
            ],
        ]);

        return $this->redirectToCrud('crud-floor-plans', 'Plano cargado correctamente.');
    }

    public function floorPlanView(Request $request, FloorPlan $floorPlan): View
    {
        $this->assertBranchAccess($floorPlan->branch_id, $this->currentUserBranchScopeIds($request));

        $request->query->set('panel', '1');
        $data = $this->prepareDashboardData($request);
        $data['editorOnlyMode'] = true;
        $data['editorOnlyFloorPlanId'] = (int) $floorPlan->id;
        $data['editorOnlyBackUrl'] = url('/admin/panel-admin-1#section-floor-plans');

        return view('tenant.admin.dashboard', $data);
    }

    public function floorPlanData(Request $request, FloorPlan $floorPlan): JsonResponse
    {
        $this->assertBranchAccess($floorPlan->branch_id, $this->currentUserBranchScopeIds($request));
        $floorPlan->load(['branch:id,name', 'physicalSpace:id,name,floor,room']);

        $overlay = is_array($floorPlan->overlay_points) ? $floorPlan->overlay_points : [];
        $pointsRaw = isset($overlay['points']) && is_array($overlay['points'])
            ? $overlay['points']
            : (array_is_list($overlay) ? $overlay : []);
        $wallsRaw = isset($overlay['walls']) && is_array($overlay['walls'])
            ? $overlay['walls']
            : [];

        $points = collect($pointsRaw)
            ->filter(fn ($point) => is_array($point))
            ->map(function (array $point) {
                return [
                    'node_id' => isset($point['node_id']) ? (int) $point['node_id'] : null,
                    'x_percent' => isset($point['x_percent']) ? (float) $point['x_percent'] : null,
                    'y_percent' => isset($point['y_percent']) ? (float) $point['y_percent'] : null,
                    'layer' => (string) ($point['layer'] ?? 'access-point'),
                    'item_type' => (string) ($point['item_type'] ?? 'access-point'),
                    'label' => isset($point['label']) ? (string) $point['label'] : null,
                    'symbol_key' => isset($point['symbol_key']) && $point['symbol_key'] !== '' ? (string) $point['symbol_key'] : null,
                    'symbol_size_m' => isset($point['symbol_size_m']) ? (float) $point['symbol_size_m'] : null,
                    'rotation_deg' => isset($point['rotation_deg']) ? (float) $point['rotation_deg'] : 0.0,
                    'signal_dbm' => isset($point['signal_dbm']) ? (float) $point['signal_dbm'] : null,
                    'radius_percent' => isset($point['radius_percent']) ? (float) $point['radius_percent'] : 12.0,
                    'radius_meters' => isset($point['radius_meters']) ? (float) $point['radius_meters'] : null,
                    'radiation_pattern' => (string) ($point['radiation_pattern'] ?? 'omni-donut'),
                    'mount_orientation' => (string) ($point['mount_orientation'] ?? 'ceiling'),
                    'mount_height_m' => isset($point['mount_height_m']) ? (float) $point['mount_height_m'] : null,
                    'azimuth_deg' => isset($point['azimuth_deg']) ? (float) $point['azimuth_deg'] : 0.0,
                    'tilt_deg' => isset($point['tilt_deg']) ? (float) $point['tilt_deg'] : 0.0,
                ];
            })
            ->values();

        $relativeFilePath = ltrim((string) $floorPlan->file_path, '/');
        $canonicalFilePath = base_path('storage/app/public/' . $relativeFilePath);
        $tenantPrefixedFilePath = storage_path('app/public/' . $relativeFilePath);

        $meta = is_array($floorPlan->meta) ? $floorPlan->meta : [];
        $scale = $this->normalizeFloorPlanScale((array) ($meta['scale'] ?? []))
            ?? $this->inferBlankPlanScale($meta, $canonicalFilePath, $tenantPrefixedFilePath);
        $structureDefaults = $this->normalizeStructureDefaults((array) ($meta['structure_defaults'] ?? []));

        $walls = collect($wallsRaw)
            ->filter(fn ($wall) => is_array($wall))
            ->map(function (array $wall) {
                return [
                    'x1_percent' => isset($wall['x1_percent']) ? (float) $wall['x1_percent'] : null,
                    'y1_percent' => isset($wall['y1_percent']) ? (float) $wall['y1_percent'] : null,
                    'x2_percent' => isset($wall['x2_percent']) ? (float) $wall['x2_percent'] : null,
                    'y2_percent' => isset($wall['y2_percent']) ? (float) $wall['y2_percent'] : null,
                    'material' => (string) ($wall['material'] ?? 'drywall'),
                    'loss_db' => isset($wall['loss_db']) ? (float) $wall['loss_db'] : null,
                    'opening_width_m' => isset($wall['opening_width_m']) ? (float) $wall['opening_width_m'] : null,
                    'room_id' => isset($wall['room_id']) && $wall['room_id'] !== '' ? (string) $wall['room_id'] : null,
                ];
            })
            ->values();

        $rooms = collect((array) ($meta['rooms'] ?? []))
            ->filter(fn ($room) => is_array($room) && !empty($room['id']))
            ->map(fn (array $room) => [
                'id' => (string) $room['id'],
                'name' => isset($room['name']) && trim((string) $room['name']) !== '' ? trim((string) $room['name']) : null,
            ])
            ->unique('id')
            ->values();

        $apNodeTypeIds = NodeType::query()
            ->where(function ($query) {
                $query->where('slug', 'like', '%access%')
                    ->orWhere('slug', 'like', '%ap%')
                    ->orWhere('name', 'like', '%access%')
                    ->orWhere('name', 'like', '%wifi%');
            })
            ->pluck('id');

        $accessPointNodes = Node::query()
            ->with(['branch:id,name', 'physicalSpace:id,name'])
            ->where('branch_id', $floorPlan->branch_id)
            ->when($apNodeTypeIds->isNotEmpty(), fn ($query) => $query->whereIn('node_type_id', $apNodeTypeIds))
            ->orderBy('name')
            ->get(['id', 'branch_id', 'physical_space_id', 'name', 'code', 'details']);

        if (!file_exists($canonicalFilePath) && file_exists($tenantPrefixedFilePath)) {
            $canonicalDir = dirname($canonicalFilePath);
            if (!is_dir($canonicalDir)) {
                @mkdir($canonicalDir, 0755, true);
            }
            @copy($tenantPrefixedFilePath, $canonicalFilePath);
            @chmod($canonicalFilePath, 0644);
        }

        return response()->json([
            'ok' => true,
            'plan' => [
                'id' => $floorPlan->id,
                'name' => $floorPlan->name,
                'file_type' => $floorPlan->file_type,
                'file_url' => '/storage/' . $relativeFilePath,
                'branch_name' => optional($floorPlan->branch)->name,
                'space_name' => optional($floorPlan->physicalSpace)->name,
                'floor' => optional($floorPlan->physicalSpace)->floor,
                'room' => optional($floorPlan->physicalSpace)->room,
                'scale' => $scale,
                'structure_defaults' => $structureDefaults,
                'points' => $points,
                'walls' => $walls,
                'rooms' => $rooms,
            ],
            'ap_nodes' => $accessPointNodes->map(fn (Node $node) => [
                'id' => $node->id,
                'name' => $node->name,
                'code' => $node->code,
                'branch' => optional($node->branch)->name,
                'space' => optional($node->physicalSpace)->name,
                'rf_defaults' => $this->extractNodeRfDefaults(is_array($node->details) ? $node->details : null),
            ])->values(),
        ]);
    }

    public function saveFloorPlanPoints(Request $request, FloorPlan $floorPlan): JsonResponse
    {
        $validated = $request->validate([
            'points' => ['nullable', 'array'],
            'points.*.node_id' => ['nullable', 'exists:nodes,id'],
            'points.*.x_percent' => ['required', 'numeric', 'between:0,100'],
            'points.*.y_percent' => ['required', 'numeric', 'between:0,100'],
            'points.*.layer' => ['nullable', 'string', 'max:50'],
            'points.*.item_type' => ['nullable', 'string', 'max:50'],
            'points.*.label' => ['nullable', 'string', 'max:120'],
            'points.*.symbol_key' => ['nullable', 'string', 'max:50'],
            'points.*.symbol_size_m' => ['nullable', 'numeric', 'between:0.2,20'],
            'points.*.rotation_deg' => ['nullable', 'numeric', 'between:-180,180'],
            'points.*.signal_dbm' => ['nullable', 'numeric', 'between:-120,0'],
            'points.*.radius_percent' => ['nullable', 'numeric', 'between:2,40'],
            'points.*.radius_meters' => ['nullable', 'numeric', 'between:0.5,500'],
            'points.*.radiation_pattern' => ['nullable', Rule::in(['omni-donut', 'sphere', 'sector-120', 'directional-60'])],
            'points.*.mount_orientation' => ['nullable', Rule::in(['ceiling', 'wall-horizontal', 'wall-vertical', 'desktop', 'custom'])],
            'points.*.mount_height_m' => ['nullable', 'numeric', 'between:0.1,50'],
            'points.*.azimuth_deg' => ['nullable', 'numeric', 'between:0,359.99'],
            'points.*.tilt_deg' => ['nullable', 'numeric', 'between:-90,90'],
            'walls' => ['nullable', 'array'],
            'walls.*.x1_percent' => ['required', 'numeric', 'between:0,100'],
            'walls.*.y1_percent' => ['required', 'numeric', 'between:0,100'],
            'walls.*.x2_percent' => ['required', 'numeric', 'between:0,100'],
            'walls.*.y2_percent' => ['required', 'numeric', 'between:0,100'],
            'walls.*.material' => ['required', Rule::in(['drywall', 'brick', 'concrete', 'glass', 'wood', 'metal', 'door', 'window'])],
            'walls.*.loss_db' => ['nullable', 'numeric', 'between:0,40'],
            'walls.*.opening_width_m' => ['nullable', 'numeric', 'between:0.2,20'],
            'walls.*.room_id' => ['nullable', 'string', 'max:80'],
            'rooms' => ['nullable', 'array'],
            'rooms.*.id' => ['required', 'string', 'max:80'],
            'rooms.*.name' => ['nullable', 'string', 'max:120'],
            'scale' => ['nullable', 'array'],
            'scale.width_m' => ['nullable', 'numeric', 'between:0.1,50000'],
            'scale.height_m' => ['nullable', 'numeric', 'between:0.1,50000'],
            'scale.unit' => ['nullable', Rule::in(['m'])],
            'structure_defaults' => ['nullable', 'array'],
            'structure_defaults.wall_height_m' => ['nullable', 'numeric', 'between:0.5,20'],
            'structure_defaults.door_height_m' => ['nullable', 'numeric', 'between:0.5,20'],
            'structure_defaults.door_width_m' => ['nullable', 'numeric', 'between:0.4,6'],
            'structure_defaults.window_base_m' => ['nullable', 'numeric', 'between:0,10'],
            'structure_defaults.window_height_m' => ['nullable', 'numeric', 'between:0.2,10'],
            'structure_defaults.window_width_m' => ['nullable', 'numeric', 'between:0.3,8'],
            'structure_defaults.orthogonal_lock' => ['nullable', 'boolean'],
            'structure_defaults.ap_mount_height_m' => ['nullable', 'numeric', 'between:0.1,20'],
            'structure_defaults.preferred_wall_material' => ['nullable', Rule::in(['drywall', 'brick', 'concrete', 'glass', 'wood', 'metal'])],
        ]);

        $sanitizedPoints = collect($validated['points'] ?? [])
            ->map(function (array $point) use ($floorPlan) {
                $nodeId = isset($point['node_id']) && $point['node_id'] !== null
                    ? (int) $point['node_id']
                    : null;

                if ($nodeId !== null) {
                    $belongsToBranch = Node::query()
                        ->where('id', $nodeId)
                        ->where('branch_id', $floorPlan->branch_id)
                        ->exists();

                    if (!$belongsToBranch) {
                        throw ValidationException::withMessages([
                            'points' => 'Uno de los AP seleccionados no pertenece a la sede del plano.',
                        ]);
                    }
                }

                return [
                    'node_id' => $nodeId,
                    'x_percent' => round((float) $point['x_percent'], 4),
                    'y_percent' => round((float) $point['y_percent'], 4),
                    'layer' => (string) ($point['layer'] ?? 'access-point'),
                    'item_type' => (string) ($point['item_type'] ?? 'access-point'),
                    'label' => isset($point['label']) ? trim((string) $point['label']) : null,
                    'symbol_key' => isset($point['symbol_key']) && trim((string) $point['symbol_key']) !== '' ? trim((string) $point['symbol_key']) : null,
                    'symbol_size_m' => isset($point['symbol_size_m']) && $point['symbol_size_m'] !== null ? round((float) $point['symbol_size_m'], 2) : null,
                    'rotation_deg' => isset($point['rotation_deg']) ? round((float) $point['rotation_deg'], 2) : 0.0,
                    'signal_dbm' => isset($point['signal_dbm']) ? round((float) $point['signal_dbm'], 2) : null,
                    'radius_percent' => isset($point['radius_percent']) ? round((float) $point['radius_percent'], 2) : 12.0,
                    'radius_meters' => isset($point['radius_meters']) && $point['radius_meters'] !== null ? round((float) $point['radius_meters'], 2) : null,
                    'radiation_pattern' => (string) ($point['radiation_pattern'] ?? 'omni-donut'),
                    'mount_orientation' => (string) ($point['mount_orientation'] ?? 'ceiling'),
                    'mount_height_m' => isset($point['mount_height_m']) && $point['mount_height_m'] !== null ? round((float) $point['mount_height_m'], 2) : null,
                    'azimuth_deg' => isset($point['azimuth_deg']) ? round((float) $point['azimuth_deg'], 2) : 0.0,
                    'tilt_deg' => isset($point['tilt_deg']) ? round((float) $point['tilt_deg'], 2) : 0.0,
                ];
            })
            ->values()
            ->all();

        $materialLossMap = [
            'drywall' => 3.0,
            'brick' => 8.0,
            'concrete' => 12.0,
            'glass' => 2.0,
            'wood' => 5.0,
            'metal' => 18.0,
            'door' => 1.5,
            'window' => 1.0,
        ];

        $sanitizedWalls = collect($validated['walls'] ?? [])
            ->map(function (array $wall) use ($materialLossMap) {
                $material = (string) ($wall['material'] ?? 'drywall');
                $loss = isset($wall['loss_db'])
                    ? round((float) $wall['loss_db'], 2)
                    : ($materialLossMap[$material] ?? 3.0);

                return [
                    'x1_percent' => round((float) $wall['x1_percent'], 4),
                    'y1_percent' => round((float) $wall['y1_percent'], 4),
                    'x2_percent' => round((float) $wall['x2_percent'], 4),
                    'y2_percent' => round((float) $wall['y2_percent'], 4),
                    'material' => $material,
                    'loss_db' => $loss,
                    'opening_width_m' => isset($wall['opening_width_m']) && $wall['opening_width_m'] !== null ? round((float) $wall['opening_width_m'], 2) : null,
                    'room_id' => isset($wall['room_id']) && trim((string) $wall['room_id']) !== '' ? trim((string) $wall['room_id']) : null,
                ];
            })
            ->values()
            ->all();

        $sanitizedRooms = collect($validated['rooms'] ?? [])
            ->map(fn (array $room) => [
                'id' => trim((string) $room['id']),
                'name' => isset($room['name']) && trim((string) $room['name']) !== '' ? trim((string) $room['name']) : null,
            ])
            ->filter(fn (array $room) => $room['id'] !== '')
            ->unique('id')
            ->values()
            ->all();

        $scale = $this->normalizeFloorPlanScale((array) ($validated['scale'] ?? []));
        $structureDefaults = $this->normalizeStructureDefaults((array) ($validated['structure_defaults'] ?? []));
        $meta = is_array($floorPlan->meta) ? $floorPlan->meta : [];
        if ($scale !== null) {
            $meta['scale'] = $scale;
        } else {
            unset($meta['scale']);
        }
        if ($structureDefaults !== null) {
            $meta['structure_defaults'] = $structureDefaults;
        } else {
            unset($meta['structure_defaults']);
        }
        if ($sanitizedRooms !== []) {
            $meta['rooms'] = $sanitizedRooms;
        } else {
            unset($meta['rooms']);
        }

        $floorPlan->update([
            'overlay_points' => [
                'points' => $sanitizedPoints,
                'walls' => $sanitizedWalls,
            ],
            'meta' => $meta,
        ]);

        return response()->json([
            'ok' => true,
            'points_count' => count($sanitizedPoints),
            'walls_count' => count($sanitizedWalls),
        ]);
    }

    private function buildBlankFloorPlanSvg(int $width, int $height): string
    {
        $step = self::BLANK_PLAN_GRID_PX;
        $innerWidth = max(1, $width - 2);
        $innerHeight = max(1, $height - 2);
        $gridLines = [];

        for ($x = 0; $x <= $width; $x += $step) {
            $gridLines[] = sprintf('<line x1="%d" y1="0" x2="%d" y2="%d" stroke="#e2e8f0" stroke-width="1" />', $x, $x, $height);
        }

        for ($y = 0; $y <= $height; $y += $step) {
            $gridLines[] = sprintf('<line x1="0" y1="%d" x2="%d" y2="%d" stroke="#e2e8f0" stroke-width="1" />', $y, $width, $y);
        }

        $grid = implode("\n", $gridLines);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <rect x="0" y="0" width="{$width}" height="{$height}" fill="#ffffff" />
  {$grid}
    <rect x="1" y="1" width="{$innerWidth}" height="{$innerHeight}" fill="none" stroke="#94a3b8" stroke-width="2" />
  <text x="24" y="36" fill="#334155" font-size="20" font-family="Arial, sans-serif">Plano en blanco · Diseñado en ITCity</text>
  <text x="24" y="58" fill="#64748b" font-size="12" font-family="Arial, sans-serif">Cada cuadro de la cuadrícula equivale a 1 metro</text>
</svg>
SVG;
    }

    private function defaultBlankPlanScale(int $widthPx, int $heightPx): array
    {
        $gridPx = self::BLANK_PLAN_GRID_PX;

        return [
            'width_m' => round($widthPx / $gridPx, 2),
            'height_m' => round($heightPx / $gridPx, 2),
            'unit' => 'm',
        ];
    }

    private function inferBlankPlanScale(array $meta, string $canonicalFilePath, string $tenantPrefixedFilePath): ?array
    {
        if (($meta['source_mode'] ?? null) !== 'blank') {
            return null;
        }

        $path = file_exists($canonicalFilePath)
            ? $canonicalFilePath
            : (file_exists($tenantPrefixedFilePath) ? $tenantPrefixedFilePath : null);

        if ($path === null) {
            return null;
        }

        $svg = @file_get_contents($path);
        if ($svg === false || $svg === '') {
            return null;
        }

        $width = null;
        $height = null;

        if (preg_match('/<svg[^>]*\swidth="([0-9.]+)"/i', $svg, $widthMatch)) {
            $width = (int) round((float) $widthMatch[1]);
        }
        if (preg_match('/<svg[^>]*\sheight="([0-9.]+)"/i', $svg, $heightMatch)) {
            $height = (int) round((float) $heightMatch[1]);
        }

        if ((!$width || !$height) && preg_match('/viewBox="0\s+0\s+([0-9.]+)\s+([0-9.]+)"/i', $svg, $viewBoxMatch)) {
            $width = $width ?: (int) round((float) $viewBoxMatch[1]);
            $height = $height ?: (int) round((float) $viewBoxMatch[2]);
        }

        if (!$width || !$height) {
            return null;
        }

        $gridPx = $this->inferBlankPlanGridStep($svg) ?? self::BLANK_PLAN_GRID_PX;

        return [
            'width_m' => round($width / $gridPx, 2),
            'height_m' => round($height / $gridPx, 2),
            'unit' => 'm',
        ];
    }

    private function inferBlankPlanGridStep(string $svg): ?int
    {
        preg_match_all('/<line\s+[^>]*x1="([0-9.]+)"\s+y1="0"\s+x2="\1"\s+y2="[0-9.]+"/i', $svg, $matches);
        $positions = collect($matches[1] ?? [])
            ->map(fn ($value) => (int) round((float) $value))
            ->filter(fn ($value) => $value >= 0)
            ->unique()
            ->sort()
            ->values();

        if ($positions->count() < 3) {
            return null;
        }

        $diffs = [];
        for ($index = 1; $index < $positions->count(); $index++) {
            $diff = $positions[$index] - $positions[$index - 1];
            if ($diff > 0) {
                $diffs[] = $diff;
            }
        }

        if (!$diffs) {
            return null;
        }

        sort($diffs);

        return $diffs[0] > 0 ? $diffs[0] : null;
    }

    public function destroyFloorPlan(FloorPlan $floorPlan): RedirectResponse
    {
        $canonicalPath = base_path('storage/app/public/' . ltrim($floorPlan->file_path, '/'));
        if (file_exists($canonicalPath)) {
            @unlink($canonicalPath);
        } else {
            Storage::disk('public')->delete($floorPlan->file_path);
        }
        $floorPlan->delete();

        return $this->redirectToCrud('crud-floor-plans', 'Plano eliminado correctamente.');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:120'],
            'branch_address' => ['nullable', 'string', 'max:255'],
            'branch_city' => ['nullable', 'string', 'max:120'],
            'branch_state' => ['nullable', 'string', 'max:120'],
            'branch_country' => ['nullable', 'string', 'max:120'],
            'branch_description' => ['nullable', 'string'],
            'branch_sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Branch::query()->create([
            'name' => $validated['branch_name'],
            'address' => $validated['branch_address'] ?? null,
            'city' => $validated['branch_city'] ?? null,
            'state' => $validated['branch_state'] ?? null,
            'country' => $validated['branch_country'] ?? null,
            'description' => $validated['branch_description'] ?? null,
            'sort_order' => $validated['branch_sort_order'] ?? 0,
        ]);

        return $this->redirectToCrud('crud-branches', 'Sede creada correctamente.');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'branch_name' => ['required', 'string', 'max:120'],
            'branch_address' => ['nullable', 'string', 'max:255'],
            'branch_city' => ['nullable', 'string', 'max:120'],
            'branch_state' => ['nullable', 'string', 'max:120'],
            'branch_country' => ['nullable', 'string', 'max:120'],
            'branch_description' => ['nullable', 'string'],
            'branch_sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $branch->update([
            'name' => $validated['branch_name'],
            'address' => $validated['branch_address'] ?? null,
            'city' => $validated['branch_city'] ?? null,
            'state' => $validated['branch_state'] ?? null,
            'country' => $validated['branch_country'] ?? null,
            'description' => $validated['branch_description'] ?? null,
            'sort_order' => $validated['branch_sort_order'] ?? 0,
        ]);

        return $this->redirectToCrud('crud-branches', 'Sede actualizada correctamente.');
    }

    public function destroyBranch(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return $this->redirectToCrud('crud-branches', 'Sede eliminada correctamente.');
    }

    public function storeNodeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'node_type_name' => ['required', 'string', 'max:120'],
            'node_type_slug' => ['required', 'string', 'max:120', 'alpha_dash', 'unique:node_types,slug'],
            'node_type_icon' => ['nullable', 'string', 'max:80'],
            'node_type_meta_json' => ['nullable', 'string'],
        ]);

        NodeType::query()->create([
            'name' => $validated['node_type_name'],
            'slug' => $validated['node_type_slug'],
            'icon' => $validated['node_type_icon'] ?? null,
            'meta' => $this->decodeJson($validated['node_type_meta_json'] ?? null, 'node_type_meta_json'),
        ]);

        return $this->redirectToCrud('crud-node-types', 'Tipo de nodo creado correctamente.');
    }

    public function updateNodeType(Request $request, NodeType $nodeType): RedirectResponse
    {
        $validated = $request->validate([
            'node_type_name' => ['required', 'string', 'max:120'],
            'node_type_slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('node_types', 'slug')->ignore($nodeType->id)],
            'node_type_icon' => ['nullable', 'string', 'max:80'],
            'node_type_meta_json' => ['nullable', 'string'],
        ]);

        $nodeType->update([
            'name' => $validated['node_type_name'],
            'slug' => $validated['node_type_slug'],
            'icon' => $validated['node_type_icon'] ?? null,
            'meta' => $this->decodeJson($validated['node_type_meta_json'] ?? null, 'node_type_meta_json'),
        ]);

        return $this->redirectToCrud('crud-node-types', 'Tipo de nodo actualizado correctamente.');
    }

    public function destroyNodeType(NodeType $nodeType): RedirectResponse
    {
        if ($nodeType->nodes()->exists()) {
            return $this->redirectToCrud('crud-node-types', 'No se puede eliminar un tipo de nodo que ya está en uso.');
        }

        $nodeType->delete();

        return $this->redirectToCrud('crud-node-types', 'Tipo de nodo eliminado correctamente.');
    }

    public function storeNode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'node_branch_id' => ['required', 'exists:branches,id'],
            'node_physical_space_id' => ['nullable', 'exists:physical_spaces,id'],
            'node_type_id' => ['required', 'exists:node_types,id'],
            'node_name' => ['required', 'string', 'max:120'],
            'node_code' => ['nullable', 'string', 'max:120'],
            'node_floor' => ['nullable', 'string', 'max:80'],
            'node_room' => ['nullable', 'string', 'max:80'],
            'node_ip_address' => [
                'nullable',
                'ip',
                Rule::unique('nodes', 'ip_address')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->input('node_branch_id'));
                }),
            ],
            'node_mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'node_cable_type' => ['nullable', 'string', 'max:120'],
            'node_status' => ['required', 'string', 'max:60'],
            'node_details_json' => ['nullable', 'string'],
        ]);

        $spaceId = $validated['node_physical_space_id'] ?? null;
        if ($spaceId !== null) {
            $spaceExists = PhysicalSpace::query()
                ->where('id', $spaceId)
                ->where('branch_id', $validated['node_branch_id'])
                ->exists();
            if (!$spaceExists) {
                throw ValidationException::withMessages([
                    'node_physical_space_id' => 'El espacio seleccionado no pertenece a la sede elegida.',
                ]);
            }
        }

        Node::query()->create([
            'branch_id' => $validated['node_branch_id'],
            'physical_space_id' => $spaceId,
            'node_type_id' => $validated['node_type_id'],
            'name' => $validated['node_name'],
            'code' => $validated['node_code'] ?? null,
            'floor' => $validated['node_floor'] ?? null,
            'room' => $validated['node_room'] ?? null,
            'ip_address' => $validated['node_ip_address'] ?? null,
            'mac_address' => $this->normalizeMacAddress($validated['node_mac_address'] ?? null),
            'cable_type' => $validated['node_cable_type'] ?? null,
            'status' => $validated['node_status'],
            'is_monitored' => $request->boolean('node_is_monitored'),
            'details' => $this->decodeJson($validated['node_details_json'] ?? null, 'node_details_json'),
        ]);

        return $this->redirectToCrud('crud-nodes', 'Nodo creado correctamente.');
    }

    public function updateNode(Request $request, Node $node): RedirectResponse
    {
        $validated = $request->validate([
            'node_branch_id' => ['required', 'exists:branches,id'],
            'node_physical_space_id' => ['nullable', 'exists:physical_spaces,id'],
            'node_type_id' => ['required', 'exists:node_types,id'],
            'node_name' => ['required', 'string', 'max:120'],
            'node_code' => ['nullable', 'string', 'max:120'],
            'node_floor' => ['nullable', 'string', 'max:80'],
            'node_room' => ['nullable', 'string', 'max:80'],
            'node_ip_address' => [
                'nullable',
                'ip',
                Rule::unique('nodes', 'ip_address')
                    ->ignore($node->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('branch_id', $request->input('node_branch_id'));
                    }),
            ],
            'node_mac_address' => ['nullable', 'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i'],
            'node_cable_type' => ['nullable', 'string', 'max:120'],
            'node_status' => ['required', 'string', 'max:60'],
            'node_details_json' => ['nullable', 'string'],
        ]);

        $spaceId = $validated['node_physical_space_id'] ?? null;
        if ($spaceId !== null) {
            $spaceExists = PhysicalSpace::query()
                ->where('id', $spaceId)
                ->where('branch_id', $validated['node_branch_id'])
                ->exists();
            if (!$spaceExists) {
                throw ValidationException::withMessages([
                    'node_physical_space_id' => 'El espacio seleccionado no pertenece a la sede elegida.',
                ]);
            }
        }

        $node->update([
            'branch_id' => $validated['node_branch_id'],
            'physical_space_id' => $spaceId,
            'node_type_id' => $validated['node_type_id'],
            'name' => $validated['node_name'],
            'code' => $validated['node_code'] ?? null,
            'floor' => $validated['node_floor'] ?? null,
            'room' => $validated['node_room'] ?? null,
            'ip_address' => $validated['node_ip_address'] ?? null,
            'mac_address' => $this->normalizeMacAddress($validated['node_mac_address'] ?? null),
            'cable_type' => $validated['node_cable_type'] ?? null,
            'status' => $validated['node_status'],
            'is_monitored' => $request->boolean('node_is_monitored'),
            'details' => $this->decodeJson($validated['node_details_json'] ?? null, 'node_details_json'),
        ]);

        return $this->redirectToCrud('crud-nodes', 'Nodo actualizado correctamente.');
    }

    public function destroyNode(Node $node): RedirectResponse
    {
        $node->delete();

        return $this->redirectToCrud('crud-nodes', 'Nodo eliminado correctamente.');
    }

    public function storeEquipmentBrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:120', 'unique:equipment_brands,name'],
        ]);

        EquipmentBrand::query()->create(['name' => $validated['brand_name']]);

        return $this->redirectToCrud('crud-equipment-brands', 'Marca creada correctamente.');
    }

    public function updateEquipmentBrand(Request $request, EquipmentBrand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:120', Rule::unique('equipment_brands', 'name')->ignore($brand->id)],
        ]);

        $brand->update(['name' => $validated['brand_name']]);

        return $this->redirectToCrud('crud-equipment-brands', 'Marca actualizada correctamente.');
    }

    public function destroyEquipmentBrand(EquipmentBrand $brand): RedirectResponse
    {
        $brand->delete();

        return $this->redirectToCrud('crud-equipment-brands', 'Marca eliminada correctamente.');
    }

    public function storeEquipmentModel(Request $request): RedirectResponse
    {
        $validated = $this->validateEquipmentModel($request);

        EquipmentModel::query()->create($validated);

        return $this->redirectToCrud('crud-equipment-models', 'Modelo creado correctamente.');
    }

    public function updateEquipmentModel(Request $request, EquipmentModel $equipmentModel): RedirectResponse
    {
        $validated = $this->validateEquipmentModel($request, $equipmentModel);

        $equipmentModel->update($validated);

        return $this->redirectToCrud('crud-equipment-models', 'Modelo actualizado correctamente.');
    }

    public function destroyEquipmentModel(EquipmentModel $equipmentModel): RedirectResponse
    {
        $equipmentModel->delete();

        return $this->redirectToCrud('crud-equipment-models', 'Modelo eliminado correctamente.');
    }

    public function storeComputerAsset(Request $request): RedirectResponse
    {
        $validated = $this->validateComputerAsset($request);
        $nodeId = $this->resolveComputerAssetNodeId($validated);
        $baseDetails = $this->decodeJson($validated['asset_details_json'] ?? null, 'asset_details_json');
        $details = $this->applyAssetOperationalMetadata(
            is_array($baseDetails) ? $baseDetails : [],
            $validated,
            null
        );

        ComputerAsset::query()->create([
            'branch_id' => $validated['asset_branch_id'],
            'node_id' => $nodeId,
            'equipment_model_id' => $validated['asset_equipment_model_id'] ?? null,
            'equipment_type' => $validated['asset_equipment_type'],
            'asset_tag' => $validated['asset_tag'] ?? null,
            'hostname' => $validated['asset_hostname'] ?? null,
            'assigned_user' => $validated['asset_assigned_user'] ?? null,
            'brand' => $validated['asset_brand'] ?? null,
            'model' => $validated['asset_model'] ?? null,
            'serial_number' => $validated['asset_serial_number'] ?? null,
            'cpu' => $validated['asset_cpu'] ?? null,
            'ram_gb' => $validated['asset_ram_gb'] ?? null,
            'storage_type' => $validated['asset_storage_type'] ?? null,
            'storage_gb' => $validated['asset_storage_gb'] ?? null,
            'operating_system' => $validated['asset_operating_system'] ?? null,
            'office_version' => $validated['asset_office_version'] ?? null,
            'purchase_date' => $validated['asset_purchase_date'] ?? null,
            'warranty_expires_at' => $validated['asset_warranty_expires_at'] ?? null,
            'status' => $validated['asset_status'],
            'notes' => $validated['asset_notes'] ?? null,
            'details' => $details,
        ]);

        return $this->redirectToCrud('crud-assets', 'Activo TI creado correctamente.');
    }

    public function updateComputerAsset(Request $request, ComputerAsset $computerAsset): RedirectResponse
    {
        $validated = $this->validateComputerAsset($request);
        $nodeId = $this->resolveComputerAssetNodeId($validated);
        $decodedDetails = $this->decodeJson($validated['asset_details_json'] ?? null, 'asset_details_json');
        $baseDetails = is_array($decodedDetails)
            ? $decodedDetails
            : (is_array($computerAsset->details) ? $computerAsset->details : []);
        $details = $this->applyAssetOperationalMetadata($baseDetails, $validated, $computerAsset);

        $computerAsset->update([
            'branch_id' => $validated['asset_branch_id'],
            'node_id' => $nodeId,
            'equipment_model_id' => $validated['asset_equipment_model_id'] ?? null,
            'equipment_type' => $validated['asset_equipment_type'],
            'asset_tag' => $validated['asset_tag'] ?? null,
            'hostname' => $validated['asset_hostname'] ?? null,
            'assigned_user' => $validated['asset_assigned_user'] ?? null,
            'brand' => $validated['asset_brand'] ?? null,
            'model' => $validated['asset_model'] ?? null,
            'serial_number' => $validated['asset_serial_number'] ?? null,
            'cpu' => $validated['asset_cpu'] ?? null,
            'ram_gb' => $validated['asset_ram_gb'] ?? null,
            'storage_type' => $validated['asset_storage_type'] ?? null,
            'storage_gb' => $validated['asset_storage_gb'] ?? null,
            'operating_system' => $validated['asset_operating_system'] ?? null,
            'office_version' => $validated['asset_office_version'] ?? null,
            'purchase_date' => $validated['asset_purchase_date'] ?? null,
            'warranty_expires_at' => $validated['asset_warranty_expires_at'] ?? null,
            'status' => $validated['asset_status'],
            'notes' => $validated['asset_notes'] ?? null,
            'details' => $details,
        ]);

        return $this->redirectToCrud('crud-assets', 'Activo TI actualizado correctamente.');
    }

    public function reassignComputerAsset(Request $request, ComputerAsset $computerAsset): RedirectResponse
    {
        $this->ensureComputerAssetAccessScope($request, $computerAsset);

        $validated = $request->validate([
            'asset_assigned_user' => ['required', 'string', 'max:120'],
            'asset_assignment_received_by' => ['nullable', 'string', 'max:120'],
            'asset_assignment_change_reason' => ['required', 'string', 'max:1000'],
            'asset_assignment_delivery_date' => ['nullable', 'date'],
            'asset_assignment_invoice_folio' => ['nullable', 'string', 'max:120'],
            'asset_assignment_supplier' => ['nullable', 'string', 'max:160'],
            'asset_assignment_received_signature_data_url' => ['nullable', 'string', 'max:400000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/'],
            'asset_interaction_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $details = is_array($computerAsset->details) ? $computerAsset->details : [];
        $newAssignedUser = trim((string) $validated['asset_assigned_user']);
        $oldAssignedUser = trim((string) ($computerAsset->assigned_user ?? ''));

        $interactionNote = trim((string) ($validated['asset_interaction_note'] ?? ''));
        if ($interactionNote === '') {
            $interactionNote = sprintf(
                'Cambio de responsable: %s -> %s',
                $oldAssignedUser !== '' ? $oldAssignedUser : 'Sin asignar',
                $newAssignedUser
            );
        }

        $metadataPayload = [
            'asset_status' => $computerAsset->status,
            'asset_assigned_user' => $newAssignedUser,
            'asset_responsiva_reference' => (string) data_get($details, 'responsiva.reference', ''),
            'asset_assignment_invoice_folio' => $validated['asset_assignment_invoice_folio'] ?? null,
            'asset_assignment_supplier' => $validated['asset_assignment_supplier'] ?? null,
            'asset_assignment_delivery_date' => $validated['asset_assignment_delivery_date'] ?? null,
            'asset_assignment_received_by' => $validated['asset_assignment_received_by'] ?? null,
            'asset_assignment_received_signature_data_url' => $validated['asset_assignment_received_signature_data_url'] ?? null,
            'asset_assignment_change_reason' => $validated['asset_assignment_change_reason'],
            'asset_interaction_note' => $interactionNote,
            'asset_branch_id' => $computerAsset->branch_id,
            'asset_equipment_type' => $computerAsset->equipment_type,
            'asset_brand' => $computerAsset->brand,
            'asset_model' => $computerAsset->model,
            'asset_hostname' => $computerAsset->hostname,
            'asset_tag' => $computerAsset->asset_tag,
            'asset_serial_number' => $computerAsset->serial_number,
        ];

        $nextDetails = $this->applyAssetOperationalMetadata($details, $metadataPayload, $computerAsset);

        $computerAsset->update([
            'assigned_user' => $newAssignedUser,
            'details' => $nextDetails,
        ]);

        return redirect()
            ->to('/admin?focus_asset=' . $computerAsset->id . '#section-assets')
            ->with('status', 'Activo TI reasignado correctamente.');
    }

    public function requestComputerAssetTransfer(Request $request, ComputerAsset $computerAsset): RedirectResponse
    {
        $this->ensureComputerAssetAccessScope($request, $computerAsset);

        $validated = $request->validate([
            'transfer_to_branch_id' => ['required', 'exists:branches,id'],
            'transfer_to_user_id' => ['required', 'exists:users,id'],
            'transfer_priority' => ['required', Rule::in(['normal', 'high', 'urgent'])],
            'transfer_reason' => ['required', 'string', 'max:1000'],
            'transfer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $targetUser = User::query()->findOrFail((int) $validated['transfer_to_user_id']);

        ComputerAssetTransferRequest::query()->create([
            'computer_asset_id' => $computerAsset->id,
            'status' => 'pending',
            'priority' => (string) $validated['transfer_priority'],
            'requested_by_user_id' => $request->user()?->id,
            'requested_by_name' => trim((string) ($request->user()?->name ?? 'Sistema')),
            'requested_from_branch_id' => $computerAsset->branch_id,
            'requested_to_branch_id' => (int) $validated['transfer_to_branch_id'],
            'requested_to_user_id' => $targetUser->id,
            'requested_to_user_name' => trim((string) ($targetUser->name ?? '')),
            'reason' => trim((string) $validated['transfer_reason']),
            'note' => trim((string) ($validated['transfer_note'] ?? '')) !== '' ? trim((string) $validated['transfer_note']) : null,
            'requested_at' => now(),
        ]);

        return redirect()
            ->to('/admin?focus_asset=' . $computerAsset->id . '#section-assets')
            ->with('status', 'Solicitud de traslado creada y enviada al agente destino.');
    }

    public function decideComputerAssetTransferRequest(Request $request, ComputerAssetTransferRequest $transferRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'rejected'])],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($transferRequest->status !== 'pending') {
            return redirect()
                ->to('/admin#section-assets')
                ->with('status', 'La solicitud ya fue atendida previamente.');
        }

        $currentUser = $request->user();
        $currentUserId = (int) ($currentUser?->id ?? 0);
        $isAuthorizedReceiver = $currentUserId > 0
            && ((int) $transferRequest->requested_to_user_id === $currentUserId || (method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin()));

        if (!$isAuthorizedReceiver) {
            abort(403, 'Solo el agente destino puede atender esta solicitud de traslado.');
        }

        $decision = (string) $validated['decision'];
        $decisionNote = trim((string) ($validated['decision_note'] ?? ''));

        $transferRequest->update([
            'status' => $decision,
            'decided_by_user_id' => $currentUserId > 0 ? $currentUserId : null,
            'decided_by_name' => trim((string) ($currentUser?->name ?? 'Sistema')),
            'decided_at' => now(),
            'decision_note' => $decisionNote !== '' ? $decisionNote : null,
        ]);

        if ($decision !== 'accepted') {
            return redirect()
                ->to('/admin#section-assets')
                ->with('status', 'Solicitud de traslado rechazada.');
        }

        $computerAsset = ComputerAsset::query()->findOrFail((int) $transferRequest->computer_asset_id);
        $details = is_array($computerAsset->details) ? $computerAsset->details : [];

        $fromBranchName = (string) optional($transferRequest->requestedFromBranch)->name;
        $toBranchName = (string) optional($transferRequest->requestedToBranch)->name;
        $fromAgentName = trim((string) $transferRequest->requested_by_name);
        $toAgentName = trim((string) $transferRequest->requested_to_user_name);

        $interactionChunks = [
            'Traspaso aceptado por agente destino.',
            'Solicitud #' . $transferRequest->id,
            'Prioridad: ' . strtoupper((string) ($transferRequest->priority ?: 'normal')),
            $fromAgentName !== '' ? ('Agente origen: ' . $fromAgentName) : null,
            $toAgentName !== '' ? ('Agente destino: ' . $toAgentName) : null,
            $fromBranchName !== '' ? ('Sede origen: ' . $fromBranchName) : null,
            $toBranchName !== '' ? ('Sede destino: ' . $toBranchName) : null,
            $decisionNote !== '' ? ('Nota de aceptación: ' . $decisionNote) : null,
        ];

        $metadataPayload = [
            'asset_status' => $computerAsset->status,
            'asset_assigned_user' => $toAgentName !== '' ? $toAgentName : ($computerAsset->assigned_user ?? ''),
            'asset_responsiva_reference' => (string) data_get($details, 'responsiva.reference', ''),
            'asset_assignment_invoice_folio' => null,
            'asset_assignment_supplier' => null,
            'asset_assignment_delivery_date' => now()->toDateString(),
            'asset_assignment_received_by' => $toAgentName !== '' ? $toAgentName : null,
            'asset_assignment_received_signature_data_url' => null,
            'asset_assignment_change_reason' => trim((string) $transferRequest->reason),
            'asset_interaction_note' => collect($interactionChunks)->filter()->join(' | '),
            'asset_branch_id' => (int) $transferRequest->requested_to_branch_id,
            'asset_equipment_type' => $computerAsset->equipment_type,
            'asset_brand' => $computerAsset->brand,
            'asset_model' => $computerAsset->model,
            'asset_hostname' => $computerAsset->hostname,
            'asset_tag' => $computerAsset->asset_tag,
            'asset_serial_number' => $computerAsset->serial_number,
            'asset_transfer_request_id' => (int) $transferRequest->id,
            'asset_transfer_from_branch' => $fromBranchName !== '' ? $fromBranchName : null,
            'asset_transfer_to_branch' => $toBranchName !== '' ? $toBranchName : null,
            'asset_transfer_from_user' => $fromAgentName !== '' ? $fromAgentName : null,
            'asset_transfer_to_user' => $toAgentName !== '' ? $toAgentName : null,
            'asset_transfer_priority' => (string) ($transferRequest->priority ?: 'normal'),
        ];

        $nextDetails = $this->applyAssetOperationalMetadata($details, $metadataPayload, $computerAsset);

        $computerAsset->update([
            'branch_id' => (int) $transferRequest->requested_to_branch_id,
            'assigned_user' => $toAgentName !== '' ? $toAgentName : $computerAsset->assigned_user,
            'details' => $nextDetails,
        ]);

        return redirect()
            ->to('/admin?focus_asset=' . $computerAsset->id . '#section-assets')
            ->with('status', 'Solicitud de traslado aceptada y movimiento aplicado al activo.');
    }

    public function destroyComputerAsset(ComputerAsset $computerAsset): RedirectResponse
    {
        $computerAsset->delete();

        return $this->redirectToCrud('crud-assets', 'Activo TI eliminado correctamente.');
    }

    public function downloadComputerAssetResponsiva(Request $request, ComputerAsset $computerAsset)
    {
        $this->ensureComputerAssetAccessScope($request, $computerAsset);
        [$pdf, $fileName] = $this->buildComputerAssetResponsivaPdf($computerAsset, true);
        return $pdf->download($fileName);
    }

    public function previewComputerAssetResponsiva(Request $request, ComputerAsset $computerAsset)
    {
        $this->ensureComputerAssetAccessScope($request, $computerAsset);
        [$pdf, $fileName] = $this->buildComputerAssetResponsivaPdf($computerAsset, false);

        return $pdf->stream($fileName);
    }

    public function verifyComputerAssetResponsiva(Request $request): View
    {
        $request->merge([
            'asset_id' => ($assetId = trim((string) $request->query('asset_id', ''))) !== '' ? $assetId : null,
            'reference' => ($reference = trim((string) $request->query('reference', ''))) !== '' ? $reference : null,
            'digest' => ($digest = trim((string) $request->query('digest', ''))) !== '' ? $digest : null,
        ]);

        $validated = $request->validate([
            'asset_id' => ['nullable', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:120'],
            'digest' => ['nullable', 'string', 'size:64', 'regex:/^[A-Fa-f0-9]{64}$/'],
        ]);

        $requestedAssetId = isset($validated['asset_id']) ? (int) $validated['asset_id'] : null;
        $requestedReference = trim((string) ($validated['reference'] ?? ''));
        $requestedDigest = strtolower(trim((string) ($validated['digest'] ?? '')));

        $hasQuery = $requestedAssetId !== null || $requestedReference !== '' || $requestedDigest !== '';
        $branchScopeIds = $this->currentUserBranchScopeIds($request);

        $assetQuery = ComputerAsset::query()
            ->with(['branch:id,name', 'node:id,name'])
            ->when($branchScopeIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchScopeIds));

        if ($requestedAssetId !== null) {
            $assetQuery->where('id', $requestedAssetId);
        }

        if ($requestedReference !== '') {
            $assetQuery->where('details->responsiva->reference', $requestedReference);
        }

        if ($requestedDigest !== '') {
            $assetQuery->where('details->responsiva->verification->digest', $requestedDigest);
        }

        $matchedAsset = $hasQuery ? $assetQuery->first() : null;

        $storedReference = '';
        $storedDigest = '';
        $storedGeneratedAt = null;
        $storedGeneratedBy = '';
        $storedSignatureHash = '';
        if ($matchedAsset) {
            $details = is_array($matchedAsset->details) ? $matchedAsset->details : [];
            $storedReference = trim((string) data_get($details, 'responsiva.reference', ''));
            $storedDigest = strtolower(trim((string) data_get($details, 'responsiva.verification.digest', '')));
            $storedGeneratedAt = data_get($details, 'responsiva.verification.generated_at');
            $storedGeneratedBy = trim((string) data_get($details, 'responsiva.verification.generated_by', ''));
            $storedSignatureHash = trim((string) data_get($details, 'responsiva.verification.delivery_signature_hash', ''));
        }

        $validDigest = $requestedDigest !== '' && $storedDigest !== '' && hash_equals($storedDigest, $requestedDigest);
        $validReference = $requestedReference === '' || ($storedReference !== '' && strcasecmp($storedReference, $requestedReference) === 0);
        $isValid = $matchedAsset !== null && $validDigest && $validReference;

        return view('tenant.admin.responsiva-verification', [
            'requestedAssetId' => $requestedAssetId,
            'requestedReference' => $requestedReference,
            'requestedDigest' => $requestedDigest,
            'hasQuery' => $hasQuery,
            'isValid' => $isValid,
            'matchedAsset' => $matchedAsset,
            'storedReference' => $storedReference,
            'storedDigest' => $storedDigest,
            'storedGeneratedAt' => $storedGeneratedAt,
            'storedGeneratedBy' => $storedGeneratedBy,
            'storedSignatureHash' => $storedSignatureHash,
        ]);
    }

    public function showComputerAssetAssignmentLog(Request $request, ComputerAsset $computerAsset): View
    {
        $this->ensureComputerAssetAccessScope($request, $computerAsset);
        $computerAsset->loadMissing(['branch:id,name', 'node:id,name']);
        $adminHistory = collect(data_get($computerAsset->details, 'admin_history', []))
            ->filter(fn ($entry) => is_array($entry))
            ->values();

        $assignmentLog = collect(data_get($computerAsset->details, 'assignment_log', []))
            ->filter(fn ($entry) => is_array($entry))
            ->map(function (array $entry) use ($adminHistory) {
                $at = data_get($entry, 'at');
                $assignedAt = data_get($entry, 'assigned_at');
                $changeReason = trim((string) data_get($entry, 'change_reason', ''));
                $interactionNote = trim((string) data_get($entry, 'interaction_note', ''));

                // Backward compatibility: older assignment_log entries may not include reason/note.
                // Try to recover from admin_history entries created around the same time.
                if ($changeReason === '' || $interactionNote === '') {
                    $assignmentAtMinute = is_string($at) && strlen($at) >= 16 ? substr($at, 0, 16) : null;

                    $fallbackHistory = $adminHistory
                        ->reverse()
                        ->first(function (array $historyEntry) use ($assignmentAtMinute) {
                            $historyAt = (string) data_get($historyEntry, 'at', '');
                            $historyMinute = strlen($historyAt) >= 16 ? substr($historyAt, 0, 16) : null;
                            $changes = collect(data_get($historyEntry, 'changes', []))
                                ->filter(fn ($value) => is_scalar($value))
                                ->map(fn ($value) => (string) $value)
                                ->values();

                            $hasReassignmentHint = $changes->contains(fn (string $line) => str_contains($line, 'Responsable:'));
                            if (!$hasReassignmentHint) {
                                return false;
                            }

                            if ($assignmentAtMinute === null || $historyMinute === null) {
                                return true;
                            }

                            return $historyMinute === $assignmentAtMinute;
                        });

                    if (is_array($fallbackHistory)) {
                        $historyNote = trim((string) data_get($fallbackHistory, 'note', ''));
                        if ($interactionNote === '' && $historyNote !== '') {
                            $interactionNote = $historyNote;
                        }
                        if ($changeReason === '' && $historyNote !== '') {
                            $changeReason = $historyNote;
                        }
                    }
                }

                return [
                    'at' => $at,
                    'at_label' => $at ? Carbon::parse((string) $at)->format('d/m/Y H:i') : 'N/A',
                    'by' => data_get($entry, 'by', 'Sistema'),
                    'serial_number' => data_get($entry, 'serial_number'),
                    'description' => data_get($entry, 'description'),
                    'invoice_folio' => data_get($entry, 'invoice_folio'),
                    'supplier' => data_get($entry, 'supplier'),
                    'campus' => data_get($entry, 'campus'),
                    'assigned_at' => $assignedAt,
                    'assigned_at_label' => $assignedAt ? Carbon::parse((string) $assignedAt)->format('d/m/Y') : null,
                    'received_by' => data_get($entry, 'received_by'),
                    'assigned_user' => data_get($entry, 'assigned_user'),
                    'transfer_request_id' => data_get($entry, 'transfer_request_id'),
                    'transfer_from_branch' => data_get($entry, 'transfer_from_branch'),
                    'transfer_to_branch' => data_get($entry, 'transfer_to_branch'),
                    'transfer_from_user' => data_get($entry, 'transfer_from_user'),
                    'transfer_to_user' => data_get($entry, 'transfer_to_user'),
                    'transfer_priority' => data_get($entry, 'transfer_priority'),
                    'change_reason' => $changeReason !== '' ? $changeReason : null,
                    'interaction_note' => $interactionNote !== '' ? $interactionNote : null,
                ];
            })
            ->values()
            ->reverse()
            ->values();

        return view('tenant.admin.computer-asset-assignment-log', [
            'asset' => $computerAsset,
            'assignmentLog' => $assignmentLog,
        ]);
    }

    public function storeSoftware(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'software_node_id' => ['nullable', 'exists:nodes,id'],
            'software_name' => ['required', 'string', 'max:120'],
            'software_version' => ['nullable', 'string', 'max:80'],
            'software_vendor' => ['nullable', 'string', 'max:120'],
            'software_contact_email' => ['nullable', 'email', 'max:255'],
            'software_contact_phone' => ['nullable', 'string', 'max:80'],
            'software_project_name' => ['nullable', 'string', 'max:120'],
            'software_details_json' => ['nullable', 'string'],
        ]);

        SoftwareSystem::query()->create([
            'node_id' => $validated['software_node_id'] ?? null,
            'name' => $validated['software_name'],
            'version' => $validated['software_version'] ?? null,
            'vendor' => $validated['software_vendor'] ?? null,
            'contact_email' => $validated['software_contact_email'] ?? null,
            'contact_phone' => $validated['software_contact_phone'] ?? null,
            'project_name' => $validated['software_project_name'] ?? null,
            'details' => $this->decodeJson($validated['software_details_json'] ?? null, 'software_details_json'),
        ]);

        return $this->redirectToCrud('crud-software', 'Sistema creado correctamente.');
    }

    public function updateSoftware(Request $request, SoftwareSystem $software): RedirectResponse
    {
        $validated = $request->validate([
            'software_node_id' => ['nullable', 'exists:nodes,id'],
            'software_name' => ['required', 'string', 'max:120'],
            'software_version' => ['nullable', 'string', 'max:80'],
            'software_vendor' => ['nullable', 'string', 'max:120'],
            'software_contact_email' => ['nullable', 'email', 'max:255'],
            'software_contact_phone' => ['nullable', 'string', 'max:80'],
            'software_project_name' => ['nullable', 'string', 'max:120'],
            'software_details_json' => ['nullable', 'string'],
        ]);

        $software->update([
            'node_id' => $validated['software_node_id'] ?? null,
            'name' => $validated['software_name'],
            'version' => $validated['software_version'] ?? null,
            'vendor' => $validated['software_vendor'] ?? null,
            'contact_email' => $validated['software_contact_email'] ?? null,
            'contact_phone' => $validated['software_contact_phone'] ?? null,
            'project_name' => $validated['software_project_name'] ?? null,
            'details' => $this->decodeJson($validated['software_details_json'] ?? null, 'software_details_json'),
        ]);

        return $this->redirectToCrud('crud-software', 'Sistema actualizado correctamente.');
    }

    public function destroySoftware(SoftwareSystem $software): RedirectResponse
    {
        $software->delete();

        return $this->redirectToCrud('crud-software', 'Sistema eliminado correctamente.');
    }

    public function storeRelation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'relation_from_node_id' => ['required', 'exists:nodes,id'],
            'relation_to_node_id' => ['required', 'exists:nodes,id', 'different:relation_from_node_id'],
            'relation_type' => ['required', 'string', 'max:80'],
            'relation_preferred_weight' => ['nullable', 'integer', 'min:1', 'max:999'],
            'relation_notes' => ['nullable', 'string'],
        ]);

        try {
            NodeRelation::query()->create([
                'from_node_id' => $validated['relation_from_node_id'],
                'to_node_id' => $validated['relation_to_node_id'],
                'relation_type' => $validated['relation_type'],
                'preferred_weight' => $validated['relation_preferred_weight'] ?? null,
                'notes' => $validated['relation_notes'] ?? null,
            ]);
        } catch (QueryException $exception) {
            return $this->redirectToCrud('crud-relations', 'La relación ya existe o no pudo guardarse.');
        }

        return $this->redirectToCrud('crud-relations', 'Relación creada correctamente.');
    }

    public function updateRelation(Request $request, NodeRelation $relation): RedirectResponse
    {
        $validated = $request->validate([
            'relation_from_node_id' => ['required', 'exists:nodes,id'],
            'relation_to_node_id' => ['required', 'exists:nodes,id', 'different:relation_from_node_id'],
            'relation_type' => ['required', 'string', 'max:80'],
            'relation_preferred_weight' => ['nullable', 'integer', 'min:1', 'max:999'],
            'relation_notes' => ['nullable', 'string'],
        ]);

        try {
            $relation->update([
                'from_node_id' => $validated['relation_from_node_id'],
                'to_node_id' => $validated['relation_to_node_id'],
                'relation_type' => $validated['relation_type'],
                'preferred_weight' => $validated['relation_preferred_weight'] ?? null,
                'notes' => $validated['relation_notes'] ?? null,
            ]);
        } catch (QueryException $exception) {
            return $this->redirectToCrud('crud-relations', 'La relación ya existe o no pudo actualizarse.');
        }

        return $this->redirectToCrud('crud-relations', 'Relación actualizada correctamente.');
    }

    public function destroyRelation(NodeRelation $relation): RedirectResponse
    {
        $relation->delete();

        return $this->redirectToCrud('crud-relations', 'Relación eliminada correctamente.');
    }

    private function decodeJson(?string $json, string $field): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ValidationException::withMessages([
                $field => 'Debe ser un JSON válido con estructura de objeto o arreglo.',
            ]);
        }

        return $decoded;
    }

    private function normalizeFloorPlanScale(array $scale): ?array
    {
        $width = isset($scale['width_m']) && $scale['width_m'] !== null && $scale['width_m'] !== ''
            ? round((float) $scale['width_m'], 2)
            : null;
        $height = isset($scale['height_m']) && $scale['height_m'] !== null && $scale['height_m'] !== ''
            ? round((float) $scale['height_m'], 2)
            : null;

        if ($width === null && $height === null) {
            return null;
        }

        return [
            'width_m' => $width,
            'height_m' => $height,
            'unit' => 'm',
        ];
    }

    private function normalizeStructureDefaults(array $defaults): ?array
    {
        $wallHeight = isset($defaults['wall_height_m']) && $defaults['wall_height_m'] !== '' ? round((float) $defaults['wall_height_m'], 2) : null;
        $doorHeight = isset($defaults['door_height_m']) && $defaults['door_height_m'] !== '' ? round((float) $defaults['door_height_m'], 2) : null;
        $doorWidth = isset($defaults['door_width_m']) && $defaults['door_width_m'] !== '' ? round((float) $defaults['door_width_m'], 2) : null;
        $windowBase = isset($defaults['window_base_m']) && $defaults['window_base_m'] !== '' ? round((float) $defaults['window_base_m'], 2) : null;
        $windowHeight = isset($defaults['window_height_m']) && $defaults['window_height_m'] !== '' ? round((float) $defaults['window_height_m'], 2) : null;
        $windowWidth = isset($defaults['window_width_m']) && $defaults['window_width_m'] !== '' ? round((float) $defaults['window_width_m'], 2) : null;
        $orthogonalLock = array_key_exists('orthogonal_lock', $defaults)
            ? filter_var($defaults['orthogonal_lock'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        $apMountHeight = isset($defaults['ap_mount_height_m']) && $defaults['ap_mount_height_m'] !== '' ? round((float) $defaults['ap_mount_height_m'], 2) : null;
        $preferredWallMaterial = isset($defaults['preferred_wall_material']) && is_string($defaults['preferred_wall_material'])
            ? strtolower(trim($defaults['preferred_wall_material']))
            : null;

        if ($preferredWallMaterial !== null && !in_array($preferredWallMaterial, ['drywall', 'brick', 'concrete', 'glass', 'wood', 'metal'], true)) {
            $preferredWallMaterial = null;
        }

        if ($wallHeight === null && $doorHeight === null && $doorWidth === null && $windowBase === null && $windowHeight === null && $windowWidth === null && $orthogonalLock === null && $apMountHeight === null && $preferredWallMaterial === null) {
            return null;
        }

        return [
            'wall_height_m' => $wallHeight,
            'door_height_m' => $doorHeight,
            'door_width_m' => $doorWidth,
            'window_base_m' => $windowBase,
            'window_height_m' => $windowHeight,
            'window_width_m' => $windowWidth,
            'orthogonal_lock' => $orthogonalLock,
            'ap_mount_height_m' => $apMountHeight,
            'preferred_wall_material' => $preferredWallMaterial,
            'unit' => 'm',
        ];
    }

    private function extractNodeRfDefaults(?array $details): array
    {
        $details = $details ?? [];

        $radius = data_get($details, 'rf.radius_meters')
            ?? data_get($details, 'rf.range_m')
            ?? data_get($details, 'wifi.radius_meters')
            ?? data_get($details, 'wifi.range_m')
            ?? data_get($details, 'radius_meters');

        return [
            'radius_meters' => $radius !== null ? round((float) $radius, 2) : null,
            'radiation_pattern' => (string) (data_get($details, 'rf.radiation_pattern')
                ?? data_get($details, 'wifi.radiation_pattern')
                ?? data_get($details, 'radiation_pattern')
                ?? 'omni-donut'),
            'mount_orientation' => (string) (data_get($details, 'rf.mount_orientation')
                ?? data_get($details, 'wifi.mount_orientation')
                ?? data_get($details, 'mount_orientation')
                ?? 'ceiling'),
            'mount_height_m' => ($mountHeight = data_get($details, 'rf.mount_height_m')
                ?? data_get($details, 'rf.height_m')
                ?? data_get($details, 'wifi.mount_height_m')
                ?? data_get($details, 'wifi.height_m')
                ?? data_get($details, 'mount_height_m')) !== null ? round((float) $mountHeight, 2) : null,
            'azimuth_deg' => round((float) (data_get($details, 'rf.azimuth_deg')
                ?? data_get($details, 'wifi.azimuth_deg')
                ?? data_get($details, 'azimuth_deg')
                ?? 0), 2),
            'tilt_deg' => round((float) (data_get($details, 'rf.tilt_deg')
                ?? data_get($details, 'wifi.tilt_deg')
                ?? data_get($details, 'tilt_deg')
                ?? 0), 2),
        ];
    }

    private function normalizeMacAddress(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return strtoupper(str_replace('-', ':', trim($value)));
    }

    private function validateEquipmentModel(Request $request, ?EquipmentModel $ignoreModel = null): array
    {
        $uniqueRule = Rule::unique('equipment_models')->where(function ($query) use ($request) {
            return $query->where('brand_id', $request->input('eqmodel_brand_id'))
                         ->where('equipment_type', $request->input('eqmodel_equipment_type'));
        });

        if ($ignoreModel) {
            $uniqueRule = $uniqueRule->ignore($ignoreModel->id);
        }

        $validated = $request->validate([
            'eqmodel_brand_id'           => ['required', 'exists:equipment_brands,id'],
            'eqmodel_equipment_type'     => ['required', 'string', 'max:60'],
            'eqmodel_name'               => ['required', 'string', 'max:120', $uniqueRule],
            'eqmodel_radius_min'         => ['nullable', 'numeric', 'min:0.1', 'max:9999'],
            'eqmodel_radius_max'         => ['nullable', 'numeric', 'min:0.1', 'max:9999'],
            'eqmodel_signal_dbm'         => ['nullable', 'integer', 'min:-120', 'max:0'],
            'eqmodel_radiation_pattern'  => ['nullable', 'string', 'max:40'],
            'eqmodel_mount_height_m'     => ['nullable', 'numeric', 'min:0.1', 'max:50'],
            'eqmodel_notes'              => ['nullable', 'string'],
        ]);

        return [
            'brand_id'              => $validated['eqmodel_brand_id'],
            'equipment_type'        => $validated['eqmodel_equipment_type'],
            'name'                  => $validated['eqmodel_name'],
            'coverage_radius_min_m' => $validated['eqmodel_radius_min'] ?? null,
            'coverage_radius_max_m' => $validated['eqmodel_radius_max'] ?? null,
            'default_signal_dbm'    => $validated['eqmodel_signal_dbm'] ?? null,
            'radiation_pattern'     => $validated['eqmodel_radiation_pattern'] ?? null,
            'mount_height_m'        => $validated['eqmodel_mount_height_m'] ?? null,
            'notes'                 => $validated['eqmodel_notes'] ?? null,
        ];
    }

    private function validateComputerAsset(Request $request): array
    {
        return $request->validate([
            'asset_branch_id' => ['required', 'exists:branches,id'],
            'asset_node_id' => ['nullable', 'exists:nodes,id'],
            'asset_equipment_model_id' => ['nullable', 'exists:equipment_models,id'],
            'asset_equipment_type' => ['required', Rule::in(array_keys(ComputerAsset::equipmentTypeOptions()))],
            'asset_tag' => ['nullable', 'string', 'max:120'],
            'asset_hostname' => ['nullable', 'string', 'max:120'],
            'asset_assigned_user' => ['nullable', 'string', 'max:120'],
            'asset_brand' => ['nullable', 'string', 'max:120'],
            'asset_model' => ['nullable', 'string', 'max:120'],
            'asset_serial_number' => ['nullable', 'string', 'max:120'],
            'asset_cpu' => ['nullable', 'string', 'max:150'],
            'asset_ram_gb' => ['nullable', 'integer', 'min:1', 'max:4096'],
            'asset_storage_type' => ['nullable', Rule::in(array_keys(ComputerAsset::storageTypeOptions()))],
            'asset_storage_gb' => ['nullable', 'integer', 'min:1', 'max:200000'],
            'asset_operating_system' => ['nullable', 'string', 'max:120'],
            'asset_office_version' => ['nullable', 'string', 'max:120'],
            'asset_purchase_date' => ['nullable', 'date'],
            'asset_warranty_expires_at' => ['nullable', 'date'],
            'asset_status' => ['required', Rule::in(array_keys(ComputerAsset::statusOptions()))],
            'asset_notes' => ['nullable', 'string'],
            'asset_details_json' => ['nullable', 'string'],
            'asset_responsiva_reference' => ['nullable', 'string', 'max:120'],
            'asset_interaction_note' => ['nullable', 'string', 'max:1000'],
            'asset_assignment_invoice_folio' => ['nullable', 'string', 'max:120'],
            'asset_assignment_supplier' => ['nullable', 'string', 'max:160'],
            'asset_assignment_delivery_date' => ['nullable', 'date'],
            'asset_assignment_received_by' => ['nullable', 'string', 'max:120'],
            'asset_assignment_received_signature_data_url' => ['nullable', 'string', 'max:400000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/'],
            'asset_assignment_change_reason' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function applyAssetOperationalMetadata(array $details, array $validated, ?ComputerAsset $existingAsset = null): array
    {
        $payload = $details;
        $history = collect(data_get($payload, 'admin_history', []))
            ->filter(fn ($entry) => is_array($entry))
            ->values()
            ->all();

        $statusOptions = ComputerAsset::statusOptions();
        $newStatus = (string) ($validated['asset_status'] ?? '');
        $newAssigned = trim((string) ($validated['asset_assigned_user'] ?? ''));
        $interactionNote = trim((string) ($validated['asset_interaction_note'] ?? ''));
        $newResponsiva = trim((string) ($validated['asset_responsiva_reference'] ?? ''));
        $assignmentInvoiceFolio = trim((string) ($validated['asset_assignment_invoice_folio'] ?? ''));
        $assignmentSupplier = trim((string) ($validated['asset_assignment_supplier'] ?? ''));
        $assignmentReceivedBy = trim((string) ($validated['asset_assignment_received_by'] ?? ''));
        $assignmentReceivedSignatureDataUrl = trim((string) ($validated['asset_assignment_received_signature_data_url'] ?? ''));
        $assignmentChangeReason = trim((string) ($validated['asset_assignment_change_reason'] ?? ''));
        $transferRequestId = isset($validated['asset_transfer_request_id']) ? (int) $validated['asset_transfer_request_id'] : null;
        $transferFromBranch = trim((string) ($validated['asset_transfer_from_branch'] ?? ''));
        $transferToBranch = trim((string) ($validated['asset_transfer_to_branch'] ?? ''));
        $transferFromUser = trim((string) ($validated['asset_transfer_from_user'] ?? ''));
        $transferToUser = trim((string) ($validated['asset_transfer_to_user'] ?? ''));
        $transferPriority = trim((string) ($validated['asset_transfer_priority'] ?? ''));
        $assignmentDeliveryDateRaw = trim((string) ($validated['asset_assignment_delivery_date'] ?? ''));
        $assignmentDeliveryDate = $assignmentDeliveryDateRaw !== ''
            ? Carbon::parse($assignmentDeliveryDateRaw)->toDateString()
            : null;

        $assignmentReceivedSignatureHash = null;
        $assignmentReceivedSignatureSignedAt = null;
        if ($assignmentReceivedSignatureDataUrl !== '') {
            $signatureBase64Payload = preg_replace('/^data:image\/png;base64,/', '', $assignmentReceivedSignatureDataUrl);
            $signatureBinary = base64_decode((string) $signatureBase64Payload, true);
            if ($signatureBinary !== false) {
                $assignmentReceivedSignatureHash = hash('sha256', $signatureBinary);
                $assignmentReceivedSignatureSignedAt = now()->toIso8601String();
            } else {
                $assignmentReceivedSignatureDataUrl = '';
            }
        }

        $assignmentLog = collect(data_get($payload, 'assignment_log', []))
            ->filter(fn ($entry) => is_array($entry))
            ->values()
            ->all();

        $actor = optional(auth()->user())->name ?? 'Sistema';
        $campusName = null;
        if (!empty($validated['asset_branch_id'])) {
            $campusName = Branch::query()->whereKey((int) $validated['asset_branch_id'])->value('name');
        }

        $assetDescription = collect([
            $validated['asset_equipment_type'] ?? ($existingAsset?->equipment_type ?? null),
            trim((string) ($validated['asset_brand'] ?? ($existingAsset?->brand ?? ''))),
            trim((string) ($validated['asset_model'] ?? ($existingAsset?->model ?? ''))),
            trim((string) ($validated['asset_hostname'] ?? ($existingAsset?->hostname ?? ''))),
            trim((string) ($validated['asset_tag'] ?? ($existingAsset?->asset_tag ?? ''))),
        ])->filter(fn ($part) => (string) $part !== '')->join(' | ');

        $assignmentSerial = trim((string) ($validated['asset_serial_number'] ?? ($existingAsset?->serial_number ?? '')));
        $shouldCreateAssignmentLog = false;

        $changes = [];
        if ($existingAsset) {
            $oldStatus = (string) ($existingAsset->status ?? '');
            $oldAssigned = trim((string) ($existingAsset->assigned_user ?? ''));
            $oldResponsiva = trim((string) data_get($existingAsset->details, 'responsiva.reference', ''));

            if ($oldStatus !== $newStatus) {
                $changes[] = sprintf(
                    'Estatus: %s → %s',
                    $statusOptions[$oldStatus] ?? ($oldStatus !== '' ? $oldStatus : 'N/A'),
                    $statusOptions[$newStatus] ?? ($newStatus !== '' ? $newStatus : 'N/A')
                );
            }

            if ($oldAssigned !== $newAssigned) {
                $changes[] = sprintf(
                    'Responsable: %s → %s',
                    $oldAssigned !== '' ? $oldAssigned : 'Sin asignar',
                    $newAssigned !== '' ? $newAssigned : 'Sin asignar'
                );
            }

            if ($oldResponsiva !== $newResponsiva) {
                $changes[] = sprintf(
                    'Responsiva: %s → %s',
                    $oldResponsiva !== '' ? $oldResponsiva : 'Sin folio',
                    $newResponsiva !== '' ? $newResponsiva : 'Sin folio'
                );
            }

            $shouldCreateAssignmentLog = $oldAssigned !== $newAssigned;
        } else {
            $changes[] = sprintf('Activo creado con estatus %s', $statusOptions[$newStatus] ?? $newStatus);
            if ($newAssigned !== '') {
                $changes[] = sprintf('Responsable inicial: %s', $newAssigned);
            }
            if ($newResponsiva !== '') {
                $changes[] = sprintf('Responsiva inicial: %s', $newResponsiva);
            }

            $shouldCreateAssignmentLog = $newAssigned !== '';
        }

        if (
            $assignmentInvoiceFolio !== ''
            || $assignmentSupplier !== ''
            || $assignmentReceivedBy !== ''
            || $assignmentChangeReason !== ''
            || $assignmentDeliveryDate !== null
            || $assignmentReceivedSignatureDataUrl !== ''
        ) {
            $shouldCreateAssignmentLog = true;
        }

        if ($shouldCreateAssignmentLog) {
            $assignmentLog[] = [
                'at' => now()->toIso8601String(),
                'by' => $actor,
                'serial_number' => $assignmentSerial !== '' ? $assignmentSerial : null,
                'description' => $assetDescription !== '' ? $assetDescription : null,
                'invoice_folio' => $assignmentInvoiceFolio !== '' ? $assignmentInvoiceFolio : null,
                'supplier' => $assignmentSupplier !== '' ? $assignmentSupplier : null,
                'campus' => $campusName,
                'assigned_at' => $assignmentDeliveryDate,
                'received_by' => $assignmentReceivedBy !== '' ? $assignmentReceivedBy : null,
                'received_signature_data_url' => $assignmentReceivedSignatureDataUrl !== '' ? $assignmentReceivedSignatureDataUrl : null,
                'received_signature_hash' => $assignmentReceivedSignatureHash,
                'received_signature_signed_at' => $assignmentReceivedSignatureSignedAt,
                'assigned_user' => $newAssigned !== '' ? $newAssigned : null,
                'transfer_request_id' => $transferRequestId,
                'transfer_from_branch' => $transferFromBranch !== '' ? $transferFromBranch : null,
                'transfer_to_branch' => $transferToBranch !== '' ? $transferToBranch : null,
                'transfer_from_user' => $transferFromUser !== '' ? $transferFromUser : null,
                'transfer_to_user' => $transferToUser !== '' ? $transferToUser : null,
                'transfer_priority' => $transferPriority !== '' ? $transferPriority : null,
                'change_reason' => $assignmentChangeReason !== '' ? $assignmentChangeReason : null,
                'interaction_note' => $interactionNote !== '' ? $interactionNote : null,
            ];

            data_set($payload, 'assignment_log', array_slice($assignmentLog, -100));
            $changes[] = 'Bitácora de asignación actualizada';
        }

        if ($newResponsiva !== '') {
            data_set($payload, 'responsiva.reference', $newResponsiva);
        } elseif (array_key_exists('asset_responsiva_reference', $validated)) {
            data_set($payload, 'responsiva.reference', null);
        }

        if (!empty($changes) || $interactionNote !== '') {
            $history[] = [
                'at' => now()->toIso8601String(),
                'by' => optional(auth()->user())->name ?? 'Sistema',
                'changes' => $changes,
                'note' => $interactionNote !== '' ? $interactionNote : null,
            ];
        }

        if (!empty($history)) {
            data_set($payload, 'admin_history', array_slice($history, -50));
        }

        return $payload;
    }

    private function ensureComputerAssetAccessScope(Request $request, ComputerAsset $computerAsset): void
    {
        $branchScopeIds = $this->currentUserBranchScopeIds($request);
        if ($branchScopeIds !== null && !in_array((int) $computerAsset->branch_id, $branchScopeIds, true)) {
            abort(404);
        }
    }

    private function buildComputerAssetResponsivaPdf(ComputerAsset $computerAsset, bool $persistGeneration): array
    {
        $computerAsset->loadMissing(['branch:id,name', 'node:id,name']);

        $details = is_array($computerAsset->details) ? $computerAsset->details : [];
        $reference = trim((string) data_get($details, 'responsiva.reference', ''));
        if ($reference === '') {
            $reference = sprintf('RESP-%s-%04d', now()->format('Ymd'), $computerAsset->id);
            if ($persistGeneration) {
                data_set($details, 'responsiva.reference', $reference);
            }
        }

        $generatedAt = now();
        $currentUser = auth()->user();
        $generatedBy = optional($currentUser)->name ?? 'Sistema';
        $signatureDeliveryDataUrl = null;
        $signatureDeliverySignedAt = null;
        $signatureDeliveryHash = null;
        if ($currentUser && is_string($currentUser->signature_data_url)) {
            $candidateSignature = trim($currentUser->signature_data_url);
            if (str_starts_with($candidateSignature, 'data:image/png;base64,')) {
                $signatureDeliveryDataUrl = $candidateSignature;
                $signatureDeliverySignedAt = $currentUser->signature_updated_at;
                $signatureDeliveryHash = is_string($currentUser->signature_hash) ? strtolower(trim($currentUser->signature_hash)) : null;
            }
        }
        $latestAssignmentLog = collect(data_get($details, 'assignment_log', []))
            ->filter(fn ($entry) => is_array($entry))
            ->values()
            ->last() ?? [];
        $equipmentTypeForDescription = ComputerAsset::equipmentTypeOptions()[$computerAsset->equipment_type] ?? (string) $computerAsset->equipment_type;

        $assetDescription = collect([
            $equipmentTypeForDescription,
            trim((string) $computerAsset->brand),
            trim((string) $computerAsset->model),
            trim((string) $computerAsset->hostname),
            trim((string) $computerAsset->asset_tag),
        ])->filter(fn ($part) => (string) $part !== '')->join(' | ');

        $assignmentForm = [
            'serial_number' => data_get($latestAssignmentLog, 'serial_number') ?: ($computerAsset->serial_number ?: null),
            'description' => data_get($latestAssignmentLog, 'description') ?: ($assetDescription !== '' ? $assetDescription : null),
            'invoice_folio' => data_get($latestAssignmentLog, 'invoice_folio'),
            'supplier' => data_get($latestAssignmentLog, 'supplier'),
            'campus' => data_get($latestAssignmentLog, 'campus') ?: (optional($computerAsset->branch)->name ?: null),
            'assigned_at' => data_get($latestAssignmentLog, 'assigned_at'),
            'received_by' => data_get($latestAssignmentLog, 'received_by'),
            'received_signature_data_url' => data_get($latestAssignmentLog, 'received_signature_data_url'),
            'received_signature_hash' => data_get($latestAssignmentLog, 'received_signature_hash'),
            'received_signature_signed_at' => data_get($latestAssignmentLog, 'received_signature_signed_at'),
            'assigned_user' => data_get($latestAssignmentLog, 'assigned_user') ?: ($computerAsset->assigned_user ?: null),
            'change_reason' => data_get($latestAssignmentLog, 'change_reason'),
        ];

        if ($persistGeneration) {
            data_set($details, 'responsiva.generated_at', $generatedAt->toIso8601String());
            data_set($details, 'responsiva.generated_by', $generatedBy);

            $history = collect(data_get($details, 'admin_history', []))
                ->filter(fn ($entry) => is_array($entry))
                ->values()
                ->all();

            $history[] = [
                'at' => $generatedAt->toIso8601String(),
                'by' => $generatedBy,
                'changes' => [sprintf('Se generó responsiva PDF: %s', $reference)],
                'note' => null,
            ];

            data_set($details, 'admin_history', array_slice($history, -50));

            $computerAsset->details = $details;
            $computerAsset->save();
        }

        $statusLabel = ComputerAsset::statusOptions()[$computerAsset->status] ?? (string) $computerAsset->status;
        $equipmentTypeLabel = ComputerAsset::equipmentTypeOptions()[$computerAsset->equipment_type] ?? (string) $computerAsset->equipment_type;
        $verificationPayload = [
            'tenant' => (string) config('app.name'),
            'asset_id' => (int) $computerAsset->id,
            'reference' => (string) $reference,
            'generated_at' => $generatedAt->toIso8601String(),
            'generated_by' => (string) $generatedBy,
            'delivery_signature_hash' => $signatureDeliveryHash,
        ];
        $verificationJson = json_encode($verificationPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($verificationJson)) {
            $verificationJson = '';
        }
        $verificationDigest = strtolower(hash('sha256', $verificationJson));
        $verificationUrl = url('/admin/responsiva/verify') . '?' . http_build_query([
            'asset_id' => $computerAsset->id,
            'reference' => $reference,
            'digest' => $verificationDigest,
        ]);
        $verificationQrDataUrl = null;
        if ($verificationUrl !== '') {
            try {
                $qrBinary = QrCode::format('png')->size(150)->margin(1)->generate($verificationUrl);
                if (is_string($qrBinary) && $qrBinary !== '') {
                    $verificationQrDataUrl = 'data:image/png;base64,' . base64_encode($qrBinary);
                }
            } catch (\Throwable $exception) {
                $verificationQrDataUrl = null;
            }
        }

        if ($persistGeneration) {
            $persistedDetails = is_array($computerAsset->details) ? $computerAsset->details : [];
            data_set($persistedDetails, 'responsiva.verification', [
                'digest' => $verificationDigest,
                'payload' => $verificationPayload,
                'generated_at' => $generatedAt->toIso8601String(),
                'generated_by' => $generatedBy,
                'delivery_signature_hash' => $signatureDeliveryHash,
            ]);
            $computerAsset->details = $persistedDetails;
            $computerAsset->save();
        }

        $pdf = Pdf::loadView('tenant.admin.responsiva-pdf', [
            'asset' => $computerAsset,
            'reference' => $reference,
            'generatedAt' => $generatedAt,
            'generatedBy' => $generatedBy,
            'signatureDeliveryDataUrl' => $signatureDeliveryDataUrl,
            'signatureDeliverySignedAt' => $signatureDeliverySignedAt,
            'signatureDeliveryHash' => $signatureDeliveryHash,
            'verificationDigest' => $verificationDigest,
            'verificationPayloadJson' => $verificationJson,
            'verificationUrl' => $verificationUrl,
            'verificationQrDataUrl' => $verificationQrDataUrl,
            'statusLabel' => $statusLabel,
            'equipmentTypeLabel' => $equipmentTypeLabel,
            'assignmentForm' => $assignmentForm,
        ]);

        $fileName = sprintf(
            'responsiva-%s-%d.pdf',
            Str::slug($reference),
            $computerAsset->id
        );

        return [$pdf, $fileName];
    }

    private function resolveComputerAssetNodeId(array $validated): ?int
    {
        $nodeId = isset($validated['asset_node_id']) && $validated['asset_node_id'] !== null
            ? (int) $validated['asset_node_id']
            : null;

        if ($nodeId === null) {
            return null;
        }

        $exists = Node::query()
            ->where('id', $nodeId)
            ->where('branch_id', $validated['asset_branch_id'])
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'asset_node_id' => 'El nodo seleccionado no pertenece a la sede elegida.',
            ]);
        }
        return $nodeId;
    }

    private function resolveHeartbeatNode(array $validated, ?ComputerAsset $asset, ?int $branchId): array
    {
        if (!empty($validated['node_id'])) {
            $node = Node::query()->find((int) $validated['node_id']);
            if ($node) {
                return [$node, [
                    'source' => 'provided_node_id',
                    'matched_node_id' => $node->id,
                ]];
            }
        }

        if ($asset && $asset->node_id) {
            $node = Node::query()->find((int) $asset->node_id);
            if ($node) {
                return [$node, [
                    'source' => 'asset_existing_node',
                    'matched_node_id' => $node->id,
                ]];
            }
        }

        $hostname = mb_strtolower(trim((string) ($validated['hostname'] ?? '')));
        if ($hostname !== '') {
            $hostnameCandidates = Node::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where(function ($query) use ($hostname) {
                    $query->whereRaw('LOWER(name) = ?', [$hostname])
                        ->orWhereRaw('LOWER(code) = ?', [$hostname]);
                })
                ->limit(3)
                ->get(['id']);

            if ($hostnameCandidates->count() === 1) {
                $node = Node::query()->find((int) $hostnameCandidates->first()->id);
                if ($node) {
                    return [$node, [
                        'source' => 'hostname_unique',
                        'matched_node_id' => $node->id,
                    ]];
                }
            }
        }

        if (!empty($validated['ip_address'])) {
            $ipCandidates = Node::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('ip_address', $validated['ip_address'])
                ->limit(3)
                ->get(['id']);

            if ($ipCandidates->count() === 1) {
                $node = Node::query()->find((int) $ipCandidates->first()->id);
                if ($node) {
                    return [$node, [
                        'source' => 'ip_unique',
                        'matched_node_id' => $node->id,
                    ]];
                }
            }
        }

        $mac = $this->normalizeMacAddress($validated['mac_address'] ?? null);
        if ($mac !== null) {
            $macCandidates = Node::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('mac_address', $mac)
                ->limit(3)
                ->get(['id']);

            if ($macCandidates->count() === 1) {
                $node = Node::query()->find((int) $macCandidates->first()->id);
                if ($node) {
                    return [$node, [
                        'source' => 'mac_unique',
                        'matched_node_id' => $node->id,
                    ]];
                }
            }
        }

        return [null, [
            'source' => 'none',
            'matched_node_id' => null,
        ]];
    }

    private function redirectToCrud(string $anchor, string $status): RedirectResponse
    {
        return redirect()->to('/admin#' . $anchor)->with('status', $status);
    }

    public function saveMySignature(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signature_data_url' => ['nullable', 'string', 'max:400000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/'],
            'clear_signature' => ['nullable', 'boolean'],
            'current_password' => ['required', 'current_password'],
        ]);

        /** @var User|null $user */
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $clearSignature = (bool) ($validated['clear_signature'] ?? false);
        $actorIp = mb_substr((string) ($request->ip() ?? ''), 0, 45);
        $actorUserAgent = mb_substr((string) ($request->userAgent() ?? ''), 0, 512);

        if (!$clearSignature) {
            $signatureDataUrl = trim((string) ($validated['signature_data_url'] ?? ''));
            if ($signatureDataUrl === '') {
                return redirect()->back()->with('error', 'Dibuja una firma antes de guardar.');
            }

            $base64Payload = preg_replace('/^data:image\/png;base64,/', '', $signatureDataUrl);
            $binarySignature = base64_decode((string) $base64Payload, true);
            if ($binarySignature === false) {
                return redirect()->back()->with('error', 'La firma enviada no tiene un formato válido.');
            }

            $signatureHash = hash('sha256', $binarySignature);

            $user->forceFill([
                'signature_data_url' => $signatureDataUrl,
                'signature_updated_at' => now(),
                'signature_hash' => $signatureHash,
                'signature_last_ip' => $actorIp !== '' ? $actorIp : null,
                'signature_last_user_agent' => $actorUserAgent !== '' ? $actorUserAgent : null,
            ])->save();

            return redirect()->back()->with('status', 'Firma digital guardada correctamente.');
        }

        $user->forceFill([
            'signature_data_url' => null,
            'signature_updated_at' => now(),
            'signature_hash' => null,
            'signature_last_ip' => $actorIp !== '' ? $actorIp : null,
            'signature_last_user_agent' => $actorUserAgent !== '' ? $actorUserAgent : null,
        ])->save();

        return redirect()->back()->with('status', 'Firma digital eliminada correctamente.');
    }

    // ── User Management ───────────────────────────────────────────────────

    public function indexUsers(Request $request): View
    {
        $users    = User::query()->with(['branch', 'branchScopes'])->orderBy('name')->get();
        $branches = Branch::query()->orderBy('name')->get();
        $permissionProfiles = (array) config('tenant_permissions.profiles', []);

        return view('tenant.admin.users', compact('users', 'branches', 'permissionProfiles'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_name'              => ['required', 'string', 'max:255'],
            'user_email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_password'          => [
                Rule::requiredIf(fn () => $request->input('user_auth_source', 'local') === 'local'),
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'user_role'              => ['required', 'in:admin,user'],
            'user_branch_id'         => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'user_branch_scope_ids'      => ['nullable', 'array'],
            'user_branch_scope_ids.*'    => ['integer', 'exists:branches,id'],
            'user_auth_source'       => ['required', 'in:local,ad'],
            'user_is_active'         => ['required', 'boolean'],
            'user_access_profile'    => ['nullable', 'string', Rule::in(array_keys((array) config('tenant_permissions.profiles', [])))],
        ]);

        $branchScopeIds = collect((array) ($validated['user_branch_scope_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        if (!empty($validated['user_branch_id'])) {
            $branchScopeIds->push((int) $validated['user_branch_id']);
        }

        $branchScopeIds = $branchScopeIds->unique()->values();

        if ($validated['user_role'] === 'user' && $branchScopeIds->isEmpty()) {
            throw ValidationException::withMessages([
                'user_branch_scope_ids' => 'Los usuarios con rol Usuario deben tener al menos una sede asignada.',
            ]);
        }

        $passwordToStore = $validated['user_password'] ?? Str::random(40);

        $user = User::query()->create([
            'name'      => $validated['user_name'],
            'email'     => $validated['user_email'],
            'password'  => bcrypt($passwordToStore),
            'role'      => $validated['user_role'],
            'branch_id' => $validated['user_branch_id'] ?? null,
            'auth_source' => $validated['user_auth_source'],
            'is_active' => (bool) $validated['user_is_active'],
            'access_profile' => $validated['user_access_profile'] ?? null,
        ]);

        $user->branchScopes()->sync($branchScopeIds->all());

        return redirect()->to('/admin/users')->with('status', 'Usuario creado correctamente.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'user_name'      => ['required', 'string', 'max:255'],
            'user_email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'user_password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'user_role'      => ['required', 'in:admin,user'],
            'user_branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'user_branch_scope_ids' => ['nullable', 'array'],
            'user_branch_scope_ids.*' => ['integer', 'exists:branches,id'],
            'user_auth_source' => ['required', 'in:local,ad'],
            'user_is_active' => ['required', 'boolean'],
            'user_access_profile' => ['nullable', 'string', Rule::in(array_keys((array) config('tenant_permissions.profiles', [])))],
        ]);

        $branchScopeIds = collect((array) ($validated['user_branch_scope_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        if (!empty($validated['user_branch_id'])) {
            $branchScopeIds->push((int) $validated['user_branch_id']);
        }

        $branchScopeIds = $branchScopeIds->unique()->values();

        if ($validated['user_role'] === 'user' && $branchScopeIds->isEmpty()) {
            throw ValidationException::withMessages([
                'user_branch_scope_ids' => 'Los usuarios con rol Usuario deben tener al menos una sede asignada.',
            ]);
        }

        $data = [
            'name'      => $validated['user_name'],
            'email'     => $validated['user_email'],
            'role'      => $validated['user_role'],
            'branch_id' => $validated['user_branch_id'] ?? null,
            'auth_source' => $validated['user_auth_source'],
            'is_active' => (bool) $validated['user_is_active'],
            'access_profile' => $validated['user_access_profile'] ?? null,
        ];

        if (!empty($validated['user_password'])) {
            $data['password'] = bcrypt($validated['user_password']);
        }

        $user->update($data);
        $user->branchScopes()->sync($branchScopeIds->all());

        return redirect()->to('/admin/users')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->to('/admin/users')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->to('/admin/users')->with('status', 'Usuario eliminado correctamente.');
    }

    public function sendPasswordResetLink(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->to('/admin/users')->with('error', 'No es necesario enviarte enlace de restablecimiento desde esta opción.');
        }

        if (($user->auth_source ?? 'local') !== 'local') {
            return redirect()->to('/admin/users')->with('error', 'Los usuarios de Active Directory deben restablecer su contraseña desde AD.');
        }

        if (!($user->is_active ?? true)) {
            return redirect()->to('/admin/users')->with('error', 'El usuario está inactivo. Actívalo antes de enviar el enlace.');
        }

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->to('/admin/users')->with('status', 'Enlace de restablecimiento enviado correctamente.');
        }

        return redirect()->to('/admin/users')->with('error', __($status));
    }

    /* ─────────────────────────────────────────────────────────────────────
     |  AD Import
     | ──────────────────────────────────────────────────────────────────── */

    public function showAdImport(): View
    {
        $branches           = Branch::query()->orderBy('name')->get();
        $permissionProfiles = (array) config('tenant_permissions.profiles', []);
        return view('tenant.admin.ad-import', compact('branches', 'permissionProfiles'));
    }

    public function fetchAdUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host'      => ['required', 'string', 'max:255'],
            'port'      => ['required', 'integer', 'min:1', 'max:65535'],
            'use_ssl'   => ['nullable', 'boolean'],
            'base_dn'   => ['required', 'string', 'max:500'],
            'bind_dn'   => ['required', 'string', 'max:500'],
            'bind_pass' => ['required', 'string'],
            'filter'    => ['nullable', 'string', 'max:500'],
        ]);

        if (!function_exists('ldap_connect')) {
            return response()->json(['error' => 'La extensión PHP LDAP no está habilitada en este servidor.'], 422);
        }

        $scheme = !empty($validated['use_ssl']) ? 'ldaps' : 'ldap';
        $uri    = "{$scheme}://{$validated['host']}:{$validated['port']}";

        $conn = @ldap_connect($uri);
        if (!$conn) {
            return response()->json(['error' => 'No se pudo crear la conexión LDAP. Verifica el host y puerto.'], 422);
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 8);

        if (!@ldap_bind($conn, $validated['bind_dn'], $validated['bind_pass'])) {
            $err = ldap_error($conn);
            ldap_close($conn);
            return response()->json(['error' => "Autenticación fallida: {$err}"], 422);
        }

        $filter = trim($validated['filter'] ?? '') !== ''
            ? $validated['filter']
            : '(&(objectClass=user)(mail=*)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';

        $attrs  = ['displayName', 'cn', 'mail', 'userPrincipalName', 'sAMAccountName', 'department', 'userAccountControl'];
        $result = @ldap_search($conn, $validated['base_dn'], $filter, $attrs, 0, 1000);

        if (!$result) {
            $err = ldap_error($conn);
            ldap_close($conn);
            return response()->json(['error' => "Error en la búsqueda LDAP: {$err}"], 422);
        }

        $entries = ldap_get_entries($conn, $result);
        ldap_close($conn);

        $users = [];
        for ($i = 0; $i < $entries['count']; $i++) {
            $e     = $entries[$i];
            $email = $e['mail'][0] ?? ($e['userprincipalname'][0] ?? null);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $uac      = (int) ($e['useraccountcontrol'][0] ?? 0);
            $disabled = ($uac & 2) !== 0;

            $users[] = [
                'name'           => $e['displayname'][0] ?? ($e['cn'][0] ?? $email),
                'email'          => strtolower(trim($email)),
                'username'       => $e['samaccountname'][0] ?? '',
                'department'     => $e['department'][0] ?? '',
                'disabled_in_ad' => $disabled,
                'exists_locally' => User::where('email', strtolower(trim($email)))->exists(),
            ];
        }

        usort($users, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $request->session()->put(
            'ad_import_candidates',
            collect($users)
                ->mapWithKeys(fn ($user) => [
                    strtolower((string) ($user['email'] ?? '')) => [
                        'name'  => (string) ($user['name'] ?? ''),
                        'email' => strtolower((string) ($user['email'] ?? '')),
                    ],
                ])
                ->filter(fn ($user, $email) => !empty($email) && !empty($user['email']))
                ->all()
        );

        return response()->json(['users' => $users, 'count' => count($users)]);
    }

    public function importAdUsers(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected_emails'             => ['required', 'array', 'min:1'],
            'selected_emails.*'           => ['required', 'email'],
            'import_role'                 => ['required', 'in:admin,user'],
            'import_profile'              => ['nullable', 'string', Rule::in(array_merge([''], array_keys((array) config('tenant_permissions.profiles', []))))],
            'import_is_active'            => ['required', 'boolean'],
            'import_branch_scope_ids'     => ['nullable', 'array'],
            'import_branch_scope_ids.*'   => ['integer', 'exists:branches,id'],
        ]);

        $adCandidates = (array) $request->session()->get('ad_import_candidates', []);
        if (empty($adCandidates)) {
            return redirect()->back()->withErrors([
                'selected_emails' => 'La sesión de importación expiró. Vuelve a consultar usuarios desde AD.',
            ]);
        }

        $branchScopeIds = collect((array) ($validated['import_branch_scope_ids'] ?? []))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values();

        $imported = 0;
        $skipped  = 0;

        foreach ((array) $validated['selected_emails'] as $selectedEmail) {
            $email = strtolower(trim((string) $selectedEmail));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $adUser = $adCandidates[$email] ?? null;
            if (!$adUser) {
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $user = User::create([
                'name'           => $adUser['name'] ?? $email,
                'email'          => $email,
                'password'       => bcrypt(Str::random(40)),
                'role'           => $validated['import_role'],
                'auth_source'    => 'ad',
                'is_active'      => (bool) $validated['import_is_active'],
                'access_profile' => $validated['import_profile'] ?? null,
            ]);

            if ($branchScopeIds->isNotEmpty()) {
                $user->branchScopes()->sync($branchScopeIds->all());
            }

            $imported++;
        }

        $request->session()->forget('ad_import_candidates');

        $msg = "Importación completada: {$imported} usuario(s) importados desde Active Directory.";
        if ($skipped > 0) {
            $msg .= " {$skipped} omitido(s) (ya existen o email inválido).";
        }

        return redirect()->to('/admin/users')->with('status', $msg);
    }
}
