<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReport extends Model
{
    /** @var list<string> */
    protected $fillable = ['forum_post_id', 'user_id', 'reason'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
