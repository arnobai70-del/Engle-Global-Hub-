<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_ARCHIVED = 'archived';

    /** @var list<string> */
    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ARCHIVED];

    /** @var list<string> */
    protected $fillable = [
        'name', 'email', 'phone', 'website_url', 'registration_number',
        'country_id', 'address', 'status', 'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['country_id' => 'integer', 'is_public' => 'boolean', 'publicly_verified_at' => 'datetime'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Active institutions the owner has verified for the public Institutions page.
     *
     * @param  Builder<Institution>  $query
     */
    public function scopePubliclyListed(Builder $query): void
    {
        $query->where('is_public', true)->where('status', self::STATUS_ACTIVE)->orderBy('name');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('name');
    }
}
