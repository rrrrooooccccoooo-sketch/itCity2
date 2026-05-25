<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodeObservedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'node_id',
        'observed_via',
        'mac_address',
        'ip_address',
        'hostname',
        'domain_name',
        'vendor_name',
        'ssid',
        'switch_port',
        'device_type',
        'is_managed',
        'first_seen_at',
        'last_seen_at',
        'meta',
    ];

    protected $casts = [
        'is_managed' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'meta' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}