<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot of a content entry taken each time it is saved.
 */
class ContentRevision extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['content_entry_id', 'user_id', 'status', 'snapshot'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['snapshot' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
