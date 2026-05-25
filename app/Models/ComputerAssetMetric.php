<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComputerAssetMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_asset_id',
        'captured_at',
        'cpu_usage_percent',
        'memory_usage_percent',
        'disk_usage_percent',
        'uptime_seconds',
        'net_rx_kbps',
        'net_tx_kbps',
        'process_count',
        'details',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'cpu_usage_percent' => 'float',
        'memory_usage_percent' => 'float',
        'disk_usage_percent' => 'float',
        'uptime_seconds' => 'integer',
        'net_rx_kbps' => 'float',
        'net_tx_kbps' => 'float',
        'process_count' => 'integer',
        'details' => 'array',
    ];

    public function computerAsset(): BelongsTo
    {
        return $this->belongsTo(ComputerAsset::class);
    }
}
