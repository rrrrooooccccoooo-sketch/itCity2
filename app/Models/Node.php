<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'physical_space_id',
        'node_type_id',
        'name',
        'code',
        'floor',
        'room',
        'ip_address',
        'layout_x',
        'layout_y',
        'mac_address',
        'cable_type',
        'status',
        'is_monitored',
        'details',
    ];

    protected $casts = [
        'is_monitored' => 'boolean',
        'layout_x' => 'float',
        'layout_y' => 'float',
        'details' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function nodeType(): BelongsTo
    {
        return $this->belongsTo(NodeType::class);
    }

    public function physicalSpace(): BelongsTo
    {
        return $this->belongsTo(PhysicalSpace::class);
    }

    public function softwareSystems(): HasMany
    {
        return $this->hasMany(SoftwareSystem::class);
    }

    public function outgoingRelations(): HasMany
    {
        return $this->hasMany(NodeRelation::class, 'from_node_id');
    }

    public function incomingRelations(): HasMany
    {
        return $this->hasMany(NodeRelation::class, 'to_node_id');
    }

    public function probes(): HasMany
    {
        return $this->hasMany(NodeProbe::class);
    }

    public function computerAssets(): HasMany
    {
        return $this->hasMany(ComputerAsset::class);
    }

    public function observedDevices(): HasMany
    {
        return $this->hasMany(NodeObservedDevice::class);
    }
}
