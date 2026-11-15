<?php

namespace App\Services\Consultancy;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentUpdated;
use App\Services\ActivityRecorder;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates and updates consultation appointments, preventing a staff member
 * from being double-booked for confirmed appointments.
 */
class AppointmentScheduler
{
    public function __construct(private ActivityRecorder $activity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, string $status, ?User $actor): Appointment
    {
        return DB::transaction(function () use ($attributes, $status, $actor): Appointment {
            $appointment = new Appointment($attributes);
            $appointment->status = $status;
            $appointment->internal_notes = $attributes['internal_notes'] ?? null;
            $this->guardAgainstClash($appointment);
            $appointment->save();
            $this->activity->record($actor, 'appointment.created', $appointment, ['status' => $status]);

            return $appointment;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Appointment $appointment, array $attributes, User $actor): Appointment
    {
        $previousStatus = $appointment->status;
        $previousTime = $appointment->scheduled_at?->toIso8601String();

        DB::transaction(function () use ($appointment, $attributes, $actor): void {
            $appointment->fill($attributes);
            $appointment->status = $attributes['status'] ?? $appointment->status;
            $appointment->internal_notes = $attributes['internal_notes'] ?? $appointment->internal_notes;
            $this->guardAgainstClash($appointment);
            $appointment->save();
            $this->activity->record($actor, 'appointment.updated', $appointment, ['status' => $appointment->status]);
        });

        $changedForStudent = $previousStatus !== $appointment->status || $previousTime !== $appointment->scheduled_at?->toIso8601String();
        if ($changedForStudent && in_array($appointment->status, [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CANCELLED], true)) {
            $appointment->application?->student->portalUser()?->notify(new AppointmentUpdated($appointment));
        }

        return $appointment;
    }

    private function guardAgainstClash(Appointment $appointment): void
    {
        if ($appointment->status !== Appointment::STATUS_CONFIRMED || $appointment->staff_user_id === null) {
            return;
        }

        $start = $appointment->scheduled_at;
        $end = $start->copy()->addMinutes($appointment->duration_minutes);

        $clash = Appointment::query()
            ->where('staff_user_id', $appointment->staff_user_id)
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->when($appointment->exists, fn ($query) => $query->whereKeyNot($appointment->getKey()))
            ->whereBetween('scheduled_at', [$start->copy()->subDay(), $end])
            ->get(['scheduled_at', 'duration_minutes'])
            ->contains(fn (Appointment $other): bool => $this->overlaps($start, $end, $other->scheduled_at, $other->scheduled_at->copy()->addMinutes($other->duration_minutes)));

        if ($clash) {
            throw ValidationException::withMessages(['scheduled_at' => 'This staff member already has a confirmed appointment at that time.']);
        }
    }

    private function overlaps(CarbonInterface $start, CarbonInterface $end, CarbonInterface $otherStart, CarbonInterface $otherEnd): bool
    {
        return $start->lt($otherEnd) && $otherStart->lt($end);
    }
}
