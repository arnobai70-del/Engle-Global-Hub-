<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only record of a staff or user action on business data.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'properties', 'ip_address'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['properties' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
