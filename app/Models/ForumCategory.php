<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A forum section. Visibility: public (anyone can read), members (signed-in
 * users) or staff (back-office roles only).
 */
class ForumCategory extends Model
{
    /** @var list<string> */
    public const VISIBILITIES = ['public', 'members', 'staff'];

    /** @var list<string> */
    protected $fillable = ['name', 'slug', 'description', 'visibility', 'requires_approval', 'is_active', 'sort_order'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['requires_approval' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function isVisibleTo(?User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($this->visibility) {
            'public' => true,
            'members' => $user !== null,
            'staff' => $user?->isBackOffice() ?? false,
            default => false,
        };
    }
}
