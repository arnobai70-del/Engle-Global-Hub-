<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Writes the admin activity log. Callers pass only non-sensitive context;
 * keys that look sensitive are dropped as a safety net.
 */
class ActivityRecorder
{
    /** @var list<string> */
    private const SENSITIVE_KEY_FRAGMENTS = ['password', 'secret', 'token', 'key', 'passport', 'note', 'body', 'path'];

    public function __construct(private Request $request) {}

    /**
     * @param  array<string, scalar|null>  $properties
     */
    public function record(?User $actor, string $action, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $this->redact($properties) ?: null,
            'ip_address' => $this->request->ip(),
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $properties
     * @return array<string, scalar|null>
     */
    private function redact(array $properties): array
    {
        return array_filter(
            $properties,
            function (mixed $value, string $key): bool {
                foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
                    if (str_contains(strtolower($key), $fragment)) {
                        return false;
                    }
                }

                return is_scalar($value) || $value === null;
            },
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
