<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceVendorProfileAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_vendor_profile_id',
        'supplier_key',
        'supplier_name',
        'action',
        'changed_by_user_id',
        'changed_by_name',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
