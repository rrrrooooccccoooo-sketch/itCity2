<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodeRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_node_id',
        'to_node_id',
        'relation_type',
        'preferred_weight',
        'from_endpoint',
        'to_endpoint',
        'is_inter_campus',
        'vpn_profile',
        'notes',
    ];

    protected $casts = [
        'is_inter_campus' => 'boolean',
        'preferred_weight' => 'integer',
    ];

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'to_node_id');
    }
}
