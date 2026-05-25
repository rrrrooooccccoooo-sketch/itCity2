<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'auth_source',
        'is_active',
        'access_profile',
        'permission_overrides',
        'signature_data_url',
        'signature_updated_at',
        'signature_hash',
        'signature_last_ip',
        'signature_last_user_agent',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'branch_id' => 'integer',
        'is_active' => 'boolean',
        'permission_overrides' => 'array',
        'signature_updated_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchScopes(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branch_scopes', 'user_id', 'branch_id')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLocalAuth(): bool
    {
        return ($this->auth_source ?? 'local') === 'local';
    }

    public function hasTenantPermission(string $permission): bool
    {
        if (!($this->is_active ?? true)) {
            return false;
        }

        if ($this->role === 'admin' && empty($this->access_profile)) {
            return true;
        }

        $permissions = $this->resolvedTenantPermissions();

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function resolvedTenantPermissions(): array
    {
        $profiles = (array) config('tenant_permissions.profiles', []);
        $profileKey = (string) ($this->access_profile ?? '');
        $profilePermissions = (array) data_get($profiles, $profileKey . '.permissions', []);

        $overrides = $this->permission_overrides;
        if (!is_array($overrides)) {
            $overrides = [];
        }

        $explicitAllow = array_values(array_filter(
            (array) ($overrides['allow'] ?? []),
            fn ($item) => is_string($item) && $item !== ''
        ));

        $explicitDeny = array_values(array_filter(
            (array) ($overrides['deny'] ?? []),
            fn ($item) => is_string($item) && $item !== ''
        ));

        $base = array_values(array_unique(array_filter(array_merge($profilePermissions, $explicitAllow), fn ($item) => is_string($item) && $item !== '')));

        if (empty($explicitDeny)) {
            return $base;
        }

        return array_values(array_filter($base, fn ($item) => !in_array($item, $explicitDeny, true)));
    }

    public function effectiveBranchScopeIds(): ?array
    {
        $ids = [];

        if ($this->branch_id !== null) {
            $ids[] = (int) $this->branch_id;
        }

        $scopeIds = $this->relationLoaded('branchScopes')
            ? $this->branchScopes->pluck('id')->all()
            : $this->branchScopes()->pluck('branches.id')->all();

        foreach ($scopeIds as $scopeId) {
            $ids[] = (int) $scopeId;
        }

        $ids = array_values(array_unique(array_filter($ids, fn ($value) => $value > 0)));

        if ($this->isAdmin() && empty($ids)) {
            return null;
        }

        return $ids;
    }
}
