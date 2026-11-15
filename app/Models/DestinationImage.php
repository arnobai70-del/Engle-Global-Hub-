<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationImage extends Model
{
    /** @var list<string> */
    protected $fillable = ['path', 'alt_text', 'sort_order'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
