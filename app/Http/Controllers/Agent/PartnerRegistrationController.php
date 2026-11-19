<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Egabe B2B partner sign-up. Uses the visitor's existing login; the partner
 * record stays pending until staff approve it in Admin › Agents.
 */
class PartnerRegistrationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user() === null) {
            return redirect()->to(route('b2b.info').'#apply');
        }
        if ($request->user()->hasVerifiedEmail() === false) {
            return redirect()->route('verification.notice');
        }

        return view('agent.register', [
            'existing' => Agent::query()->where('email', strtolower($request->user()->email))->first(),
        ]);
    }

    public function store(Request $request, ActivityRecorder $activity): RedirectResponse
    {
        $email = strtolower($request->user()->email);
        if (Agent::query()->where('email', $email)->exists()) {
            return redirect()->route('b2b.register')->withErrors(['company_name' => __('A partner or agent application already exists for your account.')]);
        }

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'registration_number' => ['nullable', 'string', 'max:100', Rule::unique('agents', 'registration_number')],
            'website_url' => ['nullable', 'url:https', 'max:2048'],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ]);

        $agent = Agent::query()->create([
            ...collect($data)->except(['consent', 'notes'])->all(),
            'email' => $email,
            'partner_type' => Agent::TYPE_B2B_PARTNER,
            'status' => Agent::STATUS_PENDING,
            'notes' => isset($data['notes']) ? 'Applicant notes: '.strip_tags($data['notes']) : null,
        ]);
        $activity->record($request->user(), 'b2b.partner_registered', $agent, ['company' => $agent->company_name]);

        return redirect()->route('b2b.register')->with('status', __('Thank you. Your partner application is waiting for approval.'));
    }
}
