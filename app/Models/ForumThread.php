<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumThread extends Model
{
    /** @var list<string> */
    protected $fillable = ['forum_category_id', 'user_id', 'title', 'slug'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_locked' => 'boolean', 'is_pinned' => 'boolean', 'last_posted_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class)->orderBy('id');
    }
}
