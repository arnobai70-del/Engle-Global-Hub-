<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultancy\StoreStudentEnquiryRequest;
use App\Models\Country;
use App\Models\StudentEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class StudentEnquiryController extends Controller
{
    public function store(StoreStudentEnquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $enquiry = StudentEnquiry::query()->create([
            'reference' => $this->newReference(),
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'destination_country_id' => Country::query()->where('iso2', $data['destination'])->value('id'),
            'highest_qualification' => $data['highest_qualification'] ?? null,
            'study_level' => $data['study_level'] ?? null,
            'preferred_intake' => $data['preferred_intake'] ?? null,
            'wants_consultation' => (bool) ($data['wants_consultation'] ?? false),
            'lead_source' => $data['lead_source'] ?? 'direct',
            'message' => $data['message'] ?? null,
            'consented_at' => now(),
        ]);

        return redirect()
            ->route('consultancy.singapore')
            ->with('enquiry_reference', $enquiry->reference);
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SE-'.Str::upper(Str::random(10));
        } while (StudentEnquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
