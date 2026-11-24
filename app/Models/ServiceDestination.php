<?php

namespace App\Models;

use App\Enums\BusinessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A country offered for a given service (for example a tourist visa destination).
 */
class ServiceDestination extends Model
{
    /** @var list<string> */
    protected $fillable = ['service', 'country_id', 'is_active', 'show_on_homepage', 'sort_order'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['service' => BusinessService::class, 'is_active' => 'boolean', 'show_on_homepage' => 'boolean', 'sort_order' => 'integer', 'country_id' => 'integer'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
