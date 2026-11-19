<?php

namespace App\Services;

/**
 * Formats one stored phone number for the places it is used: E.164 for tel: links
 * (+8801953626481), digits for wa.me links (8801953626481) and a readable form for
 * display (+880 1953-626481). Bangladeshi numbers written as 01…, 8801… or +880 1…
 * are all understood; the stored value itself is never changed.
 */
final class PhoneNumber
{
    /** E.164 (+ and digits), or null when the value is not a usable number. */
    public static function e164(?string $value): ?string
    {
        $raw = trim((string) $value);
        $digits = (string) preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }

        if (preg_match('/^01[3-9]\d{8}$/', $digits) === 1) {
            $digits = '88'.$digits;
        } elseif (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (! str_starts_with($raw, '+') && ! str_starts_with($digits, '880')) {
            return null;
        }

        return strlen($digits) >= 8 && strlen($digits) <= 15 ? '+'.$digits : null;
    }

    /** Digits only, for https://wa.me/… links. */
    public static function whatsappDigits(?string $value): ?string
    {
        $e164 = self::e164($value);

        return $e164 === null ? null : substr($e164, 1);
    }

    /**
     * Readable form: Bangladeshi mobiles as "+880 1953-626481"; other valid numbers as E.164;
     * anything unrecognised exactly as stored.
     */
    public static function display(?string $value): string
    {
        $e164 = self::e164($value);
        if ($e164 !== null && preg_match('/^\+880(1\d{3})(\d{6})$/', $e164, $parts) === 1) {
            return '+880 '.$parts[1].'-'.$parts[2];
        }

        return $e164 ?? trim((string) $value);
    }
}
