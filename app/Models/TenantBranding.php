<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantBranding extends Model
{
    protected $fillable = [
        'company_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'text_color',
    ];
}
