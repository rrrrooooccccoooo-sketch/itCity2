<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentModel extends Model
{
    protected $fillable = [
        'brand_id',
        'equipment_type',
        'name',
        'coverage_radius_min_m',
        'coverage_radius_max_m',
        'default_signal_dbm',
        'radiation_pattern',
        'mount_height_m',
        'notes',
    ];

    protected $casts = [
        'coverage_radius_min_m' => 'float',
        'coverage_radius_max_m' => 'float',
        'default_signal_dbm' => 'integer',
        'mount_height_m' => 'float',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(EquipmentBrand::class, 'brand_id');
    }

    public static function equipmentTypeOptions(): array
    {
        return [
            'access-point'  => 'Access Point (AP)',
            'switch'        => 'Switch',
            'router'        => 'Router',
            'firewall'      => 'Firewall',
            'desktop'       => 'Computadora de escritorio',
            'laptop'        => 'Laptop',
            'server'        => 'Servidor',
            'printer'       => 'Impresora',
            'phone'         => 'Teléfono / Diadema',
            'camera'        => 'Cámara IP',
            'ups'           => 'UPS',
            'other'         => 'Otro',
        ];
    }
}
