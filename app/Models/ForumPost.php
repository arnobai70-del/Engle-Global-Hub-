<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    public const STATUS_VISIBLE = 'visible';

    public const STATUS_PENDING = 'pending';

    public const STATUS_HIDDEN = 'hidden';

    /** @var list<string> */
    protected $fillable = ['forum_thread_id', 'user_id', 'body', 'body_hash'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ForumReport::class);
    }
}
