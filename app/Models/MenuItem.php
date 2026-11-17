<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** @var array<string, string> */
    public const LOCATIONS = [
        'header' => 'Header menu',
        'footer' => 'Footer menu',
        'home_tiles' => 'Homepage service tiles',
        'header_cta' => 'Header button',
    ];

    /** @var array<string, string> */
    public const VISIBILITIES = [
        'all' => 'Everyone',
        'guests' => 'Signed-out visitors only',
        'auth' => 'Signed-in users only',
        'roles' => 'Selected roles only',
    ];

    /** Icons available for homepage tiles (see components/site/icon). */
    public const ICONS = ['cap', 'id', 'passport', 'visa', 'briefcase', 'plane', 'seat', 'bed', 'mountain', 'sun', 'group', 'train', 'bus', 'car', 'bike', 'globe', 'leaf', 'map', 'box', 'build', 'tag', 'support', 'phone'];

    /** Locations whose top-level items may be a heading with sub-items instead of a link. */
    public const NESTED_LOCATIONS = ['header', 'footer'];

    /** @var list<string> */
    protected $fillable = [
        'location', 'parent_id', 'key', 'label_en', 'label_bn', 'target', 'fragment', 'icon',
        'feature', 'visibility', 'roles', 'open_in_new_tab', 'is_active', 'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['roles' => 'array', 'is_active' => 'boolean', 'open_in_new_tab' => 'boolean', 'parent_id' => 'integer', 'sort_order' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function label(): string
    {
        if (app()->getLocale() === 'bn') {
            // Without a Bengali label, use the site translation of the English label when one exists.
            return filled($this->label_bn) ? (string) $this->label_bn : (string) __((string) $this->label_en);
        }

        return (string) $this->label_en;
    }
}
