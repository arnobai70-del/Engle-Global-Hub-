<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    public const TYPE_AGENT = 'agent';

    public const TYPE_B2B_PARTNER = 'b2b_partner';

    /** @var array<string, string> */
    public const TYPES = [self::TYPE_AGENT => 'Agent', self::TYPE_B2B_PARTNER => 'Egabe B2B partner'];

    /** @var list<string> */
    protected $fillable = [
        'partner_type',
        'website_url',
        'name',
        'email',
        'phone',
        'company_name',
        'registration_number',
        'country_id',
        'status',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
