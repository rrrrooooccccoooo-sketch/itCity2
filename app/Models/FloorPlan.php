<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloorPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'physical_space_id',
        'name',
        'file_path',
        'file_type',
        'mime_type',
        'overlay_points',
        'meta',
    ];

    protected $casts = [
        'overlay_points' => 'array',
        'meta' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function physicalSpace(): BelongsTo
    {
        return $this->belongsTo(PhysicalSpace::class);
    }
}
