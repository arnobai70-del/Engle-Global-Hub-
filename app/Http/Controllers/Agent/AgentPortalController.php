<?php

namespace App\Http\Controllers\Agent;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Consultancy\StoreStudentEnquiryRequest;
use App\Models\Country;
use App\Models\ServiceEnquiry;
use App\Models\StudentEnquiry;
use App\Services\ActivityRecorder;
use App\Services\AgentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Agent portal: refer students and follow their progress. Agents see only
 * their own referrals and only student-facing status, never internal notes
 * or documents.
 */
class AgentPortalController extends Controller
{
    public function __construct(private AgentPortal $portal) {}

    public function index(Request $request): View
    {
        $agent = $this->portal->agentOrFail($request->user());

        $referrals = StudentEnquiry::query()
            ->where('referred_by_agent_id', $agent->getKey())
            ->with(['destinationCountry:id,name', 'applications:id,student_enquiry_id,reference,status'])
            ->latest('id')
            ->paginate(20, ['id', 'reference', 'full_name', 'destination_country_id', 'status', 'created_at']);

        $serviceReferrals = ServiceEnquiry::query()->where('referred_by_agent_id', $agent->getKey())
            ->latest('id')->limit(50)->get(['id', 'reference', 'service', 'category', 'full_name', 'status', 'created_at']);

        return view('agent.index', compact('agent', 'referrals', 'serviceReferrals'));
    }

    public function create(Request $request): View
    {
        $this->portal->agentOrFail($request->user());

        return view('agent.create', [
            'destinations' => StoreStudentEnquiryRequest::destinations(),
            'studyLevels' => StudentEnquiry::STUDY_LEVELS,
        ]);
    }

    public function store(StoreStudentEnquiryRequest $request, ActivityRecorder $activity): RedirectResponse
    {
        $agent = $this->portal->agentOrFail($request->user());
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
            'lead_source' => 'referral',
            'message' => $data['message'] ?? null,
            'consented_at' => now(),
        ]);
        $enquiry->forceFill(['referred_by_agent_id' => $agent->getKey()])->save();
        $activity->record($request->user(), 'agent.referral_submitted', $enquiry, ['reference' => $enquiry->reference, 'agent_id' => $agent->getKey()]);

        return redirect()->route('agent.index')->with('status', __('Referral :reference submitted.', ['reference' => $enquiry->reference]));
    }

    public function createServiceReferral(Request $request): View
    {
        $this->portal->agentOrFail($request->user());

        return view('agent.service-referral', [
            'services' => array_map(fn (string $value): BusinessService => BusinessService::from($value), ServiceEnquiry::SERVICES),
        ]);
    }

    public function storeServiceReferral(Request $request, ActivityRecorder $activity): RedirectResponse
    {
        $agent = $this->portal->agentOrFail($request->user());
        $data = $request->validate([
            'service' => ['required', Rule::in(ServiceEnquiry::SERVICES)],
            'category' => ['nullable', 'string', Rule::in(array_keys(ServiceEnquiry::CATEGORIES[$request->input('service')] ?? []))],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'route_or_destination' => ['nullable', 'string', 'max:200'],
            'travellers' => ['required', 'integer', 'between:1,20'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        $enquiry = ServiceEnquiry::query()->create([
            ...collect($data)->except('consent')->all(),
            'email' => strtolower($data['email']),
            'service' => BusinessService::from($data['service']),
            'reference' => 'EGH-SQ-'.Str::upper(Str::random(10)),
            'consented_at' => now(),
        ]);
        $enquiry->forceFill(['referred_by_agent_id' => $agent->getKey()])->save();
        $activity->record($request->user(), 'agent.service_referral_submitted', $enquiry, ['reference' => $enquiry->reference, 'agent_id' => $agent->getKey()]);

        return redirect()->route('agent.index')->with('status', __('Referral :reference submitted.', ['reference' => $enquiry->reference]));
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SE-'.Str::upper(Str::random(10));
        } while (StudentEnquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
