<?php

namespace App\Models;

use App\Enums\BusinessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer review. Written only by signed-in customers and shown publicly
 * only after staff approve it.
 */
class Review extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** @var list<string> */
    protected $fillable = ['service', 'rating', 'title', 'body', 'display_name'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['service' => BusinessService::class, 'rating' => 'integer', 'moderated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
