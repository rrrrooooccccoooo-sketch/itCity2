<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodeProbe extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'reachable',
        'latency_ms',
        'checked_ports',
        'open_ports',
        'message',
        'probed_at',
    ];

    protected $casts = [
        'reachable' => 'boolean',
        'latency_ms' => 'float',
        'checked_ports' => 'array',
        'open_ports' => 'array',
        'probed_at' => 'datetime',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
