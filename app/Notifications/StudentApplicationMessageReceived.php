<?php

namespace App\Notifications;

use App\Models\StudentApplicationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the other party a new application message arrived. The message text
 * itself is only shown after signing in.
 */
class StudentApplicationMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StudentApplicationMessage $message)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->message->author_type === StudentApplicationMessage::AUTHOR_STAFF
            ? ['database', 'mail']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New message about application '.$this->message->application->reference)
            ->line('Eagle Global Hub has sent you a message about your study application.')
            ->action('Read the message', $this->url(absolute: true));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New message on '.$this->message->application->reference,
            'body' => $this->message->author_type === StudentApplicationMessage::AUTHOR_STAFF ? 'From your consultant' : 'From the student',
            'url' => $this->url(absolute: false),
        ];
    }

    private function url(bool $absolute): string
    {
        return $this->message->author_type === StudentApplicationMessage::AUTHOR_STAFF
            ? route('student.applications.show', $this->message->application, $absolute)
            : route('admin.student-applications.show', $this->message->application, $absolute);
    }
}
