<?php

namespace App\Notifications;

use App\Models\StudentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student their application status changed. Carries only the
 * reference, the new status and the student-facing note.
 */
class StudentApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StudentApplication $application,
        public string $statusLabel,
        public ?string $studentNote,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Update on your study application '.$this->application->reference)
            ->line('The status of your application '.$this->application->reference.' is now: '.$this->statusLabel.'.');

        if ($this->studentNote !== null && $this->studentNote !== '') {
            $message->line('Message from your consultant: '.$this->studentNote);
        }

        return $message->action('View your application', route('student.applications.show', $this->application));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Application '.$this->application->reference.' updated',
            'body' => 'Status: '.$this->statusLabel.($this->studentNote ? ' — '.$this->studentNote : ''),
            'url' => route('student.applications.show', $this->application, false),
        ];
    }
}
