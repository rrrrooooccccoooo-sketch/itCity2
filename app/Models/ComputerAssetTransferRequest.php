<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComputerAssetTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_asset_id',
        'status',
        'priority',
        'requested_by_user_id',
        'requested_by_name',
        'requested_from_branch_id',
        'requested_to_branch_id',
        'requested_to_user_id',
        'requested_to_user_name',
        'reason',
        'note',
        'requested_at',
        'decided_by_user_id',
        'decided_by_name',
        'decided_at',
        'decision_note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function computerAsset(): BelongsTo
    {
        return $this->belongsTo(ComputerAsset::class);
    }

    public function requestedFromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requested_from_branch_id');
    }

    public function requestedToBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requested_to_branch_id');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function requestedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_to_user_id');
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
