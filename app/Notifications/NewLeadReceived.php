<?php

namespace App\Notifications;

use App\Models\ServiceEnquiry;
use App\Models\StudentEnquiry;
use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * Emails the team inbox (Settings › contact email) when a visitor sends a
 * consultation request, quote request or contact message from the website.
 */
class NewLeadReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StudentEnquiry|ServiceEnquiry $lead, public string $source)
    {
        $this->afterCommit();
    }

    /**
     * Sends the email to the team inbox, if one is configured.
     */
    public static function notifyTeam(StudentEnquiry|ServiceEnquiry $lead, string $source): void
    {
        try {
            $inbox = trim((string) app(SettingService::class)->get('contact', 'email'));
        } catch (Throwable) {
            $inbox = '';
        }
        if ($inbox === '' || filter_var($inbox, FILTER_VALIDATE_EMAIL) === false) {
            $inbox = (string) config('mail.from.address');
        }
        if (filter_var($inbox, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        NotificationFacade::route('mail', $inbox)->notify(new self($lead, $source));
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $topic = $lead instanceof ServiceEnquiry ? $lead->service->label() : 'Study in Singapore';
        $adminUrl = $lead instanceof ServiceEnquiry
            ? route('admin.service-enquiries.show', $lead)
            : route('admin.student-enquiries.show', $lead);

        $mail = (new MailMessage)
            ->subject('New website lead '.$lead->reference.': '.$topic)
            ->line('Source: '.$this->source)
            ->line('Name: '.$lead->full_name)
            ->line('Phone / WhatsApp: '.$lead->phone);
        if (filled($lead->email)) {
            $mail->line('Email: '.$lead->email);
        }
        if ($lead instanceof StudentEnquiry && filled($lead->study_level)) {
            $mail->line('Study level: '.$lead->study_level.(filled($lead->preferred_intake) ? ' · Intake: '.$lead->preferred_intake : ''));
        }
        if ($lead instanceof ServiceEnquiry && filled($lead->route_or_destination)) {
            $mail->line('Destination / topic: '.$lead->route_or_destination);
        }
        if (filled($lead->message)) {
            $mail->line('Message: '.$lead->message);
        }

        return $mail->action('Open in the admin panel', $adminUrl);
    }
}
