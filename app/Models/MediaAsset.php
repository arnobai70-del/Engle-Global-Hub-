<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * An image in the media library. Office gallery images appear on the About
 * page only when published.
 */
class MediaAsset extends Model
{
    /** @var array<string, string> */
    public const COLLECTIONS = [
        'general' => 'General',
        'office_gallery' => 'Office environment gallery',
        'partner_logos' => 'Partner institution logos',
    ];

    /** @var list<string> */
    protected $fillable = ['collection', 'path', 'original_name', 'mime_type', 'size_bytes', 'width', 'height', 'alt_text', 'caption', 'is_published', 'sort_order', 'uploaded_by_user_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'size_bytes' => 'integer', 'width' => 'integer', 'height' => 'integer', 'sort_order' => 'integer'];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * @param  Builder<MediaAsset>  $query
     */
    public function scopePublishedIn(Builder $query, string $collection): void
    {
        $query->where('collection', $collection)->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }
}
