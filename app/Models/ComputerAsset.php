<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

class ComputerAsset extends Model
{
    use HasFactory;

    public static function defaultEquipmentTypeOptions(): array
    {
        return [
            'desktop' => 'Desktop',
            'laptop' => 'Laptop',
            'server' => 'Server',
            'workstation' => 'Workstation',
            'aio' => 'All in One',
            'thin-client' => 'Thin Client',
            'monitor' => 'Monitor',
            'headset' => 'Diadema',
            'phone' => 'Telefono',
            'other' => 'Otro',
        ];
    }

    public static function defaultStatusOptions(): array
    {
        return [
            'in_use' => 'En uso',
            'stock' => 'En stock',
            'pending_assignment' => 'Pendiente de asignación',
            'repair' => 'En reparacion',
            'retired' => 'Retirado',
        ];
    }

    public static function equipmentTypeOptions(): array
    {
        if (Schema::hasTable('asset_equipment_type_catalogs')) {
            $items = AssetEquipmentTypeCatalog::query()
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->pluck('label', 'key')
                ->toArray();

            if (!empty($items)) {
                return $items;
            }
        }

        return static::defaultEquipmentTypeOptions();
    }

    public static function storageTypeOptions(): array
    {
        return [
            'hdd' => 'HDD',
            'ssd' => 'SSD',
            'nvme' => 'NVMe',
            'hybrid' => 'Hybrid',
            'other' => 'Otro',
        ];
    }

    public static function statusOptions(): array
    {
        $options = static::defaultStatusOptions();

        if (Schema::hasTable('asset_status_catalogs')) {
            $items = AssetStatusCatalog::query()
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->pluck('label', 'key')
                ->toArray();

            if (!empty($items)) {
                return array_merge($options, $items);
            }
        }

        return $options;
    }

    protected $fillable = [
        'branch_id',
        'node_id',
        'equipment_model_id',
        'equipment_type',
        'asset_tag',
        'hostname',
        'assigned_user',
        'brand',
        'model',
        'serial_number',
        'cpu',
        'ram_gb',
        'storage_type',
        'storage_gb',
        'operating_system',
        'office_version',
        'purchase_date',
        'warranty_expires_at',
        'status',
        'notes',
        'details',
        'last_seen_at',
        'last_cpu_usage_percent',
        'last_memory_usage_percent',
        'last_disk_usage_percent',
        'last_uptime_seconds',
        'inventory_last_captured_at',
        'domain_name',
        'bios_version',
        'motherboard_product',
        'motherboard_serial',
        'antivirus_summary',
        'installed_programs_count',
        'hotfix_count',
        'primary_ip_address',
        'primary_mac_address',
        'operating_system_version',
        'operating_system_build',
        'primary_gpu',
        'memory_modules_count',
        'physical_disks_count',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expires_at' => 'date',
        'ram_gb' => 'integer',
        'storage_gb' => 'integer',
        'details' => 'array',
        'last_seen_at' => 'datetime',
        'last_cpu_usage_percent' => 'float',
        'last_memory_usage_percent' => 'float',
        'last_disk_usage_percent' => 'float',
        'last_uptime_seconds' => 'integer',
        'inventory_last_captured_at' => 'datetime',
        'installed_programs_count' => 'integer',
        'hotfix_count' => 'integer',
        'memory_modules_count' => 'integer',
        'physical_disks_count' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function equipmentModel(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_model_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ComputerAssetMetric::class);
    }

    public function latestMetric(): HasOne
    {
        return $this->hasOne(ComputerAssetMetric::class)->latestOfMany('captured_at');
    }

    public function equipmentTypeLabel(): string
    {
        return static::equipmentTypeOptions()[$this->equipment_type] ?? ucfirst((string) $this->equipment_type);
    }

    public function statusLabel(): string
    {
        return static::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }
}
