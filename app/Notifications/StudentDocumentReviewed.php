<?php

namespace App\Notifications;

use App\Models\StudentDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student a document was verified or rejected. Never includes the
 * document itself or its contents.
 */
class StudentDocumentReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StudentDocument $document)
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
        $application = $this->document->application;
        $message = (new MailMessage)
            ->subject('Document review for application '.$application->reference)
            ->line('Your '.$this->document->document_type->label().' has been reviewed: '.$this->document->statusLabel().'.');

        if ($this->document->review_note) {
            $message->line('Reviewer note: '.$this->document->review_note);
        }

        return $message->action('View your documents', route('student.applications.show', $application));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->document->document_type->label().': '.$this->document->statusLabel(),
            'body' => $this->document->review_note ?? 'Application '.$this->document->application->reference,
            'url' => route('student.applications.show', $this->document->application, false),
        ];
    }
}
