<?php

namespace App\Models;

use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use Billable, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'company_name',
        'logo_url',
        'plan',
        'is_active',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];

    public function stripeName(): ?string
    {
        return $this->company_name ?? data_get($this->data, 'company_name');
    }

    public function stripeEmail(): ?string
    {
        return data_get($this->data, 'billing_email');
    }
}
