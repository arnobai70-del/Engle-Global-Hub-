<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only points ledger entry (positive to award, negative to deduct).
 */
class RewardTransaction extends Model
{
    /** @var list<string> */
    protected $fillable = ['user_id', 'reward_program_id', 'points', 'reason', 'expires_at', 'created_by_user_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['points' => 'integer', 'expires_at' => 'datetime'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(RewardProgram::class, 'reward_program_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
