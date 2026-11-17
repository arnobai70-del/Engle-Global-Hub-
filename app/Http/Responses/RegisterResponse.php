<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

/**
 * After signing up, a visitor who chose "Agent / B2B partner" continues to the
 * partner application; everyone else goes to their dashboard.
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $user = $request->user();
        if ($user instanceof User && $user->account_type === 'agent') {
            return redirect()->route('b2b.register');
        }

        return redirect()->intended(Fortify::redirects('register'));
    }
}
