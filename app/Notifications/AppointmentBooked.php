<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation email for a consultation booked on the public booking page.
 * Sent in the language the visitor used to book.
 */
class AppointmentBooked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment, public string $reference, public ?string $place)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->appointment->scheduled_at->translatedFormat('l, j F Y, g:i A');
        $mode = __(Appointment::MODES[$this->appointment->mode] ?? $this->appointment->mode);

        $message = (new MailMessage)
            ->subject(__('Your free consultation is booked: :when', ['when' => $when]))
            ->greeting(__('Hello :name,', ['name' => $this->appointment->enquiry?->full_name ?? '']))
            ->line(__('Your free consultation with Eagle Global Hub is booked.'))
            ->line(__('When: :when (Dhaka time), :minutes minutes.', ['when' => $when, 'minutes' => $this->appointment->duration_minutes]))
            ->line(__('How: :mode', ['mode' => $mode]));

        if ($this->place !== null) {
            $message->line(__('Where: :place', ['place' => $this->place]));
        }

        return $message
            ->line(__('Your reference is :reference. If you need another time, reply to this email or call us.', ['reference' => $this->reference]))
            ->action(__('Book another time'), route('appointments.book'));
    }
}
