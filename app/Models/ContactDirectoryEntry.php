<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A business phone line (Affiliator contact format): up to ten phone numbers
 * plus one 24/7 IP calling number, entered by staff.
 */
class ContactDirectoryEntry extends Model
{
    public const KIND_PHONE = 'phone';

    public const KIND_IP_24X7 = 'ip_24x7';

    public const MAX_PHONES = 10;

    public const MAX_IP_LINES = 1;

    /** @var list<string> */
    protected $fillable = ['kind', 'label', 'contact_name', 'department', 'phone', 'availability', 'is_active', 'is_public', 'sort_order'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_public' => 'boolean', 'sort_order' => 'integer'];
    }

    /**
     * @param  Builder<ContactDirectoryEntry>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_active', true)->where('is_public', true)->orderByRaw("case when kind = 'ip_24x7' then 0 else 1 end")->orderBy('sort_order');
    }
}
