<?php

namespace App\Models;

use App\Enums\BusinessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A promotion or special offer written by staff. Public only while published
 * and within its validity dates.
 */
class Offer extends Model
{
    /** @var list<string> */
    protected $fillable = ['title', 'slug', 'service', 'summary', 'terms', 'valid_from', 'valid_until', 'is_published', 'sort_order'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'service' => BusinessService::class,
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Offer>  $query
     */
    public function scopeCurrent(Builder $query): void
    {
        $today = now()->toDateString();

        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn (Builder $query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->orderBy('sort_order')->orderByDesc('id');
    }
}
