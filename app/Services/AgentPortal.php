<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\User;

/**
 * Resolves the agent profile behind a login. Access requires a verified
 * email that matches an agent record staff have marked active.
 */
class AgentPortal
{
    public function agentFor(User $user): ?Agent
    {
        if (! $user->hasVerifiedEmail()) {
            return null;
        }

        return Agent::query()
            ->where('email', strtolower($user->email))
            ->where('status', Agent::STATUS_ACTIVE)
            ->first();
    }

    public function agentOrFail(User $user): Agent
    {
        $agent = $this->agentFor($user);
        abort_if($agent === null, 403, 'This area is for active Eagle Global Hub agents.');

        return $agent;
    }
}
