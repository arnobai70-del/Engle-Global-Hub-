<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A loyalty programme defined by staff. Inactive until its terms and earning
 * rules are written and it is published. No monetary value is implied.
 */
class RewardProgram extends Model
{
    /** @var list<string> */
    protected $fillable = ['name', 'slug', 'description', 'terms', 'earning_rules', 'points_expire_after_days', 'starts_on', 'ends_on', 'is_published'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_published' => 'boolean', 'points_expire_after_days' => 'integer'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RewardTransaction::class);
    }

    /**
     * @param  Builder<RewardProgram>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $today = now()->toDateString();
        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', $today))
            ->where(fn (Builder $query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $today));
    }

    public function isReadyToPublish(): bool
    {
        return trim((string) $this->terms) !== '' && trim((string) $this->earning_rules) !== '';
    }
}
