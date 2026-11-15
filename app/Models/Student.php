<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_ARCHIVED = 'archived';

    /** @var list<string> */
    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_ARCHIVED];

    /** @var list<string> */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'country_id',
        'date_of_birth', 'reference_code', 'status', 'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['country_id' => 'integer', 'date_of_birth' => 'date'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The portal login linked to this profile. Set by staff workflows only, never mass-assigned.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    /**
     * The login that may see this profile in the student portal: the linked
     * account, or else a verified account with the same email that is not
     * linked to another profile.
     */
    public function portalUser(): ?User
    {
        if ($this->user_id !== null) {
            return $this->user;
        }

        return User::query()
            ->where('email', $this->email)
            ->whereNotNull('email_verified_at')
            ->whereDoesntHave('student')
            ->first();
    }
}
