<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceVendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_key',
        'supplier_name',
        'known_brands',
        'known_models',
        'serial_prefixes',
        'last_used_at',
    ];

    protected $casts = [
        'known_brands' => 'array',
        'known_models' => 'array',
        'serial_prefixes' => 'array',
        'last_used_at' => 'datetime',
    ];
}
