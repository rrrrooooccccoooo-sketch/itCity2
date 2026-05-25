<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentBrand extends Model
{
    protected $fillable = ['name'];

    public function equipmentModels(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'brand_id');
    }
}
