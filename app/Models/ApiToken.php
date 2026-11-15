<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A bearer token for the mobile/API clients. Only its SHA-256 hash is stored.
 */
class ApiToken extends Model
{
    public const LIFETIME_DAYS = 90;

    /** @var list<string> */
    protected $fillable = ['user_id', 'name', 'token_hash', 'expires_at'];

    /** @var list<string> */
    protected $hidden = ['token_hash'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'last_used_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Creates a token and returns it with the plain value, which is shown once.
     *
     * @return array{0: self, 1: string}
     */
    public static function issue(User $user, string $name): array
    {
        $plain = Str::random(48);
        $token = self::query()->create([
            'user_id' => $user->getKey(),
            'name' => $name,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addDays(self::LIFETIME_DAYS),
        ]);

        return [$token, $plain];
    }

    public static function findValid(string $plain): ?self
    {
        return self::query()->where('token_hash', hash('sha256', $plain))->where('expires_at', '>', now())->first();
    }
}
