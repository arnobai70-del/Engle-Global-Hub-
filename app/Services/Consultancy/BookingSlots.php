<?php

namespace App\Services\Consultancy;

use App\Models\Appointment;
use App\Services\SettingService;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Free consultation times for public booking, from the opening hours set in
 * Admin › Appointments › Public booking hours. Nothing is assumed: without days,
 * hours, a slot length and a booking window, public booking stays hidden.
 *
 * Times are office (Dhaka) wall-clock times, stored the same way staff enter
 * appointments in admin, so admin and public bookings line up.
 */
class BookingSlots
{
    public const GROUP = 'booking';

    public const OFFICE_TIMEZONE = 'Asia/Dhaka';

    /** ISO weekday => label. */
    public const WEEKDAYS = [6 => 'Saturday', 7 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

    public function __construct(private SettingService $settings) {}

    /**
     * @return array{days: list<int>, start: ?string, end: ?string, slot_minutes: ?int, days_ahead: ?int, notice_hours: int, modes: list<string>}
     */
    public function settings(): array
    {
        try {
            $values = $this->settings->group(self::GROUP);
        } catch (Throwable) {
            $values = [];
        }
        $list = static fn (mixed $value): array => array_values(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen'));

        return [
            'days' => array_values(array_map('intval', array_filter($list($values['days'] ?? ''), static fn (string $day): bool => isset(self::WEEKDAYS[(int) $day])))),
            'start' => $this->time($values['start'] ?? null),
            'end' => $this->time($values['end'] ?? null),
            'slot_minutes' => filled($values['slot_minutes'] ?? null) ? (int) $values['slot_minutes'] : null,
            'days_ahead' => filled($values['days_ahead'] ?? null) ? (int) $values['days_ahead'] : null,
            'notice_hours' => (int) ($values['notice_hours'] ?? 0),
            'modes' => array_values(array_intersect($list($values['modes'] ?? ''), array_keys(Appointment::MODES))),
        ];
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();

        return $settings['days'] !== [] && $settings['start'] !== null && $settings['end'] !== null && $settings['start'] < $settings['end']
            && $settings['slot_minutes'] > 0 && $settings['days_ahead'] > 0 && $settings['modes'] !== [];
    }

    public function isAvailable(): bool
    {
        return $this->isConfigured() && $this->free() !== [];
    }

    /**
     * Free slots per day: 'Y-m-d' => list of 'H:i'.
     *
     * @return array<string, list<string>>
     */
    public function free(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }
        $settings = $this->settings();
        $earliest = $this->officeNow()->addHours($settings['notice_hours']);
        $firstDay = $this->officeNow()->startOfDay();
        $lastDay = $firstDay->addDays((int) $settings['days_ahead']);
        $taken = $this->takenRanges($firstDay, $lastDay->endOfDay());

        $free = [];
        for ($day = $firstDay; $day->lessThanOrEqualTo($lastDay); $day = $day->addDay()) {
            if (! in_array($day->dayOfWeekIso, $settings['days'], true)) {
                continue;
            }
            $closing = $day->setTimeFromTimeString((string) $settings['end']);
            for ($slot = $day->setTimeFromTimeString((string) $settings['start']); $slot->addMinutes((int) $settings['slot_minutes'])->lessThanOrEqualTo($closing); $slot = $slot->addMinutes((int) $settings['slot_minutes'])) {
                if ($slot->greaterThan($earliest) && ! $this->overlaps($slot, $slot->addMinutes((int) $settings['slot_minutes']), $taken)) {
                    $free[$day->toDateString()][] = $slot->format('H:i');
                }
            }
        }

        return $free;
    }

    public function isFree(string $date, string $time): bool
    {
        return in_array($time, $this->free()[$date] ?? [], true);
    }

    /**
     * A slot as stored on the appointment (office wall-clock time in the app timezone).
     */
    public function toStored(string $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($date.' '.$time, (string) config('app.timezone'));
    }

    /**
     * "Now" in the office, expressed in the app timezone the same way stored appointments are.
     */
    private function officeNow(): CarbonImmutable
    {
        return CarbonImmutable::now(self::OFFICE_TIMEZONE)->shiftTimezone((string) config('app.timezone'));
    }

    /**
     * @return list<array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function takenRanges(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Appointment::query()->whereIn('status', [Appointment::STATUS_REQUESTED, Appointment::STATUS_CONFIRMED])
            ->whereBetween('scheduled_at', [$from->subDay(), $to])
            ->get(['scheduled_at', 'duration_minutes'])
            ->map(fn (Appointment $appointment): array => [
                CarbonImmutable::parse($appointment->scheduled_at),
                CarbonImmutable::parse($appointment->scheduled_at)->addMinutes((int) $appointment->duration_minutes),
            ])->all();
    }

    /**
     * @param  list<array{0: CarbonImmutable, 1: CarbonImmutable}>  $taken
     */
    private function overlaps(CarbonImmutable $start, CarbonImmutable $end, array $taken): bool
    {
        foreach ($taken as [$takenStart, $takenEnd]) {
            if ($start->lessThan($takenEnd) && $takenStart->lessThan($end)) {
                return true;
            }
        }

        return false;
    }

    private function time(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : null;
    }
}
