<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageBlock extends Model
{
    protected $fillable = [
        'section',
        'key',
        'title',
        'subtitle',
        'body',
        'image_path',
        'image_alt',
        'icon',
        'url',
        'cta_label',
        'meta',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
