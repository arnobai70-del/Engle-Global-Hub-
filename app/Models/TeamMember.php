<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A management or staff profile shown on the website once published.
 */
class TeamMember extends Model
{
    /** @var list<string> */
    protected $fillable = ['name', 'role_title', 'bio', 'sort_order', 'is_published'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @param  Builder<TeamMember>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }
}
