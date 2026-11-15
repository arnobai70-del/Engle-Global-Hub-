<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject('Consultation appointment '.$this->appointment->statusLabel());

        return $this->appointment->status === Appointment::STATUS_CANCELLED
            ? $message->line('Your consultation appointment on '.$this->when().' has been cancelled.')->action('View your application', $this->url(true))
            : $message->line('Your consultation appointment is confirmed for '.$this->when().' ('.(Appointment::MODES[$this->appointment->mode] ?? $this->appointment->mode).').')
                ->action('View details', $this->url(true));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Appointment '.strtolower($this->appointment->statusLabel()),
            'body' => $this->when(),
            'url' => $this->url(false),
        ];
    }

    private function when(): string
    {
        return $this->appointment->scheduled_at->format('j M Y, g:i A');
    }

    private function url(bool $absolute): string
    {
        return route('student.applications.show', $this->appointment->student_application_id, $absolute);
    }
}
