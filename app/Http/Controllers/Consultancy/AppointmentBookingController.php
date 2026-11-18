<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultancy\StoreStudentEnquiryRequest;
use App\Models\Appointment;
use App\Notifications\AppointmentBooked;
use App\Services\Consultancy\AppointmentScheduler;
use App\Services\Consultancy\BookingSlots;
use App\Services\Consultancy\StudentLeadRecorder;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Public consultation booking (/book-appointment). Shows the free times from
 * Admin › Appointments › Public booking hours; a booking is saved as a Student Enquiry
 * plus a confirmed, unassigned Appointment, and the visitor gets a confirmation email.
 */
class AppointmentBookingController extends Controller
{
    public function show(BookingSlots $slots): View
    {
        return view('consultancy.book-appointment', [
            'free' => $slots->free(),
            'settings' => $slots->settings(),
            'destinations' => rescue(fn () => StoreStudentEnquiryRequest::destinations(), collect(), false),
        ]);
    }

    public function store(Request $request, BookingSlots $slots, StudentLeadRecorder $leads, AppointmentScheduler $scheduler): RedirectResponse
    {
        $settings = $slots->settings();
        $data = $request->validate([
            'slot' => ['required', 'date_format:Y-m-d H:i'],
            'mode' => ['required', Rule::in($settings['modes'])],
            'destination' => ['nullable', 'string', 'size:2'],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'email' => ['required', 'email', 'max:254'],
            'note' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ], [
            'phone.regex' => __('Enter a valid phone number, for example +8801712345678.'),
            'consent.accepted' => __('Please agree to be contacted about your enquiry.'),
            'email.required' => __('Enter your email so we can send the confirmation.'),
            'slot.required' => __('Choose a day and time.'),
        ]);
        [$data['date'], $data['time']] = explode(' ', $data['slot']);

        // One booking per slot: the check and the save happen under a lock.
        [$appointment, $reference] = Cache::lock('booking-slot:'.$data['date'].' '.$data['time'], 10)->block(5, function () use ($data, $slots, $settings, $leads, $scheduler): array {
            if (! $slots->isFree($data['date'], $data['time'])) {
                throw ValidationException::withMessages(['slot' => __('Sorry, that time was just taken. Please choose another time.')]);
            }

            return DB::transaction(function () use ($data, $slots, $settings, $leads, $scheduler): array {
                $lead = $leads->record([
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'destination' => $data['destination'] ?? 'MY',
                    'message' => trim('Booked a free consultation online: '.$data['date'].' '.$data['time'].' ('.(Appointment::MODES[$data['mode']] ?? $data['mode']).').'."\n".($data['note'] ?? '')),
                ], 'Appointment booking');

                $appointment = $scheduler->create([
                    'student_enquiry_id' => $lead->getKey(),
                    'staff_user_id' => null,
                    'scheduled_at' => $slots->toStored($data['date'], $data['time']),
                    'duration_minutes' => (int) $settings['slot_minutes'],
                    'mode' => $data['mode'],
                    'location_or_link' => $data['mode'] === 'office' ? $this->officeAddress() : null,
                    'student_note' => $data['note'] ?? null,
                ], Appointment::STATUS_CONFIRMED, null);

                return [$appointment, $lead->reference];
            });
        });

        Notification::route('mail', $data['email'])->notify(
            (new AppointmentBooked($appointment->load('enquiry'), $reference, $this->place($data['mode'], $data['phone'])))->locale(app()->getLocale())
        );

        return redirect()->route('appointments.book')->with('booking', [
            'reference' => $reference,
            'when' => $appointment->scheduled_at->translatedFormat('l, j F Y, g:i A'),
            'email' => $data['email'],
        ]);
    }

    private function place(string $mode, string $phone): ?string
    {
        return match ($mode) {
            'office' => $this->officeAddress(),
            'phone' => __('We will call you on :phone.', ['phone' => $phone]),
            'video' => __('We will email you the video call link before the meeting.'),
            default => null,
        };
    }

    private function officeAddress(): ?string
    {
        $address = data_get(rescue(fn () => app(HomepageContentService::class)->siteChrome(), [], false), 'siteContact.address');

        return filled($address) ? (string) $address : null;
    }
}
