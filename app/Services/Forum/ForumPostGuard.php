<?php

namespace App\Services\Forum;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Spam checks and personal-information masking for forum posts.
 */
class ForumPostGuard
{
    public const MAX_LINKS = 2;

    /**
     * Masks email addresses, phone numbers and passport-style numbers so
     * students do not publish personal details by mistake.
     */
    public function redact(string $text): string
    {
        $text = trim(strip_tags($text));
        $text = (string) preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email removed]', $text);
        $text = (string) preg_replace('/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/', '[phone removed]', $text);

        return (string) preg_replace('/\b[A-Z]{1,2}\d{7,8}\b/', '[document number removed]', $text);
    }

    /**
     * @throws ValidationException when the post looks like spam
     */
    public function assertNotSpam(string $body, User $author): void
    {
        if (preg_match_all('#https?://#i', $body) > self::MAX_LINKS) {
            throw ValidationException::withMessages(['body' => 'Posts may contain at most '.self::MAX_LINKS.' links.']);
        }

        $duplicate = ForumPost::query()->where('user_id', $author->getKey())
            ->where('body_hash', self::hash($body))->where('created_at', '>=', now()->subDay())->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['body' => 'You have already posted this message.']);
        }
    }

    public static function hash(string $body): string
    {
        return hash('sha256', mb_strtolower(preg_replace('/\s+/u', ' ', trim($body)) ?? ''));
    }
}
