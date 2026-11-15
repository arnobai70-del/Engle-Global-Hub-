<?php

namespace App\Notifications;

use App\Models\ServiceEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a customer their enquiry status changed, or tells staff an enquiry
 * was assigned to them. Internal notes are never included.
 */
class ServiceEnquiryUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ServiceEnquiry $enquiry, public bool $forStaff)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->forStaff ? ['database'] : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your enquiry '.$this->enquiry->reference)
            ->line('Your '.$this->enquiry->service->label().' enquiry '.$this->enquiry->reference.' is now: '.$this->enquiry->statusLabel().'.')
            ->action('View your enquiries', route('service-enquiries.mine'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->forStaff
            ? ['title' => 'Enquiry '.$this->enquiry->reference.' assigned to you', 'body' => $this->enquiry->service->label(), 'url' => route('admin.service-enquiries.show', $this->enquiry, false)]
            : ['title' => 'Enquiry '.$this->enquiry->reference.' updated', 'body' => 'Status: '.$this->enquiry->statusLabel(), 'url' => route('service-enquiries.mine', [], false)];
    }
}
