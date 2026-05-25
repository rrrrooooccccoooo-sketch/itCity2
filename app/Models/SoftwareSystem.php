<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'name',
        'version',
        'vendor',
        'contact_email',
        'contact_phone',
        'project_name',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
