<?php

namespace App\Services\Consultancy;

use App\Models\Country;
use App\Models\StudentEnquiry;
use App\Notifications\NewLeadReceived;
use Illuminate\Support\Str;

/**
 * Saves a website study lead as a Student Enquiry (Admin › Student Enquiries) and emails
 * the team. Used by the consultation form, the contact page, the eligibility checker,
 * the course finder and appointment booking.
 */
class StudentLeadRecorder
{
    /**
     * @param  array{full_name: string, phone: string, email?: ?string, destination?: ?string, highest_qualification?: ?string, study_level?: ?string, preferred_intake?: ?string, message?: ?string}  $data
     */
    public function record(array $data, string $source): StudentEnquiry
    {
        $lead = StudentEnquiry::query()->create([
            'reference' => $this->newReference(),
            'full_name' => $data['full_name'],
            'email' => filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null,
            'phone' => $data['phone'],
            // Malaysia is the main destination; the Singapore page sends SG.
            'destination_country_id' => Country::query()->where('iso2', $data['destination'] ?? 'MY')->value('id'),
            'highest_qualification' => $data['highest_qualification'] ?? null,
            'study_level' => $data['study_level'] ?? null,
            'preferred_intake' => $data['preferred_intake'] ?? null,
            'wants_consultation' => true,
            'lead_source' => 'direct',
            'message' => $data['message'] ?? null,
            'consented_at' => now(),
        ]);
        NewLeadReceived::notifyTeam($lead, $source);

        return $lead;
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SE-'.Str::upper(Str::random(10));
        } while (StudentEnquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
