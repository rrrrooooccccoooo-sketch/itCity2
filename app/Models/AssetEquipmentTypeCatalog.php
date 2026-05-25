<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetEquipmentTypeCatalog extends Model
{
    use HasFactory;

    protected $table = 'asset_equipment_type_catalogs';

    protected $fillable = [
        'key',
        'label',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
