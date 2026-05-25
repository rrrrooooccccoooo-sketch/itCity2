<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'description',
        'sort_order',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }

    public function computerAssets(): HasMany
    {
        return $this->hasMany(ComputerAsset::class);
    }
}
